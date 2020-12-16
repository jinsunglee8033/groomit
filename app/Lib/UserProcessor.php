<?php

namespace App\Lib;

use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\User;
use App\Model\PromoCode;
use App\Model\UserPhoto;
use Carbon\Carbon;
use DB;

class UserProcessor
{

    public static function signup() {

    }

    public function signup_user($request) { //Not to be used any longer

        DB::beginTransaction();

        try {


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
            $user->hear_from =  empty($request->heard_from) ? $request->hear_from : $request->heard_from;

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
//                //Helper::send_mail('tech@groomit.me', '[groomit][UserProcessorSignup_user] user device token is empty upon signup', $user->email);
//            }

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
//            Helper::log('### REFERRAL CODE ###', $user->referral_code);
//            Helper::log('### REFERRAL AMOUNT ###', $user->referral_amount);

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


    public static function get_referral_code($user_id) {


        $rc = PromoCode::where('type', 'R')
            ->where('user_id', $user_id)
            ->first();

        if (empty($rc)) {
            $referral_code = self::generate_referral_code($user_id);

            $new_rc = new PromoCode;
            $new_rc->code = $referral_code;
            $new_rc->type = 'R';
            $new_rc->amt_type = 'A';
            $new_rc->amt = env('REFERRAL_CODE_AMT'); //Since 02/26/2020
            $new_rc->user_id = $user_id;
            $new_rc->status = 'A';
            $new_rc->first_only = 'Y';
            $new_rc->no_insurance = 'N';
            $new_rc->include_tax = 'N';
            $new_rc->cdate = Carbon::now();
            $new_rc->save();

            $referral_amount =  env('REFERRAL_CODE_AMT'); //$15 since 07/03/2020 //Since 02/26/2020

        } else {

            Helper::log('### RC ###', $rc->code);
            $referral_code = $rc->code;
            $referral_amount = $rc->amt;

        }

        //return $referral_code;
        return [ 'referral_code' =>  $referral_code,
                 'referral_amount' => $referral_amount
        ];
    }


    private static function generate_referral_code($user_id) {
        # 1. First name + last 4 digit of phone #
        # 2. increase by 1 when duplicated
        # 3. when phone is not there, total random code
        $user = User::find($user_id);
        if (empty($user)) {
            throw new \Exception('Invalid user ID provied while generating referral code: ' . $user_id);
        }

        if (empty(trim($user->phone)) || empty(trim($user->first_name))) {
            return self::generate_random_code();
        }

        $last_4_digit_of_phone = substr(trim($user->phone), -4);
        $referal_code = str_replace(' ', '*', strtoupper($user->first_name)) . $last_4_digit_of_phone;

        while (true) {
            $rc = PromoCode::whereRaw('code = ?', [strtoupper($referal_code)])->first();
            if (empty($rc)) {
                break;
            }

            $last_4_digit_of_phone = (int)$last_4_digit_of_phone + 1;
            $referal_code = str_replace(' ', '*', strtoupper($user->first_name)) . $last_4_digit_of_phone;
        }

        return $referal_code;
    }


    ## random 10 digit referral code
    private static function generate_random_code($length = 10) {
        //$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@$%&";
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $stop = false;

        while (!$stop) {
            $referral_code = '';
            for ($i = 0; $i < $length; $i++) {
                $referral_code .= $chars{mt_rand(0, strlen($chars)-1)};
            }

            $rc = PromoCode::whereRaw('code = ?', [strtoupper($referral_code)])->first();
            if (empty($rc)) {
                $stop = true;
            }
        }

        return $referral_code;
    }



}