<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/21/18
 * Time: 3:57 PM
 */

namespace App\Http\Controllers\User\API;


use App\Http\Controllers\Controller;
use App\Lib\PromoCodeProcessor;
use App\Lib\ScheduleProcessor;
use App\Model\AppVersion;
use App\Model\Appointment;
use App\Model\AppointmentPhoto;
use App\Model\Constants;
use App\Model\UserFavoriteGroomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Lib\AppointmentProcessor;
use App\Lib\Helper;

use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\AppointmentList;
use App\Model\AppointmentPet;
use App\Model\AppointmentProduct;
use App\Model\Groomer;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\Product;
use App\Model\ProductDenom;
use App\Model\PromoCode;
use App\Model\User;
use App\Model\UserBilling;

use Carbon\Carbon;

use DB;
use Log;

class AppointmentController extends Controller
{

    public function show(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
            'api_key' => 'required',
            'token'   => 'required'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;


        ### get closest one upcoming appointment ###
        $upcoming = AppointmentList::where('user_id', $user->user_id)
            ->whereNotIn('status', ['C', 'F', 'S', 'P', 'L'])
            ->whereRaw("((accepted_date is not null and accepted_date >= '" . Carbon::today() ."') or (accepted_date is null and reserved_date >= '" . Carbon::today() . "'))")
            ->orderBy('reserved_date', 'asc')
            ->first();

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

                ### Remove Groomer password at API response ###
                unset($groomer->password);

                $upcoming->groomer = $groomer;

                $fav = UserFavoriteGroomer::where('user_id', $user->user_id)->where('groomer_id', $upcoming->groomer_id)->first();
                if (!empty($fav)) {
                    $upcoming->groomer->favorite = 'Y';
                } else {
                    $upcoming->groomer->favorite = 'N';
                }
            }

            $upcoming->status_name = Constants::$appointment_status[$upcoming->status];
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
                if(!empty( $addr->address2 ) && ( $addr->address2  != '')) {
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

                ### Remove Groomer password at API response ###
                unset($groomer->password);

                $recent->groomer = $groomer;

                $fav = UserFavoriteGroomer::where('user_id', $user->user_id)->where('groomer_id', $recent->groomer_id)->first();
                if (!empty($fav)) {
                    $recent->groomer->favorite = 'Y';
                } else {
                    $recent->groomer->favorite = 'N';
                }
            }

            $recent->status_name = Constants::$appointment_status[$recent->status];
        }

        return response()->json([
            'code' => '0',
            'upcoming' => $upcoming,
            'recent' => $recent
        ]);
    }


    public function history(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'sdate'   => 'required',
              'edate'   => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;


            ### status list ###
            # - N : New
            # - G : Groomer assigned
            # - W : Work in progress
            # - C : Cancelled
            # - S : Work Completed
            # - F : Failed ( Maybe payment failure ? )

            ### list of information to be returned ###
            # - appoitment general
            # - groomer information
            # - pet images ( before / after )

            $appointments = AppointmentList::where('user_id', $user->user_id)
              ->whereIn('status', ['P'])
              ->where('accepted_date', '>=', $request->sdate)
              ->where('accepted_date', '<=', Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59'))
              ->orderBy('accepted_date', 'desc')
              ->take(3)->get();

            $arr_appointments = [];
            if (count($appointments) > 0) {
                foreach ($appointments as $ap) {
                    $arr_appointments[] = AppointmentProcessor::get_info_v3($ap);
                }
            }

            return response()->json([
                'code' => '0',
                'appointments' => $arr_appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    public function last(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;


            ### status list ###
            # - N : New
            # - G : Groomer assigned
            # - W : Work in progress
            # - C : Cancelled
            # - S : Work Completed
            # - F : Failed ( Maybe payment failure ? )

            ### list of information to be returned ###
            # - appoitment general
            # - groomer information
            # - pet images ( before / after )

            $appointments = AppointmentList::where('user_id', $user->user_id)
              ->whereIn('status', ['P'])
              ->orderBy('accepted_date', 'desc')
              ->take(3)->get();

            $arr_appointments = [];
            if (count($appointments) > 0) {
                foreach ($appointments as $ap) {
                    $arr_appointments[] = AppointmentProcessor::get_info_v3($ap);
                }
            }

            return response()->json([
              'code' => '0',
              'appointments' => $arr_appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }



    public function upcoming(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;


            #<option value="N" selected>Groomer Not Assigned Yet</option>
            #<option value="D" >Groomer Assigned</option>
            #<option value="W" >Work In Progress</option>
            #<option value="C" >Cancelled</option>                           ***
            #<option value="S" >Work Completed</option>                      ***
            #<option value="F" >Payment Failure</option>                     ***
            #<option value="R" >Failed to hold amount. Please retry after updating customer credit card.</option>
            #<option value="L" >Cancelled &amp; Rescheduled</option>         ***
            #<option value="P" >Payment Completed</option>                   ***


            ### list of information to be returned ###
            # - appoitment general
            # - groomer information
            # - pet images ( before / after )

            $arr_appointments = [];

            //Shows all, not just one.
            $appointments = AppointmentList::where('user_id', $user->user_id)
                  ->whereNotIn('status', ['C', 'F', 'S', 'P' , 'L'])
                  ->whereRaw("((accepted_date is not null and accepted_date >= '" . Carbon::today() ."') or (accepted_date is null and reserved_date >= '" . Carbon::today() . "'))")
                  ->orderBy('reserved_date', 'asc')
                  ->get();

            if ( count($appointments) > 0 ) {
                foreach ($appointments as $ap) {
                    $arr_appointments[] = AppointmentProcessor::get_info_v3($ap);
                }
            }

            return response()->json([
              'code' => '0',
              'appointments' => $arr_appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function times(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
            'api_key' => 'required',
            'token'   => 'required',
            'date'    => 'required|date'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $today = Carbon::today();

        if ($request->date > $today) {
            $times = Helper::get_time_windows();
        } else {
            $times = Helper::get_time_windows_by_date($request->date);
        }

        return response()->json([
            'code' => '0',
            'times' => $times
        ]);
    }



    public function places(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
            'api_key'   => 'required',
            'token'     => 'required'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
                'code' => '-1',
                'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $places = DB::select("
            select place_id, place_name, seq
              from place 
             order by 3
            " );


        return response()->json([
            'code' => '0',
            'places' => $places
        ]);
    }


    private function calculate_appointment($request, $user) {
        try {
            $times = Helper::get_time_windows();
            $time = null;

            foreach ($times as $t) {
                if ($t->id == $request->time) {
                    $time = $t;
                }
            }
            //$user->user_id
            //$address = Address::find($request->address_id);
            $address = Address::where( 'address_id', $request->address_id)->where( 'user_id', $user->user_id)->where('status','A')->first();
            if (empty($address)) {
                return [
                    'code' => 'address',
                    'msg'  => 'Can not found the address'
                ];
            }

            $allowzip = AllowedZip::where('zip', $address->zip)->where('available', 'x')->first();
            if (empty($allowzip)) {
                return [
                    'code' => 'address',
                    'msg'  => 'The address is not our service area'
                ];
            }

            //$payment = UserBilling::find($request->billing_id);
            $payment = UserBilling::where('billing_id',$request->billing_id)->where( 'user_id', $user->user_id)->where('status','A')->first();
            if (empty($payment)) {
                return [
                    'code' => 'payment',
                    'msg'  => 'Can not found the payment information.'
                ];
            }

            $promo      = null;
            if (!empty($request->promo_code)) {
                $promo      = PromoCode::find(strtoupper($request->promo_code));
                if (empty($promo)) {
                    return [
                      'code' => 'promocode',
                      'msg'  => 'Invalid promo code provided.'
                    ];
                }
            }

            $reqpets    = json_decode (json_encode ($request->pets), FALSE);

            $pets = [];
            foreach ($reqpets as $p) {
                $p = (object) $p;

                $pet = Pet::find($p->pet_id);
                if (empty($pet)) {
                    continue;
                }

                $pet->info = new \stdClass();

                ### package ###
                $pet->info->package = Product::find($p->package);
                if (empty($pet->info->package)) {
                    continue;
                }
                if ($pet->info->package->size_required == 'Y') {
                    $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $p->package)->where('size_id', $pet->size)->first();
                } else {
                    $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $p->package)->first();
                }
                if (empty($denom)) {
                    continue;
                }
                $pet->info->package->denom = $denom->denom;

                ### shampoo ###
                $pet->info->shampoo = Product::find($p->shampoo);
                if (empty($pet->info->shampoo)) {
                    continue;
                }
                if ($pet->info->shampoo->size_required == 'Y') {
                    $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $p->shampoo)->where('size_id', $pet->size)->first();
                } else {
                    $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $p->shampoo)->first();
                }
                if (empty($denom)) {
                    continue;
                }
                $pet->info->shampoo->denom = $denom->denom;

                ### addons ###
                $pet->info->add_ons = $p->add_ons;
                foreach ($pet->info->add_ons as $addon) {
                    $product = Product::find($addon->prod_id);
                    if (empty($product)) {
                        continue;
                    }
                    if ($product->size_required == 'Y') {
                        $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $addon->prod_id)->where('size_id', $pet->size)->first();
                    } else {
                        $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $addon->prod_id)->first();
                    }if (empty($denom)) {
                        continue;
                    }
                    $addon->denom = $denom->denom;
                }

                $pet->info->sub_total = ScheduleProcessor::get_sub_total_by_pet($pet->info->package, $pet->info->add_ons, $pet->info->shampoo);
                $pet->info->tax = AppointmentProcessor::get_tax($address->zip, $pet->info->sub_total, 0, 0, 0);
                $pet->info->total = $pet->info->sub_total + $pet->info->tax;

                $pets[] = $pet;
            }

            if (!empty($promo)) {
                $package_list = [];
                foreach ($pets as $pet) {
                    $package_list[] = isset($pet->info->package) ? $pet->info->package->prod_id : '';
                }

                $msg = PromoCodeProcessor::checkIfUsed($user->user_id, $promo, null, $package_list);
                if (!empty($msg)) {
                    return [
                        'code' => 'promocode',
                        'msg'  => $msg
                    ];
                }
            }

            ### SAMEDAY BOOKING ###
            $sameday_booking = 0;
            $today = Carbon::now()->format('Y-m-d');
            $service_req_date = $request->date;

            if( $service_req_date == $today) {
                $sameday_booking = env('SAMEDAY_BOOKING');
                if( !empty($promo) && !empty($promo->type) && ($promo->type == 'S') ) { //In case of Membership, no sameday_booking
                    $sameday_booking = 0;
                }
            }

            ### FAV FEE, Favorite Groomer Fee ###
            $fav_type = isset($request->fav_type) ? $request->fav_type : '' ;
            $fav_groomer_id = isset($request->fav_groomer_id) ? $request->fav_groomer_id : null ;
            $fav_fee = 0;
            if( $fav_type == 'F' ) {
                if( !is_null($fav_groomer_id) && $fav_groomer_id > 0 ) {
                    $fav_fee = env('FAV_GROOMER_FEE');
                }else {
                    return [
                        'code' => 'promocode',
                        'msg'  => 'No Groomer is selected. Please start over your appointment.'
                    ];
                }
            }
//            if( $fav_type =='F' && !is_null($fav_groomer_id) && $fav_groomer_id > 0 ) {
//                $fav_fee = env('FAV_GROOMER_FEE');
//            }

            ### calculate ###
            ### get_total_price($pets, $promo, $zip, $use_credit)

            $ret = ScheduleProcessor::get_total_price($pets, $promo, $address->zip, $request->use_credit, 0, $user->user_id, $sameday_booking, $fav_fee );

            $ar = new \stdClass();
            $ar->datetime       = $request->date . ' ' . $time->time;
            $ar->time           = $time;
            $ar->pet            = $pets;
            $ar->address        = $address;
            $ar->payment        = $payment;

            $ar->fav_type =  $fav_type ;
            $ar->fav_groomer_id = $fav_groomer_id ;
            $ar->sub_total      = $ret['sub_total'];
            $ar->credit_amt     = $ret['credit_amt'];
            $ar->promo_code     = isset($promo) ? strtoupper($promo->code) : '';
            $ar->promo_amt      = $ret['promo_amt'];
            $ar->discount_applied = $ret['discount_applied'];
            $ar->tax            = $ret['tax'];
            $ar->safety_insurance = $ret['safety_insurance'];
            $ar->sameday_booking = $ret['sameday_booking'];
            $ar->fav_fee = $ret['fav_fee'];
            $ar->total          = $ret['total'];
            $ar->new_credit     = $ret['new_credit'];
            $ar->available_credit = $ret['available_credit'];

            return [
                'code'  => '0',
                'ar'    => $ar
            ];

        } catch (\Exception $ex) {
            Helper::log('Exception', [
              'EXCEPTION' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);

            return [
              'code'  => '-9',
              'msg'   => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ];
        }
    }

    #   appointment: {
    #       pet_type: string,
    #       pets: Array<{
    #           pet_id: number,
    #           package: number,
    #           shampoo: number,
    #           add_ons: Array<{
    #               prod_id: number
    #           }>
    #         }>,
    #       address_id: number,
    #       billing_id: number,
    #       date: string,
    #       time: int
    #   };
    public function confirm(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
            'api_key' => 'required',
            'token'   => 'required'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;


        $res = self::calculate_appointment($request, $user);

        if ($res['code'] !== '0') {
            return response()->json($res);
        }

        $ar = $res['ar'];

        return response()->json([
            'code'        => '0',
            'sub_total'   => $ar->sub_total,
            'credit_amt'  => $ar->credit_amt,
            'promo_code'  => $ar->promo_code,
            'promo_amt'   => $ar->promo_amt,
            'tax'         => $ar->tax,
            'safety_insurance' => $ar->safety_insurance,
            'sameday_booking' => $ar->sameday_booking,
            'fav_fee' => $ar->fav_fee,
            'discount_applied' => $ar->discount_applied,
            'total'       => $ar->total,
            'new_credit'  => $ar->new_credit,
            'available_credit'  => $ar->available_credit
        ]);
    }

    public function post(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $res = self::calculate_appointment($request, $user);

            if ($res['code'] !== '0') {
                return response()->json($res);
            }

            $ar = $res['ar'];

            $ar->place_id = $request->place_id ;
            $ar->place_other_name = $request->place_other_name ;

            ## order_from : D => desktop, A => app ###
            $ret = AppointmentProcessor::add_appointment($ar, $user, 'A');
            if (!empty($ret['msg'])) {
                return response()->json([
                    'code'  => '-1',
                    'msg'   => $ret['msg']
                ]);
            }

            return response()->json([
                'code'  => '0',
                'msg'   => 'The appointment successfully applied.',
                'appointment_id' => empty($ret['appointment_id']) ? '' : $ret['appointment_id']
            ]);

        } catch (\Exception $ex) {
            Helper::log('Exception', [
              'EXCEPTION' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);

            return response()->json([
                'code'  => '-9',
                'msg'   => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function update(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'appointment_id'   => 'required',
              'date'    => 'required|date',
              'time'    => 'required'
                //fav_type is added.
                //fav_groomer_id is added
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

//            $package = AppointmentProduct::where('appointment_id', $request->appointment_id)->first();
//            if (!empty($package) && in_array($package->prod_id, [28, 29])) {
//                return response()->json([
//                  'msg' => 'This is Non-Refundable Booking of ECO package. You cannot change the date/time.'
//                ]);
//            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $time = Helper::get_time_by_id($request->time);
            if (empty($time)) {
                throw new \Exception('Invalid time provided');
            }

            $datetime = $request->date . ' ' . $time->time;
            $msg = AppointmentProcessor::edit($user, $request->appointment_id, $datetime, $time, $request->fav_type, $request->fav_groomer_id );

            if (!empty($msg)) {
                return response()->json([
                    'code'  => '-2',
                    'msg'   => $msg
                ]);
            }

            return response()->json([
                'code'  => '0',
                'msg'   => 'The appointment has been updated successfully.'
            ]);

        } catch (\Exception $ex) {
            Helper::log('Exception', [
              'EXCEPTION' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);

            return response()->json([
              'code'  => '-9',
              'msg'   => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function cancel(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'appointment_id'   => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

//            $package = AppointmentProduct::where('appointment_id', $request->appointment_id)->first();
//            if (!empty($package) && in_array($package->prod_id, [28, 29])) {
//                return response()->json([
//                  'msg' => 'This is Non-Refundable Booking. Cancel is not allowed !!'
//                ]);
//            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $app = AppointmentList::find($request->appointment_id);
            if (empty($app)) {
                return response()->json([
                    'code'  => '-2',
                    'msg'   => 'Invalid appointment ID provided'
                ]);
            }

            $res = AppointmentProcessor::cancel($app, $user, $request->note);

            if (!empty($res['msg'])) {
                return response()->json([
                    'code'  => '-2',
                    'msg'   => $res['msg']
                ]);
            }

            return response()->json([
                'code'  => '0',
                'msg'   => 'The appointment cancelled successfully.'
            ]);

        } catch (\Exception $ex) {
            Helper::log('Exception', [
              'EXCEPTION' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);

            return response()->json([
              'code'  => '-9',
              'msg'   => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    //Return Rescheduling fee or Cancelling fee
    public function get_fee(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token'   => 'required',
                //'appointment_id'   => 'required', //Not required ,because of Favorite groomer fee
                'fee_type'   => 'required'       //R:Rescheduling Fee, C:Cancelling Fee, F:Favorite groomer fee
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'code' => '-1',
                    'msg' => $msg,
                    'fee_amount' => 0
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Invalid API key provided',
                    'fee_amount' => 0
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Your session has expired. Please login again. [ADR_GID]',
                    'fee_amount' => 0
                ]);
            }

            if( $request->fee_type == 'F' ) { //Favorite Groomer Fee
                return response()->json([
                    'code'  => '0',
                    'msg' => '',
                    'fee_amount'   => env('FAV_GROOMER_FEE')
                ]);
            }


            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $app = AppointmentList::find($request->appointment_id);
            if (empty($app)) {
                return response()->json([
                    'code'  => '-2',
                    'msg'   => 'Invalid appointment ID provided',
                    'fee_amount' => 0
                ]);
            }



            if( in_array( $request->fee_type , ['R','C']) ) { //Rescheduling Fee
                $ret = AppointmentProcessor::get_fee_amount($request->fee_type, $app );
                if( is_array($ret) && $ret['msg'] == '') {
                    $fee_amount = $ret['fee_amount'];
                }else {
                    return response()->json([
                        'code'  => '-4',
                        'msg'   => $ret['msg'],
                        'fee_amount' => 0
                    ]);
                }
            }else {
                return response()->json([
                    'code'  => '-3',
                    'msg'   => 'Wrong Fee Type',
                    'fee_amount' => 0
                ]);
            }


            return response()->json([
                'code'  => '0',
                'msg' => '',
                'fee_amount'   => $fee_amount
            ]);

        } catch (\Exception $ex) {
            Helper::log('Exception', [
                'EXCEPTION' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);

            return response()->json([
                'code'  => '-9',
                'msg'   => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString() ,
                'fee_amount' => 0
            ]);
        }
    }

    public function product(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
          'api_key'   => 'required',
          'token'     => 'required',
          'pet_type'  => 'required',
          'size_id'   => 'required_if:pet_type,dog',
          'zip'       => 'required',
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $allowzip = AllowedZip::where('zip', $request->zip)->where('available', 'x')->first();
        if (empty($allowzip)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Service is not available in the area. [' . $request->zip . ']'
            ]);
        }

        $packages = DB::select("
            select p.prod_id, p.prod_name, p.prod_desc, pd.group_id, pd.denom, 'N' as selected
              from product p
              join product_denom pd on p.prod_id = pd.prod_id
             where p.prod_type = 'P'
               and p.pet_type = :pet_type
               and p.status = 'A'
               and ((p.size_required = 'Y' and pd.size_id = :size_id) or (p.size_required <> 'Y'))
               and pd.group_id = :group_id
             order by seq 
            ", [
          'pet_type'    => $request->pet_type,
          'size_id'     => $request->size_id,
          'group_id'    => $allowzip->group_id
        ]);

        $shampoos = DB::select("
            select p.prod_id, p.prod_name, p.prod_desc, pd.group_id, pd.denom, 'N' as selected
              from product p
              join product_denom pd on p.prod_id = pd.prod_id
             where p.prod_type = 'S'
               and p.pet_type = :pet_type
               and p.status = 'A'
               and ((p.size_required = 'Y' and pd.size_id = :size_id) or (p.size_required <> 'Y'))
               and pd.group_id = :group_id
             order by seq 
            ", [
          'pet_type'    => $request->pet_type,
          'size_id'     => $request->size_id,
          'group_id'    => $allowzip->group_id
        ]);

        $add_ons = DB::select("
            select p.prod_id, p.prod_name, p.prod_desc, pd.group_id, pd.denom, 'N' as selected
              from product p
              join product_denom pd on p.prod_id = pd.prod_id
             where p.prod_type = 'A'
               and p.pet_type = :pet_type
               and p.status = 'A'
               and ((p.size_required = 'Y' and pd.size_id = :size_id) or (p.size_required <> 'Y'))
               and pd.group_id = :group_id
             order by seq 
            ", [
          'pet_type'    => $request->pet_type,
          'size_id'     => $request->size_id,
          'group_id'    => $allowzip->group_id
        ]);

        return response()->json([
          'code' => '0',
          'packages' => $packages,
          'shampoos' => $shampoos,
          'add_ons'  => $add_ons
        ]);
    }


    public function tip(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'appointment_id' => 'required',
              'tip' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $msg = AppointmentProcessor::tip($user->user_id, $request->appointment_id, $request->tip);
            if (!empty($msg)) {
                return response()->json([
                  'code' => '-3',
                  'msg'  => $msg
                ]);
            }

            return response()->json([
              'code' => '0',
              'msg'  => ''
            ]);
        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    public function rating(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'appointment_id' => 'required',
              'rating'  => 'required|regex:/^\d+$/'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $msg = AppointmentProcessor::rate($user->user_id, $request->appointment_id, $request->rating);
            if (!empty($msg)) {
                return response()->json([
                  'code' => '-3',
                  'msg'  => $msg
                ]);
            }

            return response()->json([
                'code' => '0',
                'msg'  => ''
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'code'  => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function survey(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token'   => 'required',
                'appointment_id' => 'required',
                //'ov'  => 'required|regex:/^\d{1}$/',
                'sc'  => 'required|regex:/^\d{1}$/',
                'gq'  => 'required|regex:/^\d{1}$/',
                'cl'  => 'required|regex:/^\d{1}$/',
                'va'  => 'required|regex:/^\d{1}$/',
                'cs'  => 'required|regex:/^\d{1}$/'
                //'su'  => 'regex:/^\d+$/',
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'code' => '-1',
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;
            //$ov = isset($request->ov) ? $request->ov : null;
            $sc = isset($request->sc) ? $request->sc : null;
            $gq = isset($request->gq) ? $request->gq : null;
            $cl = isset($request->cl) ? $request->cl : null;
            $va = isset($request->va) ? $request->va : null;
            $cs = isset($request->cs) ? $request->cs : null;
            $su = isset($request->su) ? $request->su : null;

            $msg = AppointmentProcessor::survey($user->user_id, $request->appointment_id,  $sc,$gq,$cl,$va,$cs,$su);
            if (!empty($msg)) {
                return response()->json([
                    'code' => '-3',
                    'msg'  => $msg
                ]);
            }

            return response()->json([
                'code' => '0',
                'msg'  => ''
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'code'  => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    public function survey_get(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token'   => 'required',
                'appointment_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'code' => '-1',
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $ap = AppointmentList::where("appointment_id",$request->appointment_id)->first();
            if (empty($ap)) {
                return response()->json([
                    'code' => '-3',
                    'msg' => 'Wrong Appointment ID.'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user) || $ap->user_id != $user->user_id ) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG

            $survey = AppointmentProcessor::survey_get($request->appointment_id);
            if (!empty($msg)) {
                return response()->json([
                    'code' => '0',
                    'survey'  => ''
                ]);
            }

            return response()->json([
                'code' => '0',
                'survey'  => $survey
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'code'  => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_by_id(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'appointment_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $ap = AppointmentList::where("appointment_id",$request->appointment_id)->first();
            if (empty($ap)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Please enter an valid appointment id.'
                ]);
            }
            $ap = AppointmentProcessor::get_info_v3($ap);

            return response()->json([
                'code' => '0',
                'appointment' => $ap
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code'  => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    public function get_count(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $count = AppointmentList::where("user_id",$user->user_id)
                ->whereNotIn('status', ['C', 'L'])
                ->count();

            //Save App version.
            $app_version = new AppVersion();
            $app_version->user_id = $user->user_id;
            $app_version->app_ver =  empty($request->app_ver) ? '' : $request->app_ver ;
            $app_version->cdate      = Carbon::now();
            $app_version->save();

            //Version Upgrades.
            if($request->app_ver < '2.0.60'){
                return response()->json([
                    'code' => '-10',
                    'msg' => 'We are sorry but your current version is out of date. Please download and install the latest version from App Store.'
                ]);
            }else {

                 //-20 : show warning message from admin to customers when emergency notices.
//                return response()->json([
//                    'code' => '-20',
//                    'msg' => 'Currently we cannot accept bookings until 05/01.<br/>We hope to service you soon.',
//                    'count' => $count
//                ]);
                return response()->json([
                    'code' => '0',
                    'count' => $count
                ]);
            }

        } catch (\Exception $ex) {
            return response()->json([
              'code'  => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    //================== package tour ===================//

    public function explode_get_sizes(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            ############################################
            ###############  END VALIDATION  ###########

            $sizes = DB::select("
                select
                    size_id,
                    size_name,
                    size_desc,
                    size
                from size
                order by size_id
            ");


            return response()->json([
                'code' => '0',
                'sizes' => $sizes
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code'  => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function explode_get_package_addon(Request $request)
    {
        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key'   => 'required',
              'pet_type'  => 'required',
              'size_id'   => 'required_if:pet_type,dog',
              'zip' => ''
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            ############################################
            ###############  END VALIDATION  ###########

            $allowzip = AllowedZip::where('zip', $request->zip)->where('available', 'x')->first();
            if (empty($allowzip)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Service is not available in the area. [' . $request->zip . ']'
                ]);
            }

            $packages = DB::select("
            select p.prod_id, p.prod_name, p.prod_desc, pd.group_id, pd.denom, 'N' as selected
              from product p
              join product_denom pd on p.prod_id = pd.prod_id
             where p.prod_type = 'P'
               and p.pet_type = :pet_type
               and p.status = 'A'
               and ((p.size_required = 'Y' and pd.size_id = :size_id) or (p.size_required <> 'Y'))
               and pd.group_id = :group_id
             order by seq 
            ", [
              'pet_type'    => $request->pet_type,
              'size_id'     => $request->size_id,
              'group_id'    => $allowzip->group_id
            ]);

            $shampoos = DB::select("
            select p.prod_id, p.prod_name, p.prod_desc, pd.group_id, pd.denom, 'N' as selected
              from product p
              join product_denom pd on p.prod_id = pd.prod_id
             where p.prod_type = 'S'
               and p.pet_type = :pet_type
               and p.status = 'A'
               and ((p.size_required = 'Y' and pd.size_id = :size_id) or (p.size_required <> 'Y'))
               and pd.group_id = :group_id
             order by seq 
            ", [
              'pet_type'    => $request->pet_type,
              'size_id'     => $request->size_id,
              'group_id'    => $allowzip->group_id
            ]);

            $add_ons = DB::select("
            select p.prod_id, p.prod_name, p.prod_desc, pd.group_id, pd.denom, 'N' as selected
              from product p
              join product_denom pd on p.prod_id = pd.prod_id
             where p.prod_type = 'A'
               and p.pet_type = :pet_type
               and p.status = 'A'
               and ((p.size_required = 'Y' and pd.size_id = :size_id) or (p.size_required <> 'Y'))
               and pd.group_id = :group_id
             order by seq 
            ", [
              'pet_type'    => $request->pet_type,
              'size_id'     => $request->size_id,
              'group_id'    => $allowzip->group_id
            ]);

            return response()->json([
              'code' => '0',
              'packages' => $packages,
              'shampoos' => $shampoos,
              'add_ons' => $add_ons
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_survey_appt_id(Request $request) {
        try {
            $v = \Validator::make($request->all(), [
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

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [USR_UPD]'
                ]);
            }

            $ret =
                \Illuminate\Support\Facades\DB::select("select a.appointment_id, a.groomer_id, g.first_name, g.last_name,  date_format(a.accepted_date, '%m/%d/%Y %H:%i') accepted_date, a.total
            from appointment_list a left join groomer g on a.groomer_id = g.groomer_id 
            where a.appointment_id not in (select appointment_id from survey)
            and a.user_id = :user_id
            and a.status = 'P'
            and a.accepted_date >= curdate() - interval 30 day
            order by a.accepted_date desc
            limit 0,1
            ", [
                    'user_id' => $user->user_id
                ]);
            if (count($ret) > 0) {
                $appointment_id = $ret[0]->appointment_id;
            } else {

                return response()->json([
                    'msg' => 'We can not find a valid appointment for you',
                    'appointment_id' => ''
                ]);

            }

            return response()->json([
                'msg' => '',
                'appointment_id' => $appointment_id,
                'groomer_id' => $ret[0]->groomer_id,
                'first_name' => $ret[0]->first_name,
                'last_name' => $ret[0]->last_name,
                'accepted_date' => $ret[0]->accepted_date,
                'total' => $ret[0]->total,
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}