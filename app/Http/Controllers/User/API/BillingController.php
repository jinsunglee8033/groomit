<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/14/18
 * Time: 4:30 AM
 */

namespace App\Http\Controllers\User\API;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\PaymentProcessor;
use App\Model\UserBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Model\User;

class BillingController extends Controller
{

    public function show(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
          'api_key' => 'required',
          'token'   => 'required'
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

        $billings = UserBilling::where('user_id', $user->user_id)
          ->where('status', 'A')
          ->orderBy('default_card', 'desc')
          ->orderBy('billing_id', 'desc')
          ->get();

        $years  = Helper::get_expire_years();
        $months = Helper::get_expire_months();
        $states = Helper::get_states();

        return response()->json([
            'code'      => '0',
            'billings'  => $billings,
            'years'     => $years,
            'months'    => $months,
            'states'    => $states
        ]);
    }

    public function save(Request $request) {
        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              //'billing_id' => 'numeric|min:1|max:1000000',
              'card_number' => 'required|regex:/^\d{15,16}$/',
              'card_holder' => 'required',
              'expire_mm' => 'required|regex:/^\d{2}$/',
              'expire_yy' => 'required|regex:/^\d{2}$/',
              'cvv' => 'required|regex:/^\d{3,4}$/',
              'address1' => 'required',
              'city' => 'required',
              'state' => 'required',
              'zip' => 'required|regex:/^\d{5}$/',
              'default_card' => ''
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

            if (empty($request->billing_id)) {
                $ret = PaymentProcessor::add_card(
                  $user->user_id,
                  $request->card_holder,
                  $request->card_number,
                  $request->expire_mm,
                  $request->expire_yy,
                  $request->cvv,
                  $request->address1,
                  $request->address2,
                  $request->city,
                  $request->state,
                  $request->zip,
                  $request->default_card
                );
            } else {
                $ret = PaymentProcessor::update_card(
                  $user->user_id,
                  $request->billing_id,
                  $request->card_holder,
                  $request->card_number,
                  $request->expire_mm,
                  $request->expire_yy,
                  $request->cvv,
                  $request->address1,
                  $request->address2,
                  $request->city,
                  $request->state,
                  $request->zip,
                  $request->default_card
                );
            }

            if (!empty($ret['msg'])) {
                return response()->json([
                  'code' => '-1',
                  'msg'  => $ret['msg']
                ]);
            }

            return response()->json([
              'code' => '0',
              'msg'  => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg'  => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function set_default(Request $request) {

        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'billing_id' => 'required'
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

            $billing = UserBilling::find($request->billing_id);

            if (empty($billing) || $billing->status != 'A') {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'The billing is not available.'
                ]);
            }

            if ($billing->user_id != $user->user_id) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'The billing is not belong to you.'
                ]);
            }

            PaymentProcessor::set_default_card($billing);

            return response()->json([
              'code' => '0',
              'msg'  => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg'  => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function remove(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'billing_id' => 'required'
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

            $billing = UserBilling::find($request->billing_id);
            if (empty($billing)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'The billing is not available.'
                ]);
            }

            if ($billing->user_id != $user->user_id) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'The billing is not belong to you.'
                ]);
            }

            $billing->status = 'D';
            $billing->update();

            return response()->json([
              'code' => '0',
              'msg'  => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg'  => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}