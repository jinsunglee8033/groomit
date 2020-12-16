<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 11/8/18
 * Time: 6:22 PM
 */

namespace App\Http\Controllers\User\API;

use App\Lib\Helper;

use App\Lib\ImageProcessor;
use App\Lib\UserProcessor;
use App\Model\User;
use App\Model\UserCertificate;
use App\Model\UserPhoto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Log;
use Carbon\Carbon;

class ProfileController extends Controller
{

    public function update(Request $request) {
        try {

            $v = Validator::make($request->all(), [
              'api_key'     => 'required',
              'token'       => 'required',
              'first_name'  => 'required',
              'last_name'   => 'required',
              'phone'       => 'required|regex:/^\d{10}$/'
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

            $email = \Crypt::decrypt($request->token);

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'code' => '-2',
                    'msg'  => 'Your session has expired. Please login again. [USR_UPD]'
                ]);
            }

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $user->first_name   = $request->first_name;
            $user->last_name    = $request->last_name;
            $user->name         = $request->first_name . ' ' . $request->last_name;
            $user->phone        = $request->phone;
            if (!empty($request->zip)) {
                $user->zip      = $request->zip;
            }
            if (!empty($request->dog)) {
                $user->dog      = $request->dog;
            }
            if (!empty($request->cat)) {
                $user->cat      = $request->cat;
            }

            $user->save();

            if (!empty($request->photo)) {
                $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
                if (empty($user_photo)) {
                    $user_photo = new UserPhoto;
                    $user_photo->user_id = $user->user_id;
                }
                $user_photo->photo = ImageProcessor::optimize(base64_decode($request->photo));
                $user_photo->save();

                //$user->photo = base64_encode($user_photo->photo); //What's this for ??
                try{
                    $user->photo = base64_encode($user_photo->photo);
                } catch (\Exception $ex) {
                    $user->photo = $user_photo->photo ;
                }
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

                //$user->certificate = base64_encode($certificate->photo); //What's this for ??
                try{
                    $user->certificate = base64_encode($certificate->photo);
                } catch (\Exception $ex) {
                    $user->certificate = $certificate->photo ;
                }
            }

            //$user->referral_code = UserProcessor::get_referral_code($user->user_id);
            $referral_arr = UserProcessor::get_referral_code($user->user_id);
            $user->referral_code = $referral_arr['referral_code'];
            $user->referral_amount = $referral_arr['referral_amount'];

            return response()->json([
                'code'  => '0',
                'msg'   => '',
                'user'  => $user
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function delete(Request $request) {
        try {
            if (getenv('APP_ENV') == 'production') {
                return response()->json([
                  'code' => '-1',
                  'msg'  => 'Can not delete the profile.'
                ]);
            }

            $v = Validator::make($request->all(), [
              'api_key'     => 'required',
              'email'       => 'required'
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

            $user = User::where('email', $request->email)->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg'  => 'Your session has expired. Please login again. [USR_UPD]'
                ]);
            }
            $user->email = $user->email . ' deleted ' . Carbon::now();
            $user->update();

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            return response()->json([
              'code'  => '0',
              'msg'   => '',
              'user'  => $user
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_device_token(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key'   => 'required',
              'token'     => 'required',
              'device_token' => 'required'
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

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            $user->device_token = $request->device_token;
            $user->mdate = Carbon::now();
            $user->save();

            return response()->json([
              'code' => '0',
              'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}