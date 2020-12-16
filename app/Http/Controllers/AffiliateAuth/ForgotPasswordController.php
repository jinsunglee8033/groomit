<?php

namespace App\Http\Controllers\AffiliateAuth;

use App\Model\Affiliate;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Log;
use Validator;
use App\Lib\Helper;
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 10/26/16
 * Time: 3:01 PM
 */
class ForgotPasswordController extends Controller
{

    public function show_verify_email_form()
    {
        return view('affiliate.forgot-password');
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

            $aff = Affiliate::whereRaw("lower(email) = '". strtolower($request->email) . "'")->first();

            if (empty($aff)) {
                return response()->json([
                    'msg' => 'The email does not exist in our system.'
                ]);
            }

            session('email', $aff->email);

            # 1. send random 6 digits to the user.
            $temp_key = mt_rand(100000, 999999);

            $subject = "[Groomit] Here is your temporary KEY.";
            //$msg = " - key : $temp_key";
            //$ret = Helper::send_html_mail($aff->email, $subject, $msg);


            $data = [];
            $data['temp_key'] = $temp_key;
            $data['email'] = $aff->email;
            $data['name'] = $aff->first_name;
            $data['subject'] = $subject;

            $ret = Helper::send_html_mail('affiliate_forgot_password', $data);

            if (!empty($ret)) {
                return response()->json([
                    'msg' => 'Failed to send verification key: ' . $ret
                ]);
            }

            # 2. save new temp key to user table
            $aff->forgot_pwd_key = $temp_key;
            $aff->save();

            $request->session()->put('email', $request->email);

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
        return view('affiliate.forgot-password-key');
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

            $aff = Affiliate::whereRaw("lower(email) = '". strtolower($request->email) . "'")->first();
            if (empty($aff)) {
                return response()->json([
                    'msg' => 'The email does not exist in our system.'
                ]);
            }

            session('email', $aff->email);

            if ($aff->forgot_pwd_key != $request->key) {
                return response()->json([
                    'msg' => 'Your temporary KEY is not correct.'
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
        return view('affiliate.update-password');
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

            $aff = Affiliate::whereRaw("lower(email) = '". strtolower($request->email) . "'")->first();
            if (empty($aff)) {
                return response()->json([
                    'msg' => 'The email does not exist in our system.'
                ]);
            }

            $aff->password = bcrypt($request->password);
            $aff->save();

            $request->session()->flush();

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