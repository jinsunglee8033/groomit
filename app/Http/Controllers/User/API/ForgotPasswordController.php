<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 11/8/18
 * Time: 4:36 PM
 */

namespace App\Http\Controllers\User\API;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Log;
use Validator;
use App\Model\User;
use App\Lib\Helper;
use App\Lib\UserProcessor;

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
                  'code' => '-1',
                  'msg'  => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg'  => 'Invalid API key provided'
                ]);
            }

            $user = User::where('email', strtolower($request->email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg'  => 'The email does not exist in our system.'
                ]);
            }

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

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
                return response()->json([
                    'code' => '-2',
                    'msg'  => 'Failed to send temporary KEY: ' . $ret
                ]);
            }

            # 2. save new temp key to user table
            $user->forgot_pwd_key = $temp_key;
            $user->save();

            return response()->json([
              'code' => '0',
              'msg'  => ''
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
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
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $user = User::where('email', strtolower($request->email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg'  => 'The email does not exist in our system.'
                ]);
            }

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            if ($user->forgot_pwd_key != $request->key) {
                return response()->json([
                  'code' => '-2',
                  'msg'  => 'Provided forgot password temporary key does not match'
                ]);
            }

            return response()->json([
                'code' => '0',
                'msg'  => ''
            ]);
        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg'  => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_password(Request $request) {
        try {

            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'email'   => 'required|email',
              'key'     => 'required',
              'passwd'  => 'required',
              'passwd_confirm' => 'required|same:passwd'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'code'  => '-1',
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'code'  => '-2',
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $user = User::where('email', strtolower($request->email))->first();
            if (empty($user)) {
                return response()->json([
                  'code'  => '-2',
                  'msg'   => 'The email does not exist in our system.'
                ]);
            }

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            if ($user->forgot_pwd_key != $request->key) {
                return response()->json([
                  'code' => '-2',
                  'msg'  => 'Your temporary key is not correct.'
                ]);
            }

            $user->passwd = \Crypt::encrypt($request->passwd);
            $user->save();

            return response()->json([
                'code'  => '0',
                'msg'   => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-1',
                'msg'  => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}