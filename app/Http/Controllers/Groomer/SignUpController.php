<?php

namespace App\Http\Controllers\Groomer;

use App\Lib\CreditProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\Groomer;
use App\Model\GroomerLoginHistory;
use App\Model\UserPhoto;
use App\Model\PromoCode;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Validator;
use App\Model\User;
use App\Lib\Helper;
use DB;

class SignUpController extends Controller
{

    public function login(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'email' => 'required|email',
                'passwd' => '',
                'login_channel' => 'required|in:i',
                'vendor_token' => '',
                'device_token' => ''
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

            if ($request->login_channel != 'i' && empty($request->vendor_token)) {
                return response()->json([
                    'msg' => 'The vendor token is required'
                ]);
            }

            if ($request->login_channel == 'i' && empty($request->passwd)) {
                return response()->json([
                    'msg' => 'The password is required'
                ]);
            }

            $groomer = Groomer::where('email', strtolower($request->email))
                ->where('status', 'A')
                ->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Invalid email or password provided'
                ]);
            }

            if (!empty($request->device_token)) {
                $groomer->device_token = $request->device_token;
                $groomer->save();
            } else {
                //Helper::send_mail('tech@groomit.me', '[groomer][GroomerSignUpController] user device token is empty upon login', $groomer->email);
            }

            switch ($request->login_channel) {
                case 'i':
                    if (!empty($groomer->password)) {
                        $decrypted_passwd = \Crypt::decrypt($groomer->password);
                    } else {
                        $decrypted_passwd = "";
                    }

                    if ($decrypted_passwd != $request->passwd) {
                        GroomerLoginHistory::save_login_history($groomer->groomer_id, $request->ip() ,'F','Wrong PW', 'I');
                        return response()->json([
                            'msg' => 'Invalid email or password provided'
                        ]);
                    }

                    break;
            }

            $email_token = \Crypt::encrypt($request->email);

            if (!empty($groomer->profile_photo)) {
                //$groomer->photo = base64_encode($groomer->profile_photo);
                try{
                    $groomer->photo = base64_encode($groomer->profile_photo);
                } catch (\Exception $ex) {
                    $groomer->photo = $groomer->profile_photo ;
                }

            } else {
                $groomer->photo = '';
            }

            # get referral code
            //$user->referral_code = $this->get_referral_code($user->user_id);
            //Helper::log('### REFERRAL CODE ###', $user->referral_code);

            # save_login_history
            GroomerLoginHistory::save_login_history($groomer->groomer_id, $request->ip() ,'S','','I');

            return response()->json([
                'msg' => '',
                'token' => $email_token,
                'groomer' => $groomer
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function login_as(Request $request) {

        try {

            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token' => 'required',
              'groomer_id' => 'required'
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

            $email = \Crypt::decrypt($request->token);
            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                  'msg' => 'Your session has expired. Please login again. [GET_USR]'
                ]);
            }

            if ($groomer->is_admin !== 'Y') {
                return response()->json([
                  'msg' => 'You are not authorized to use login as.'
                ]);
            }

            $to_groomer = Groomer::find($request->groomer_id);
            if (empty($to_groomer)) {
                return response()->json([
                  'msg' => 'The groomer is not available.'
                ]);
            }
            $to_email_token = \Crypt::encrypt($to_groomer->email);

            return response()->json([
              'msg' => '',
              'token' => $to_email_token,
              'groomer' => $to_groomer
            ]);
        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function logout(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required'
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

            $email = \Crypt::decrypt($request->token);
            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has already been expired.'
                ]);
            }

            # save_logout_history
            GroomerLoginHistory::save_login_history($groomer->groomer_id, $request->ip() ,'S','','O');

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