<?php

namespace App\Http\Controllers\AffiliateAuth;

use App\Model\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Log;
use Validator;
use App\Lib\Helper;

class SpawForgotPasswordController extends Controller
{

    public function show_verify_email_form()
    {
        return view('affiliate.spaw-forgot-password');
    }

    public function verify_email(Request $request) {
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

            $aff = User::whereRaw("lower(email) = '". strtolower($request->email) . "'")->first();

            if (empty($aff)) {
                return response()->json([
                    'msg' => 'We are sorry, but your email does not belongs to our lists. Please dobule check your email.'
                ]);
            }

            session('email', $aff->email);

            # 1. send random 6 digits to the user.
            $temp_key = mt_rand(100000, 999999);

            $subject = "[Groomit/Spaw]  Here is your Verification KEY. ";
            //$msg = " - key : $temp_key";
            //$ret = Helper::send_html_mail($aff->email, $subject, $msg);


            $data = [];
            $data['temp_key'] = $temp_key;
            $data['email'] = $aff->email;
            $data['name'] = $aff->first_name;
            $data['subject'] = $subject;

            //$ret = Helper::send_html_mail('affiliate_forgot_password', $data);
             $ret = Helper::send_html_mail('spaw_affiliate_forgot_password', $data);

            if (!empty($ret)) {
                return response()->json([
                    'msg' => 'Failed to send verification key: ' . $ret
                ]);
            }

            # 2. save new temp key to user table
            $aff->forgot_pwd_key = $temp_key;
            $aff->save();

            $request->session()->put('email', strtolower($request->email) );

            return response()->json([
                'msg' => ''
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function show_verify_key_form()
    {
        return view('affiliate.spaw-forgot-password-key');
    }

    public function verify_key(Request $request) {
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

            $aff = User::whereRaw("lower(email) = '". strtolower($request->email) . "'")->first();
            if (empty($aff)) {
                return response()->json([
                    'msg' => 'The email does not exist in our system.'
                ]);
            }

            session('email', $aff->email);

            if ($aff->forgot_pwd_key != $request->key) {
                return response()->json([
                    'msg' => 'Your Verification KEY is not correct.'
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

    public function show_update_password_form()
    {
        return view('affiliate.spaw-update-password');
    }

    public function update_password(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required|confirmed',
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

            $aff = User::whereRaw("lower(email) = '". strtolower($request->email) . "'")->first();
            if (empty($aff)) {
                return response()->json([
                    'msg' => 'The email does not exist in our system.'
                ]);
            }
            if( session()->has('email') &&
                strtolower($request->email) != session('email') ) {
                return response()->json([
                    'msg' => 'Your session is expired. Please try again'
                ]);
            }

            $aff->passwd = \Crypt::encrypt($request->password);
            $aff->save();

            $request->session()->flush();

            //Try to login
            $credential = [
                'email' => strtolower($request->email),
                'password' => $request->password
            ];

            if(!Auth::guard('user')->attempt($credential)) {
                return response()->json([
                    'msg' => 'We cannot process your request. Please contact us with your email.'
                ]);

            }else {
                return response()->json([
                    'msg' => ''
                ]);
            }

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}