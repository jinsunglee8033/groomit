<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/8/18
 * Time: 2:53 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\ScheduleProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Lib\AppointmentProcessor;
use App\Lib\Helper;
use App\Lib\UserProcessor;

use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\Pet;
use App\Model\Product;
use App\Model\ProductDenom;
use App\Model\PromoCode;
use App\Model\User;
use App\Model\UserBilling;

use DB;
//Seems not to be used any longer.
//class APIController extends Controller
//{
//
//
//    //No longer used, could be removed at any time.
////    public function index() {
////        Session::put('user.menu.show', 'Y');
////        Session::put('user.menu.top-title', 'Home');
////
////        ##############
////        $user = \App\User::find(87);
////        Auth::guard('user')->login($user);
////        ##############
////
////        $user_id = $user->user_id;
////
////        Helper::log('### HomeController.show() ###', [
////            'Yo'
////        ]);
////
////        ### get upcoming ###
////        $upcoming = AppointmentProcessor::get_upcoming($user_id, 1);
////
////        ### get recent ###
////        $recent = AppointmentProcessor::get_recent($user_id, 1);
////
////        Helper::log('### upcoming ###', [
////            $upcoming
////        ]);
////
////        //$user->referral_code = UserProcessor::get_referral_code($user->user_id);
////        $referral_arr = UserProcessor::get_referral_code($user->user_id);
////        $user->referral_code = $referral_arr['referral_code'];
////        $user->referral_amount = $referral_arr['referral_amount'];
////
////        return response()->json([
////            'upcoming' => $upcoming,
////            'recent' => $recent,
////            'user' => $user
////        ]);
////    }
//
//    //seems not to be used.
////    public function pet_size_list(Request $request) {
////        $sizes = DB::select("
////            select size_id, size_name, size_desc, size from size
////            ");
////
////        Helper::log('### sizes ###', [
////            $sizes
////        ]);
////
////        return response()->json([
////            'code' => '0',
////            'sizes' => $sizes
////        ]);
////    }
//
//    //No routing, so could be removed.
////    public function address_list(Request $request) {
////        $addresses = DB::select("
////            select address_id, name, address1, address2, city, state, zip, lat, lng, default_address
////              from address
////             where user_id = :user_id
////               and status = 'A'
////            ", [
////                'user_id' => $request->user_id
////            ]);
////
////        Helper::log('### addresses ###', [
////            $addresses
////        ]);
////
////        return response()->json([
////            'code' => '0',
////            'addresses' => $addresses
////        ]);
////    }
//
//// Seems not to be usee
////    public function pet_list(Request $request) {
////        $address = Address::find($request->address_id);
////        $allowzip = AllowedZip::where('zip', $address->zip)->where('available', 'x')->first();
////
////        $pets = DB::select("
////            select pet_id, name, type, gender, breed, size as size_id
////              from pet
////             where user_id = :user_id
////               and type = :pet_type
////            ", [
////                'user_id' => $request->user_id,
////                'pet_type' => $request->pet_type
////            ]);
////
////        foreach ($pets as $pet) {
////            $pet->packages = DB::select("
////                select p.prod_id, p.prod_name, p.prod_desc, pd.group_id, pd.denom, 'N' as selected
////                  from product p
////                  join product_denom pd on p.prod_id = pd.prod_id
////                 where p.prod_type = 'P'
////                   and p.pet_type = :pet_type
////                   and p.status = 'A'
////                   and ((p.size_required = 'Y' and pd.size_id = :size_id) or (p.size_required <> 'Y'))
////                   and pd.group_id = :group_id
////                 order by seq
////                ", [
////                    'pet_type' => $pet->type,
////                    'size_id' => $pet->size_id,
////                    'group_id' => $allowzip->group_id
////                ]);
////
////            $pet->shampoos = DB::select("
////                select p.prod_id, p.prod_name, p.prod_desc, pd.group_id, pd.denom, 'N' as selected
////                  from product p
////                  join product_denom pd on p.prod_id = pd.prod_id
////                 where p.prod_type = 'S'
////                   and p.pet_type = :pet_type
////                   and p.status = 'A'
////                   and ((p.size_required = 'Y' and pd.size_id = :size_id) or (p.size_required <> 'Y'))
////                   and pd.group_id = :group_id
////                 order by seq
////                ", [
////                    'pet_type' => $pet->type,
////                    'size_id' => $pet->size_id,
////                    'group_id' => $allowzip->group_id
////                ]);
////
////            $pet->add_ons = DB::select("
////                select p.prod_id, p.prod_name, p.prod_desc, pd.group_id, pd.denom, 'N' as selected
////                  from product p
////                  join product_denom pd on p.prod_id = pd.prod_id
////                 where p.prod_type = 'A'
////                   and p.pet_type = :pet_type
////                   and p.status = 'A'
////                   and ((p.size_required = 'Y' and pd.size_id = :size_id) or (p.size_required <> 'Y'))
////                   and pd.group_id = :group_id
////                 order by seq
////                ", [
////                    'pet_type'  => $pet->type,
////                    'size_id'   => $pet->size_id,
////                    'group_id' => $allowzip->group_id
////                ]);
////        }
////
////        Helper::log('### PETS ###', [
////            $pets
////        ]);
////
////        return response()->json([
////            'code' => '0',
////            'pets' => $pets
////        ]);
////    }
//
////Seems not to be used
////    public function billing_list(Request $request) {
////        $billings = DB::select("
////            select billing_id, card_number, card_holder, default_card
////              from user_billing
////             where user_id = :user_id
////               and status = 'A'
////            ", [
////                'user_id' => $request->user_id
////            ]);
////
////        Helper::log('### billings ###', [
////            $billings
////        ]);
////
////        return response()->json([
////            'code' => '0',
////            'billings' => $billings
////        ]);
////    }
//
////    public function times(Request $request) {
////        $times = Helper::get_time_windows();
////
////        return response()->json([
////            'code' => '0',
////            'times' => $times
////        ]);
////    }
//
//    //Seems not to be used.
////    public function post_appointment(Request $request) {
////        try {
////            $times = Helper::get_time_windows();
////            $time = null;
////
////            foreach ($times as $t) {
////                if ($t->id == $request->time) {
////                    $time = $t;
////                }
////            }
////
////            Helper::log('time', $time);
////
////            $address = Address::find($request->address_id);
////            if (empty($address)) {
////                return response()->json([
////                  'code' => 'address',
////                  'msg'  => 'Can not find the address'
////                ]);
////            }
////
////            Helper::log('address', $address);
////
////            $allowzip = AllowedZip::where('zip', $address->zip)->where('available', 'x')->first();
////            if (empty($allowzip)) {
////                return response()->json([
////                  'code' => 'address',
////                  'msg'  => 'The address is not our service area'
////                ]);
////            }
////
////            Helper::log('allowzip', $allowzip);
////
////            $payment = UserBilling::find($request->billing_id);
////            if (empty($payment)) {
////                return response()->json([
////                  'code' => 'payment',
////                  'msg'  => 'Can not found the payment information.'
////                ]);
////            }
////
////            Helper::log('payment', $payment);
////
////            $promo = PromoCode::find(strtoupper($request->promo_code));
////
////
////            Helper::log('promo', $promo);
////
////            Helper::log('REQUEST-PETS', $request->pets);
////
////            Helper::log('JSON-PETS ################### START');
////            // $reqpets = (object)$request->pets;
////            $reqpets = json_decode (json_encode ($request->pets), FALSE);
////            Helper::log('JSON-PETS ################### END');
////
////            Helper::log('JSON-PETS', $reqpets);
////
////            $pets = [];
////            foreach ($reqpets as $p) {
////                $p = (object) $p;
////                Helper::log('############ LOOPING ######  ', $p);
////
////                $pet = Pet::find($p->pet_id);
////                if (empty($pet)) {
////                    continue;
////                }
////
////                Helper::log('pet', $pet);
////
////                $pet->info = new \stdClass();
////
////                ### package ###
////                $pet->info->package = Product::find($p->package);
////                if (empty($pet->info->package)) {
////                    continue;
////                }
////                if ($pet->info->package->size_required == 'Y') {
////                    $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $p->package)->where('size_id', $pet->size)->first();
////                } else {
////                    $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $p->package)->first();
////                }
////                if (empty($denom)) {
////                    continue;
////                }
////                $pet->info->package->denom = $denom->denom;
////
////                Helper::log('package', $pet->info->package);
////
////                ### shampoo ###
////                $pet->info->shampoo = Product::find($p->shampoo);
////                if (empty($pet->info->shampoo)) {
////                    continue;
////                }
////                if ($pet->info->shampoo->size_required == 'Y') {
////                    $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $p->shampoo)->where('size_id', $pet->size)->first();
////                } else {
////                    $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $p->shampoo)->first();
////                }
////                if (empty($denom)) {
////                    continue;
////                }
////                $pet->info->shampoo->denom = $denom->denom;
////
////                Helper::log('shampoo', $pet->info->shampoo);
////
////
////                Helper::log('############ ADDONS ######  ', $p->add_ons);
////
////                ### addons ###
////                $pet->info->add_ons = $p->add_ons;
////                foreach ($pet->info->add_ons as $addon) {
////                    Helper::log('############ ADDONS ## ADDON ######  ', $addon);
////                    $product = Product::find($addon->prod_id);
////                    if (empty($product)) {
////                        continue;
////                    }
////                    if ($product->size_required == 'Y') {
////                        $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $addon->prod_id)->where('size_id', $pet->size)->first();
////                    } else {
////                        $denom = ProductDenom::where('group_id', $allowzip->group_id)->where('prod_id', $addon->prod_id)->first();
////                    }if (empty($denom)) {
////                        continue;
////                    }
////                    $addon->denom = $denom->denom;
////                }
////
////
////                Helper::log('addons', $pet->info->add_ons);
////
////                $pet->info->sub_total = ScheduleProcessor::get_sub_total_by_pet($pet->info->package, $pet->info->add_ons, $pet->info->shampoo);
////                $pet->info->tax = AppointmentProcessor::get_tax($address->zip, $pet->info->sub_total, 0, 0, 0);
////                $pet->info->total = $pet->info->sub_total + $pet->info->tax;
////
////                Helper::log('############ pet #####', $pet);
////
////                $pets[] = $pet;
////            }
////
////
////            Helper::log('############# TOTAL PRICE ### PETS ### ', $pets);
////            Helper::log('############# TOTAL PRICE ### address ### ', $address);
////
////            ### calculate ###
////            ### get_total_price($pets, $promo, $zip, $use_credit)
////
////            ### SAMEDAY BOOKING ###
////            $sameday_booking = 0;
////            $today = Carbon::now()->format('Y-m-d');
////            $service_req_date = $request->date ;
////            if( $service_req_date == $today) {
////                $sameday_booking = env('SAMEDAY_BOOKING');
////                if( !empty($promo) && !empty($promo->type) && ($promo->type == 'S') ) { //In case of Membership, no sameday_booking
////                    $sameday_booking = 0;
////                }
////            }
////            //fav_groomer_fee : Seems not to be used, so skip.
////
////            $ret = ScheduleProcessor::get_total_price($pets, $promo, $address->zip, $request->use_credit, null, $sameday_booking);
////
////            Helper::log('############# TOTAL PRICE', $ret);
////
////            $sub_total      = $ret['sub_total'];
////            $credit_amt     = $ret['credit_amt'];
////            $promo_amt      = $ret['promo_amt'];
////            $tax            = $ret['tax'];
////            $safety_insurance = $ret['safety_insurance'];
////            $total          = $ret['total'];
////            $new_credit     = $ret['new_credit'];
////
////            $ar = new \stdClass();
////            $ar->datetime   = $request->date . ' ' . $time->time;
////            $ar->time       = $time;
////            $ar->pet        = $pets;
////            $ar->address    = $address;
////            $ar->payment    = $payment;
////            $ar->fav_type = isset($request->fav_type) ? $request->fav_type : '' ;
////
////            $ar->sub_total  = $sub_total;
////            $ar->credit_amt = $credit_amt;
////            $ar->promo_code = isset($promo) ? $promo->code : '';
////            $ar->promo_amt  = $promo_amt;
////            $ar->tax        = $tax;
////            $ar->safety_insurance = $safety_insurance;
////            $ar->sameday_booking = $sameday_booking;
////            $ar->total      = $total;
////            $ar->new_credit = $new_credit;
////
////            $user = User::find($request->user_id);
////            if (empty($user)) {
////                return response()->json([
////                    'msg' => 'User not found. Please login again!'
////                ]);
////            }
////
////            ## order_from : D => desktop, A => app ###, Not to be used any longer.
////            $ret = AppointmentProcessor::add_appointment($ar, $user, 'A');
////            if (!empty($ret['msg'])) {
////                Helper::log('############# ADD APPOINTMENT ERROR #####', $ret);
////
////                return response()->json([
////                    'code'  => '-1',
////                    'msg' => $ret['msg']
////                ]);
////            }
////
////            return response()->json([
////                'code'  => '0',
////                'msg'   => 'The appointment successfully applied.' ,
////                'appointment_id' => empty($ret['appointment_id']) ? '' : $ret['appointment_id']
////            ]);
////
////        } catch (\Exception $ex) {
////            Helper::log('Exception', [
////              'EXCEPTION' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
////            ]);
////
////            return response()->json([
////                'code'  => '-9',
////                'msg'   => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
////            ]);
////        }
////    }
//
//}