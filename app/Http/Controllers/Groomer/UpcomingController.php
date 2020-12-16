<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/15/18
 * Time: 10:58 AM
 */

namespace App\Http\Controllers\Groomer;


use App\Http\Controllers\Controller;
use App\Lib\AppointmentProcessor;
use App\Lib\Helper;
use App\Lib\ProfitSharingProcessor;
use App\Model\Address;
use App\Model\AppointmentList;
use App\Model\Groomer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpcomingController extends Controller
{

    public function getList(Request $request) {
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $from_date = empty($request->from) ? Carbon::today() : $request->from;
            $to_date = empty($request->to) ? '2099-12-31' : $request->to;

//            $distance_type = $request->distance_type;
//            $x = $request->x;
//            $y = $request->y;

            $data = DB::select("
                select 
                    a.appointment_id,
                    a.reserved_date, 
                    substr(trim(a.reserved_at), -17) as reserved_time,
                    f_get_pet_type(a.appointment_id) as pet_type,
                    f_get_package_name(a.appointment_id) as package_name,
                    concat(b.city, ',', b.zip) as address,
                    a.address_id,
                    (select count(*) from appointment_pet where appointment_id = a.appointment_id) as pet_qty,
                    a.accepted_date,
                    e.place_id,  e.place_name,d.other_name other_place_name
                from appointment_list a
                    left join address b on a.address_id = b.address_id
                    left join appointment_place d on a.appointment_id = d.appointment_id
                    left join place e on d.place_id = e.place_id
                where a.status not in ('C', 'F', 'S', 'P', 'L')
                and (
                    accepted_date is null and reserved_date >= ? or 
                    accepted_date >= ? 
                )
                and (
                    accepted_date is null and cast(reserved_date as date) <= ? or 
                    cast(accepted_date as date) <= ? 
                )
                and a.accepted_date is not null
                and a.groomer_id = ?
                order by a.accepted_date 
            ", [$from_date, $from_date, $to_date, $to_date, $groomer->groomer_id]);

            if (count($data) > 0) {
                foreach ($data as $o) {
                    $address = Address::find($o->address_id);
                    $o->address_info = $address;
                    //$o->earning = ProfitSharingProcessor::getProfit($o->appointment_id);
                    $o->estimated_earning = ProfitSharingProcessor::getEstimatedProfit($o->appointment_id);
                    $o->estimated_bonus = ProfitSharingProcessor::getEstimatedBonus($o->appointment_id);

                    //Actually, this will never be used, because GA uses /api/v2/groomer/availability/get
//                    $o->distance = 'N/A';
//                    if( in_array($distance_type, ['C','H','P'] ) ){
//                        //Get Distance in miles
//                        $o->distance = Helper::get_distance_to_groomer( $o->groomer_id, $o->appointment_id, $distance_type, $x, $y );
//                    }


                }
            }


            return response()->json([
                'msg' => '',
                'data' => $data
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
    }

    public function onTheWay(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required'
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $app = AppointmentList::where('appointment_id', $request->appointment_id)
                ->where('groomer_id', $groomer->groomer_id)
                ->first();

            if ($app->status != 'D') {
                switch ($app->status) {
                    case 'R':
                        return response()->json([
                          'msg' => 'Fail to charge credit card. Please be sure to notify Customer Care.'
                        ]);
                        break;
                    case 'C':
                        return response()->json([
                          'msg' => 'The appointment was already cancelled.'
                        ]);
                        break;
                    case 'O':
                        return response()->json([
                          'msg' => ''
                        ]);
                        break;
                    default:
                        return response()->json([
                          'msg' => 'This was already processed. Please refresh your page again.'
                        ]);
                        break;
                }
            }

            $ret = AppointmentProcessor::groomer_on_the_way(
                $app->appointment_id,
                $groomer->first_name . ' '. $groomer->last_name,
                $groomer->groomer_id,
                'G'
            );

            if (!empty($ret['error_msg'])) {
                throw new \Exception($ret['error_msg'], $ret['error_code']);
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

    public function getDetail(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required'
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $app = AppointmentList::find($request->appointment_id);
            if (empty($app)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provied'
                ]);
            }

            if ($app->groomer_id != $groomer->groomer_id) {
                return response()->json([
                    'msg' => 'This appointment is not assigned to you'
                ]);
            }

            if ($app->status == 'P') {
                return response()->json([
                    'msg' => 'This appointment status is completed already'
                ]);
            }

            $app = AppointmentProcessor::get_info($app);

            return response()->json([
                'msg' => '',
                'info' => $app
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
    }

}