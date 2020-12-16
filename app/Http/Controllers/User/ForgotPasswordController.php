<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Log;
use Validator;
use App\Model\User;
use App\Lib\Helper;
use App\Lib\UserProcessor;

/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 10/26/16
 * Time: 3:01 PM
 */
class ForgotPasswordController extends Controller
{

    private function process_verify_email(Request $request) {
        $user = User::where('email', strtolower($request->email))->first();
        if (empty($user)) {
            return 'The email does not exist in our system.';
        }

        # 1. send random 6 digits to the user.
        $temp_key = mt_rand(100000, 999999);

        $subject = "[Groomit] Here is your temporary KEY.";
        //$msg = " - key : $temp_key";
        //$ret = Helper::send_html_mail($user->email, $subject, $msg);


        $data = [];
        $data['temp_key'] = $temp_key;
        $data['email'] = $user->email;
        $data['name'] = $user->first_name;
        $data['subject'] = $subject;

        $referral_arr = UserProcessor::get_referral_code($user->user_id);
        $data['referral_code'] = $referral_arr['referral_code'];
        $data['referral_amount'] = $referral_arr['referral_amount'];

        $ret = Helper::send_html_mail('forgot_password', $data);

        if (!empty($ret)) {
            return 'Failed to send temporary KEY: ' . $ret;
        }

        # 2. save new temp key to user table
        $user->forgot_pwd_key = $temp_key;
        $user->save();

        return '';
    }

    public function verify_email_desktop(Request $request) {
        try {

            $v = Validator::make($request->all(), [
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

            $msg = $this->process_verify_email($request);

            return response()->json([
                'msg' => $msg
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

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

            $msg = $this->process_verify_email($request);

            return response()->json([
                'msg' => $msg
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    private function process_verify_key(Request $request) {
        $user = User::where('email', strtolower($request->email))->first();
        if (empty($user)) {
            return 'The email does not exist in our system.';
        }

        if ($user->forgot_pwd_key != $request->key) {
            return 'Your temporary KEY is not correct.';
        }

        return '';
    }

    public function verify_key_desktop(Request $request) {
        try {

            $v = Validator::make($request->all(), [
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

            $msg = $this->process_verify_key($request);

            return response()->json([
                'msg' => $msg
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

            $msg = $this->process_verify_key($request);

            return response()->json([
                'msg' => $msg
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function process_update_password(Request $request) {
        $user = User::where('email', strtolower($request->email))->first();
        if (empty($user)) {
            return 'The email does not exist in our system.';
        }

        $user->passwd = \Crypt::encrypt($request->passwd);
        $user->save();

        return '';
    }

    public function update_password_desktop(Request $request) {
        try {

            $v = Validator::make($request->all(), [
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

            $msg = $this->process_update_password($request);

            return response()->json([
                'msg' => $msg
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

            $msg = $this->process_update_password($request);

            return response()->json([
                'msg' => $msg
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}