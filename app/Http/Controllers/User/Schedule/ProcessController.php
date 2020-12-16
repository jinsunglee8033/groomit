<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/15/18
 * Time: 2:35 PM
 */

namespace App\Http\Controllers\User\Schedule;


use App\Http\Controllers\Controller;
use App\Lib\AppointmentProcessor;
use App\Lib\Helper;
use App\Lib\ScheduleProcessor;
use App\Model\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ProcessController extends Controller
{

    public function process() {
        try {

            $ar = new \stdClass();

            $date = ScheduleProcessor::getDate();
            $time = ScheduleProcessor::getTime();

            $pets = ScheduleProcessor::getPets();

            $size = ScheduleProcessor::getCurrentSize();
            $address = ScheduleProcessor::getAddress();

            $payment = ScheduleProcessor::getPayment();

            $ret = ScheduleProcessor::getTotal();

            $sub_total = $ret['sub_total'];
            $credit_amt = $ret['credit_amt'];
            $promo = ScheduleProcessor::getPromoCode();
            $promo_amt = $ret['promo_amt'];
            $tax = $ret['tax'];
            $safety_insurance = $ret['safety_insurance'];
            $sameday_booking = $ret['sameday_booking'];
            $fav_fee = $ret['fav_fee'];
            $total = $ret['total'];
            $new_credit = $ret['new_credit'];

            $place = ScheduleProcessor::getPlace();
            $place_other = ScheduleProcessor::getPlaceOther();

            // FAV GROOMER
            $fav_groomer = ScheduleProcessor::getFavGroomer();
            $fav_groomer_id = ScheduleProcessor::getFavGroomer_id();

            $v = Validator::make(compact(
                'date','time', 'pets', 'address', 'payment'
            ), [
                'date' => 'required|date',
                'time' => 'required',
                'pets' => 'required',
                'address' => 'required',
                'payment' => 'required'
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

            $ar->datetime = $date . ' ' . $time->time;
            $ar->time = $time;
            $ar->pet = $pets;
            $ar->size = $size;
            $ar->address = $address;
            $ar->payment = $payment;

            $ar->sub_total = $sub_total;
            $ar->credit_amt = $credit_amt;
            $ar->promo_code = isset($promo) ? $promo->code : '';
            $ar->promo_amt = $promo_amt;
            $ar->tax = $tax;
            $ar->safety_insurance = $safety_insurance;
            $ar->sameday_booking = $sameday_booking;
            $ar->fav_fee = $fav_fee;
            $ar->total = $total;
            $ar->new_credit = $new_credit;
            $ar->place_id = $place;
            $ar->place_other_name = $place_other;

//          FAV GROOOMER TYPE
            $ar->fav_type = $fav_groomer;
            $ar->fav_groomer_id = $fav_groomer_id;

            $user_id = Auth::guard('user')->user()->user_id;
            $user = User::find($user_id);
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has been expired. Please login again!'
                ]);
            }

            ## order_from : D => desktop, A => app ###
            $ret = AppointmentProcessor::add_appointment($ar, $user, 'D');
            if (!empty($ret['msg'])) {
                return response()->json([
                    'msg' => $ret['msg']
                ]);
            }

            ### clear session for schedule ###
            ScheduleProcessor::clearAll();

            return response()->json([
                'msg' => '',
                'appointment_id' => empty($ret['appointment_id']) ? '' : $ret['appointment_id']
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function clear($pet_id = null) {

        ScheduleProcessor::removePetByID($pet_id);

        return redirect('/user/home');
    }

}