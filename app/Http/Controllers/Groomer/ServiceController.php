<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 4/21/18
 * Time: 10:35 AM
 */

namespace App\Http\Controllers\Groomer;


use App\Http\Controllers\Controller;
use App\Lib\AppointmentProcessor;
use App\Lib\Helper;
use App\Lib\ProfitSharingProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\AppointmentList;
use App\Model\AppointmentPet;
use App\Model\Groomer;
use App\Model\GroomerArrived;
use App\Model\Pet;
use App\Model\ProfitSharing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ServiceController extends Controller
{

    public function getAddons(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required',
                'pet_id' => 'required'
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

            $pet = Pet::find($request->pet_id);
            if (empty($pet)) {
                return response()->json([
                    'msg' => 'Unable to find requested pet'
                ]);
            }

            $ap = AppointmentList::find($request->appointment_id);
            if (empty($ap)) {
                return response()->json([
                    'msg' => 'Unable to find requested appointment information'
                ]);
            }

            $ap_pet = AppointmentPet::where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $pet->pet_id)
                ->first();
            if (empty($ap_pet)) {
                return response()->json([
                    'msg' => 'The pet does not belong to this appointment'
                ]);
            }

            $zip = '';
            $address = Address::find($ap->address_id);
            if (!empty($address)) {
                $zip = $address->zip;
            }

            $group_id = 1;
            if (!empty($zip)) {
                $allowed_zip = AllowedZip::where('zip', $zip)->first();
                if (!empty($allowed_zip)) {
                    $group_id = $allowed_zip->group_id;
                }
            }

            $packages = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where (
                    a.size_required = 'Y' and b.size_id = :size_id or 
                    a.size_required = 'N'
                ) and a.prod_type = 'P'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = :pet_type
            ", [
                'size_id' => $pet->size,
                'zip' => $zip,
                'pet_type' => $pet->type,
                'group_id' => $group_id
            ]);

            $add_ons = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where (
                    a.size_required = 'Y' and b.size_id = :size_id or 
                    a.size_required = 'N'
                )
                and a.prod_type = 'A'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = :pet_type
                order by a.seq
            ", [
                'size_id' => $pet->size,
                'zip' => $zip,
                'group_id' => $group_id,
                'pet_type' => $pet->type
            ]);

            $shampoos = DB::select("
                select
                    a.prod_id,
                    a.prod_name,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'S'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = :pet_type
            ", [
                'zip' => $zip,
                'group_id' => $group_id,
                'pet_type' => $pet->type
            ]);

            return response()->json([
                'msg' => '',
                'packages' => $packages,
                'add_ons' => $add_ons,
                'shampoos' => $shampoos
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required',
                'pet_id' => 'required',
                'package_id' => 'required',
                'shampoo_id' => 'required',
                'addons' => ''
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
                    'msg' => 'Unable to find appointment: ' . $request->appointment_id
                ]);
            }

            if (!in_array($ap->status, ['O', 'W'])) {
                return response()->json([
                    'msg' => 'Wrong status'
                ]);
            }

            if ($ap->groomer_id != $groomer->groomer_id) {
                return response()->json([
                    'msg' => 'The appointmetn is not bound to the groomer'
                ]);
            }

            $pet = Pet::find($request->pet_id);
            if (empty($pet)) {
                return response()->json([
                    'msg' => 'Unable to find pet'
                ]);
            }

            /*if ($pet->type == 'cat') {
                return response()->json([
                    'msg' => 'Service update is not allowed for cat'
                ]);
            }*/

            $ap_pet = AppointmentPet::where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $pet->pet_id)
                ->first();
            if (empty($ap_pet)) {
                return response()->json([
                    'msg' => 'Pet does not belong to the appointment'
                ]);
            }

            DB::beginTransaction();

            $add_ons = [];
            if (!empty($request->add_ons)) {
                $add_ons = explode(",", $request->add_ons);
            }

            AppointmentProcessor::update_service(
                $request->appointment_id,
                $request->pet_id,
                $pet->size,
                $request->package_id,
                $request->shampoo_id,
                $add_ons,
                $groomer->first_name . ' ' . $groomer->last_name . ' (Groomer: ' . $groomer->groomer_id . ')'
            );

            DB::commit();

            $ap = AppointmentList::find($request->appointment_id);

            return response()->json([
                'msg' => '',
                'before_tax_total' => $ap->sub_total,
                'after_tax_total' => $ap->total
            ]);

        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function complete(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required'
                //Do not check validation of x,y
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
                    'msg' => 'Unable to find appointment: ' . $request->appointment_id
                ]);
            }

            if (!in_array($ap->status, ['O', 'W','P'])) {
                return response()->json([
                    'msg' => 'Wrong status'
                ]);
            }

            if (in_array($ap->status, ['P'])) {
                 //If it's already paid, return as if it's succeeded.
                 //sometimes, groomers are click 'Complete' repeatedly.
                return response()->json([
                    'msg' => '',
                    'earning' => ProfitSharingProcessor::getProfit($ap->appointment_id)
                ]);
            }

            if ($ap->groomer_id != $groomer->groomer_id) {
                return response()->json([
                    'msg' => 'The appointment is not bound to the groomer'
                ]);
            }

            if (!empty($request->pets)) {
                $pets = json_decode($request->pets);
                if (!is_array($pets) || count($pets) < 1) {
                    return response()->json([
                        'msg' => 'Invalid comments json structure provided'
                    ]);
                }

                foreach ($pets as $o) {
                    if (!isset($o->pet_id)) {
                        return response()->json([
                            'msg' => 'Pet ID is required'
                        ]);
                    }

                    $ap_pet = AppointmentPet::where('appointment_id', $ap->appointment_id)
                        ->where('pet_id', $o->pet_id)
                        ->first();
                    if (empty($ap_pet)) {
                        return response()->json([
                            'msg' => 'Invalid pet ID provided for comments'
                        ]);
                    }
                }
            }

            $ap->status = 'S';
            $ap->save();

            //Save x,y coordination here
            $x = isset($request->x) ? trim($request->x) : null ;
            $y = isset($request->y) ? trim($request->y) : null ;

            if( !is_null($x) && ($x != '') && !is_null($y) && ($y != '') ){
                $ga = GroomerArrived::where('groomer_id', $ap->groomer_id)
                    ->where('appointment_id', $ap->appointment_id)
                    ->orderBy('id', 'desc')
                    ->first();
                if( !empty($ga) && !empty($ga->g_lat) && !empty( $ga->g_lng )  &&
                            !empty($ga->c_lat) && !empty( $ga->c_lng ) ){

                    $ga->comp_lat = $x;
                    $ga->comp_lng = $y;

                    $distant_comp_app = Helper::get_distance($x, $y, $ga->g_lat, $ga->g_lng );
                    $ga->distance_comp_app = $distant_comp_app ;
                    $distant_comp_google = Helper::get_distance($x, $y, $ga->c_lat, $ga->c_lng );
                    $ga->distance_comp_google = $distant_comp_google;
                    $ga->comp_date = Carbon::now();

                    $ga->save();
                }
                // Calculate distant between two points
//

            }
            ### now complete the appointment ###
            $proc = new AppointmentProcessor();
            $ret = $proc->charge_appointment($ap);
            if (!empty($ret['error_msg'])) {
                $ap->status = 'F';
                $ap->note = $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                $ap->save();

                return response()->json([
                    'msg' => $ret['error_msg'] . ' [' . $ret['error_code'] . ']'
                ]);
            }

            ### put groomer's comments for each pet ###
            if (!empty($request->pets)) {
                $pets = json_decode($request->pets);
                foreach ($pets as $o) {
                    if (!empty($o->comments)) {
                        $ret = DB::statement("
                            update appointment_pet
                            set groomer_note = :comments
                            where appointment_id = :appointment_id
                            and pet_id = :pet_id
                        ", [
                            'comments' => $o->comments,
                            'appointment_id' => $ap->appointment_id,
                            'pet_id' => $o->pet_id
                        ]);

                        if ($ret < 1) {
                            return response()->json([
                                'msg' => 'Failed to update pet comments'
                            ]);
                        }
                    }
                }
            }

            $earning_amt  =  ProfitSharingProcessor::getProfit($ap->appointment_id) ;
            $msg_to_groomer= "";
            //If groomer_arrived data exist
            if( !empty($ga) ) {

                $accepted_date = Carbon::parse($ap->accepted_date);
                $arrival_date = Carbon::parse($ga->cdate );

                $mins_diff = $arrival_date->diffInMinutes($accepted_date, false) ; //accpeted_date - arrival_date w/ plus/minus value.
                if( $mins_diff < 0) {
                    $mins_diff = -$mins_diff ; //make it plus.
                    $msg_to_groomer = "You arrived <strong>$mins_diff</strong> minutes late." ;
                }else {
                    $msg_to_groomer = "You arrived <strong>$mins_diff</strong> minutes early.";
                }
                 $msg_to_groomer .= " To earn full commission & bonus if applicable you must arrive at location on time.";
            }

            return response()->json([
                'msg' => '',
                'earning' => $earning_amt,
                'msg_to_groomer' => $msg_to_groomer
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }
}