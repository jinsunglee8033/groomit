<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 7/24/18
 * Time: 11:00 AM
 */

namespace App\Http\Controllers\User\Appointment;


use App\Http\Controllers\Controller;
use App\Lib\AppointmentProcessor;
use App\Lib\Helper;
use App\Lib\Converge;
use App\Lib\CreditProcessor;
use App\Lib\PromoCodeProcessor;
use App\Model\AppointmentList;
use App\Model\AppointmentProduct;
use App\Model\Credit;
use App\Model\CCTrans;
use App\Model\Groomer;
use App\Model\UserFavoriteGroomer;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;
use Validator;

class EditController extends Controller
{
    public function show(Request $request, $appointment_id) {
        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Modify Date');
        Session::put('user.url');

        $user = \Auth::guard('user')->user();

        $app = AppointmentList::find($appointment_id);
        if (empty($app)) {
            return back()->withErrors([
                'Invalid appointment ID provided'
            ]);
        }

        $eco_packages = AppointmentProduct::where('appointment_id', $app->appointment_id)->whereIn('prod_id', [28, 29])->get();

        if( empty($app->accepted_date) ){
            $service_date =  $app->reserved_date;
            $date = Carbon::parse($service_date)->format('Y-m-d');
            $time = Carbon::parse($service_date)->format('H:i:s');
            $reserved_at = $app->reserved_at ; //2020-04-03 03:00pm - 07:00pm
        }else {
            $service_date =  $app->accepted_date ;
            $date = Carbon::parse($service_date)->format('Y-m-d');
            $time = Carbon::parse($service_date)->format('H:i:s');
            $reserved_at = '';
        }


        $time_windows = Helper::get_time_windows_by_date($date);
        $time_o = Helper::get_time_by_value($time, $reserved_at);//When reserved_at is found, it has precedence.

        $time_id = isset($time_o) ? $time_o->id : null;

//        $cancelling_fee = 0;
//        $ret = AppointmentProcessor::get_fee_amount('C', $app );
//        if(is_array($ret)){
//            $cancelling_fee = $ret['fee_amount'];
//        }

        $rescheduling_fee = 0;
        $ret = AppointmentProcessor::get_fee_amount('R', $app );
        if(is_array($ret)){
            $rescheduling_fee = $ret['fee_amount'];
        }

        // FAV GROOMER
        $favs = UserFavoriteGroomer::where('user_id', $user->user_id)->get();
        $num_favs = count($favs);

        if(count(array($favs))>0){
            foreach ($favs as $fav){
                $id = $fav->groomer_id;
                $groomer_obj = Groomer::where('groomer_id', $id)->first();
                $fav->name  = $groomer_obj->first_name;
                $fav->pic   = $groomer_obj->profile_photo;
            }
        }

        return view('user.appointment.edit', [
            'time_windows' => $time_windows,
            'appointment_id' => $appointment_id,
            'date' => $date,
            'time_id' => $time_id,
            'time' => $time,
            'time_time' => $time_o->time,
            'eco_packages' => $eco_packages,
            //'cancelling_fee' => $cancelling_fee,
            'rescheduling_fee' => $rescheduling_fee,
            'favs'          => $favs,
            'num_favs'      => $num_favs,
            'fav_type'        => $app->fav_type,
            'fav_groomer_id'        => isset($app->my_favorite_groomer) && ($app->my_favorite_groomer > 0) ? $app->my_favorite_groomer : 0
        ]);
    }
    public function show_cancel(Request $request, $appointment_id) {
        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Modify Date');
        Session::put('user.url');

        $app = AppointmentList::find($appointment_id);
        if (empty($app)) {
            return back()->withErrors([
                'Invalid appointment ID provided'
            ]);
        }

        $eco_packages = AppointmentProduct::where('appointment_id', $app->appointment_id)->whereIn('prod_id', [28, 29])->get();

        if( empty($app->accepted_date) ){
            $service_date =  $app->reserved_date;
            $date = Carbon::parse($service_date)->format('Y-m-d');
            $time = Carbon::parse($service_date)->format('H:i:s');
            $reserved_at = $app->reserved_at ; //2020-04-03 03:00pm - 07:00pm
        }else {
            $service_date =  $app->accepted_date ;
            $date = Carbon::parse($service_date)->format('Y-m-d');
            $time = Carbon::parse($service_date)->format('H:i:s');
            $reserved_at = '';
        }


        $time_windows = Helper::get_time_windows_by_date($date);
        $time_o = Helper::get_time_by_value($time, $reserved_at);//When reserved_at is found, it has precedence.

        $time_id = isset($time_o) ? $time_o->id : null;

        $cancelling_fee = 0;
        $ret = AppointmentProcessor::get_fee_amount('C', $app );
        if(is_array($ret)){
            $cancelling_fee = $ret['fee_amount'];
        }

//        $rescheduling_fee = 0;
//        $ret = AppointmentProcessor::get_fee_amount('R', $app );
//        if(is_array($ret)){
//            $rescheduling_fee = $ret['fee_amount'];
//        }

        //return view('user.appointment.edit-at', [
        return view('user.appointment.cancel', [
            'time_windows' => $time_windows,
            'appointment_id' => $appointment_id,
            'date' => $date,
            'time_id' => $time_id,
            'time' => $time,
            'time_time' => $time_o->time,
            'eco_packages' => $eco_packages,
            'cancelling_fee' => $cancelling_fee
            //'rescheduling_fee' => $rescheduling_fee
        ]);
    }
    //Reschedules by a customer(D)
    public function post(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'appointment_id' => 'required',
                'date' => 'required|date',
                'time' => 'required',
                'time_id' => 'required'
                //fav_type
                //fav_groomer_id
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

//            $package = AppointmentProduct::where('appointment_id', $request->appointment_id)->first();
//            if (!empty($package) && in_array($package->prod_id, [28, 29])) {
//                return response()->json([
//                  'msg' => 'ECO is Non-Refundable and cannot change date/time neither.'
//                ]);
//            }

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Session expired. Please login again!');
            }

            //$time = Helper::get_time_by_id($request->time);
            $time = Helper::get_time_by_id($request->time_id);
            if (empty($time)) {
                throw new \Exception('Invalid time provided');
            }

            $datetime = $request->date . ' ' . $time->time;

            $msg = AppointmentProcessor::edit($user, $request->appointment_id, $datetime, $time, $request->fav_type, $request->fav_groomer_id );

            return response()->json([
                'msg' => $msg
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function delete(Request $request) { //cancel appointment.

        $user = Auth::guard('user')->user();
        if (empty($user)) {
            return response()->json([
              'msg' => 'Session expired. Please login again!'
            ]);
        }

        $app = AppointmentList::find($request->appointment_id);
        if (empty($app)) {
            return response()->json([
              'msg' => 'Invalid appointment ID provided'
            ]);
        }

        $res = AppointmentProcessor::cancel($app, $user, $request->note);

        return response()->json($res);
    }
}