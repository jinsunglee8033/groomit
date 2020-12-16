<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 11/2/18
 * Time: 2:29 PM
 */

namespace App\Http\Controllers\User\API;

use App\Lib\CreditProcessor;
use App\Lib\ImageProcessor;
use App\Lib\UserProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\Message;
use App\Model\UserCertificate;
use App\Model\UserLoginHistory;
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
              'api_key'   => 'required',
              'email'     => 'required|email',
              'passwd'    => '',
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
                    'code' => '-1',
                    'msg' => $msg
                ]);
            }

            /*if ($request->new_version != 'Y') {
                return response()->json([
                    'msg' => 'Please Upgrade to the Latest Version!'
                ]);
            }*/

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-1',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            if ($request->login_channel != 'i' && empty($request->vendor_token)) {
                return response()->json([
                  'code' => '-1',
                  'msg' => 'The vendor token is required'
                ]);
            }

            if ($request->login_channel == 'i' && empty($request->passwd)) {
                return response()->json([
                  'code' => '-1',
                  'msg' => 'The password is required'
                ]);
            }

            $user = User::where('email', strtolower($request->email))
              ->where('status', 'A')
              ->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-1',
                  'msg' => 'Invalid email or password provided'
                ]);
            }

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            if (!empty($request->device_token)) {
                $user->device_token = $request->device_token;
                $user->save();
            } else {
//                if (getenv('APP_ENV') == 'production') {
//                    Helper::send_mail('tech@groomit.me', '[groomit]][APISignUpControllerLogin] user device token is empty upon login', $user->email);
//                }
            }

            switch ($request->login_channel) {
                case 'i':
                    if (!empty($user->passwd)) {
                        $decrypted_passwd = \Crypt::decrypt($user->passwd);
                    } else {
                        $decrypted_passwd = "";
                    }

                    if ($decrypted_passwd != $request->passwd) {

                        UserLoginHistory::save_login_history($user->user_id, $request->email, 'P', $request->ip(), 'I', 'F');

                        return response()->json([
                          'code' => '-1',
                          'msg' => 'Invalid email or password provided'
                        ]);
                    }

                    break;
            }

            $email_token = \Crypt::encrypt($request->email);

            $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
            if (!empty($user_photo)) {
                //$user->photo = base64_encode($user_photo->photo);
                try{
                    $user->photo = base64_encode($user_photo->photo);
                } catch (\Exception $ex) {
                    $user->photo = $user_photo->photo ;
                }
            } else {
                $user->photo = '';
            }

            $certificate = UserCertificate::where('user_id', $user->user_id)->first();
            if (!empty($certificate)) {
                //$user->certificate = base64_encode($certificate->photo);
                try{
                    $user->certificate  = base64_encode($certificate->photo);
                } catch (\Exception $ex) {
                    $user->certificate  = $certificate->photo ;
                }
            } else {
                $user->certificate = '';
            }

            # get referral code
            $referral_arr = UserProcessor::get_referral_code($user->user_id);
            $user->referral_code = $referral_arr['referral_code'];
            $user->referral_amount = $referral_arr['referral_amount'];
//            Helper::log('### REFERRAL CODE ###', $user->referral_code);
//            Helper::log('### REFERRAL AMOUNT ###', $user->referral_amount);
//            Helper::log('### USER INFORMATION ###', $user);

//            $res_user = [];
//            $res_user['user_id'] = $user->user_id;
//            $res_user['name'] = $user->name;
//            $res_user['first_name'] = $user->first_name;
//            $res_user['last_name'] = $user->last_name;
//            $res_user['phone'] = $user->phone;
//            $res_user['hear_from'] = $user->hear_from;
//            $res_user['status'] = $user->status;
//            $res_user['cdate'] = $user->cdate;
//            $res_user['mdate'] = $user->mdate;
//            $res_user['device_token'] = $user->device_token;
//            $res_user['yelp_review'] = $user->yelp_review;
//            $res_user['zip'] = $user->zip;
//            $res_user['photo'] = $user->photo;
//            $res_user['certificate'] = $user->certificate;
//            $res_user['referral_code'] = $user->referral_code;

            # save_login_history($user_id, $email, $login_channel, $ip_addr, $log_inout, $result)
            # login_channel : P => APP
            UserLoginHistory::save_login_history($user->user_id, $request->email, 'P', $request->ip(), 'I', 'S');

            return response()->json([
                'code'  => '0',
                'msg'   => '',
                'token' => $email_token,
                'user'  => $user
            ]);
        } catch (\Exception $ex) {
            Helper::log('#### EXCEPTION ####', $ex->getTraceAsString());

            return response()->json([
                'code' => '-9',
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
            $user = User::where('email', strtolower($email))->where('status', 'A')->first();

            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has already been expired.'
                ]);
            }

            # save_login_history($user_id, $email, $login_channel, $ip_addr, $log_inout, $result)
            # login_channel : P => APP
            UserLoginHistory::save_login_history($user->user_id, $email, 'P', $request->ip(), 'O', 'S');

            return response()->json([
                'code'  => '0',
                'msg'   => ''
            ]);
        } catch (\Exception $ex) {
            Helper::log('#### EXCEPTION ####', $ex->getTraceAsString());

            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function login_3rd_party(Request $request) {

        try {

            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'email'   => 'required|email',
              'name'    => 'required',
              'user_id' => 'required',
              'photo'   => '',
              'login_channel' => 'required|in:f,g,a',
              'device_token' => ''
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

            /*if ($request->new_version != 'Y') {
                return response()->json([
                    'msg' => 'Please Upgrade to the Latest Version!'
                ]);
            }*/

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-1',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $user = User::where('email', strtolower($request->email))
              ->first();
            if (empty($user)) {

                $user = new User;

                switch($request->login_channel) {
                    case 'f':
                        $user->fb_token = $request->user_id;
                        break;
                    case 'g':
                        $user->gg_token = $request->user_id;
                        break;
                    case 'a':
                        $user->ap_token = $request->user_id;
                        break;
                }


                $user->email = strtolower($request->email);
                $user->name = $request->name;
                $user->cdate = Carbon::now();

                $user->save();

                if (!empty($request->photo)) {
                    $user_photo = new UserPhoto;
                    $user_photo->user_id = $user->user_id;
                    $user_photo->photo = ImageProcessor::optimize(base64_decode($request->photo));
                    $user_photo->cdate = Carbon::now();
                    $user_photo->save();
                }

            } else {

                if ($user->status != 'A') {
                    return response()->json([
                      'code' => '-1',
                      'msg' => 'Unable to login with inactive user.'
                    ]);
                }

                switch($request->login_channel) {
                    case 'f':
                        $user->fb_token = $request->user_id;
                        break;
                    case 'g':
                        $user->gg_token = $request->user_id;
                        break;
                    case 'a':
                        $user->ap_token = $request->user_id;
                        break;
                }

                $user->save();

                if (!empty($request->photo)) {
                    $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
                    if (empty($user_photo)) {
                        $user_photo = new UserPhoto;
                    }

                    $user_photo->user_id = $user->user_id;
                    $user_photo->photo = ImageProcessor::optimize(base64_decode($request->photo));
                    $user_photo->cdate = Carbon::now();

                    $user_photo->save();
                }
            }

            if (!empty($request->device_token)) {
                $user->device_token = $request->device_token;
                $user->save();
            } else {
//                if (getenv('APP_ENV') == 'production') {
//                    Helper::send_mail('tech@groomit.me', '[groomit][APISignUpControllerLogin_3rd_party] user device token is empty upon login 3rd party', $user->email);
//                }
            }

            $email_token = \Crypt::encrypt($request->email);

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

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            # save_login_history($user_id, $email, $login_channel, $ip_addr, $log_inout, $result)
            # login_channel : 3 => 3rd party
            UserLoginHistory::save_login_history($user->user_id, $request->email, '3', $request->ip(), 'I', 'S');

            return response()->json([
                'code' => '0',
                'msg' => '',
                'token' => $email_token,
                'user' => $user
            ]);
        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function signup(Request $request) {

        DB::beginTransaction();

        try {
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'first_name' => 'required',
              'last_name' => 'required',
              'email' => 'required|email',
              'passwd' => 'required',
              'passwd_confirm' => 'required|same:passwd',
              'phone' => 'required|regex:/^\d{10}$/',
              'login_channel' => 'required|in:f,g,i',
              'zip' => 'required_with:hear_from|regex:/^\d{5}$/',
              'vendor_token' => '',
              'device_token' => ''
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
                    'code' => '-1',
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                DB::rollback();

                return response()->json([
                    'code' => '-1',
                    'msg' => 'Invalid API key provided'
                ]);
            }

            if ($request->login_channel != 'i' && empty($request->vendor_token)) {
                DB::rollback();

                return response()->json([
                    'code' => '-1',
                    'msg' => 'Vendor token is required'
                ]);
            }

            if (strpos($request->email, '.con')) {
                DB::rollback();

                return response()->json([
                  'code' => '-1',
                  'msg' => 'Please confirm your email address.'
                ]);
            }

            ### first of all check if email already exists ###
            $user = User::where('email', strtolower($request->email))->first();
            if (!empty($user)) {
                DB::rollback();

                return response()->json([
                  'code' => '-1',
                  'msg' => 'Your Email already exists.'
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
                        'code' => '-1',
                        'msg' => 'Referral code doest not exists in our system'
                    ]);
                } else {
                    if ($promo_code->type != 'R') {
                        DB::rollback();

                        return response()->json([
                          'code' => '-1',
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
            $user->hear_from = $request->hear_from;

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
//            if (empty($request->device_token)) {
//                Helper::send_mail('tech@groomit.me', '[groomit][APISignupControllerSignup]user device token is empty upon signup', $user->email);
//            }

            $user->save();

            ### check if address is available ###
            $zip = AllowedZip::where('zip', $request->zip)
              ->whereRaw("lower(available) = 'x'")
              ->first();

            ### create new address with zip only when it's allowed one ###
            if (!empty($zip)) {

                $county = empty($zip) ? null : $zip->county_name;

                $addr = new Address;
                $addr->user_id = $user->user_id;
                $addr->name = '';
                $addr->zip = $request->zip;
                $addr->county = $county;
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

                $user->photo = $request->photo;
            }

            if (!empty($request->certificate)) {

                $certificate = UserCertificate::where('user_id', $user->user_id)->first();
                if (empty($certificate)) {
                    $certificate = new UserPhoto;
                }

                $certificate->user_id = $user->user_id;
                $certificate->photo = ImageProcessor::optimize(base64_decode($request->certificate));
                $certificate->cdate = Carbon::now();
                $certificate->save();

                //$user->certificate = base64_encode($certificate->photo);
                try{
                    $user->certificate = base64_encode($certificate->photo);
                } catch (\Exception $ex) {
                    $user->certificate = $certificate->photo ;
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
                        'code' => '-1',
                        'msg' => 'Invalid code provided'
                    ]);
                }

                $msg = CreditProcessor::giveSignupCredit($user, $promo_code->code);
                if (!empty($msg)) {
                    DB::rollback();

                    return response()->json([
                        'code' => '-1',
                        'msg' => $msg
                    ]);
                }
            }

            ## Send welcome SMS
//            if (!empty($user->phone)) {
//                $message = 'Welcome to Groomit! To book a grooming package, click here. ';
//                $phone = $user->phone;
//                $ret = Helper::send_sms($phone, $message);
//
//                if (!empty($ret)) {
//                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message);
//                }
//
//                Message::save_sms_to_user($message, $user, null);
//            }


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
                    'code' => '-1',
                    'msg' => 'Failed to send welcome email'
                ]);
            }

            ## Send email end ##
            $email_token = \Crypt::encrypt($request->email);

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            DB::commit();


//            $segment = new \Segment();
//            $segment->init("5Ve8XWVizx6obmb2aunTqyQ89tta5a0c");
//
//            $segment->identify( [
//                    "userId" => empty($user->user_id)? 'SessionTimeOut': $user->user_id,
//                    "email" =>  strtolower(trim($request->email)),
//                    "signup_date" => substr(Carbon::now()->toIso8601String(),0,19) ,
//                    "device_type" => 'P', //APP
//                    "first_name" =>$request->first_name,
//                    "last_name" =>$request->last_name,
//                    "phone_number" =>$request->phone,
//                    "zip" =>empty($request->zip) ? '' :$request->zip ,
//                    "user_id" => $user->user_id
//                ]
//            );
//
//
//            $segment->track([
//                    "userId" =>  empty($user->user_id)? 'SessionTimeOut': $user->user_id,
//                    "event" => 'sign up',
//                    "email" =>  strtolower(trim($request->email)),
//                    "signup_date" => substr(Carbon::now()->toIso8601String(),0,19) ,
//                    "device_type" => 'P', //APP
//                    "first_name" =>$request->first_name,
//                    "last_name" =>$request->last_name,
//                    "phone_number" =>$request->phone,
//                    "zip" => empty($request->zip) ? '' :$request->zip ,
//                    "user_id" => $user->user_id,
//                    "hear_about_us" => empty($request->hear_from) ? '' : $request->hear_from,
//                    "referral_code" =>  empty($request->referral_code)? '' : $request->referral_code
//                ]
//            );



            return response()->json([
                'code' => '0',
                'msg' => '',
                'token' => $email_token,
                'user' => $user
            ]);
        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function signup2(Request $request) {

        DB::beginTransaction();

        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'passwd' => 'required',
                'passwd_confirm' => 'required|same:passwd',
                'phone' => 'required|regex:/^\d{10}$/',
                'login_channel' => 'required|in:f,g,i',
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required',
                'vendor_token' => '',
                'device_token' => ''
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
                    'code' => '-1',
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                DB::rollback();

                return response()->json([
                    'code' => '-1',
                    'msg' => 'Invalid API key provided'
                ]);
            }

            if ($request->login_channel != 'i' && empty($request->vendor_token)) {
                DB::rollback();

                return response()->json([
                    'code' => '-1',
                    'msg' => 'Vendor token is required'
                ]);
            }

            if (strpos($request->email, '.con')) {
                DB::rollback();

                return response()->json([
                    'code' => '-1',
                    'msg' => 'Please confirm your email address.'
                ]);
            }

            ### first of all check if email already exists ###
            $user = User::where('email', strtolower($request->email))->first();
            if (!empty($user)) {
                DB::rollback();

                return response()->json([
                    'code' => '-1',
                    'msg' => 'Your Email already exists.'
                ]);
            }

            $user = User::where('phone', $request->phone)->where('status', 'B')->first();
            if (!empty($user)) {
                DB::rollback();
                return response()->json([
                    'code' => '-1',
                    'msg' => 'We cannot process your request.Please contact our customer care[P99].'
                ]);
            }

            if( isset($request->address1) && $request->address1 != '') {
                $addr1_1st = trim(substr($request->address1, 0 , strpos($request->address1 , ' '))) ;

                $fraud_address = Address::join('user', 'user.user_id', '=', 'address.user_id')
                    ->where('user.status', 'B')
                    ->where('address.zip', '=', $request->zip)
                    ->whereRaw( "lower(address.address1) like ? " , [  strtolower($addr1_1st)  . '%' ] )
                    ->first();

                if (!empty($fraud_address)) {
                    DB::rollback();
                    return response()->json([
                        'code' => '-1',
                        'msg' => 'We cannot process your request.Please contact our customer care[A99].'
                    ]);
                }
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
                        'code' => '-1',
                        'msg' => 'Referral code doest not exists in our system'
                    ]);
                } else {
                    if ($promo_code->type != 'R') {
                        DB::rollback();

                        return response()->json([
                            'code' => '-1',
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
            $user->hear_from = $request->hear_from;
            $user->dog = $request->dog;
            $user->cat = $request->cat;

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
//            if (empty($request->device_token)) {
//                Helper::send_mail('tech@groomit.me', '[groomit][APISignupControllerSignup]user device token is empty upon signup', $user->email);
//            }

            $user->save();

            ### check if address is available ###
            $zip = AllowedZip::where('zip', $request->zip)
                ->whereRaw("lower(available) = 'x'")
                ->first();

            ### create new address with zip only when it's allowed one ###
            if (!empty($zip)) {

                $county = empty($zip) ? null : $zip->county_name;


                $addr = new Address;
                $addr->user_id = $user->user_id;
                $addr->name = '';
                $addr->zip = $request->zip;
                $addr->address1 = $request->address1;
                $addr->address2 = $request->address2;
                $addr->city = $request->city;
                $addr->county = $county;
                $addr->state = $request->state;
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

                $user->photo = $request->photo;
            }

            if (!empty($request->certificate)) {

                $certificate = UserCertificate::where('user_id', $user->user_id)->first();
                if (empty($certificate)) {
                    $certificate = new UserPhoto;
                }

                $certificate->user_id = $user->user_id;
                $certificate->photo = ImageProcessor::optimize(base64_decode($request->certificate));
                $certificate->cdate = Carbon::now();
                $certificate->save();

                //$user->certificate = base64_encode($certificate->photo);
                try{
                    $user->certificate = base64_encode($certificate->photo);
                } catch (\Exception $ex) {
                    $user->certificate = $certificate->photo ;
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
                        'code' => '-1',
                        'msg' => 'Invalid code provided'
                    ]);
                }

                $msg = CreditProcessor::giveSignupCredit($user, $promo_code->code);
                if (!empty($msg)) {
                    DB::rollback();

                    return response()->json([
                        'code' => '-1',
                        'msg' => $msg
                    ]);
                }
            }

            ## Send welcome SMS
//            if (!empty($user->phone)) {
//                $message = 'Welcome to Groomit! To book a grooming package, click here. ';
//                $phone = $user->phone;
//                $ret = Helper::send_sms($phone, $message);
//
//                if (!empty($ret)) {
//                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message);
//                }
//
//                Message::save_sms_to_user($message, $user, null);
//            }


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
                    'code' => '-1',
                    'msg' => 'Failed to send welcome email'
                ]);
            }

            ## Send email end ##
            $email_token = \Crypt::encrypt($request->email);

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            DB::commit();


//            $segment = new \Segment();
//            $segment->init("5Ve8XWVizx6obmb2aunTqyQ89tta5a0c");
//
//            $segment->identify( [
//                    "userId" => empty($user->user_id)? 'SessionTimeOut': $user->user_id,
//                    "email" =>  strtolower(trim($request->email)),
//                    "signup_date" => substr(Carbon::now()->toIso8601String(),0,19) ,
//                    "device_type" => 'P', //APP
//                    "first_name" =>$request->first_name,
//                    "last_name" =>$request->last_name,
//                    "phone_number" =>$request->phone,
//                    "zip" =>empty($request->zip) ? '' :$request->zip ,
//                    "user_id" => $user->user_id
//                ]
//            );
//
//
//            $segment->track([
//                    "userId" =>  empty($user->user_id)? 'SessionTimeOut': $user->user_id,
//                    "event" => 'sign up',
//                    "email" =>  strtolower(trim($request->email)),
//                    "signup_date" => substr(Carbon::now()->toIso8601String(),0,19) ,
//                    "device_type" => 'P', //APP
//                    "first_name" =>$request->first_name,
//                    "last_name" =>$request->last_name,
//                    "phone_number" =>$request->phone,
//                    "zip" => empty($request->zip) ? '' :$request->zip ,
//                    "user_id" => $user->user_id,
//                    "hear_about_us" => empty($request->hear_from) ? '' : $request->hear_from,
//                    "referral_code" =>  empty($request->referral_code)? '' : $request->referral_code
//                ]
//            );



            return response()->json([
                'code' => '0',
                'msg' => '',
                'token' => $email_token,
                'user' => $user
            ]);
        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json([
                'code' => '-9',
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
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-1',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-1',
                  'msg' => 'Your session has expired. Please login again. [GET_USR]'
                ]);
            }

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            return response()->json([
              'code' => '0',
              'msg' => '',
              'user' => $user
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}