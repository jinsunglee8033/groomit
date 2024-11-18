<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/18/19
 * Time: 12:51 PM
 */

namespace App\Lib;

use App\Model\CCTrans;
use App\Model\Groomer;
use App\Model\GroomerDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use \Illuminate\Http\Request;


class eversign {


    # Groomers Agreement
    private static $template_agreement = "";
    private static $template_w9 = "";
    private static $template_ach = "";

    private static $api_url = 'https://api.eversign.com/api/document?access_key=&business_id=';

    ##
    //https://api.eversign.com/api/download_final_document
    //? access_key = YOUR_ACCESS_KEY
    //& business_id = 1
    //& document_hash = j6yMcaF2gQBIIS
    //& audit_trail = 1
    private static $api_url_final_document =
        'https://api.eversign.com/api/download_final_document?access_key=&business_id=';

    public static function post_sign($data) {

        $data_string = json_encode($data);

        $ch = curl_init(self::$api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //execute post
        $result = curl_exec($ch);

        Helper::log('#### ESIGN RESULT ####', $result);

        //close connection
        curl_close($ch);

        return $result;
    }

    public static function post_w9($groomer_id) {

        try {
            $groomer = Groomer::find($groomer_id);

            GroomerDocument::where('groomer_id', $groomer->groomer_id)->where('type', 'J')->where('status', 'A')->update([
              'status' => 'C'
            ]);

            $doc = new GroomerDocument();
            $doc->groomer_id = $groomer->groomer_id;
            $doc->type = 'J';
            $doc->signed = 'N';
            $doc->locked = 'N';
            $doc->cdate = \Carbon\Carbon::now();
            $doc->save();

            $data = [
              "sandbox" => 1,
              "template_id" => self::$template_w9,
              "title" =>  "Groomer W9 form",
              "message" => "Please sign and submit W9 form.",
              "custom_requester_name" => "",
              "custom_requester_email" => "",
              "redirect" => "http://demo.groomit.me/eversign/complete/" . $doc->id,
              "redirect_decline" => "",
              "client" => "",
              "expires" => '',
              "embedded_signing_enabled" => 1,
              "signers" => [
                [
                  "role" => "Groomer",
                  "name" => $groomer->first_name . ' ' . $groomer->last_name,
                  "email" => $groomer->email,
                  "pin" => "",
                  "message" => "Please sign and submit W9 form.",
                  "deliver_email" => "",
                  "language" => "en"
                ]
              ],
              "recipients" => [],
              "fields" => []
            ];

            $result = self::post_sign($data);

            $result = json_decode($result);

            Helper::log('#### ESIGN JSON RESULT ####', $result);

            if (!empty($result->success) &&  $result->success == false) {
                return $result;
            }

            Helper::log('#### ESIGN JSON RESULT ## DOCUMENT HASH ####', $result->document_hash);

            if (!empty($result->document_hash)) {
                $doc->e_doc_id = $result->document_hash;

                foreach ($result->signers as $signer) {
                    $doc->esign_url = $signer->embedded_signing_url;
                    $result->esign_url = $doc->esign_url;
                }
                $doc->save();
            }

            $result->doc_id = $doc->id;

            return $result;
        } catch (\Exception $ex) {
            Helper::log('#### ESIGN Exception ####', $ex->getTraceAsString());

            return null;
        }
    }

    public static function post_ach(Request $request, $groomer_id) {

        try {
            $groomer = Groomer::find($groomer_id);
            $groomer->bank_name = $request->bank_name;
            $groomer->account_holder = $request->account_holder;
            $groomer->account_number = $request->account_number;
            $groomer->routing_number = $request->routing_number;
            $groomer->update();

            GroomerDocument::where('groomer_id', $groomer->groomer_id)->where('type', 'A')->where('status', 'A')->update([
              'status' => 'C'
            ]);

            $doc = new GroomerDocument();
            $doc->groomer_id = $groomer->groomer_id;
            $doc->type = 'A';
            $doc->signed = 'N';
            $doc->locked = 'N';
            $doc->cdate = \Carbon\Carbon::now();
            $doc->save();

            $data = [
              "sandbox" => 1,
              "template_id" => self::$template_ach,
              "title" =>  "Groomer ACH form",
              "message" => "Please sign and submit ACH form.",
              "custom_requester_name" => "",
              "custom_requester_email" => "",
              "redirect" => "http://demo.groomit.me/eversign/complete/" . $doc->id,
              "redirect_decline" => "",
              "client" => "",
              "expires" => '',
              "embedded_signing_enabled" => 1,
              "signers" => [
                [
                    "role" => "Groomer",
                    "name" => $groomer->first_name . ' ' . $groomer->last_name,
                    "email" => $groomer->email,
                    "pin" => "",
                    "message" => "Please sign and submit ACH form.",
                    "deliver_email" => "",
                    "language" => "en"
                ]
              ],
              "recipients" => [],
              "fields" => [
                [
                    "identifier" => "name_of_bank",
                    "value" => $request->bank_name
                ],
                [
                    "identifier" => "contact_name",
                    "value" => $request->account_holder
                ],
                [
                    "identifier" => "bank_account_number",
                    "value" => $request->account_number
                ],
                [
                    "identifier" => "bank_routing_number",
                    "value" => $request->routing_number
                ]
              ]
            ];

            $result = self::post_sign($data);

            $result = json_decode($result);

            Helper::log('#### ESIGN JSON RESULT ####', $result);

            if (!empty($result->success) &&  $result->success == false) {
                return $result;
            }

            Helper::log('#### ESIGN JSON RESULT ## DOCUMENT HASH ####', $result->document_hash);

            if (!empty($result->document_hash)) {
                $doc->e_doc_id = $result->document_hash;

                foreach ($result->signers as $signer) {
                    $doc->esign_url = $signer->embedded_signing_url;
                    $result->esign_url = $doc->esign_url;
                }
                $doc->save();
            }

            $result->doc_id = $doc->id;

            return $result;
        } catch (\Exception $ex) {
            Helper::log('#### ESIGN Exception ####', $ex->getTraceAsString());

            return null;
        }
    }


    public static function post_agreement($groomer_id) {

        try {
            $groomer = Groomer::find($groomer_id);

            GroomerDocument::where('groomer_id', $groomer->groomer_id)->where('type', 'G')->where('status', 'A')->update([
              'status' => 'C'
            ]);

            $doc = new GroomerDocument();
            $doc->groomer_id = $groomer->groomer_id;
            $doc->type = 'G';
            $doc->signed = 'N';
            $doc->locked = 'N';
            $doc->cdate = \Carbon\Carbon::now();
            $doc->save();

            $data = [
              "sandbox" => 1,
              "template_id" => self::$template_agreement,
              "title" =>  "Groomer Agreement form",
              "message" => "Please sign and submit Groomer Agreement form.",
              "custom_requester_name" => "",
              "custom_requester_email" => "",
              "redirect" => "",
              "redirect_decline" => "",
              "client" => "",
              "expires" => '',
              "embedded_signing_enabled" => 1,
              "signers" => [
                [
                    "role" => "Groomer",
                    "name" => $groomer->first_name . ' ' . $groomer->last_name,
                    "email" => $groomer->email,
                    "pin" => "",
                    "message" => "Please sign and submit Groomer Agreement form.",
                    "deliver_email" => "",
                    "language" => "en"
                ]
              ],
              "recipients" => [],
              "fields" => []
            ];

            $result = self::post_sign($data);

            $result = json_decode($result);

            Helper::log('#### ESIGN JSON RESULT ####', $result);

            if (isset($result->success)) {

                Helper::log('#### ESIGN JSON RESULT ### RESULT ####', $result->success);

                if (!$result->success) {
                    return $result;
                }
            }

            Helper::log('#### ESIGN JSON RESULT ## DOCUMENT HASH ####', $result->document_hash);

            if (!empty($result->document_hash)) {
                $doc->e_doc_id = $result->document_hash;

                foreach ($result->signers as $signer) {
                    $doc->esign_url = $signer->embedded_signing_url;
                    $result->esign_url = $doc->esign_url;
                }
                $doc->save();
            }

            $result->doc_id = $doc->id;

            return $result;
        } catch (\Exception $ex) {
            Helper::log('#### ESIGN Exception ####', $ex->getTraceAsString());

            return null;
        }
    }
}