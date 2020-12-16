<?php

namespace App\Http\Controllers\Groomer;

use App\Model\Groomer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Log;
use Validator;
use App\Model\User;
use App\Lib\Helper;
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 10/26/16
 * Time: 3:01 PM
 */
class ForgotPasswordController extends Controller
{

    public function verify_email(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'email' => 'required|email'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $groomer = Groomer::where('email', strtolower($request->email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'The email does not exist in our system.'
                ]);
            }

            # 1. send random 6 digits to the user.
            $temp_key = mt_rand(100000, 999999);

            $subject = "[Groomer] Here is your temporary key.";
            //$msg = " - key : $temp_key";
            //$ret = Helper::send_html_mail($user->email, $subject, $msg);


            $data = [];
            $data['temp_key'] = $temp_key;
            $data['email'] = $groomer->email;
            $data['name'] = $groomer->first_name;
            $data['subject'] = $subject;

            $ret = Helper::send_html_mail('groomer.forgot-password', $data);

            if (!empty($ret)) {
                return response()->json([
                    'msg' => 'Failed to send temporary key: ' . $ret
                ]);
            }

            # 2. save new temp key to user table
            $groomer->forgot_pwd_key = $temp_key;
            $groomer->forgot_pwd_set_date = Carbon::now();
            $groomer->save();

            return response()->json([
                'msg' => ''
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function verify_key(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'email' => 'required|email',
                'key' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $groomer = Groomer::where('email', strtolower($request->email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'The email does not exist in our system.'
                ]);
            }

            if ($groomer->forgot_pwd_key != $request->key) {
                return response()->json([
                    'msg' => 'Your temporarily KEY is not correct. Please check it again.'
                ]);
            }

            if ($groomer->forgot_pwd_key_expired) {
                return response()->json([
                    'msg' => 'Your KEY has been expired. Please try again.'
                ]);
            }

            return response()->json([
                'msg' => ''
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_password(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'email' => 'required|email',
                'passwd' => 'required',
                'passwd_confirm' => 'required|same:passwd'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $groomer = Groomer::where('email', strtolower($request->email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'The email does not exist in our system.'
                ]);
            }

            $groomer->password = \Crypt::encrypt($request->passwd);
            $groomer->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}