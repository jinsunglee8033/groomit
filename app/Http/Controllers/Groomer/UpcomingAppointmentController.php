<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 10/31/16
 * Time: 5:22 PM
 */

namespace App\Http\Controllers\Groomer;

use App\Lib\Helper;
use App\Lib\ImageProcessor;
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

class UpcomingAppointmentController extends Controller
{

    public function browse(Request $request) {
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
            Log::info('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [GRM_BRW]'
                ]);
            }

            $appointments = DB::select("
                select 
                    a.*
                from appointment a 
                where a.status in ('G', 'W')
                and a.groomer_id = :groomer_id
            ", [
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

    public function detail(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [GRM_DTL]'
                ]);
            }

            $appt = Appointment::where('appointment_id', $request->appointment_id)
                ->whereIn('status', ['G', 'W', 'F'])
                ->where('groomer_id', $groomer->groomer_id)
                ->first();

            if (empty($appt)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            ### pet list ###
            $appt->pets = DB::select("
                select 
                    a.pet_id, 
                    c.name as pet_name,
                    c.dob as pet_dob,
                    timestampdiff(month, c.dob, curdate()) as age,
                    b.prod_id as package_id,
                    b.prod_name as package_name,
                    a.amt as price
                from appointment_product a 
                    inner join product b on a.prod_id = b.prod_id
                    inner join pet c on a.pet_id = c.pet_id
                where a.appointment_id = :appointment_id
                and b.prod_type = 'P'
                and b.pet_type = c.type
            ", [
                'appointment_id' => $appt->appointment_id
            ]);

            foreach ($appt->pets as $p) {

                $year = intval($p->age / 12);
                $month = intval($p->age % 12);
                $p->age = ($year > 0 ? $year . ' year' . ($year > 1) ? 's' : ' ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');

                $photo = PetPhoto::where('pet_id', $p->pet_id)->first();
                if (!empty($photo)) {
                    //$p->photo = base64_encode($photo->photo);
                    try{
                        $p->photo = base64_encode($photo->photo);
                    } catch (\Exception $ex) {
                        $p->photo = $photo->photo ;
                    }

                }

                $before_img = AppointmentPhoto::where('appointment_id', $appt->appointment_id)
                    ->where('type', 'B')
                    ->where('pet_id', $p->pet_id)
                    ->first();
                if (!empty($before_img)) {
                    $p->before_photo = base64_encode($before_img->photo);
                }

                $afeter_img = AppointmentPhoto::where('appointment_id', $appt->appointment_id)
                    ->where('type', 'A')
                    ->where('pet_id', $p->pet_id)
                    ->first();
                if (!empty($afeter_img)) {
                    $p->after_photo = base64_encode($afeter_img->photo);
                }
            }

            return response()->json([
                'msg' => '',
                'appointment' => $appt
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function cancel(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [GRM_CNC]'
                ]);
            }

            $appt = Appointment::where('appointment_id', $request->appointment_id)->first();

            if (empty($appt)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            $user = User::where('user_id', $appt->user_id)->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Something is wrong. User empty on appointment'
                ]);
            }

            $push_msg = "Groomer " . $groomer->name . " has cancelled your appointment reserved at  " . $appt->reserved_at;

            if (!empty($user->device_token)) {
                $ret = Helper::send_notification('groomit', $push_msg, $user->device_token);
                if (!empty($ret)) {
                    return response()->json([
                        'msg' => 'Failed to send push notification to customer : ' . $ret
                    ]);
                }
            } else {
                Helper::send_mail('tech@groomit.me', '[groomit] user device token is empty upon appointment cancel by groomer', $user->email);
            }

            $appt->groomer_id = null;
            $appt->status = 'N';
            $appt->save();

            $ar = new AppointmentReject;
            $ar->appointment_id = $appt->appointment_id;
            $ar->groomer_id = $groomer->groomer_id;
            $ar->reject_date = Carbon::now();
            $ar->save();

            ### save message ###
            $message = new Message;
            $message->src_type = 'G';
            $message->appointment_id = $appt->appointment_id;
            $message->groomer_id = $groomer->groomer_id;
            $message->user_id = $appt->user_id;
            $message->message = $push_msg;
            $message->cdate = Carbon::now();
            $message->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function leave_now(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [GRM_LN]'
                ]);
            }

            $appt = Appointment::where('appointment_id', $request->appointment_id)->first();

            if (empty($appt)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            $user = User::where('user_id', $appt->user_id)->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Something is wrong. User empty on appointment'
                ]);
            }

            $push_msg = "Groomer " . $groomer->name . " is leaving now for your appointment reserved at  " . $appt->reserved_at;

            if (!empty($user->device_token)) {
                $ret = Helper::send_notification('groomit', $push_msg, $user->device_token);
                if (!empty($ret)) {
                    return response()->json([
                        'msg' => 'Failed to send push notification to customer : ' . $ret
                    ]);
                }
            } else {
                Helper::send_mail('tech@groomit.me', '[groomit] user device token is empty upon appointment leave now by groomer', $user->email);
            }

            $appt->status = 'W';
            $appt->save();

            ### save message ###
            $message = new Message;
            $message->src_type = 'G';
            $message->appointment_id = $appt->appointment_id;
            $message->groomer_id = $groomer->groomer_id;
            $message->user_id = $appt->user_id;
            $message->message = $push_msg;
            $message->cdate = Carbon::now();
            $message->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function complete(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required',
                'pet_photos' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [GRM_CMPT]'
                ]);
            }

            $appt = Appointment::where('appointment_id', $request->appointment_id)->first();

            if (empty($appt)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            $appt->status = 'S';
            $appt->save();

            $pet_photos = json_decode($request->pet_photos);
            foreach ($pet_photos as $o) {
                DB::statement("
                    delete from appointment_photo
                    where appointment_id = :appointment_id
                    and pet_id = :pet_id
                ", [
                    'appointment_id' => $appt->appointment_id,
                    'pet_id' => $o->pet_id
                ]);

                $ap = new AppointmentPhoto;
                $ap->type = 'B';
                $ap->appointment_id = $appt->appointment_id;
                $ap->pet_id = $o->pet_id;
                $ap->image = ImageProcessor::optimize(base64_decode($o->before_photo));
                $ap->groomer_id = $groomer->groomer_id;
                $ap->upload_date = Carbon::now();
                $ap->save();

                $ap = new AppointmentPhoto;
                $ap->type = 'A';
                $ap->appointment_id = $appt->appointment_id;
                $ap->pet_id = $o->pet_id;
                $ap->image = ImageProcessor::optimize(base64_decode($o->after_photo));
                $ap->groomer_id = $groomer->groomer_id;
                $ap->upload_date = Carbon::now();
                $ap->save();
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
}