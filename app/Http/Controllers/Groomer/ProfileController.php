<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/11/18
 * Time: 11:00 AM
 */

namespace App\Http\Controllers\Groomer;

use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\Address;
use App\Model\AppointmentList;
use App\Model\Groomer;
use App\Model\GroomerArrived;
use App\Model\Message;
use App\Model\PromoCode;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function update(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required',
                'email' => 'required',
                'bio' => 'required',
                'password' => '',
                'password_confirm' => 'same:password'
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
                    'msg' => 'Your session has expired. Please login again. [GET_USR]'
                ]);
            }

            $cnt = Groomer::where('groomer_id', '!=', $groomer->groomer_id)
                ->whereRaw("lower(trim(email)) = ?", [strtolower(trim($request->email))])
                ->count();
            if ($cnt > 0) {
                return response()->json([
                    'msg' => strtolower(trim($request->email)) . ' is already taken by other groomer'
                ]);
            }

            $groomer->first_name = $request->first_name;
            $groomer->last_name = $request->last_name;
            $groomer->phone = $request->phone;
            $groomer->email = strtolower(trim($request->email));
            $groomer->bio = $request->bio;
            $groomer->mdate = Carbon::now();
            $groomer->modified_by = $groomer->groomer_id;

            if (!empty($request->password)) {
                $groomer->password = Crypt::encrypt($request->password);
            }

            $groomer->save();

            return response()->json([
                'msg' => '',
                'groomer' => $groomer
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [GET_USR]'
                ]);
            }

            // Add groomer_referal_code
            $pc = PromoCode::where('groomer_id', $groomer->groomer_id)->first();
            $groomer->groomer_referal_code = !empty($pc->code) ? $pc->code : '';

            return response()->json([
                'msg' => '',
                'groomer' => $groomer
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_device_token(Request $request) {
        try {

            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token' => 'required',
              'device_token' => 'required'
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
                  'msg' => 'Your session has expired. Please login again. [GET_USR]'
                ]);
            }

            $groomer->device_token = $request->device_token;
            $groomer->mdate = Carbon::now();
            $groomer->update();

            return response()->json([
              'msg' => ''
            ]);
        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function arrived(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key'       => 'required',
                'token'         => 'required',
                'appointment_id'=> 'required',
                'x'             => 'required',
                'y'             => 'required'
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

            $ap = AppointmentList::find($request->appointment_id);
            if (empty($ap)) {
                return response()->json([
                    'msg' => 'Unable to find appointmenet'
                ]);
            }

            if( $ap->groomer_id != $groomer->groomer_id){
                return response()->json([
                    'msg' => 'Wrong groomer of the appointment'
                ]);
            }

            $address = Address::where('address_id', $ap->address_id)->first();

            $ap_lat = $address->lat;
            $ap_lng = $address->lng;

            $full_address = $address->address1 . ' ' . $address->address2 . ' ' . $address->city . ' ' . $address->state . ' ' . $address->zip;

            //
            // check if it exist! if not call address_to_geolocation($address)
            //
            if($ap_lat == null || $ap_lng == null){
                $ret = Helper::address_to_geolocation($full_address);

                if($ret['msg'] == '') {
                    $ap_lat = $ret['lat'];
                    $ap_lng = $ret['lng'];
                }
            }

            $x = trim($request->x);
            $y = trim($request->y);

            //
            // Calculate distant between two points
            // And get result Y,N
            //
            $distance = Helper::get_distance($x, $y, $ap_lat, $ap_lng);
            if( getenv('APP_ENV') == 'production' ) {
                if(  $distance * 5280.0 > 2000){ //Do not accept if too far away.
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Arrived used too far. :' . $ap->appointment_id , "[$x][$y][$ap_lat][$ap_lng][" . $distant * 5280.0 . "]");
                    return response()->json([
                        'msg' => 'Your location is too far away from your customer. Please try again when arrived at the destination.'
                    ]);
                }
            }

            $result = 'Y';

            $ret = GroomerArrived::where('groomer_id', $groomer->groomer_id)
                ->where('appointment_id', $ap->appointment_id)
                ->first();
            if($ret){
                $result = 'Y';
            }else {
                // Insert this information to Table.
                $ga = new GroomerArrived();
                $ga->groomer_id = $groomer->groomer_id;
                $ga->appointment_id = $ap->appointment_id;
                $ga->address_id = $address->address_id;
                $ga->g_lat = $x;
                $ga->g_lng = $y;
                $ga->c_lat = $ap_lat;
                $ga->c_lng = $ap_lng;
                $ga->distance = $distance;
                $ga->result = $result;
                $ga->cdate = Carbon::now();

                $ga->save();
            }

            //Send Notifications to a customer.
            //$msg = "Your Groomit Groomer " . trim($groomer->first_name) . ' ' . trim($groomer->last_name) . " arrived at your location.";
            $msg = "Your Groomit Groomer arrived in your area. Please be ready, the groomer will be there soon.";

            $user = User::find($ap->user_id);

            ### SMS to user
            if (!empty($user->phone)) {
                $ret = Helper::send_sms($user->phone, $msg);
                Message::save_sms_to_user($msg, $user, $ap->appointment_id);

//                if (!empty($ret)) {
//                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $msg);
//                }
            }

            if (!empty($user->device_token) && ($user->device_token != '') ) {
                Helper::send_notification("", $msg, $user->device_token, 'Notice', "");
            }

            return response()->json([
                'msg' => '',
                'result' => $result
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}