<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/13/18
 * Time: 5:51 PM
 */

namespace App\Http\Controllers\User\API;


use App\Http\Controllers\Controller;
use App\Model\AllowedZip;
use App\Model\ZipQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Lib\AddressProcessor;
use App\Lib\Helper;

use App\Model\Address;
use App\Model\User;

use Carbon\Carbon;
use DB;

class AddressController extends Controller
{
    public function show(Request $request) {
        ############### START VALIDATION ###########
        ############################################
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

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $addresses = DB::select("
            select address_id, name, address1, address2, city, state, zip, lat, lng, default_address
              from address
             where user_id = :user_id
               and status = 'A'
            ", [
          'user_id' => $user->user_id
        ]);

        Helper::log('### addresses ###', [
          $addresses
        ]);

        return response()->json([
            'code' => '0',
            'addresses' => $addresses
        ]);
    }

    public function save(Request $request) {
        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key'     => 'required',
              'token'       => 'required',
              'address1'    => 'required',
              'address2'    => '',
              'city'        => 'required',
              'state'       => 'required',
              'zip'         => 'required|regex:/^\d{5}$/'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
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
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg'  => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            if (empty($request->address_id)) {
                $ret = AddressProcessor::add(
                  $user->user_id,
                  $request->address1,
                  $request->address2,
                  $request->city,
                  $request->state,
                  $request->zip,
                  $request->default_address
                );
            } else {
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
            }

            if (!empty($ret['msg'])) {
                return response()->json([
                  'code'    => '-2',
                  'msg'     => $ret['msg']
                ]);
            }

            return response()->json([
              'code'    => '0',
              'msg'     => ''
            ]);


        } catch (\Exception $ex) {
            return response()->json([
              'code'    => '-9',
              'msg'     => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function remove(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key'     => 'required',
              'token'       => 'required',
              'address_id'  => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
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
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg'  => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $address = Address::find($request->address_id);
            if (!empty($address)) {
                if ($address->user_id == $user->user_id) {
                    $address->status = 'D';
                    $address->update();
                }
            }

            return response()->json([
                'code'    => '0',
                'msg'     => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code'    => '-9',
                'msg'     => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    public function check_zip(Request $request) {
        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key'     => 'required',
              'zip'         => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
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

            ############################################
            ###############  END VALIDATION  ###########

            if( !isset($request->zip) || strlen($request->zip) > 10){
                return response()->json([
                    'code' => '-9',
                    'msg' => 'We are sorry but we cannot recognize your location. Please try again.'
                ]);
                exit;
            }

            ### save zip code query log for later use ###
            $q = new ZipQuery();
            $q->path  = isset($request->path) ? $request->path : '-';
            $q->zip   = $request->zip;
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
                  'code' => '-1',
                  'msg' => 'We are sorry but your address does not belongs to our available service areas.'
                ]);
            } else {
                if($q->state == ''){ //if no input on state
                    $q->city = $allowed_zip->city_name;
                    $q->state = $allowed_zip->state_abbr;
                    $q->save();
                }

                if ($allowed_zip->available != 'x') {
                    return response()->json([
                      'code' => '-1',
                      'msg' => 'We are sorry but your address does not belongs to our available service areas. ' . $allowed_zip->allowed
                    ]);
                }
            }

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