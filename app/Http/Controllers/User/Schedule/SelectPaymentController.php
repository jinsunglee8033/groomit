<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/14/18
 * Time: 2:46 PM
 */

namespace App\Http\Controllers\User\Schedule;


use App\Http\Controllers\Controller;
use App\Lib\CreditProcessor;
use App\Lib\Helper;
use App\Lib\PromoCodeProcessor;
use App\Lib\ScheduleProcessor;
use App\Model\PromoCode;
use App\Model\UserBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SelectPaymentController extends Controller
{

    public function show() {

        $payments = UserBilling::where('user_id', Auth::guard('user')->user()->user_id)
            ->where('status', 'A')
            ->orderBy('billing_id','desc')->get();


        $years = Helper::get_expire_years();
        $months = Helper::get_expire_months();
        $states = Helper::get_states();

        $payment = ScheduleProcessor::getPayment();
        if (empty($payment)) {
            if (count($payments) > 0) {
                foreach ($payments as $o) {
                    if ($o->default_card == 'Y') {
                        ScheduleProcessor::setPayment($o);
                        break;
                    }
                }
            }
        }

        $pet_type = ScheduleProcessor::getCurrentPetType();
        if (empty($pet_type)) {
            return redirect('/user')->withErrors([
                'exception' => 'Your session has been expired. Please try again!'
            ]);
        }

        $ret = ScheduleProcessor::getTotal();

        $sub_total = $ret['sub_total'];
        $safety_insurance = $ret['safety_insurance'];
        $sameday_booking = $ret['sameday_booking'];
        $fav_fee = $ret['fav_fee'];
        $tax = $ret['tax'];
        $promo_amt = $ret['promo_amt'];
        $discount_applied = $ret['discount_applied'];
        $credit_amt = $ret['credit_amt'];
        $total = $ret['total'];
        $available_credit = $ret['available_credit'];
        $new_credit = $ret['new_credit'];
        $use_credit = $ret['use_credit'];

        $promo = ScheduleProcessor::getPromoCode();
        $promo_code = '';
        if (!empty($promo)) {
            $promo_code = strtoupper($promo->code);
        }

        // $available_credit = CreditProcessor::getAvailableCredit(Auth::guard('user')->user()->user_id);

        return view('user.schedule.select-payment', [
            'payments' => $payments,
            'years' => $years,
            'months' => $months,
            'states' => $states,
            'sub_total' => $sub_total,
            'safety_insurance' => $safety_insurance,
            'sameday_booking' => $sameday_booking,
            'fav_fee' => $fav_fee,
            'tax' => $tax,
            'promo_amt' => $promo_amt,
            'discount_applied'  => $discount_applied,
            'credit_amt' => $credit_amt,
            'total' => $total,
            'new_credit' => $new_credit,
            'available_credit' => $available_credit,
            'promo_code' => $promo_code,
            'use_credit' => $use_credit,
            'pets' => ScheduleProcessor::getPets(),
            'address' => ScheduleProcessor::getAddress()
        ]);
    }

    public function post(Request $request) {
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

            ScheduleProcessor::setPayment($card);

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function useCredit(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'use_credit' => 'required|in:Y,N'
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

            ScheduleProcessor::setUseCredit($request->use_credit);
            $ret = ScheduleProcessor::getTotal();

            return response()->json([
                'msg' => '',
                'sub_total' => $ret['sub_total'],
                'safety_insurance' => $ret['safety_insurance'],
                'sameday_booking' => $ret['sameday_booking'],
                'fav_fee' => $ret['fav_fee'],
                'tax' => $ret['tax'],
                'promo_amt' => $ret['promo_amt'],
                'discount_applied' => $ret['discount_applied'],
                'credit_amt' => $ret['credit_amt'],
                'total' => $ret['total'],
                'new_credit' => $ret['new_credit'],
                'available_credit' => $ret['available_credit']
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function applyCode(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'promo_code' => ''
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
                    'msg' => "Please login first"
                ]);
            }

            if (!empty($request->promo_code)) {
                $promo_code = PromoCode::find(strtoupper($request->promo_code));
                if (empty($promo_code)) {
                    ### clear discount ###
                    ScheduleProcessor::setPromoCode(null);
                    return response()->json([
                        'msg' => 'Promotion code you entered does not exists in our system'
                    ]);
                }

                $package_list = null;
                $pets = ScheduleProcessor::getPets();
                if (!empty($pets)) {
                    $package_list = [];
                    foreach ($pets as $pet) {
                        $package_list[] = isset($pet->info->package) ? $pet->info->package->prod_id : '';
                    }
                }
                $msg = PromoCodeProcessor::checkIfUsed(Auth::guard('user')->user()->user_id, $promo_code, null, $package_list);
                if (!empty($msg)) {
                    ### clear discount ###
                    ScheduleProcessor::setPromoCode(null);
                    return response()->json([
                        'msg' => $msg
                    ]);
                }
                Session::put('schedule.promo', $promo_code);
                //ScheduleProcessor::setPromoCode($promo_code);
            } else {
                ScheduleProcessor::setPromoCode(null);
            }

            $ret = ScheduleProcessor::getTotal();

            return response()->json([
                'msg' => '',
                'sub_total' => $ret['sub_total'],
                'safety_insurance' => $ret['safety_insurance'],
                'sameday_booking' => $ret['sameday_booking'],
                'fav_fee' => $ret['fav_fee'],
                'tax' => $ret['tax'],
                'promo_amt' => $ret['promo_amt'],
                'discount_applied' => $ret['discount_applied'],
                'credit_amt' => $ret['credit_amt'],
                'total' => $ret['total'],
                'new_credit' => $ret['new_credit'],
                'available_credit' => $ret['available_credit']
            ]);

        } catch (\Exception $ex) {
            Helper::log('#### EXCEPTION ####', $ex->getTraceAsString());

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function thankYou() {
        return view('user.schedule.thank-you', []);
    }

}