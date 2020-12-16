<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/14/18
 * Time: 1:16 PM
 */

namespace App\Http\Controllers\Groomer;


use App\Http\Controllers\Controller;
use App\Lib\AppointmentProcessor;
use App\Lib\Helper;
use App\Lib\ProfitSharingProcessor;
use App\Model\Address;
use App\Model\AppointmentList;
use App\Model\AppointmentProduct;
use App\Model\Breed;
use App\Model\Groomer;
use App\Model\Message;
use App\Model\Size;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpenAppointmentController extends Controller
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

            //Includes distances from C:Current Location of Groomer, H:Home of Groomer, P:Previous service address of Groomer
            //Actually, this will never be used, because GA uses /api/v2/groomer/availability/get
//            $distance_type = $request->distance_type;
//            $x = $request->x;
//            $y = $request->y;
//            $data = AppointmentProcessor::get_open_appointments2($groomer->groomer_id, $distance_type, $x, $y );

            if (empty($data)) {
                $data = [];
            }

            return response()->json([
                'msg' => '',
                'data' => $data
            ]);

        } catch (\Exception $ex) {

            Helper::log('#### EXCEPTION ####', $ex->getTraceAsString());

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
    }

    public function confirm(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required',
                'time' => 'required|date_format:"H:i"'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                throw new \Exception($msg);
            }

            if (!Helper::check_app_key($request->api_key)) {
                throw new \Exception('Invalid API key provided');
            }

            $email = \Crypt::decrypt($request->token);
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                throw new \Exception('Your session has expired. Please login again');
            }

            $app = AppointmentList::find($request->appointment_id);
            if (empty($app)) {
                throw new \Exception('Invalid appointment ID provided');
            }

            if ($app->status != 'N') {
                throw new \Exception('Appointment already taken');
            }

            $app->status = 'D';
            $app->groomer_id = $groomer->groomer_id;

            $app->accepted_date = Carbon::createFromFormat('Y-m-d H:i:s',Carbon::parse($app->reserved_date)->format('Y-m-d') . ' ' . $request->time . ':00');
            $app->mdate = Carbon::now();
            $app->modified_by = '[APP] ' . $groomer->first_name . ' ' . $groomer->last_name;
            $app->groomer_assigned_by = 'G';
            $app->save();

            $msg = AppointmentProcessor::notify_groomer_assignment($app, $groomer);
            if (!empty($msg)) {
                throw new \Exception($msg);
            }

            $user = User::findOrFail($app->user_id);
            $groomer = Groomer::where('groomer_id', $app->groomer_id)->first();
            $address = Address::find($app->address_id);
            $pets = DB::select("
                    select 
                        a.pet_id,
                        c.name as pet_name,
                        c.dob as pet_dob,
                        c.age as pet_age,
                        b.prod_name as package_name,
                        a.amt as price,
                        c.breed,
                        c.size,
                        c.dob,
                        c.special_note as note,
                        c.type
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                ", [
              'appointment_id' => $app->appointment_id
            ]);

            AppointmentProcessor::send_notification_to_user_for_groomer_confirm($app, $user, $groomer, $address, $pets);

            $ret = DB::delete(" delete from groomer_accept_history where appointment_id = :appointment_id"
                , [ 'appointment_id' =>  $app->appointment_id ] );

            $ret = DB::insert("
                insert into groomer_accept_history (appointment_id, groomer_id, accepted_date, cdate, by_type, by_name , by_id )
                values (:appointment_id, :groomer_id, :accepted_date, :cdate ,'G', :by_name, :by_id )
            ", [
                'appointment_id' =>  $app->appointment_id,
                'groomer_id' =>  $groomer->groomer_id,
                'accepted_date' => $app->accepted_date,
                'cdate' => Carbon::now(),
                'by_name' =>  $groomer->first_name . ' ' . $groomer->last_name,
                'by_id' =>  $groomer->groomer_id,
            ]);

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
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
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            if ($app->status != 'N') {
                return response()->json([
                    'msg' => 'Appointment already taken'
                ]);
            }

            $app = AppointmentProcessor::get_info($app);

            return response()->json([
                'msg' => '',
                'detail' => $app
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
    }
}