<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/3/16
 * Time: 5:29 PM
 */

namespace App\Http\Controllers;

use App\Lib\AddressProcessor;
use App\Model\ZipQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use App\Lib\Helper;
use App\Model\User;
use App\Model\Address;
use App\Model\AllowedZip;
use Log;
use DB;

class AddressController extends Controller
{
    public function get_by_id(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'address_id' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }


            $address = Address::where("address_id",$request->address_id)->where('status', 'A')->first();

            return response()->json([
                'msg' => '',
                'address' => $address,
                'phone' => $user->phone,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function add_address(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|regex:/^\d{5}$/s',
                'default_address' => 'required|in:Y,N',
                'phone' => 'required'
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

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [ADR_ADD]'
                ]);
            }

            $user->phone = $request->phone;
            if (!empty($request->first_name)) $user->first_name = $request->first_name;
            if (!empty($request->last_name)) $user->last_name = $request->last_name;
            $user->save();

            $ret = AddressProcessor::add(
                $user->user_id,
                $request->address1,
                $request->address2,
                $request->city,
                $request->state,
                $request->zip,
                $request->default_address
            );

            if (!empty($ret['msg'])) {
                return response()->json([
                    'msg' => $ret['msg']
                ]);
            }

            $address = $ret['address'];

            return response()->json([
                'msg' => '',
                'address_id' => $address->address_id
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_address(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|regex:/^\d{5}$/s',
                'default_address' => 'required|in:Y,N',
                'phone' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [ADR_UPD]'
                ]);
            }
            $user->phone = $request->phone;
            if (!empty($request->first_name)) $user->first_name = $request->first_name;
            if (!empty($request->last_name)) $user->last_name = $request->last_name;
            $user->save();


            $ret = AddressProcessor::update(
                $user->user_id,
                $request->address_id,
                $request->address1,
                $request->address2,
                $request->city,
                $request->state,
                $request->zip,
                $request->default_address
            );

            if (!empty($ret['msg'])) {
                return response()->json([
                    'msg' => $ret['msg']
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

    public function remove_address(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'address_id' => 'required'
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

            $address = Address::where('address_id', $request->address_id)->first();
            if (empty($address)) {
                return response()->json([
                    'msg' => 'Invalid address ID provided'
                ]);
            }

            //$ret = $address->delete();
            $address->status = 'D';
            $address->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_user_address(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [ADR_ADRS]'
                ]);
            }


            $address = Address::where('user_id', $user->user_id)
                ->where('status', 'A')
                ->orderBy('default_address', 'desc')
                ->get();

            return response()->json([
                'msg' => '',
                'addresses' => $address,
                'phone' => $user->phone,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function check_zip(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                //'token' => 'required',
                'zip' => 'required'
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

            /*$email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [ADR05]'
                ]);
            }*/

            ### save zip code query log for later use ###
            if( !isset($request->zip) || strlen($request->zip) > 10){
                return response()->json([
                    'msg' => 'We are sorry but we cannot recognize your location. Please try again'
                ]);
                exit;
            }

            $q = new ZipQuery;
            $q->path = isset($request->path) ? $request->path : '-';
            $q->zip = $request->zip;
            $q->address1 = isset($request->address1)? $request->address1 : '' ;
            $q->city = isset($request->city)? $request->city : '' ;
            $q->state =isset($request->state)? $request->state : '' ;
            $q->full_address =isset($request->address)? $request->address : '' ;
            $q->cdate = Carbon::now();
            $q->save();

            ### check zip code ###
            $allowed_zip = AllowedZip::where('zip', $request->zip)->first();
            if (empty($allowed_zip)) {
                return response()->json([
                    'msg' => 'We are sorry but your address does not belongs to our available service areas. '
                ]);
            } else {
                if($q->state == ''){ //if no input on state
                    $q->city = $allowed_zip->city_name;
                    $q->state = $allowed_zip->state_abbr;
                    $q->save();
                }
                if ($allowed_zip->available != 'x') {
                    return response()->json([
                        'msg' => 'We are sorry but your address does not belongs to our available service areas. ' . $allowed_zip->allowed
                    ]);
                }
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
}