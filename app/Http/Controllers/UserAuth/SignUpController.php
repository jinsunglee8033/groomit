<?php

namespace App\Http\Controllers;

use App\Lib\CreditProcessor;
use App\Lib\ImageProcessor;
use App\Lib\UserProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
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
                'email' => 'required|email',
                'passwd' => '',
                'login_channel' => 'required|in:i'
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

            if ($request->login_channel == 'i' && empty($request->passwd)) {
                return response()->json([
                    'msg' => 'The password is required'
                ]);
            }

            $user = User::where('email', strtolower($request->email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Invalid email or password provided'
                ]);
            }

            switch ($request->login_channel) {
                case 'i':
                    if (!empty($user->passwd)) {
                        $decrypted_passwd = \Crypt::decrypt($user->passwd);
                    } else {
                        $decrypted_passwd = "";
                    }

                    if ($decrypted_passwd != $request->passwd) {
                        return response()->json([
                            'msg' => 'Invalid email or password provided'
                        ]);
                    }

                    break;
            }

            # get referral code
            //$user->referral_code = UserProcessor::get_referral_code($user->user_id);
            //Helper::log('### REFERRAL CODE ###', $user->referral_code);
            $referral_arr = UserProcessor::get_referral_code($user->user_id);
            $user->referral_code = $referral_arr['referral_code'];
            $user->referral_amount = $referral_arr['referral_amount'];
//            Helper::log('### REFERRAL CODE ###', $user->referral_code);
//            Helper::log('### REFERRAL AMOUNT ###', $user->referral_amount);
//

            return response()->json([
                'msg' => '',
                'user' => $user
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function login_3rd_party(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'email' => 'required|email',
                'name' => 'required',
                'user_id' => 'required',
                'photo_url' => '',
                'login_channel' => 'required|in:f,g'
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


            $user = User::where('email', strtolower($request->email))->first();
            if (empty($user)) {

                $user = new User;

                switch($request->login_channel) {
                    case 'f':
                        $user->fb_token = $request->user_id;
                        break;
                    case 'g':
                        $user->gg_token = $request->user_id;
                        break;
                }


                $user->email = strtolower($request->email);
                $user->name = $request->name;
                $user->cdate = Carbon::now();

                $user->save();

                if (!empty($request->photo)) {
                    $user_photo = new UserPhoto;
                    $user_photo->user_id = $user->user_id;
                    $user_photo->photo = file_get_contents($request->photo_url);
                    $user_photo->cdate = Carbon::now();
                    $user_photo->save();
                }

            } else {
                switch($request->login_channel) {
                    case 'f':
                        $user->fb_token = $request->user_id;
                        break;
                    case 'g':
                        $user->gg_token = $request->user_id;
                        break;
                }

                $user->save();

                if (!empty($request->photo)) {
                    $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
                    if (empty($user_photo)) {
                        $user_photo = new UserPhoto;
                    }

                    $user_photo->user_id = $user->user_id;
                    $user_photo->photo = file_get_contents($request->photo_url);
                    $user_photo->cdate = Carbon::now();

                    $user_photo->save();
                }
            }


            $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
            if (!empty($user_photo)) {
                //$user->photo = base64_encode($user_photo->photo);
                try{
                    $user->photo = base64_encode($user_photo->photo);
                } catch (\Exception $ex) {
                    $user->photo = $user_photo->photo ;
                }
            }

            # get referral code
            //$user->referral_code = UserProcessor::get_referral_code($user->user_id);
            $referral_arr = UserProcessor::get_referral_code($user->user_id);
            $user->referral_code = $referral_arr['referral_code'];
            $user->referral_amount = $referral_arr['referral_amount'];

            return response()->json([
                'msg' => '',
                'user' => $user
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function signup_user(Request $request) {

        DB::beginTransaction();

        try {
            $v = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'passwd' => 'required',
                'passwd_confirm' => 'required|same:passwd',
                'phone' => 'required|regex:/^\d{10}$/',
                'login_channel' => 'required|in:f,g,i',
                'zip' => 'required_with:hear_from|regex:/^\d{5}$/',
                'vendor_token' => ''
            ], [
                'password_confirm.same' => 'Password doesn\'t match, please retry.'
            ]);

            if ($v->fails()) {

                DB::rollback();

                $msg = '';

                foreach ($v->messages()->toArray() as $k => $v) {

                    $msg .= (empty($msg) ? '' : " | ") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            if ($request->login_channel != 'i' && empty($request->vendor_token)) {
                DB::rollback();

                return response()->json([
                    'msg' => 'Vendor token is required'
                ]);
            }

            if (strpos($request->email, '.con')) {
                DB::rollback();

                return response()->json([
                    'msg' => 'Please confirm your email address.'
                ]);
            }

            ### first of all check if email already exists ###
            $user = User::where('email', strtolower($request->email))->first();
            if (!empty($user)) {
                DB::rollback();

                return response()->json([
                    'msg' => 'Email already taken'
                ]);
            }

            ### check if referral code exists ###
            if (!empty($request->referral_code)) {
                $promo_code = PromoCode::whereRaw("code = '" . strtoupper($request->referral_code) ."'")
                    //->where('type', 'R')
                    ->where('status', 'A')
                    ->first();
                if (empty($promo_code)) {
                    DB::rollback();

                    return response()->json([
                        'msg' => 'Referral code doest not exists in our system'
                    ]);
                } else {
                    if ($promo_code->type != 'R') {
                        DB::rollback();

                        return response()->json([
                            'msg' => 'You entered a Promo Code, please enter it during scheduling under payments'
                        ]);
                    }
                }
            }

            $user = new User;

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = strtolower($request->email);
            $user->phone = $request->phone;
            $user->hear_from = empty($request->heard_from) ? $request->hear_from : $request->heard_from;

            switch ($request->login_channel) {
                case 'i':
                    if (!empty($request->passwd)) {
                        $user->passwd = \Crypt::encrypt($request->passwd);
                    }
                    break;
                case 'f':
                    $user->fb_token = $request->vendor_token;
                    break;
                case 'g':
                    $user->gg_token = $request->vendor_token;
                    break;
            }

            $user->zip = $request->zip;

            $user->cdate = Carbon::now();
            $user->device_token = $request->device_token;
            if (empty($request->device_token)) {
                //Helper::send_mail('tech@groomit.me', '[groomit][UserAuth/SignUpControllerSignup_user] user device token is empty upon signup', $user->email);
            }
            $user->save();


            ### check if address is available ###
            $zip = AllowedZip::where('zip', $request->zip)
                ->whereRaw("lower(available) = 'x'")
                ->first();

            $county = empty($zip) ? null : $zip->county_name;

            ### create new address with zip only when it's allowed one ###
            if (!empty($zip)) {
                $addr = new Address;
                $addr->user_id = $user->user_id;
                $addr->name = '';
                $addr->county = $county;
                $addr->zip = $request->zip;
                $addr->default_address = 'Y';
                $addr->status = 'A';
                $addr->save();
            }

            ### Generate referral code for the new user ###
            //$user->referral_code = UserProcessor::get_referral_code($user->user_id);
            $referral_arr = UserProcessor::get_referral_code($user->user_id);
            $user->referral_code = $referral_arr['referral_code'];
            $user->referral_amount = $referral_arr['referral_amount'];

            if (!empty($request->photo)) {

                $photo = UserPhoto::where('user_id', $user->user_id)->first();
                if (empty($photo)) {
                    $photo = new UserPhoto;
                }

                $photo->user_id = $user->user_id;
                $photo->photo = ImageProcessor::optimize(base64_decode($request->photo));
                $photo->cdate = Carbon::now();
                $photo->save();

                //$user->photo = base64_encode($photo->photo);
                try{
                    $user->photo = base64_encode($photo->photo);
                } catch (\Exception $ex) {
                    $user->photo = $photo->photo ;
                }

            }

            ### Give $25 credit if referral code has been used ###
            if (!empty($request->referral_code)) {
                $promo_code = PromoCode::whereRaw("code = '" . strtoupper($request->referral_code) ."'")
                    ->where('type', 'R')
                    ->where('status', 'A')
                    ->first();
                if (empty($promo_code)) {
                    DB::rollback();

                    return response()->json([
                        'msg' => 'Invalid code provided'
                    ]);
                }

                $msg = CreditProcessor::giveSignupCredit($user, $promo_code->code);
                if (!empty($msg)) {
                    DB::rollback();

                    return response()->json([
                        'msg' => $msg
                    ]);
                }
            }


            ## Send welcome email ##
            $subject = "Welcome to GROOMIT";

            $data = [];
            $data['email'] = $request->email;
            $data['name'] = $request->first_name;
            $data['subject'] = $subject;
            $data['referral_code'] = $user->referral_code;
            $data['referral_amount'] = $user->referral_amount;

            $ret = Helper::send_html_mail('welcome', $data);

            if (!empty($ret)) {
                DB::rollback();

                return response()->json([
                    'msg' => 'Failed to send welcome email'
                ]);
            }

            ## Send email end ##
            $email_token = \Crypt::encrypt($request->email);

            DB::commit();

            return response()->json([
                'msg' => '',
                'token' => $email_token,
                'user' => $user
            ]);
        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_user_profile(Request $request) {
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

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [GET_USR]'
                ]);
            }

            $email_token = \Crypt::encrypt($request->email);

            return response()->json([
                'msg' => '',
                'user' => $user
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}