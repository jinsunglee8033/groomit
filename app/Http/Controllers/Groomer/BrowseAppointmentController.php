<?php

namespace App\Http\Controllers\Groomer;

use App\Lib\Helper;
use App\Model\Appointment;
use App\Model\AppointmentPhoto;
use App\Model\AppointmentProduct;
use App\Model\AppointmentReject;
use App\Model\Groomer;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\Product;
use App\Model\ProductDenom;
use App\Model\User;
use App\Model\Message;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Log;
use Validator;
use DateTime;
use DateTimeZone;

/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 10/20/16
 * Time: 3:53 PM
 */
class BrowseAppointmentController extends Controller
{

    public function accept(Request $request) {
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
            Log::info('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [GRM_ACT]'
                ]);
            }

            $ap = Appointment::find($request->appointment_id);
            if (empty($ap)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            ### send push notification and save message ###
            $push_msg = "Groomer " . $groomer->name . " has accepted your appointment reserved at  " . $ap->reserved_at;

            $user = User::where('user_id', $ap->user_id)->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Failed to find user associated with the appointment'
                ]);
            }

            if (!empty($user->device_token)) {
                $ret = Helper::send_notification('groomit', $push_msg, $user->device_token);
                if (!empty($ret)) {
                    return response()->json([
                        'msg' => 'Failed to send push notification to customer : ' . $ret
                    ]);
                }
            } else {
                //Helper::send_mail('tech@groomit.me', '[groomit] user device token is empty upon appointment accept by groomer', $user->email);
            }

            $ap->groomer_id = $groomer->groomer_id;
            $ap->status = 'G';
            $ap->accepted_date = Carbon::now();
            $ap->save();

            ### save message ###
            $message = new Message;
            $message->src_type = 'G';
            $message->appointment_id = $ap->appointment_id;
            $message->groomer_id = $groomer->groomer_id;
            $message->user_id = $ap->user_id;
            $message->message = $push_msg;
            $message->cdate = Carbon::now();
            $message->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : '
            ]);
        }
    }

    public function reject(Request $request) {
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
            Log::info('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [GRM_RJT]'
                ]);
            }

            $ar = AppointmentReject::where('appointment_id', $request->appointment_id)
                ->where('groomer_id', $groomer->groomer_id)->first();

            if (!empty($ar)) {
                return response()->json([
                    'msg' => 'You have already rejected this one'
                ]);
            }

            $ar = new AppointmentReject;
            $ar->appointment_id = $request->appointment_id;
            $ar->groomer_id = $groomer->groomer_id;
            $ar->reject_date = Carbon::now();
            $ar->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : '
            ]);
        }
    }

    public function detail(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required',
                'location' => 'required'
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

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [GRM_DTL]'
                ]);
            }

            $location = json_decode($request->location);
            if (empty($location->lat) || empty($location->lat)) {
                $loc = Helper::address_to_geolocation($location->address);

                $location->lat = $loc['lat'];
                $location->lng = $loc['lng'];
            }

            $ret = DB::select("
                select 
                    concat(address1, ' ', ifnull(address2, ''), ' ', city, ' ', state, ' ', zip) as address,
                    reserved_at,
                    total,
                    f_get_distance_in_miles(:lat1, :lng1, lat, lng) as distance
                from appointment
                where appointment_id = :appointment_id
            ", [
                'appointment_id' => $request->appointment_id,
                'lat1' => $location->lat,
                'lng1' => $location->lng
            ]);

            if (count($ret) < 1) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            $appointment = $ret[0];

            if (empty($appointment)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            $packages = DB::select("
                select 
                    b.pet_id,
                    b.name as pet_name,
                    timestampdiff(month, b.dob, curdate()) as age,
                    d.breed_name as breed,
                    e.size_name as size,
                    c.prod_name as package_name,
                    (
                        select sum(amt)
                        from appointment_product
                        where pet_id = a.pet_id
                    ) as total,
                    (
                        select photo
                        from pet_photo 
                        where pet_id = a.pet_id
                        order by cdate desc
                        limit 1
                    ) as photo
                from appointment_product a
                    inner join pet b on a.pet_id = b.pet_id
                    inner join product c on a.prod_id = c.prod_id
                    inner join breed d on b.breed = d.breed_id
                    inner join size e on b.size = e.size_id
                where a.appointment_id = :appointment_id 
                and c.prod_type = 'P'
                and c.pet_type = b.type
            ", [
                'appointment_id' => $request->appointment_id
            ]);

            foreach ($packages as $p) {
                //$p->photo = base64_encode($p->photo);
                try{
                    $p->photo = base64_encode($p->photo);
                } catch (\Exception $ex) {
                    $p->photo = $p->photo ;
                }


                $year = intval($p->age / 12);
                $month = intval($p->age % 12);
                $p->age = ($year > 0 ? $year . ' year' . ($year > 1) ? 's' : ' ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');
            }

            $appointment->packages = $packages;

            return response()->json([
                'msg' => '',
                'appointment' => $appointment
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : '
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function browse(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'filter' => 'required'
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

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [GRM_BRW]'
                ]);
            }

            Log::info('#### FILTER #### : ' . var_export($request->filter, true));

            $filter = json_decode($request->filter);

            log::info('### LAT ### : ' . var_export($filter->location->lat, true));
            log::info('### LNG ### : ' . var_export($filter->location->lng, true));

            $new_str = new DateTime($filter->date, new DateTimeZone('UTC') );
            $new_str->setTimeZone(new DateTimeZone( 'America/New_York' ));


            $date = $new_str->format('m/d/Y');

            if (empty($filter->location->lat) || empty($filter->location->lat)) {
                $loc = Helper::address_to_geolocation($filter->location->address);

                $filter->location->lat = $loc['lat'];
                $filter->location->lng = $loc['lng'];
            }

            $appointments = DB::select("
                select * 
                from (
                    select 
                        a.*,
                        f_get_distance_in_miles(:lat1, :lng1, a.lat, a.lng) as distance
                    from appointment_list a 
                    where a.status = 'N'
                    and a.reserved_at >= str_to_date(:date, '%m/%d/%Y')
                    and appointment_id not in (
                        select appointment_id
                        from appointment_rejected 
                        where groomer_id = :groomer_id
                    )
                ) a 
                order by distance asc
            ", [
                'date' => $date,
                'lat1' => $filter->location->lat,
                'lng1' => $filter->location->lng,
                'groomer_id' => $groomer->groomer_id
            ]);

            foreach ($appointments as $o) {

                $photos = DB::select("
                    select b.photo_id, b.photo
                    from appointment_product a 
                        inner join pet_photo b on a.pet_id = b.pet_id
                    where a.appointment_id = :appointment_id 
                ", [
                    'appointment_id' => $o->appointment_id
                ]);

                foreach ($photos as $p) {
                    //$p->photo = base64_encode($p->photo);
                    try{
                        $p->photo = base64_encode($p->photo);
                    } catch (\Exception $ex) {
                        $p->photo = $p->photo ;
                    }

                }

                if (empty($photos)) {
                    $photos = [];
                }

                $o->photos = $photos;
            }

            return response()->json([
                'msg' => '',
                'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}