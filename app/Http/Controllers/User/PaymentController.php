<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/14/18
 * Time: 3:54 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\PaymentProcessor;
use App\Model\Address;
use App\Model\UserBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    public function show() {
        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Payments');

        $user_id = Auth::guard('user')->user()->user_id;

        $payments = UserBilling::where('user_id', $user_id)
            ->where('status', 'A')
            ->orderBy('billing_id', 'desc')->get();

        $years = Helper::get_expire_years();
        $months = Helper::get_expire_months();
        $states = Helper::get_states();

        return view('user.payments', [
            'payments' => $payments,
            'years' => $years,
            'months' => $months,
            'states' => $states
        ]);
    }

    public function add(Request $request) {
        try {

            $v = Validator::make($request->all(), [
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
                    'msg' => $msg
                ]);
            }

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [USR_ADDBL]'
                ]);
            }

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

    public function update(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'billing_id' => 'required',
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
                    'msg' => $msg
                ]);
            }

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [USR_ADDBL]'
                ]);
            }

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

    public function load(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'billing_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $card = UserBilling::where('user_id', Auth::guard('user')->user()->user_id)
                ->where('billing_id', $request->billing_id)
                ->where('status', 'A')
                ->first();

            if (empty($card)) {
                return response()->json([
                    'msg' => 'Invalid payment ID provided'
                ]);
            }

            return response()->json([
                'msg' => '',
                'data' => $card
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function delete_card(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'billing_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [USR_ADDBL]'
                ]);
            }

            $user_billing = UserBilling::find($request->billing_id);
            if (empty($user_billing)) {
                return response()->json([
                    'msg' => 'Invalid billing ID provided'
                ]);
            }

            if ($user_billing->user_id != $user->user_id) {
                return response()->json([
                    'msg' => 'Provided token does not match'
                ]);
            }

            $user_billing->status = 'D';
            $user_billing->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_service_address(Request $request) {
        try {

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [USR_ADDBL]'
                ]);
            }

            $service_address = Address::where('user_id', $user->user_id)
                ->where('default_address', 'Y')
                ->where('status', 'A')
                ->first();

            if(empty($service_address)){
                return response()->json([
                    'msg' => '-1'
                ]);
            }

            return response()->json([
                'msg' => '',
                'data' => $service_address
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}