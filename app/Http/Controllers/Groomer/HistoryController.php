<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/15/18
 * Time: 10:38 AM
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

class HistoryController extends Controller
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

            $sdate = $request->from;
            $edate = $request->to;

            if (empty($sdate)) {
                $sdate = Carbon::today()->subMonths(2);
            }

            if (empty($edate)) {
                $edate = Carbon::today()->addDay();
            }

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
                    ifnull(a.tip, 0) tip,
                    a.accepted_date,
                    e.place_id,  e.place_name,d.other_name other_place_name
                from appointment_list a
                    left join address b on a.address_id = b.address_id
                    left join appointment_place d on a.appointment_id = d.appointment_id
                    left join place e on d.place_id = e.place_id
                where a.status = 'P'
                and a.accepted_date is not null
                and a.groomer_id = :groomer_id
                and a.accepted_date >= :sdate
                and a.accepted_date <= :edate
                order by a.accepted_date desc
            ", [
                'groomer_id' => $groomer->groomer_id,
                'sdate' => $sdate,
                'edate' => $edate
            ]);

            if (count($data) > 0) {
                foreach ($data as $o) {
                    $address = Address::find($o->address_id);
                    $o->address_info = $address;

                    $o->earning = ProfitSharingProcessor::getProfit($o->appointment_id);
                    //$o->estimated_earning = ProfitSharingProcessor::getEstimatedProfit($o->appointment_id);
                }
            }

            return response()->json([
                'msg' => '',
                'data' => $data,
                'from'  => $sdate,
                'to'  => $edate
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

            if ($app->status != 'P') {
                return response()->json([
                    'msg' => 'This appointment status is not completed'
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