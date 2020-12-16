<?php

namespace App\Lib;

use App\Lib\Helper;

/**
 * The script implements the HTTPS protocol, via the PHP cURL extension.
 *
 * The URLs to test are:
 * for testing: pilot-payflowpro.verisign.com
 * production: payflowpro.verisign.com
 *
 * The nice thing about this protocol is that if you *don't* get a
 * $response, you can simply re-submit the transaction *using the same
 * REQUEST_ID* until you *do* get a response -- every time PayPal gets
 * a transaction with the same REQUEST_ID, it will not process a new
 * transactions, but simply return the same results, with a DUPLICATE=1
 * parameter appended.
 *
 * API rebuild by Radu Manole,
 * radu@u-zine.com, March 2007
 *
 * Many thanks to Sieber Todd, tsieber@paypal.com
 */
class converge_curl
{

    var $submiturl;
    var $mid;
    var $user;
    var $pin;
    var $errors = '';
    var $ClientCertificationId = '13fda2433fc2123d8b191d2d011b7fdc'; // deprecated - use a random id
    var $currencies_allowed = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD');
    var $test_mode = 1; // 1 = true, 0 = production

    private function init() {

        $this->test_mode = 1;
        if (getenv('APP_ENV') == 'production') {
            $this->test_mode = 0;
        }

        if ($this->test_mode == 1) {
            echo 'here: test mode';
            //$this->submiturl = 'https://www.myvirtualmerchant.com/VirtualMerchantDemo/process.do';
            $this->submiturl = 'https://api.demo.convergepay.com/VirtualMerchantDemo/process.do';
            $this->mid = '500694';
            $this->user = 'webpage';
            $this->pin = 'X9M8V28VPQAFZUAJHWU8WZCO8YLGTE4KOYS9UROYLIP0R3GF4PK7PUV687OOHCGE';

            /*$this->mid = "686968";
            $this->user = "BLACK011";
            $this->pin = "1ES0561JBTIBDUNN6ERSZZQRP6HSZSMBMQJ59DBDZB3ZU4L7IUT6C4OQK9S4ZOVY";*/
        } else {
            echo 'here: no test mode';
            $this->submiturl = 'https://api.convergepay.com/VirtualMerchant/process.do';

            //self::$api_url = 'https://api.convergepay.com/VirtualMerchant/processxml.do';
            $this->mid = '782639';
            $this->user = 'webapi';
            $this->pin = 'DOBHSBZJUBJBPS10VIXYLLB7BO776QN0HU6IBZE6F8FMN6OY2WNCU7OPCIOY1MKC';
        }

        // check for CURL
        if (!function_exists('curl_init')) {
            $this->set_errors('Curl function not found.');
            return;
        }
    }

    // get token
    function get_token($card_number, $card_expire, $data_array) {

        $this->init();

        if ($this->validate_card_number($card_number) == false) {
            $this->set_errors('Card Number not valid');
            return;
        }
        if ($this->validate_card_expire($card_expire) == false) {
            $this->set_errors('Card Expiration Date not valid');
            return;
        }

        // build hash
        //$tempstr = $card_number . $amount . date('YmdGis') . "1";
        //$request_id = md5($tempstr);

        ### DEFAULT ###
        $data['ssl_transaction_type'] = 'ccgettoken';
        $data['ssl_merchant_id'] = $this->mid;
        $data['ssl_user_id'] = $this->user;
        $data['ssl_pin'] = $this->pin;
        //$data['ssl_show_form'] = 'false';
        //$data['ssl_result_format'] = 'ascii';
        //$data['ssl_test_mode'] = 'false';

        ### CARD INFO ###
        $data['ssl_card_number'] = $card_number;
        $data['ssl_exp_date'] = $card_expire;
        //$data['ssl_card_present'] = 'Y';
        //$data['ssl_cvv2cvc2'] = $data_array['cvv'];
        //$data['ssl_cvv2cvc2_indicator'] = 1;

        ### BILLING INFO ###
        //$data['ssl_first_name'] = $data_array['firstname'];
        //$data['ssl_last_name'] = $data_array['lastname'];
        $data['ssl_avs_address'] = $data_array['street'];
        //$data['ssl_city'] = $data_array['city'];
        //$data['ssl_state'] = $data_array['state'];
        $data['ssl_avs_zip'] = $data_array['zip'];
        $data['ssl_verify'] = 'Y';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $result = str_replace("\n", "&", $result);
        Helper::log(' ### GETTOKEN RESULT ### ' . $result);

        $converge = $this->get_curl_result($result); //result arrray
        Helper::log(' ### CONVERGE ### ' . var_export($converge, true));

        if (isset($converge['ssl_result']) && $converge['ssl_result'] == 0) {
            return $result;
        } else {
            if (isset($converge['errorMessage']) && isset($converge['errorName'])) {
                $this->set_errors($converge['errorMessage'] . ' [' . $converge['errorName'] . ']');
            } else if (isset($converge['ssl_result_message'])) {
                $this->set_errors($converge['ssl_result_message']);
            }

            return false;
        }
    }

    // sale
    function sale_transaction($card_number, $card_expire, $amount, $card_name, $currency = 'USD', $data_array = array())
    {

        if ($this->validate_card_number($card_number) == false) {
            $this->set_errors('Card Number not valid');
            return;
        }
        if ($this->validate_card_expire($card_expire) == false) {
            $this->set_errors('Card Expiration Date not valid');
            return;
        }
        if (!is_numeric($amount) || $amount <= 0) {
            $this->set_errors('Amount is not valid');
            return;
        }
        if (!in_array($currency, $this->currencies_allowed)) {
            $this->set_errors('Currency not allowed');
            return;
        }

        // build hash
        $tempstr = $card_number . $amount . date('YmdGis') . "1";
        $request_id = md5($tempstr);

        ### DEFAULT ###
        $data['ssl_transaction_type'] = 'ccsale';
        $data['ssl_merchant_id'] = $this->mid;
        $data['ssl_user_id'] = $this->user;
        $data['ssl_pin'] = $this->pin;
        $data['ssl_show_form'] = 'false';
        $data['ssl_result_format'] = 'ascii';
        $data['ssl_test_mode'] = 'false';

        ### CARD INFO ###
        $data['ssl_card_number'] = $card_number;
        $data['ssl_exp_date'] = $card_expire;
        $data['ssl_amount'] = $amount;
        $data['ssl_card_present'] = 'Y';
        $data['ssl_cvv2cvc2'] = $data_array['cvv'];
        $data['ssl_cvv2cvc2_indicator'] = 1;

        ### BILLING INFO ###
        $data['ssl_first_name'] = $data_array['firstname'];
        $data['ssl_last_name'] = $data_array['lastname'];
        $data['ssl_avs_address'] = $data_array['street'];
        $data['ssl_city'] = $data_array['city'];
        $data['ssl_state'] = $data_array['state'];
        $data['ssl_avs_zip'] = $data_array['zip'];

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $result = str_replace("\n", "&", $result);

        //log::warn(' ### CCSALES RESULT ### ', $result);
        Helper::log('### CCSALES RESULT ###' . $result);

        $converge = $this->get_curl_result($result); //result arrray

        Helper::log('### CONVERGE ###' . var_export($converge, true));
        //log::warn(' ### CONVERGE ### ', var_export($converge, true));

        if (isset($converge['ssl_result']) && $converge['ssl_result'] == 0) {
            return $result;
        } else {
            if (isset($converge['errorMessage']) && isset($converge['errorName'])) {
                $this->set_errors($converge['errorMessage'] . ' [' . $converge['errorName'] . ']');
            } else {
                $this->set_errors($converge['ssl_result_message']);
            }

            return false;
        }
    }

    // Authorization
    function authorization($card_number, $card_expire, $amount, $card_holder_name, $currency = 'USD', $data_array = array())
    {
        $this->init();

        if ($this->validate_card_number($card_number) == false) {
            $this->set_errors('Card Number not valid');
            return;
        }
        if ($this->validate_card_expire($card_expire) == false) {
            $this->set_errors('Card Expiration Date not valid');
            return;
        }
        if (!is_numeric($amount) || $amount <= 0) {
            $this->set_errors('Amount is not valid');
            return;
        }
        if (!in_array($currency, $this->currencies_allowed)) {
            $this->set_errors('Currency not allowed');
            return;
        }

        // build hash
        $tempstr = $card_number . $amount . date('YmdGis') . "1";
        $request_id = md5($tempstr);

        // body of the POST
        ### DEFAULT ###
        $data['ssl_transaction_type'] = 'ccavsonly';
        $data['ssl_merchant_id'] = $this->mid;
        $data['ssl_user_id'] = $this->user;
        $data['ssl_pin'] = $this->pin;
        $data['ssl_show_form'] = 'false';
        $data['ssl_result_format'] = 'ascii';
        $data['ssl_test_mode'] = 'false';

        ### CARD INFO ###
        $data['ssl_card_number'] = $card_number;
        $data['ssl_exp_date'] = $card_expire;
        $data['ssl_amount'] = $amount;
        $data['ssl_card_present'] = 'Y';
        $data['ssl_cvv2cvc2'] = $data_array['cvv'];
        $data['ssl_cvv2cvc2_indicator'] = 1;

        ### BILLING INFO ###
        $data['ssl_first_name'] = $data_array['firstname'];
        $data['ssl_last_name'] = $data_array['lastname'];
        $data['ssl_avs_address'] = $data_array['street'];
        $data['ssl_city'] = $data_array['city'];
        $data['ssl_state'] = $data_array['state'];
        $data['ssl_avs_zip'] = $data_array['zip'];

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST



        // $rawHeader = curl_exec($ch); // run the whole process
        // $info = curl_getinfo($ch); //grabbing details of curl connection
        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $result = str_replace("\n", "&", $result);

        $converge = $this->get_curl_result($result); //result arrray

        if (isset($converge['ssl_result']) && $converge['ssl_result'] == 0) {
            return $result;
        } else {
            if (isset($converge['errorMessage']) && isset($converge['errorName'])) {
                $this->set_errors($converge['errorMessage'] . ' [' . $converge['errorName'] . ']');
            } else {
                $this->set_errors(isset($converge['ssl_result_message']) ? $converge['ssl_result_message'] : 'unknowon error');
            }
            return false;
        }
    }

    // Credit Transaction
    function credit_transaction($origid)
    {

        if (strlen($origid) < 3) {
            $this->set_errors('OrigID not valid');
            return;
        }

        // build hash
        $tempstr = date('YmdGis') . "2";
        $request_id = md5($tempstr);

        // body
        ### DEFAULT ###
        $data['ssl_transaction_type'] = 'ccreturn';
        $data['ssl_merchant_id'] = $this->mid;
        $data['ssl_user_id'] = $this->user;
        $data['ssl_pin'] = $this->pin;
        $data['ssl_show_form'] = 'false';
        $data['ssl_result_format'] = 'ascii';
        $data['ssl_test_mode'] = 'false';

        ### CARD INFO ###
        $data['ssl_txn_id'] = $origid;

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $result = str_replace("\n", "&", $result);

        $converge = $this->get_curl_result($result); //result arrray

        if (isset($converge['ssl_result']) && $converge['ssl_result'] == 0) {
            return $result;
        } else {
            if (isset($converge['errorMessage']) && isset($converge['errorName'])) {
                $this->set_errors($converge['errorMessage'] . ' [' . $converge['errorName'] . ']');
            } else {
                $this->set_errors($converge['ssl_result_message']);
            }
            return false;
        }
    }

    // Void Transaction
    function void_transaction($origid)
    {

        if (strlen($origid) < 3) {
            $this->set_errors('OrigID not valid');
            return;
        }

        // build hash
        $tempstr = date('YmdGis') . "2";
        $request_id = md5($tempstr);

        // body
        ### DEFAULT ###
        $data['ssl_transaction_type'] = 'ccvoid';
        $data['ssl_merchant_id'] = $this->mid;
        $data['ssl_user_id'] = $this->user;
        $data['ssl_pin'] = $this->pin;
        $data['ssl_show_form'] = 'false';
        $data['ssl_result_format'] = 'ascii';
        $data['ssl_test_mode'] = 'false';

        ### CARD INFO ###
        $data['ssl_txn_id'] = $origid;

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $result = str_replace("\n", "&", $result);

        $converge = $this->get_curl_result($result); //result arrray

        if (isset($converge['ssl_result']) && $converge['ssl_result'] == 0) {
            return $result;
        } else {
            if (isset($converge['errorMessage']) && isset($converge['errorName'])) {
                $this->set_errors($converge['errorMessage'] . ' [' . $converge['errorName'] . ']');
            } else {
                $this->set_errors($converge['ssl_result_message']);
            }
            return false;
        }
    }

    function return_transaction($origid)
    {

        if (strlen($origid) < 3) {
            $this->set_errors('OrigID not valid');
            return;
        }

        // build hash
        $tempstr = date('YmdGis') . "2";
        $request_id = md5($tempstr);

        // body
        ### DEFAULT ###
        $data['ssl_transaction_type'] = 'ccreturn';
        $data['ssl_merchant_id'] = $this->mid;
        $data['ssl_user_id'] = $this->user;
        $data['ssl_pin'] = $this->pin;
        $data['ssl_show_form'] = 'false';
        $data['ssl_result_format'] = 'ascii';
        $data['ssl_test_mode'] = 'false';

        ### CARD INFO ###
        $data['ssl_txn_id'] = $origid;

        $headers = $this->get_curl_headers();
        $headers[] = "X-VPS-Request-ID: " . $request_id;

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->submiturl);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

        $result = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $result = str_replace("\n", "&", $result);

        $converge = $this->get_curl_result($result); //result arrray

        if (isset($converge['ssl_result']) && $converge['ssl_result'] == 0) {
            return $result;
        } else {
            if (isset($converge['errorMessage']) && isset($converge['errorName'])) {
                $this->set_errors($converge['errorMessage'] . ' [' . $converge['errorName'] . ']');
            } else {
                $this->set_errors($converge['ssl_result_message']);
            }
            return false;
        }
    }

    // Curl custom headers; adjust appropriately for your setup:
    function get_curl_headers()
    {
        $headers = array();

        $headers[] = "Content-Type: text/namevalue"; //or maybe text/xml
        $headers[] = "X-VPS-Timeout: 30";
        $headers[] = "X-VPS-VIT-OS-Name: Linux";  // Name of your OS
        $headers[] = "X-VPS-VIT-OS-Version: RHEL 4";  // OS Version
        $headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";  // What you are using
        $headers[] = "X-VPS-VIT-Client-Version: 0.01";  // For your info
        $headers[] = "X-VPS-VIT-Client-Architecture: x86";  // For your info
        $headers[] = "X-VPS-VIT-Client-Certification-Id: " . $this->ClientCertificationId . ""; // get this from payflowintegrator@paypal.com
        $headers[] = "X-VPS-VIT-Integration-Product: MyApplication";  // For your info, would populate with application name
        $headers[] = "X-VPS-VIT-Integration-Version: 0.01"; // Application version

        return $headers;
    }

    // parse result and return an array
    function get_curl_result($result)
    {
        if (empty($result)) return;

        $converge = array();
        $valArray = explode("&", $result);
        foreach ($valArray as $val) {
            $valArray2 = explode("=", $val);
            if (count($valArray2) > 1)
            $converge[$valArray2[0]] = $valArray2[1];
        }
        return $converge;
    }

    function validate_card_expire($mmyy)
    {
        if (!is_numeric($mmyy) || strlen($mmyy) != 4) {
            return false;
        }
        $mm = substr($mmyy, 0, 2);
        $yy = substr($mmyy, 2, 2);
        if ($mm < 1 || $mm > 12) {
            return false;
        }
        $year = date('Y');
        $yy = substr($year, 0, 2) . $yy; // eg 2007
        if (is_numeric($yy) && $yy >= $year && $yy <= ($year + 10)) {
        } else {
            return false;
        }
        if ($yy == $year && $mm < date('n')) {
            return false;
        }
        return true;
    }

    // luhn algorithm
    function validate_card_number($card_number)
    {
        $card_number = preg_replace('[^0-9]', '', $card_number);
        if ($card_number < 9) return false;
        $card_number = strrev($card_number);
        $total = 0;
        for ($i = 0; $i < strlen($card_number); $i++) {
            $current_number = substr($card_number, $i, 1);
            if ($i % 2 == 1) {
                $current_number *= 2;
            }
            if ($current_number > 9) {
                $first_number = $current_number % 10;
                $second_number = ($current_number - $first_number) / 10;
                $current_number = $first_number + $second_number;
            }
            $total += $current_number;
        }
        return ($total % 10 == 0);
    }

    function get_errors()
    {
        if ($this->errors != '') {
            return $this->errors;
        }
        return false;
    }

    function set_errors($string)
    {
        $this->errors = $string;
    }

    function get_version()
    {
        return '4.03';
    }
}

?>