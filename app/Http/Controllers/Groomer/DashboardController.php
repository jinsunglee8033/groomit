<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 4/9/18
 * Time: 10:38 AM
 */

namespace App\Http\Controllers\Groomer;


use App\Http\Controllers\Controller;
use App\Lib\AppointmentProcessor;
use App\Lib\EarningProcessor;
use App\Lib\Helper;
use App\Model\AppointmentList;
use App\Model\Groomer;
use App\Model\GroomerArrived;
use App\Model\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class DashboardController extends Controller
{

    public function getInfo(Request $request) {
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

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [MSGS]'
                ]);
            }

            ### 1. my appointments ###
            $my_appointment_cnt = AppointmentList::where('groomer_id', $groomer->groomer_id)
                ->whereIn('status', ['D', 'O'])
                ->where('accepted_date', '>=', Carbon::now()->subMinutes(60*24) )
                ->count();

            ### 2. next appointment ###
            $ap = AppointmentList::where('groomer_id', $groomer->groomer_id)
                ->whereIn('status', ['D', 'O'])
                ->where('accepted_date', '>=', Carbon::now()->subMinutes(60*24) )
                ->orderBy('accepted_date', 'asc')
                ->first();

            if (empty($ap)) {
                $next_appointment = null;
            } else {
                $next_appointment = AppointmentProcessor::get_info($ap);
            }

            ### 3. total earning ###
            //$current_earning = EarningProcessor::getCurrentEarning($groomer->groomer_id);
            $last_3_weeks_earning = EarningProcessor::getLast3WeeksEarning($groomer->groomer_id);
            $total_earning = $last_3_weeks_earning->earning;//$current_earning->earning + $last_3_weeks_earning->earning;

            ### 4. Unread Message ###
            $unread_cnt = Message::whereIn('receiver_type', ['B', 'G'])
                ->where('receiver_id', $groomer->groomer_id)
                ->whereNull('read_date')
                ->count();

            ### 5. Open Appointment Cnt ###
            $open_appointments = AppointmentProcessor::get_open_appointments2($groomer->groomer_id);
            $open_ap_cnt = count($open_appointments);

            ### 6. Arrived ### it's moved under next_appointment
//            if($next_appointment == null){
//                $arrived = false;
//            } else {
//                $ga = GroomerArrived::where('appointment_id', $ap->appointment_id)
//                    ->where('groomer_id', $groomer->groomer_id)
//                    ->first();
//                if (empty($ga)) {
//                    $arrived = false;
//                } else {
//                    $arrived = true;
//                }
//            }

            return response()->json([
                'msg' => '',
                'my_appointment_cnt' => $my_appointment_cnt,
                'next_appointment' => $next_appointment,
                'total_earning' => $total_earning,
                'unread_cnt' => $unread_cnt,
                'open_appointment_cnt' => $open_ap_cnt
                //'arrived' => $arrived
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

}