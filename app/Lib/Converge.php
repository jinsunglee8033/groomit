<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/1/17
 * Time: 3:44 PM
 */

namespace App\Lib;

use App\Model\AppointmentList;
use App\Model\CCTrans;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class Converge
{
    //The PIN in demo was expired, so it'll not work if you execute it.
    private static $api_url = 'https://api.demo.convergepay.com/VirtualMerchantDemo/processxml.do';
    private static $ssl_merchant_id = '001349';
    private static $ssl_user_id = 'webpage';
    private static $ssl_pin = 'W48UPQ';

    private static $skip_api_call_on_demo = true;
//    private static $api_url = 'https://api.convergepay.com/VirtualMerchant/processxml.do';
//    private static $ssl_merchant_id = '782639';
//    private static $ssl_user_id = 'webapi';
//    private static $ssl_pin = 'DOBHSBZJUBJBPS10VIXYLLB7BO776QN0HU6IBZE6F8FMN6OY2WNCU7OPCIOY1MKC';


    public static function init() {
        if (getenv('APP_ENV') == 'production') {
            self::$api_url = 'https://api.convergepay.com/VirtualMerchant/processxml.do';
            self::$ssl_merchant_id = '782639';
            self::$ssl_user_id = 'webapi';
            self::$ssl_pin = 'DOBHSBZJUBJBPS10VIXYLLB7BO776QN0HU6IBZE6F8FMN6OY2WNCU7OPCIOY1MKC';
        } /*else {
            self::$api_url = 'https://api.convergepay.com/VirtualMerchant/processxml.do';
            self::$ssl_merchant_id = '782639';
            self::$ssl_user_id = 'webapi';
            self::$ssl_pin = 'DOBHSBZJUBJBPS10VIXYLLB7BO776QN0HU6IBZE6F8FMN6OY2WNCU7OPCIOY1MKC';
        }*/
    }

    public static function complete($void_ref, $token, $amt, $ref_id, $category = 'S',  $type = 'S', $is_voided = false) {
        try {
            self::init();

            $tx2 = new CCTrans;
            $tx2->appointment_id = $ref_id;
            $tx2->type = 'S'; //Always 'Sales' type in case of cccomplete. instead, void origin 'A' tx, by setting void_date.
            $tx2->category = $category;
            $tx2->token = $token;
            $tx2->amt = $amt;
            $tx2->auth_only_void_ref = $void_ref;
            $tx2->cdate = Carbon::now();
            $tx2->error_name = 'cccomplete'; //kinds of comments for cccomplete.
            $tx2->save();

            if (getenv('APP_ENV') != 'production' && self::$skip_api_call_on_demo) {
                $ret_void_ref = 'D' . rand ( 10000000 , 99999999 );
                $ret_approval_code = 'D' . rand ( 100000 , 999999 );

                $tx2->result = 0;
                $tx2->result_msg = '';
                $tx2->result_date = Carbon::now();
                $tx2->void_ref = $ret_void_ref;
                $tx2->approval_code = $ret_approval_code;
                if ($is_voided) {
                    $tx2->void_date = Carbon::now();
                }
                $tx2->save();

                $orig_auth_only_trans = CCTrans::where('appointment_id', $ref_id )
                    ->where('type', 'A')
                    ->where('void_ref', $void_ref )
                    ->where('result', 0)
                    ->whereNull('void_date')
                    //->where('amt', '!=', 0.01) : Needs to allow it to void $0.01 auth too.
                    ->first();
                $orig_auth_only_trans->void_date = Carbon::now();
                $orig_auth_only_trans->save();

                return [
                    'error_code' => '',
                    'error_msg' => '',
                    'void_ref' => $ret_void_ref,
                    'new_cctrans_id' => $tx2->id
                ];
            }

            $xml = "<txn>";
            $xml .= "<ssl_merchant_id>" . self::$ssl_merchant_id . "</ssl_merchant_id>";
            $xml .= "<ssl_user_id>" . self::$ssl_user_id . "</ssl_user_id>";
            $xml .= "<ssl_pin>" . self::$ssl_pin . "</ssl_pin>";
            $xml .= "<ssl_transaction_type>cccomplete</ssl_transaction_type>";
            $xml .= "<ssl_txn_id>" . $void_ref . "</ssl_txn_id>";
            //$xml .= "<ssl_amount>" . $amt . "</ssl_amount>"; //When complete on partial amount out of full auth_oknly amount. 2 decimals
            $xml .= "</txn>";

            $data = [
                'xmldata' => $xml
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // times out after 45 secs

            $result = trim(curl_exec($ch));
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            Helper::log('### CONVERGE REQUEST ###', $xml);
            Helper::log('### CONVERGE RESONSE ###', $result);

            if ($result === false) {
                $tx2->result = -1;
                $tx2->result_date = Carbon::now();
                $tx2->result_msg = 'No response from Converge';
                $tx2->save();

                return [
                    'error_code' => -1,
                    'error_msg' => $error
                ];
            } else{
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
            }

            if (!isset($result['ssl_result']) || $result['ssl_result'] != 0) {

                if (isset($result['errorMessage']) && isset($result['errorName']) && isset($result['errorCode'])) {

                    $tx2->result = isset($result['ssl_result']) ? $result['ssl_result'] : -2;
                    $tx2->error_code = $result['errorCode'];
                    $tx2->error_msg = $result['errorMessage'];
                    $tx2->error_name = $result['errorName'];
                    $tx2->result_msg = isset($result['ssl_result_message']) ? $result['ssl_result_message'] : '';
                    $tx2->result_date = Carbon::now();
                    $tx2->save();

                    return [
                        'error_code' => $result['errorCode'],
                        'error_msg' => $result['errorMessage'] . '[' . $result['errorName'] . ']'
                    ];
                } else if (isset($result['ssl_result_message'])) {
                    $tx2->result = isset($result['ssl_result']) ? $result['ssl_result'] : -2;
                    $tx2->result_msg = isset($result['ssl_result_message']) ? $result['ssl_result_message'] : '';
                    $tx2->result_date = Carbon::now();
                    $tx2->save();

                    return [
                        'error_code' => -2,
                        'error_msg' => $result['ssl_result_message']
                    ];
                }

                $tx2->result = -3;
                $tx2->result_msg = 'Unknown Error';
                $tx2->result_date = Carbon::now();
                $tx2->save();

                return [
                    'error_code' => -3,
                    'error_msg' => 'Unknown Error'
                ];
            }

            if (!isset($result['ssl_txn_id']) || empty($result['ssl_txn_id'])) {
                $tx2->result = -4;
                $tx2->result_msg = 'Empty transaction ID returned from credit card company';
                $tx2->result_date = Carbon::now();
                $tx2->save();
                return [
                    'error_code' => -4,
                    'error_msg' => 'Empty transaction ID returned from credit card company'
                ];
            }

            $tx2->result = 0;
            $tx2->result_msg = '';
            $tx2->result_date = Carbon::now();
            $tx2->void_ref = $result['ssl_txn_id'];
            $tx2->approval_code = empty($result['ssl_approval_code']) ? '' : $result['ssl_approval_code'];
            if ($is_voided) {
                $tx2->void_date = Carbon::now();
            }
            $tx2->save();


            $orig_auth_only_trans = CCTrans::where('appointment_id', $ref_id )
                ->where('type', 'A')
                ->where('void_ref', $void_ref )
                ->where('result', 0)
                ->whereNull('void_date')
                //->where('amt', '!=', 0.01)
                ->first();
            $orig_auth_only_trans->void_date = Carbon::now();
            $orig_auth_only_trans->save();

            return [
                'error_code' => '',
                'error_msg' => '',
                'void_ref' => $result['ssl_txn_id'],
                'new_cctrans_id' => $tx2->id
            ];

        } catch (\Exception $ex) {

            Helper::log('### auth complete failed ###', [
                'trace' => $ex->getTrace()
            ]);

            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString()
            ];
        }
    }

    public static function auth_only($token, $amt, $ref_id, $category = 'S') {
        try {
            self::init();

            ### check if there is 'S' type record ###

            $completed_trans = CCTrans::where('appointment_id', $ref_id)
                ->where('type', 'S')
                ->where('category', $category)
                ->where('result', '0')
                ->whereNull('void_date')
                //->where('amt', '!=', 0.01)
                ->orderBy('id','desc')
                ->first();

            //Check if the amount is the same with the total of the appt, so prevent duplication.
            if (!empty($completed_trans) && $completed_trans->amt == $amt ) {
                $appt = AppointmentList::find( $ref_id);
                if ( $appt->total == $amt) {
                    return [
                        'error_code' => '',
                        'error_msg' => '',
                        'void_ref' => $completed_trans->void_ref
                    ];
                }
            }

            $tx = new CCTrans;
            $tx->appointment_id = $ref_id;
            $tx->type = 'A';
            $tx->category = $category;
            $tx->token = $token;
            $tx->amt = $amt;
            $tx->cdate = Carbon::now();
            $tx->save();

            if (getenv('APP_ENV') != 'production' && self::$skip_api_call_on_demo) {
                $ret_void_ref = 'D' . rand ( 10000000 , 99999999 );
                $ret_approval_code = 'D' . rand ( 100000 , 999999 );

                $tx->result = 0;
                $tx->result_msg = '';
                $tx->result_date = Carbon::now();
                $tx->void_ref = $ret_void_ref ;
                $tx->approval_code = $ret_approval_code ;
                $tx->save();

                return [
                    'error_code' => '',
                    'error_msg' => '',
                    'void_ref' =>  $ret_void_ref
                ];
            }

            $xml = "<txn>";
            $xml .= "<ssl_merchant_id>" . self::$ssl_merchant_id . "</ssl_merchant_id>";
            $xml .= "<ssl_user_id>" . self::$ssl_user_id . "</ssl_user_id>";
            $xml .= "<ssl_pin>" . self::$ssl_pin . "</ssl_pin>";
            $xml .= "<ssl_transaction_type>ccauthonly</ssl_transaction_type>";
            $xml .= "<ssl_token>" . $token . "</ssl_token>";
            $xml .= "<ssl_amount>" . $amt . "</ssl_amount>";
            //$xml .= "<ssl_transaction_currency>USD</ssl_transaction_currency>";
            $xml .= "<ssl_customer_code>" . $ref_id . $category . "</ssl_customer_code>"; // tag : S or T ( Sales or Tip )
            $xml .= "<ssl_invoice_number>" . $ref_id . $category . "</ssl_invoice_number>";

            $xml .= "</txn>";

            $data = [
                'xmldata' => $xml
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // times out after 45 secs

            $result = trim(curl_exec($ch));
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            Helper::log('### CONVERGE REQUEST ###', $xml);
            Helper::log('### CONVERGE RESONSE ###', $result);

            if ($result === false) {
                $tx->result = -1;
                $tx->result_date = Carbon::now();
                $tx->result_msg = 'No response from Converge';
                $tx->save();

                return [
                    'error_code' => -1,
                    'error_msg' => $error
                ];
            } else{
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
            }

            if (!isset($result['ssl_result']) || $result['ssl_result'] != 0) {

                if (isset($result['errorMessage']) && isset($result['errorName']) && isset($result['errorCode'])) {

                    $tx->result = isset($result['ssl_result']) ? $result['ssl_result'] : -2;
                    $tx->error_code = $result['errorCode'];
                    $tx->error_msg = $result['errorMessage'];
                    $tx->error_name = $result['errorName'];
                    $tx->result_msg = isset($result['ssl_result_message']) ? $result['ssl_result_message'] : '';
                    $tx->result_date = Carbon::now();
                    $tx->save();

                    return [
                        'error_code' => $result['errorCode'],
                        'error_msg' => $result['errorMessage'] . '[' . $result['errorName'] . ']'
                    ];
                } else if (isset($result['ssl_result_message'])) {
                    $tx->result = isset($result['ssl_result']) ? $result['ssl_result'] : -2;
                    $tx->result_msg = isset($result['ssl_result_message']) ? $result['ssl_result_message'] : '';
                    $tx->result_date = Carbon::now();
                    $tx->save();

                    return [
                        'error_code' => -2,
                        'error_msg' => $result['ssl_result_message']
                    ];
                }

                $tx->result = -3;
                $tx->result_msg = 'Unknown Error';
                $tx->result_date = Carbon::now();
                $tx->save();

                return [
                    'error_code' => -3,
                    'error_msg' => 'Unknown Error'
                ];
            }

            if (!isset($result['ssl_txn_id']) || empty($result['ssl_txn_id'])) {
                $tx->result = -4;
                $tx->result_msg = 'Empty transaction ID returned from credit card company';
                $tx->result_date = Carbon::now();
                $tx->save();
                return [
                    'error_code' => -4,
                    'error_msg' => 'Empty transaction ID returned from credit card company'
                ];
            }

            $tx->result = 0;
            $tx->result_msg = '';
            $tx->result_date = Carbon::now();
            $tx->void_ref = $result['ssl_txn_id'];
            $tx->approval_code = $result['ssl_approval_code'];
            $tx->save();

            return [
                'error_code' => '',
                'error_msg' => '',
                'void_ref' => $result['ssl_txn_id']
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    public static function sales($token, $amt, $ref_id, $category = 'S') {
        try {

            $sales_trans = CCTrans::where('appointment_id', $ref_id)
                ->where('type', 'S')
                ->where('category', $category)
                ->where('result', 0)
                ->whereNull('void_date')
                ->orderBy('id', 'desc')
                ->first();

            if (!empty($sales_trans) && $sales_trans->amt == $amt) { //If the same amount is already charged with the same appt_id, return a success.
                //Check if the amount is the same with the total of the appt, so prevent duplication.
                $appt = AppointmentList::find( $ref_id);
                if ( $appt->total == $amt) {
                    return [
                        'error_code' => '',
                        'error_msg' => '',
                        'void_ref' => $sales_trans->void_ref
                    ];
                }
            }

            self::init();

            $tx = new CCTrans;
            $tx->appointment_id = $ref_id;
            $tx->type = 'S';
            $tx->category = $category;
            $tx->token = $token;
            $tx->amt = $amt;
            $tx->cdate = Carbon::now();
            $tx->save();

            if (getenv('APP_ENV') != 'production' && self::$skip_api_call_on_demo) {
                $ret_void_ref = 'D' . rand ( 10000000 , 99999999 );
                $ret_approval_code = 'D' . rand ( 100000 , 999999 );

                $tx->result = 0;
                $tx->result_msg = '';
                $tx->result_date = Carbon::now();
                $tx->void_ref = $ret_void_ref;
                $tx->approval_code = $ret_approval_code;
                $tx->save();

                return [
                    'error_code' => '',
                    'error_msg' => '',
                    'void_ref' => $ret_void_ref
                ];
            }

            $xml = "<txn>";
            $xml .= "<ssl_merchant_id>" . self::$ssl_merchant_id . "</ssl_merchant_id>";
            $xml .= "<ssl_user_id>" . self::$ssl_user_id . "</ssl_user_id>";
            $xml .= "<ssl_pin>" . self::$ssl_pin . "</ssl_pin>";
            $xml .= "<ssl_transaction_type>ccsale</ssl_transaction_type>";
            $xml .= "<ssl_token>" . $token . "</ssl_token>";
            $xml .= "<ssl_amount>" . $amt . "</ssl_amount>";
            //$xml .= "<ssl_transaction_currency>USD</ssl_transaction_currency>";
            $xml .= "<ssl_customer_code>" . $ref_id . $category . "</ssl_customer_code>"; // tag : S or T ( Sales or Tip )
            $xml .= "<ssl_invoice_number>" . $ref_id . $category . "</ssl_invoice_number>";

            $xml .= "</txn>";

            $data = [
                'xmldata' => $xml
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // times out after 45 secs

            $result = trim(curl_exec($ch));
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            Helper::log('### CONVERGE REQUEST ###', $xml);
            Helper::log('### CONVERGE RESONSE ###', $result);

            if ($result === false) {
                $tx->result = -1;
                $tx->result_date = Carbon::now();
                $tx->result_msg = 'No response from Converge';
                $tx->save();

                return [
                    'error_code' => -1,
                    'error_msg' => $error
                ];
            } else{
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
            }

            if (!isset($result['ssl_result']) || $result['ssl_result'] != 0) {

                if (isset($result['errorMessage']) && isset($result['errorName']) && isset($result['errorCode'])) {

                    $tx->result = isset($result['ssl_result']) ? $result['ssl_result'] : -2;
                    $tx->error_code = $result['errorCode'];
                    $tx->error_msg = $result['errorMessage'];
                    $tx->error_name = $result['errorName'];
                    $tx->result_msg = isset($result['ssl_result_message']) ? $result['ssl_result_message'] : '';
                    $tx->result_date = Carbon::now();
                    $tx->save();

                    return [
                        'error_code' => $result['errorCode'],
                        'error_msg' => $result['errorMessage'] . '[' . $result['errorName'] . ']'
                    ];
                } else if (isset($result['ssl_result_message'])) {
                    $tx->result = isset($result['ssl_result']) ? $result['ssl_result'] : -2;
                    $tx->result_msg = isset($result['ssl_result_message']) ? $result['ssl_result_message'] : '';
                    $tx->result_date = Carbon::now();
                    $tx->save();

                    return [
                        'error_code' => -2,
                        'error_msg' => $result['ssl_result_message']
                    ];
                }

                $tx->result = -3;
                $tx->result_msg = 'Unknown Error';
                $tx->result_date = Carbon::now();
                $tx->save();

                return [
                    'error_code' => -3,
                    'error_msg' => 'Unknown Error'
                ];
            }

            if (!isset($result['ssl_txn_id']) || empty($result['ssl_txn_id'])) {
                $tx->result = -4;
                $tx->result_msg = 'Empty transaction ID returned from credit card company';
                $tx->result_date = Carbon::now();
                $tx->save();
                return [
                    'error_code' => -4,
                    'error_msg' => 'Empty transaction ID returned from credit card company'
                ];
            }

            $tx->result = 0;
            $tx->result_msg = '';
            $tx->result_date = Carbon::now();
            $tx->void_ref = $result['ssl_txn_id'];
            $tx->approval_code = $result['ssl_approval_code'];
            $tx->save();

            return [
                'error_code' => '',
                'error_msg' => '',
                'void_ref' => $result['ssl_txn_id']
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    public static function voucher_sales($token, $amt, $ref_id, $category = 'S') {
        try {
            self::init();

            $tx = new CCTrans;
            $tx->ref_type = 'G';
            $tx->ref = $ref_id;
            $tx->type = 'S';
            $tx->category = $category;
            $tx->token = $token;
            $tx->amt = $amt;
            $tx->cdate = Carbon::now();
            $tx->save();

            if (getenv('APP_ENV') != 'production' && self::$skip_api_call_on_demo) {
                $ret_void_ref = 'D' . rand ( 10000000 , 99999999 );
                $ret_approval_code = 'D' . rand ( 100000 , 999999 );

                $tx->result = 0;
                $tx->result_msg = '';
                $tx->result_date = Carbon::now();
                $tx->void_ref = $ret_void_ref ;
                $tx->approval_code = $ret_approval_code ;
                $tx->save();

                return [
                    'error_code' => '',
                    'error_msg' => '',
                    'void_ref' => $ret_void_ref
                ];
            }

            $xml = "<txn>";
            $xml .= "<ssl_merchant_id>" . self::$ssl_merchant_id . "</ssl_merchant_id>";
            $xml .= "<ssl_user_id>" . self::$ssl_user_id . "</ssl_user_id>";
            $xml .= "<ssl_pin>" . self::$ssl_pin . "</ssl_pin>";
            $xml .= "<ssl_transaction_type>ccsale</ssl_transaction_type>";
            $xml .= "<ssl_token>" . $token . "</ssl_token>";
            $xml .= "<ssl_amount>" . $amt . "</ssl_amount>";
            //$xml .= "<ssl_transaction_currency>USD</ssl_transaction_currency>";
            $xml .= "<ssl_customer_code>VOUCHER-" . $ref_id . $category . "</ssl_customer_code>"; // tag : S or T ( Sales or Tip )
            $xml .= "<ssl_invoice_number>VOUCHER-" . $ref_id . $category . "</ssl_invoice_number>";

            $xml .= "</txn>";

            $data = [
                'xmldata' => $xml
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // times out after 45 secs

            $result = trim(curl_exec($ch));
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            Helper::log('### CONVERGE REQUEST ###', $xml);
            Helper::log('### CONVERGE RESONSE ###', $result);

            if ($result === false) {
                $tx->result = -1;
                $tx->result_date = Carbon::now();
                $tx->result_msg = 'No response from Converge';
                $tx->save();

                return [
                    'error_code' => -1,
                    'error_msg' => $error
                ];
            } else{
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
            }

            if (!isset($result['ssl_result']) || $result['ssl_result'] != 0) {

                if (isset($result['errorMessage']) && isset($result['errorName']) && isset($result['errorCode'])) {

                    $tx->result = isset($result['ssl_result']) ? $result['ssl_result'] : -2;
                    $tx->error_code = $result['errorCode'];
                    $tx->error_msg = $result['errorMessage'];
                    $tx->error_name = $result['errorName'];
                    $tx->result_msg = isset($result['ssl_result_message']) ? $result['ssl_result_message'] : '';
                    $tx->result_date = Carbon::now();
                    $tx->save();

                    return [
                        'error_code' => $result['errorCode'],
                        'error_msg' => $result['errorMessage'] . '[' . $result['errorName'] . ']'
                    ];
                } else if (isset($result['ssl_result_message'])) {
                    $tx->result = isset($result['ssl_result']) ? $result['ssl_result'] : -2;
                    $tx->result_msg = isset($result['ssl_result_message']) ? $result['ssl_result_message'] : '';
                    $tx->result_date = Carbon::now();
                    $tx->save();

                    return [
                        'error_code' => -2,
                        'error_msg' => $result['ssl_result_message']
                    ];
                }

                $tx->result = -3;
                $tx->result_msg = 'Unknown Error';
                $tx->result_date = Carbon::now();
                $tx->save();

                return [
                    'error_code' => -3,
                    'error_msg' => 'Unknown Error'
                ];
            }

            if (!isset($result['ssl_txn_id']) || empty($result['ssl_txn_id'])) {
                $tx->result = -4;
                $tx->result_msg = 'Empty transaction ID returned from credit card company';
                $tx->result_date = Carbon::now();
                $tx->save();
                return [
                    'error_code' => -4,
                    'error_msg' => 'Empty transaction ID returned from credit card company'
                ];
            }

            $tx->result = 0;
            $tx->result_msg = '';
            $tx->result_date = Carbon::now();
            $tx->void_ref = $result['ssl_txn_id'];
            $tx->approval_code = $result['ssl_approval_code'];
            $tx->save();

            return [
                'error_code' => '',
                'error_msg' => '',
                'void_ref' => $result['ssl_txn_id']
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    public static function void($appointment_id, $category, $void_ref, $type = 'S', $amt=0) { //$amt exist only when partial refunds after completion.
        try {
            self::init();

            $orig_amt = $amt ;
            $orig_void_ref = $void_ref  ;
            Helper::log('### Converge::void.amt###', $orig_amt );

            $tx = CCTrans::where('appointment_id', $appointment_id)
                ->where('category', $category)
                ->where('result', 0)
                ->where('void_ref', $void_ref)
                ->where('type', $type)
                ->whereNull('void_date')
                ->orderBy('amt', 'desc')
                ->first();

            if (empty($tx)) {
                return [
                    'error_code' => -999,
                    'error_msg' => 'Unable to find credit card transaction to void'
                ];
            }

            $new_cctrans_id = $appointment_id;

            if (getenv('APP_ENV') != 'production' && self::$skip_api_call_on_demo) {
                $result_date = $tx->result_date;
                if ($type == 'A') {
                    if ($orig_amt == 0) { //In case of full void, void the cccomplete too.
                        $ret = self::complete($void_ref, $tx->token, $tx->amt, $tx->appointment_id, $category, $type, true);
                    }else {
                        $ret = self::complete($void_ref, $tx->token, $tx->amt, $tx->appointment_id, $category, $type);
                    }
                    if (!empty($ret['error_msg'])) {
                        return $ret;
                    }

                    $void_ref = $ret['void_ref'];
                    $result_date = Carbon::now();
                    $new_cctrans_id = $ret['new_cctrans_id'];
                }

                if($orig_amt == 0) { //Set void_date only when full refunds only.
                    $tx->void_date = Carbon::now();
                    $tx->save();
                }

                $new_void_ref = 'D' . rand ( 10000000 , 99999999 );
                $new_approval_code = 'D' . rand ( 100000 , 999999 );

                ### log void cc_trans ###
                if($amt == 0){ //Full void
                    $ret = DB::insert("insert into cc_trans ( appointment_id, type, category, token, amt, cdate, result, result_msg, result_date, void_ref, approval_code,orig_sales_id ) 
                    select appointment_id, 'V' as type, category, token, amt,  :cdate as cdate,
                        0 as result, '' as result_msg, :result_date as result_date,  :void_ref as void_ref,  :approval_code as approval_code, :new_cctrans_id
                    from cc_trans
                    where id = :id ", [
                        'void_ref' => $new_void_ref,
                        'id' => $tx->id,
                        'approval_code' => $new_approval_code,
                        'cdate' => Carbon::now(),
                        'result_date' => Carbon::now(),
                        'new_cctrans_id' => $new_cctrans_id
                    ]);
                }else { //Partial void
                    $ret = DB::insert("insert into cc_trans ( appointment_id, type, category, token, amt, cdate, result, result_msg, result_date, void_ref, approval_code, error_name , orig_sales_id ) 
                    select appointment_id, 'V' as type, category, token, :amt as amt ,  :cdate as cdate,
                        0 as result, '' as result_msg, :result_date as result_date,  :void_ref as void_ref,  :approval_code as approval_code, 'Partial Void' as error_name, :new_cctrans_id
                    from cc_trans
                    where id = :id ", [
                        'void_ref' => $new_void_ref,
                        'id' => $tx->id,
                        'amt' => $amt,
                        'approval_code' => $new_approval_code,
                        'cdate' => Carbon::now(),
                        'result_date' => Carbon::now(),
                        'new_cctrans_id' => $new_cctrans_id
                    ]);
                }


                return [
                    'error_code' => '',
                    'error_msg' => ''
                ];
            }

            ### complte auth_only first ###
            $result_date = $tx->result_date;
            if ($type == 'A') {
                if ($orig_amt == 0) { //In case of full void, void the cccomplete too.
                    $ret = self::complete($void_ref, $tx->token, $tx->amt, $tx->appointment_id, $category, $type, true );
                }else {
                    $ret = self::complete($void_ref, $tx->token, $tx->amt, $tx->appointment_id, $category, $type);
                }
                if (!empty($ret['error_msg'])) {
                    return $ret;
                }

                $void_ref = $ret['void_ref'];
                $result_date = Carbon::now();
                $new_cctrans_id = $ret['new_cctrans_id'];
            }

            if($type == 'S') {
                if($orig_amt == 0) { //in case of full refund/void
                    $trans_type = $result_date < Carbon::today() ? 'ccreturn' : 'ccvoid';
                }else { //in case of partial refund/void, always use ccreturn, becasue ccvoid does not allow partial refund/void.
                    $trans_type =  'ccreturn';
                }

            }else {
                //In case of void of A, it's completed just before, so could be voided.
                if($orig_amt == 0) { //in case of full refund/void
                    $trans_type = 'ccvoid'; //This is possible because it's completed just before, so could be voided.
                }else { //in case of partial refund/void, always use ccreturn, becasue ccvoid does not allow partial refund/void.
                    $trans_type =  'ccreturn';
                }
            }



            $xml = "<txn>";
            $xml .= "<ssl_merchant_id>" . self::$ssl_merchant_id . "</ssl_merchant_id>";
            $xml .= "<ssl_user_id>" . self::$ssl_user_id . "</ssl_user_id>";
            $xml .= "<ssl_pin>" . self::$ssl_pin . "</ssl_pin>";
            $xml .= "<ssl_transaction_type>" . $trans_type . "</ssl_transaction_type>";
            $xml .= "<ssl_txn_id>" . $void_ref . "</ssl_txn_id>";
            if($amt > 0 ){
                $xml .= "<ssl_amount>" . $amt . "</ssl_amount>"; //for partial refund w/ ccreturn. not for ccvoid. in case of ccvoid it's for ccsale/cccredit/ccforce only
            }
            $xml .= "</txn>";

            $data = [
                'xmldata' => $xml
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // times out after 45 secs

            $result = trim(curl_exec($ch));
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            Helper::log('### CONVERGE REQUEST ###', $xml);
            Helper::log('### CONVERGE RESONSE ###', $result);

            if ($result === false) {
                return [
                    'error_code' => -1,
                    'error_msg' => $error,
                    'info' => $info,
                    'xml' => $xml
                ];
            } else{
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
            }

            if (!isset($result['ssl_result']) || $result['ssl_result'] != 0) {

                if (isset($result['errorMessage']) && isset($result['errorName']) && isset($result['errorCode'])) {
                    return [
                        'error_code' => $result['errorCode'],
                        'error_msg' => $result['errorMessage'] . '[' . $result['errorName'] . ']'
                    ];
                } else if (isset($result['ssl_result_message'])) {
                    return [
                        'error_code' => -2,
                        'error_msg' => $result['ssl_result_message']
                    ];
                }

                return [
                    'error_code' => -3,
                    'error_msg' => 'Unknown Error'
                ];
            }

            if( $orig_amt == 0 ) { // Set void_date only when full voids, not partial voids.
                $tx->void_date = Carbon::now();
                $tx->save();
            }


            $new_void_ref = isset($result['ssl_txn_id']) ? $result['ssl_txn_id'] : '';
            $new_approval_code = '';//isset($result['ssl_approval_code']) ? $result['ssl_approval_code'] : '';

            ### log void cc_trans ###
            if($amt == 0) { //Full void
                $ret = DB::insert(" insert into cc_trans ( appointment_id, type, category, token, amt, cdate, result, result_msg, result_date, void_ref, approval_code , orig_sales_id ) 
                           select appointment_id, 'V' as type, category, token, amt ,   :cdate as cdate,  0 as result, '' as result_msg,  :result_date as result_date,  :void_ref as void_ref, :approval_code as approval_code, :new_cctrans_id 
                           from cc_trans
                           where id = :id ", [
                    'void_ref' => $new_void_ref,
                    'id' => $tx->id,
                    'approval_code' => $new_approval_code,
                    'cdate' => Carbon::now(),
                    'result_date' => Carbon::now(),
                    'new_cctrans_id' => $new_cctrans_id
                ]);
            }else { //Partial voids
                $ret = DB::insert(" insert into cc_trans ( appointment_id, type, category, token, amt, cdate, result, result_msg, result_date, void_ref, approval_code, error_name, orig_sales_id ) 
                           select appointment_id, 'V' as type, category, token, :amt as amt ,   :cdate as cdate,  0 as result, '' as result_msg,  :result_date as result_date,  :void_ref as void_ref, :approval_code as approval_code, 'Partial Void' as error_name, :new_cctrans_id 
                           from cc_trans
                           where id = :id ", [
                    'void_ref' => $new_void_ref,
                    'id' => $tx->id,
                    'amt' => $amt,
                    'approval_code' => $new_approval_code,
                    'cdate' => Carbon::now(),
                    'result_date' => Carbon::now(),
                    'new_cctrans_id' => $new_cctrans_id
                ]);
            }
            if ($ret < 1) {
                Helper::send_mail('tech@groomit.me', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to log void cc_trans', $tx->id);
            }

            return [
                'error_code' => '',
                'error_msg' => ''
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage() . ':' . $ex->getTraceAsString()
            ];
        }
    }

    public static function remove_token($token) {
        try {
            self::init();

            if (getenv('APP_ENV') != 'production' && self::$skip_api_call_on_demo) {
                return [
                    'error_code' => '',
                    'error_msg' => ''
                ];
            }

            $xml = "<txn>";
            $xml .= "<ssl_merchant_id>" . self::$ssl_merchant_id . "</ssl_merchant_id>";
            $xml .= "<ssl_user_id>" . self::$ssl_user_id . "</ssl_user_id>";
            $xml .= "<ssl_pin>" . self::$ssl_pin . "</ssl_pin>";
            $xml .= "<ssl_transaction_type>ccupdatetoken</ssl_transaction_type>";
            $xml .= "<ssl_token>" . $token . "</ssl_token>";
            $xml .= "</txn>";

            $data = [
                'xmldata' => $xml
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // times out after 45 secs

            $result = trim(curl_exec($ch));
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            Helper::log('### CONVERGE REQUEST ###', $xml);
            Helper::log('### CONVERGE RESONSE ###', $result);

            if ($result === false) {
                return [
                    'error_code' => -1,
                    'error_msg' => $error,
                    'info' => $info,
                    'xml' => $xml
                ];
            } else{
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
            }

            if (!isset($result['ssl_result']) || $result['ssl_result'] != 0) {

                if (isset($result['errorMessage']) && isset($result['errorName']) && isset($result['errorCode'])) {
                    return [
                        'error_code' => $result['errorCode'],
                        'error_msg' => $result['errorMessage'] . '[' . $result['errorName'] . ']'
                    ];
                } else if (isset($result['ssl_result_message'])) {
                    return [
                        'error_code' => -2,
                        'error_msg' => $result['ssl_result_message']
                    ];
                }

                return [
                    'error_code' => -3,
                    'error_msg' => 'Unknown Error'
                ];
            }

            if (!isset($result['ssl_token']) || empty($result['ssl_token'])) {
                return [
                    'error_code' => -8,
                    'error_msg' => 'Empty credit card token returned'
                ];
            }

            return [
                'error_code' => '',
                'error_msg' => ''
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    //public static function update_token($token, $card_number, $exp_date, $cvv2, $avs_address, $avs_city, $avs_state, $avs_zip) {
    public static function update_token($token, $card_number, $exp_date, $cvv2, $avs_zip) {
        try {
            self::init();

            if (getenv('APP_ENV') != 'production' && self::$skip_api_call_on_demo) {
                return [
                    'error_code' => '',
                    'error_msg' => '',
                    'token' => 'DEMO-TOKEN',
                    'card_number' => substr($card_number, 0, 4) . '********' . substr($card_number, -4)
                ];
            }

            $xml = "<txn>";
            $xml .= "<ssl_merchant_id>" . self::$ssl_merchant_id . "</ssl_merchant_id>";
            $xml .= "<ssl_user_id>" . self::$ssl_user_id . "</ssl_user_id>";
            $xml .= "<ssl_pin>" . self::$ssl_pin . "</ssl_pin>";
            $xml .= "<ssl_transaction_type>ccupdatetoken</ssl_transaction_type>";
            $xml .= "<ssl_token>" . $token . "</ssl_token>";
            $xml .= "<ssl_card_number>" . $card_number . "</ssl_card_number>";
            $xml .= "<ssl_exp_date>" . $exp_date . "</ssl_exp_date>";
//            $xml .= "<ssl_avs_address>" . $avs_address . "</ssl_avs_address>";
//            $xml .= "<ssl_avs_city>" . $avs_city . "</ssl_avs_city>";
//            $xml .= "<ssl_avs_state>" . $avs_state . "</ssl_avs_state>";
            $xml .= "<ssl_avs_zip>" . $avs_zip . "</ssl_avs_zip>";
            $xml .= "<ssl_cvv2cvc2>" . $cvv2 . "</ssl_cvv2cvc2>";
            $xml .= "<ssl_verify>Y</ssl_verify>";
            $xml .= "</txn>";

            $data = [
                'xmldata' => $xml
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // times out after 45 secs

            $result = trim(curl_exec($ch));
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            Helper::log('### CONVERGE REQUEST ###', $xml);
            Helper::log('### CONVERGE RESONSE ###', $result);

            if ($result === false) {
                return [
                    'error_code' => -1,
                    'error_msg' => $error,
                    'info' => $info,
                    'xml' => $xml
                ];
            } else{
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
            }

            if (!isset($result['ssl_result']) || $result['ssl_result'] != 0) {

                if (isset($result['errorMessage']) && isset($result['errorName']) && isset($result['errorCode'])) {
                    return [
                        'error_code' => $result['errorCode'],
                        'error_msg' => $result['errorMessage'] . '[' . $result['errorName'] . ']'
                    ];
                } else if (isset($result['ssl_result_message'])) {
                    return [
                        'error_code' => -2,
                        'error_msg' => $result['ssl_result_message']
                    ];
                }

                return [
                    'error_code' => -3,
                    'error_msg' => 'Unknown Error'
                ];
            }

            if (!isset($result['ssl_avs_response'])) {
                return [
                    'error_code' => -4,
                    'error_msg' => 'Empty AVS response code found'
                ];
            }

          //if (!in_array($result['ssl_avs_response'], ["X", "Y", "M", "D", "U", "S", "G", "Z"])) {
            if (!in_array($result['ssl_avs_response'], ["X", "Y", "M", "D", "U", "S", "R", "Z" ])) {

                return [
                    'error_code' => -5,
                    'error_msg' => 'AVS not match. Please check your address again.'
                ];
            }

            if (!isset($result['ssl_cvv2_response'])) {
                return [
                    'error_code' => -6,
                    'error_msg' => 'Empty CVV2 response code found'
                ];
            }

            if ($result['ssl_cvv2_response'] !='M') {
                return [
                    'error_code' => -7,
                    'error_msg' => 'CVV2 code not match'
                ];
            }

            if (!isset($result['ssl_token']) || empty($result['ssl_token'])) {
                return [
                    'error_code' => -8,
                    'error_msg' => 'Empty credit card token returned'
                ];
            }

            return [
                'error_code' => '',
                'error_msg' => '',
                'token' => $result['ssl_token'],
                'card_number' => $result['ssl_card_number']
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    //public static function get_token($card_number, $exp_date, $cvv2, $avs_address, $avs_city, $avs_state, $avs_zip) {
    //Trying to get Token for the credit card when adding a new credit card, so it could be saved at user_bill.
    public static function get_token($card_number, $exp_date, $cvv2, $avs_zip) {
        try {
            self::init();

            if (getenv('APP_ENV') != 'production' && self::$skip_api_call_on_demo) {
                return [
                    'error_code' => '',
                    'error_msg' => '',
                    'token' => substr($card_number, 0, 4) . rand(10000000,99999999) . substr($card_number, -4),
                    'card_number' => substr($card_number, 0, 4) . '********' . substr($card_number, -4)
                ];
            }

            $xml = "<txn>";
            $xml .= "<ssl_merchant_id>" . self::$ssl_merchant_id . "</ssl_merchant_id>";
            $xml .= "<ssl_user_id>" . self::$ssl_user_id . "</ssl_user_id>";
            $xml .= "<ssl_pin>" . self::$ssl_pin . "</ssl_pin>";
            $xml .= "<ssl_transaction_type>ccgettoken</ssl_transaction_type>";
            $xml .= "<ssl_card_number>" . $card_number . "</ssl_card_number>";
            $xml .= "<ssl_exp_date>" . $exp_date . "</ssl_exp_date>";
//            $xml .= "<ssl_avs_address>" . $avs_address . "</ssl_avs_address>";
//            $xml .= "<ssl_avs_city>" . $avs_city . "</ssl_avs_city>";
//            $xml .= "<ssl_avs_state>" . $avs_state . "</ssl_avs_state>";
            $xml .= "<ssl_avs_zip>" . $avs_zip . "</ssl_avs_zip>";
            $xml .= "<ssl_cvv2cvc2>" . $cvv2 . "</ssl_cvv2cvc2>";
            $xml .= "<ssl_verify>Y</ssl_verify>";
            $xml .= "<ssl_add_token>Y</ssl_add_token>";
            $xml .= "</txn>";

            $data = [
                'xmldata' => $xml
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // times out after 45 secs

            $result = trim(curl_exec($ch));
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            Helper::log('### CONVERGE REQUEST ###', $xml);
            Helper::log('### CONVERGE RESONSE ###', $result);

            if ($result === false) {
                return [
                    'error_code' => -1,
                    'error_msg' => $error,
                    'info' => $info,
                    'xml' => $xml
                ];
            } else{
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
            }

            if (!isset($result['ssl_result']) || $result['ssl_result'] != 0) {

                if (isset($result['errorMessage']) && isset($result['errorName']) && isset($result['errorCode'])) {
                    return [
                        'error_code' => $result['errorCode'],
                        'error_msg' => $result['errorMessage'] . '[' . $result['errorName'] . ']'
                    ];
                } else if (isset($result['ssl_result_message'])) {
                    return [
                        'error_code' => -2,
                        'error_msg' => $result['ssl_result_message']
                    ];
                }

                return [
                    'error_code' => -3,
                    'error_msg' => 'Unknown Error'
                ];
            }

            if (!isset($result['ssl_avs_response'])) {
                return [
                    'error_code' => -4,
                    'error_msg' => 'Empty AVS response code found'
                ];
            }

          //if (!in_array($result['ssl_avs_response'], ["X", "Y", "A", "W", "R", "B", "P", "M", "D", "U", "S", "G", "Z"])) {
            //if (!in_array($result['ssl_avs_response'], ["X", "Y", "M", "D", "U", "S", "G", "Z"])) {
            if (!in_array($result['ssl_avs_response'], ["X", "Y", "M", "D", "U", "S", "R", "Z" ])) {
                return [
                    'error_code' => -5,
                    'error_msg' => 'AVS not match. Please check your address again.'
                ];
            }

            if (!isset($result['ssl_cvv2_response'])) {
                return [
                    'error_code' => -6,
                    'error_msg' => 'Empty CVV2 response code found'
                ];
            }

            if (!in_array($result['ssl_cvv2_response'], ['M', 'P', 'S', 'U', 'Y'])) {
                return [
                    'error_code' => -7,
                    'error_msg' => 'CVV2 code not match'
                ];
            }

            if (!isset($result['ssl_token']) || empty($result['ssl_token'])) {
                return [
                    'error_code' => -8,
                    'error_msg' => 'Empty credit card token returned'
                ];
            }

            return [
                'error_code' => '',
                'error_msg' => '',
                'token' => $result['ssl_token'],
                'card_number' => $result['ssl_card_number']
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    //Not used.
    //public static function avs_check($card_number, $exp_date, $avs_address, $avs_city, $avs_state, $avs_zip) {
    public static function avs_check($card_number, $exp_date, $avs_zip) {
        try {

            self::init();

            if (getenv('APP_ENV') != 'production' && self::$skip_api_call_on_demo) {
                return [
                    'error_code' => '',
                    'error_msg' => ''
                ];
            }

            $xml = "<txn>";
            $xml .= "<ssl_merchant_id>" . self::$ssl_merchant_id . "</ssl_merchant_id>";
            $xml .= "<ssl_user_id>" . self::$ssl_user_id . "</ssl_user_id>";
            $xml .= "<ssl_pin>" . self::$ssl_pin . "</ssl_pin>";
            $xml .= "<ssl_transaction_type>ccavsonly</ssl_transaction_type>";
            $xml .= "<ssl_card_number>" . $card_number . "</ssl_card_number>";
            $xml .= "<ssl_exp_date>" . $exp_date . "</ssl_exp_date>";
//            $xml .= "<ssl_avs_address>" . $avs_address . "</ssl_avs_address>";
//            $xml .= "<ssl_city>" . $avs_city . "</ssl_city>";
//            $xml .= "<ssl_state>" . $avs_state . "</ssl_state>";
            $xml .= "<ssl_avs_zip>" . $avs_zip . "</ssl_avs_zip>";
            $xml .= "</txn>";

            $data = [
                'xmldata' => $xml
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // times out after 45 secs

            $result = trim(curl_exec($ch));
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            Helper::log('### CONVERGE REQUEST ###', $xml);
            Helper::log('### CONVERGE RESONSE ###', $result);

            if ($result === false) {
                return [
                    'error_code' => -1,
                    'error_msg' => $error
                ];
            } else{
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
            }

            Helper::log('### CONVERGE RESPONSE - JSON ###', var_export($result, true));

            if (!isset($result['ssl_result']) || $result['ssl_result'] != 0) {

                if (isset($result['errorMessage']) && isset($result['errorName']) && isset($result['errorCode'])) {
                    return [
                        'error_code' => $result['errorCode'],
                        'error_msg' => $result['errorMessage'] . '[' . $result['errorName'] . ']'
                    ];
                } else if (isset($result['ssl_result_message'])) {
                    return [
                        'error_code' => -2,
                        'error_msg' => $result['ssl_result_message']
                    ];
                }

                return [
                    'error_code' => -3,
                    'error_msg' => 'Unknown Error'
                ];
            }

            if (!isset($result['ssl_avs_response'])) {
                return [
                    'error_code' => -4,
                    'error_msg' => 'Empty AVS response code found'
                ];
            }

          //if (!in_array($result['ssl_avs_response'], ["X", "Y", "M", "D", "U", "S", "G", "Z"])) {
            if (!in_array($result['ssl_avs_response'], ["X", "Y", "M", "D", "U", "S", "R", "Z"])) {
                return [
                    'error_code' => -5,
                    'error_msg' => 'AVS not match. Please check your address again.'
                ];
            }

            return [
                'error_code' => '',
                'error_msg' => ''
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }



}