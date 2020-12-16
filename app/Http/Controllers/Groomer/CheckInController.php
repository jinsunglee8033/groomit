<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 4/21/18
 * Time: 10:01 AM
 */

namespace App\Http\Controllers\Groomer;


use App\Http\Controllers\Controller;
use App\Lib\AppointmentProcessor;
use App\Lib\Helper;
use App\Lib\ImageProcessor;
use App\Model\Address;
use App\Model\AppointmentList;
use App\Model\AppointmentPet;
use App\Model\AppointmentPhoto;
use App\Model\Groomer;
use App\Model\Pet;
use App\Model\TaxZip;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckInController extends Controller
{

    public function hasWork(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
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

            $app = AppointmentList::where('groomer_id', $groomer->groomer_id)
                ->whereIn('status', ['O', 'W'])
                ->whereRaw("((accepted_date is not null and accepted_date >= '" . Carbon::today() ."') or (accepted_date is null and reserved_date >= '" . Carbon::today() . "'))")
                ->orderBy('reserved_date', 'asc')
                ->first();

            return response()->json([
                'msg' => '',
                'result' => !empty($app) ? 'Y' : 'N',
                'appointment_id' => !empty($app) ? $app->appointment_id : null
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function getInfo(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
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

            $groomer = Groomer::where('email', strtolower($email))
                ->where('status', 'A')
                ->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $ap = AppointmentList::where('groomer_id', $groomer->groomer_id)
                ->whereIn('status', ['O', 'W', 'D'])
                ->whereRaw("((accepted_date is not null and accepted_date >= ?) or (accepted_date is null and reserved_date >= ?))", [
                    Carbon::today(), Carbon::today()
                ])
                ->orderBy('accepted_date', 'asc')
                ->first();

            if (empty($ap)) {
                return response()->json([
                    'msg' => 'No check-in work found'
                ]);
            }

            $info = AppointmentProcessor::get_info($ap);

            ### return tax rates ###
            $address = Address::find($info->appointment_id);
            $tax = 7.75;
            if (!empty($address)) {
                $tax_o = TaxZip::where("zip", $request->zip)->first();
                if (!empty($tax_o)) {
                    $tax = $tax_o->rates;
                }
            }

            return response()->json([
                'msg' => '',
                'info' => $info,
                'tax_rates' => $tax
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function updateImage(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key'   => 'required',
                'token'     => 'required',
                'appointment_id' => 'required',
                'pet_id'    => 'required',
                'type'      => 'required|in:B,A',
                'image'     => 'required'
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

            $pet = Pet::find($request->pet_id);
            if (empty($pet)) {
                return response()->json([
                    'msg' => 'Unable to find pet'
                ]);
            }

            if ($ap->groomer_id != $groomer->groomer_id) {
                return response()->json([
                    'msg' => 'This appointment is not bound to you'
                ]);
            }

            $ap_pet = AppointmentPet::where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $pet->pet_id)
                ->first();
            if (empty($ap_pet)) {
                return response()->json([
                    'msg' => 'This pet is not included in the appointment'
                ]);
            }

            $ap_photo = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $pet->pet_id)
                ->where('type', $request->type)
                ->first();

            if (empty($ap_photo)) {
                $ap_photo = new AppointmentPhoto;
                $ap_photo->appointment_id = $ap->appointment_id;
                $ap_photo->pet_id = $pet->pet_id;
                $ap_photo->type = $request->type;
            }

            $datetime = empty($request->localtime) ? Carbon::now() : Carbon::parse($request->localtime); //Consider TZ for other areas?

            // Time zone check to EST. ONLY
            if (!empty($request->localtime)) {
                $ret = DB::select("
                select c.utc + 5 as gap
                from appointment_list a, address b, allowed_zip c
                where a.address_id = b.address_id
                and b.zip = c.zip
                and a.appointment_id = :appointment_id
                limit 0,1
                ",
                    [
                        'appointment_id' => $ap->appointment_id
                    ]);
                if (count($ret) > 0) {
                    $gap = $ret[0]->gap;
                }
                if ($gap == 0) {
                    // time same zone
                } elseif ($gap == -1) {
                    $datetime->addHours(1);
                } elseif ($gap == -2) {
                    $datetime->addHours(2);
                } elseif ($gap == -3) {
                    $datetime->addHours(3);
                } elseif ($gap == -4) {
                    $datetime->addHours(4);
                } elseif ($gap == -5) {
                    $datetime->addHours(5);
                } elseif ($gap == -6) {
                    $datetime->addHours(6);
                } elseif ($gap == -7) {
                    $datetime->addHours(7);
                } elseif ($gap == -8) {
                    $datetime->addHours(8);
                }
            }

            $datetime_format = $datetime->format('Y-m-d');

            //Do not allow check in, if it's prior day of the service date.
            if( $datetime_format <  Carbon::parse($ap->accepted_date)->format('Y-m-d') ) {
                return response()->json([
                    'msg' => 'You cannot process it before your service date. Please double check it.'
                ]);
            }

            $ap_photo->image = ImageProcessor::optimize(base64_decode($request->image));
            $ap_photo->groomer_id   = $groomer->groomer_id;
            $ap_photo->upload_date  = $datetime;
            $ap_photo->save();

            if ($request->type == 'B') {
                if (empty($ap->check_in) || $ap->check_in > $datetime) {
                    $ap->check_in   = $datetime;
                    $ap->mdate = Carbon::now();
                    $ap->modified_by = '[APP] ' . $groomer->first_name . ' ' . $groomer->last_name;
                    if( in_array($ap->status, [ 'D','O'] ) ) { //Update status only when 'D' & 'O'. This tx could be sent after 'P', in some case.
                        $ap->status = 'W';
                    }

                }
            } else {
                if (empty($ap->check_out) || $ap->check_out < $datetime) {
                    $ap->check_out  = $datetime;
                    $ap->mdate = Carbon::now();
                    $ap->modified_by = '[APP] ' . $groomer->first_name . ' ' . $groomer->last_name;
                }
            }

            $ap->save();

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