<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 4/19/18
 * Time: 11:30 AM
 */

namespace App\Http\Controllers;


use App\Lib\Helper;
use App\Model\Address;
use App\Model\AppointmentList;
use App\Model\AppointmentProduct;
use App\Model\Groomer;
use App\Model\PetPhoto;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{

    public function get_info(Request $request) {
        try {

            return response()->json([
                'msg' => 'Your version is out of date. Please download and install the latest version through App Store.'
            ]);

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
            Helper::log('### email ###', $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_GRMW]'
                ]);
            }

            ### get closest one upcoming appointment ###
            $upcoming = AppointmentList::where('user_id', $user->user_id)
                ->whereNotIn('status', ['C', 'F', 'S', 'P', 'L'])
                ->whereRaw("((accepted_date is not null and accepted_date >= '" . Carbon::today() ."') or (accepted_date is null and reserved_date >= '" . Carbon::today() . "'))")
                ->orderBy('reserved_date', 'asc')
                ->first();

            ### for testing ###
            /*
            if (empty($upcoming)) {
                $upcoming = AppointmentList::whereNotIn('status', ['C', 'F', 'S', 'P', 'L'])
                    ->whereRaw("((accepted_date is not null and accepted_date >= '" . Carbon::today() ."') or (accepted_date is null and reserved_date >= '" . Carbon::today() . "'))")
                    ->orderBy('reserved_date', 'asc')
                    ->whereNotNull('address_id')
                    ->first();
            }*/

            if (!empty($upcoming)) {
                ### date format change ###
                if ($upcoming->accepted_date) {
                    $upcoming->check_date = Carbon::parse($upcoming->accepted_date)->format('m/d/Y h:i A');
                    $upcoming->display_date = $upcoming->check_date;
                } else {
                    $upcoming->check_date = Carbon::parse($upcoming->reserved_date)->format('m/d/Y h:i A');
                    $upcoming->display_date = $upcoming->reserved_at;
                }

                ### pets ###
                $pets = AppointmentProduct::join('pet', 'pet.pet_id', '=', 'appointment_product.pet_id')
                    ->join('product', 'product.prod_id', '=', 'appointment_product.prod_id')
                    ->where('product.prod_type', 'P')
                    ->where('appointment_product.appointment_id', $upcoming->appointment_id)
                    ->select(
                        'pet.pet_id',
                        'pet.name',
                        'pet.type',
                        'product.prod_name as package'
                    )->get();

                $upcoming->pets = $pets;
                foreach ($upcoming->pets as $pet) {
                    $pet_photo = PetPhoto::where('pet_id', $pet->pet_id)->orderBy('cdate', 'desc')->first();
                    if (!empty($pet_photo)) {
                        $pet->photo = base64_encode($pet_photo->photo);
                    }
                }

                ### address ###
                $addr = Address::find($upcoming->address_id);
                if (!empty($addr)) {
                    $upcoming->addr = $addr;
                }

                $groomer = Groomer::find($upcoming->groomer_id);
                if (!empty($groomer)) {
                    $upcoming->groomer = $groomer;
                }
            }

            ### get last one recent appointment ###
            $recent = AppointmentList::where('user_id', $user->user_id)
                ->whereIn('status', ['P'])
                ->where('accepted_date', '<>', 'null')
                ->orderBy('reserved_date', 'desc')
                ->first();

            if (!empty($recent)) {
                ### date format change ###
                $recent->reserved_date = Carbon::parse($recent->reserved_date)->format('m/d/Y h:i A');

                ### pets ###
                $pets = AppointmentProduct::join('pet', 'pet.pet_id', '=', 'appointment_product.pet_id')
                    ->join('product', 'product.prod_id', '=', 'appointment_product.prod_id')
                    ->where('product.prod_type', 'P')
                    ->where('appointment_product.appointment_id', $recent->appointment_id)
                    ->select(
                        'pet.pet_id',
                        'pet.name',
                        'pet.type',
                        'product.prod_name as package'
                    )->get();

                $recent->pets = $pets;
                foreach ($recent->pets as $pet) {
                    $pet_photo = PetPhoto::where('pet_id', $pet->pet_id)->orderBy('cdate', 'desc')->first();
                    if (!empty($pet_photo)) {
                        $pet->photo = base64_encode($pet_photo->photo);
                    }
                }

                ### address ###
                $addr = Address::find($recent->address_id);
                if (!empty($addr)) {
                    if( !empty($addr->address2) && ($addr->address2 != '' ) ) {
                        $recent->address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }else {
                        $recent->address = $addr->address1 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }

                }

                ### address ###
                $addr = Address::find($recent->address_id);
                if (!empty($addr)) {
                    $recent->addr = $addr;
                }

                $groomer = Groomer::find($recent->groomer_id);
                if (!empty($groomer)) {
                    $recent->groomer = $groomer;
                }
            }

            return response()->json([
                'msg' => '',
                'recent' => $recent,
                'upcoming' => $upcoming
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}