<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/8/18
 * Time: 2:36 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\CreditProcessor;
use App\Lib\Helper;
use App\Lib\ImageProcessor;
use App\Lib\PetProcessor;
use App\Lib\ScheduleProcessor;
use App\Lib\UserProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\Breed;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\PromoCode;
use App\Model\RequestReferal;
use App\Model\User;
use App\Model\UserLoginHistory;
use App\Model\UserPhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function FBLogin(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'email' => 'required|email',
                'name' => 'required',
                'user_id' => 'required',
                'photo_url' => ''
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



            $user = User::where('email', strtolower($request->email))
                ->first();

            if (empty($user)) {

                $user = new User;

                $user->fb_token = $request->user_id;
                $user->email = strtolower($request->email);
                $user->name = $request->name;
                $user->cdate = Carbon::now();

                $refe = RequestReferal::where('ip_addr', $request->ip())->orderBy('cdate', 'desc')->first();
                if (!empty($refe)) {
                    $user->referral_url = $refe->url;
                } else {
                    $user->referral_url = 'facebook.com';
                }

                $user->save();

                if (!empty($request->photo_url)) {
                    $user_photo = new UserPhoto;
                    $user_photo->user_id = $user->user_id;
                    $user_photo->photo = file_get_contents($request->photo_url);
                    $user_photo->cdate = Carbon::now();
                    $user_photo->save();
                }

            } else {

                if ($user->status != 'A') {
                    return response()->json([
                        'msg' => 'Unable to login for the user'
                    ]);
                }

                $user->fb_token = $request->user_id;
                $user->save();

                if (!empty($request->photo_url)) {
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

            $user = \App\User::find($user->user_id);
            Auth::guard('user')->login($user);

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
    }

    public function login(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
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

            $user = User::where('email', strtolower($request->email))
                ->where('status', 'A')
                ->first();

            if (empty($user)) {

                # save_login_history($user_id, $email, $login_channel, $ip_addr, $log_inout, $result)
                UserLoginHistory::save_login_history('', $request->email, 'E', $request->ip(), 'I', 'F');

                return response()->json([
                    'msg' => 'Invalid email or password provided'
                ]);
            }

            $credential = [
              'email' => strtolower($request->email),
              'password' => $request->password
            ];

            if(!Auth::guard('user')->attempt($credential)) {

                # save_login_history($user_id, $email, $login_channel, $ip_addr, $log_inout, $result)
                UserLoginHistory::save_login_history($user->user_id, $request->email, 'E', $request->ip(), 'I', 'F');

                return response()->json([
                    'msg' => 'Invalid email or password provided'
                ]);
            }

            # save_login_history($user_id, $email, $login_channel, $ip_addr, $log_inout, $result)
            UserLoginHistory::save_login_history($user->user_id, $request->email, 'E', $request->ip(), 'I', 'S');

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ' ]'
            ]);
        }
    }

    public function logout(Request $request) {

        try {

            $login_as_user = Session::get('login-as-user');

            if (Auth::guard('user')->check()) {
                $user = Auth::guard('user')->user();
                $user_id = $user->user_id;
                $email = $user->email;

                Auth::guard('user')->logout();
                Session::put('schedule', null);

                $request->session()->flush();
                $request->session()->regenerate();

                if (!empty($login_as_user)) {
                    Auth::guard('admin')->login($login_as_user);
                    return redirect('/admin/user/' . $user_id);
                }
            }

            # save_login_history($user_id, $email, $login_channel, $ip_addr, $log_inout, $result)
            UserLoginHistory::save_login_history($user_id, $email, 'E', $request->ip(), 'O', 'S');

//            return response()->json([
//                'msg' => ''
//            ]);
            return redirect('/');

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ' ]'
            ]);
        }

    }

    public function sign_up(Request $request) {


        $prev_url = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : null ;
        $full_prev_url = $prev_url;
        if (!empty($prev_url)) {
            $link_array = explode('/',$prev_url);
            $prev_url = end($link_array);
        }

        Session::put('schedule.prev_url', $prev_url);
        Session::put('schedule.full_prev_url', $full_prev_url);

        $zip = ScheduleProcessor::getZip();
        $address1 = ScheduleProcessor::getAddress1();
        $city = ScheduleProcessor::getCity();
        $state = ScheduleProcessor::getState();

        return view('user/sign-up', [
            'zip' => $zip,
            'address1' => $address1,
            'city' => $city,
            'state' => $state
        ]);
    }

    //Signup from Desktop
    public function register(Request $request) {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required|regex:/^\d{10}$/',
                'email' => 'required|email',
                'hear_from' => 'required',
                'referral_code' => '',
                'address1' => 'required',
                'address2' => '',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|regex:/^\d{5}$/',
                'password' => 'required',
                'password_confirm' => 'required|same:password'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            if (strpos($request->email, '.con')) {
                DB::rollback();

                return response()->json([
                    'msg' => 'Incorrect email.Please check your email again.'
                ]);
            }

            ### first of all check if email already exists ###
            $user = User::whereRaw("lower(trim(email)) = ?", [strtolower(trim($request->email))])->first();
            if (!empty($user)) {
                DB::rollback();

                return response()->json([
                    'msg' => 'Email already taken'
                ]);
            }

            ### check if phone already exists && active ###
            $user = User::where('phone', $request->phone)
                ->where('status', 'A')
                ->first();
            if (!empty($user)) {
                DB::rollback();

                return response()->json([
                    'msg' => 'Your Phone Number already exists.'
                ]);
            }

            $user = User::where('phone', $request->phone)
                ->where('status', 'B')
                ->first();
            if (!empty($user)) {
                DB::rollback();

                return response()->json([
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
                        'msg' => 'We cannot process your request.Please contact our customer care[A99].'
                    ]);
                }
            }
            
                
            ### check if address is available ###
            $zip = AllowedZip::where('zip', $request->zip)
                ->whereRaw("lower(available) = 'x'")
                ->first();

            ### check if address is allowed area or not.
            if (empty($zip)) {
                return response()->json([
                    'msg' => 'Your area is not available for our service.'
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
            $user->email = strtolower(trim($request->email));
            $user->phone = $request->phone;
            $user->hear_from = $request->hear_from;
            $user->register_from = 'D'; # D: Desktop, A: App
            $user->dog = $request->dog;
            $user->cat = $request->cat;

            if (!empty($request->password)) {
                $user->passwd = \Crypt::encrypt($request->password);
            }

            $user->zip = $request->zip;
            $user->cdate = Carbon::now();

            $refe = RequestReferal::where('ip_addr', $request->ip())->orderBy('cdate', 'desc')->first();
            if (!empty($refe)) {
                $user->referral_url = $refe->url;
            } else {
                $user->referral_url = $request->ip();
            }

            $user->save();

            ### save photo ###
            if (!empty($request->photo)) {
                $photo = UserPhoto::where('user_id', $user->user_id)->first();
                if (empty($photo)) {
                    $photo = new UserPhoto;
                }

                $photo->user_id = $user->user_id;
                $photo->photo = file_get_contents($request->photo);
                $photo->cdate = Carbon::now();
                $photo->save();
            }

            ### check if address is available ###
//            $zip = AllowedZip::where('zip', $request->zip)
//                ->whereRaw("lower(available) = 'x'")
//                ->first();

            //$allowed_zip = AllowedZip::where('zip', $request->zip)->first();
            $county = empty($zip) ? null : $zip->county_name;

            ### create new address with zip only when it's allowed one ###
            //if (!empty($zip)) {
                $addr = new Address;
                $addr->user_id = $user->user_id;
                $addr->name = '';
                $addr->address1 = $request->address1;
                $addr->address2 = $request->address2;
                $addr->city = $request->city;
                $addr->county = $county;
                $addr->state = $request->state;
                $addr->zip = $request->zip;
                $addr->default_address = 'Y';
                $addr->status = 'A';
                $addr->save();
            //}

            ### Generate referral code for the new user ###
            //$user->referral_code = UserProcessor::get_referral_code($user->user_id);
            $referral_arr = UserProcessor::get_referral_code($user->user_id);
            $user->referral_code = $referral_arr['referral_code'];
            $user->referral_amount = $referral_arr['referral_amount'];

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
            $data['referral_code'] = empty($user->referral_code) ? '' : $user->referral_code ;
            $data['referral_amount'] = empty($user->referral_amount) ? 0 : $user->referral_amount ;

            $ret = Helper::send_html_mail('welcome', $data);

            if (!empty($ret)) {
                DB::rollback();

                return response()->json([
                    'msg' => 'Failed to send welcome email'
                ]);
            }

            DB::commit();

            $user = \App\User::find($user->user_id);
            Auth::guard('user')->login($user);

            # save_login_history($user_id, $email, $login_channel, $ip_addr)
            UserLoginHistory::save_login_history($user->user_id, $request->email, 'E', $request->ip(), 'I', 'S');

//            $segment = new \Segment();
//            $segment->init("5Ve8XWVizx6obmb2aunTqyQ89tta5a0c");
//
//            $segment->identify( [
//                    "userId" =>  empty($user->user_id)? 'SessionTimeOut': $user->user_id,
//                    "email" =>  strtolower(trim($request->email)),
//                    "signup_date" => substr(Carbon::now()->toIso8601String(),0,19) ,
//                    "device_type" => 'E', //WEB
//                    "first_name" =>$request->first_name,
//                    "last_name" =>$request->last_name,
//                    "phone_number" =>$request->phone,
//                    "zip" =>empty($request->zip) ? '' :$request->zip ,
//                    "user_id" => $user->user_id
//                ]
//            );
//
//            $segment->track([
//                    "userId" =>  empty($user->user_id)? 'SessionTimeOut': $user->user_id,
//                    "event" => 'sign up',
//                    "email" =>  strtolower(trim($request->email)),
//                    "signup_date" => substr(Carbon::now()->toIso8601String(),0,19) ,
//                    "device_type" => 'E', //WEB
//                    "first_name" =>$request->first_name,
//                    "last_name" =>$request->last_name,
//                    "phone_number" =>$request->phone,
//                    "zip" => empty($request->zip) ? '' :$request->zip ,
//                    "user_id" => $user->user_id,
//                    "hear_about_us" => empty($request->hear_from) ? '' : $request->hear_from,
//                    "referral_code" =>  empty($request->referral_code)? '' : $request->referral_code
//                    ]
//                );



            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {

            DB::rollback();

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}