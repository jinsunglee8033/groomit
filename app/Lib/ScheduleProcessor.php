<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 7/20/18
 * Time: 10:08 AM
 */

namespace App\Lib;


use App\Model\Address;
use App\Model\AllowedExceptionPackage;
use App\Model\AllowedExceptionPet;
use App\Model\AllowedZip;
use App\Model\Pet;
use App\Model\Product;
use App\Model\PromoCode;
use App\Model\Size;
use App\Model\UserBilling;
use Auth;
use Carbon\Carbon;
use Session;
use stdClass;

class ScheduleProcessor
{

    ### BEGIN - current selection ###

    public static function clearAll() {
        self::setCurrentAddons(null);
        self::setCurrentPackage(null);
        self::setCurrentPetType(null);
        self::setCurrentShampoo(null);
        self::setCurrentSize(null);
        self::setCurrentPet(null);

        self::setPayment(null);
        self::setAddress(null);
        self::setDate(null);
        self::setTime(null);
        self::setUseCredit(null);

        // self::setZip(null);
        self::setPromoCode(null);
        self::setPets(null);
        self::setPlace(null);
        self::setPlaceOther(null);

        // FAV GROOMER
        self::setFavGroomer(null);
        self::setFavGroomer_id(null);

    }

    public static function getCurrentPackageId() {
        $package = self::getCurrentPackage();
        if (empty($package)) {
            return null;
        }

        return $package->prod_id;
    }

    public static function getCurrentPackageName() {
        $package = self::getCurrentPackage();
        if (empty($package)) {
            return null;
        }

        return $package->prod_name;
    }

    public static function getCurrentPackage() {
        return Session::get('schedule.current-package');
    }

    public static function setCurrentPackage(Product $package = null) {

        if (!empty($package)) {
            if ($package->prod_type != 'P') {
                throw new \Exception('Invalid package provided');
            }

            $current_pet_type = self::getCurrentPetType();
            if (!empty($current_pet_type) && $current_pet_type != $package->pet_type) {
                throw new \Exception('Pet type does not match: current(' . $current_pet_type . ') / package(' . $package->pet_type . ')');
            }

            $zip = self::getZip();
            $size = self::getCurrentSize();

            $package->denom = Helper::get_price($package->prod_id, $size, $zip);
        }

        Session::put('schedule.current-package', $package);
    }

    public static function getCurrentShampooId() {
        $shampoo = self::getCurrentShampoo();
        if (empty($shampoo)) {
            return null;
        }

        return $shampoo->prod_id;
    }

    public static function getCurrentShampoo() {
        return Session::get('schedule.current-shampoo');
    }

    public static function setCurrentShampoo(Product $shampoo = null) {
        if (!empty($shampoo)) {
            if ($shampoo->prod_type != 'S') {
                throw new \Exception('Invalid shampoo provided');
            }

            $zip = self::getZip();
            $size = self::getCurrentSize();
            $shampoo->denom = Helper::get_price($shampoo->prod_id, $size, $zip);
        }

        Session::put('schedule.current-shampoo', $shampoo);
    }

    public static function getCurrentSize() {
        return Session::get('schedule.current-size');
    }

    public static function setCurrentSize($size, $need_reset = false) {
        if (!is_null($size)) {
            $o = Size::find($size);
            if (empty($o)) {
                throw new \Exception('Invalid size provided');
            }
        }

        Session::put('schedule.current-size', $size);

        if ($need_reset) {
            ### need to reset package & add-on & shampoo since size changed ###
            $package = self::getCurrentPackage();
            self::setCurrentPackage($package);

            $shampoo = self::getCurrentShampoo();
            self::setCurrentShampoo($shampoo);

            $addons = self::getCurrentAddons();
            if (count($addons) > 0) {
                foreach ($addons as $o) {
                    $zip = self::getZip();
                    $size = self::getCurrentSize();
                    $o->denom = Helper::get_price($o->prod_id, $size, $zip);
                }
            }
            self::setCurrentAddons($addons);
        }
    }

    public static function getCurrentPetType() {
        return Session::get('schedule.current-pet-type');
    }

    public static function setCurrentPetType($pet_type) {
        if (!empty($pet_type)) {
            $first_pet_type = self::getFirstPetType();
            if (!empty($first_pet_type) && $first_pet_type != $pet_type) {
                throw new \Exception('Pet type mismatch with other pets');
            }
        }

        Session::put('schedule.current-pet-type', $pet_type);
    }

    public static function getFirstPetType() {

        $pets = self::getPets();
        if (empty($pets)) {
            return null;
        }

        if (count($pets) < 1) {
            return null;
        }

        return $pets[0]->type;
    }

    public static function getCurrentAddons() {
        return Session::get('schedule.current-add-ons');
    }

    public static function setCurrentAddons($addons) {
        Session::put('schedule.current-add-ons', $addons);
    }

    public static function addToCurrentAddons(Product $addon) {
        if (empty($addon)) {
            throw new \Exception('Empty addon provided');
        }

        if ($addon->prod_type != 'A') {
            throw new \Exception('Invalid addon provided');
        }

        $current_addons = self::getCurrentAddons();
        $found = false;
        if (count($current_addons)) {
            foreach ($current_addons as $o) {
                if ($o->prod_id == $addon->prod_id) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            $zip = self::getZip();
            $size = self::getCurrentSize();
            $addon->denom = Helper::get_price($addon->prod_id, $size, $zip);
            Session::push('schedule.current-add-ons', $addon);
        }
    }

    public static function removeFromCurrentAddons(Product $addon) {
        if (empty($addon)) {
            throw new \Exception('Empty addon provided');
        }

        if ($addon->prod_type != 'A') {
            throw new \Exception('Invalid addon provided');
        }

        $current_addons = self::getCurrentAddons();
        $idx = 0;
        if (count($current_addons) > 0) {
            foreach ($current_addons as $o) {
                if ($o->prod_id == $addon->prod_id) {
                    unset($current_addons[$idx]);
                    $current_addons = array_values($current_addons);
                    break;
                }
                $idx++;
            }
        }

        Session::put('schedule.current-add-ons', $current_addons);
    }

    public static function setAddAnotherPet($add_another_pet) {
        Session::put('schedule.add-another-pet', $add_another_pet);
    }

    public static function getAddAnotherPet() {
        return Session::get('schedule.add-another-pet');
    }

    ### END - current selection ###



    ### BEGIN - schedule info ###

    public static function getPets() {
        return Session::get('schedule.pets');
    }

    public static function addToPets(Pet $pet, $clear = false) {
        if (empty($pet)) {
            throw new \Exception('Empty pet provided');
        }

        $current_pet_type = self::getCurrentPetType();
        if (!empty($current_pet_type) && $current_pet_type != $pet->type) {
            throw new \Exception('Invalid pet type');
        }

        $pets = self::getPets();

        $idx = 0;
        if (count($pets) > 0) {
            foreach ($pets as $o) {
                if ($o->pet_id == $pet->pet_id) {
                    unset($pets[$idx]);
                    $pets = array_values($pets);
                    break;
                }
                $idx++;
            }
        }

        ### if while not in adding antoher pet, remove current pet ###
        $add_another_pet = self::getAddAnotherPet();
        if ($add_another_pet != 'Y') {
            $idx = 0;
            $current_pet = self::getCurrentPet();
            if (count($pets) > 0 && !empty($current_pet)) {
                foreach ($pets as $o) {
                    if ($o->pet_id == $current_pet->pet_id) {
                        unset($pets[$idx]);
                        $pets = array_values($pets);
                        break;
                    }
                    $idx++;
                }
            }
        }

        $info = new stdClass();
        $info->package = self::getCurrentPackage();
        if (empty($info->package)) {
            throw new \Exception('Current package is not set');
        }

        $info->shampoo = self::getCurrentShampoo();
        if (empty($info->shampoo)) {
            throw new \Exception('Current shampoo is not set');
        }

        $info->add_ons = self::getCurrentAddons();
        $info->sub_total = self::getCurrentSubTotal();
        $zip = self::getZip();
        $info->tax = AppointmentProcessor::get_tax($zip, $info->sub_total, 0, 0, 0);
        $info->total = $info->sub_total + $info->tax;
        $pet->info = $info;

        $pets[] = $pet;
        Session::put('schedule.pets', $pets);

        if ($clear) {
            self::clearCurrentSelection();
        }
    }

    public static function getCurrentSubTotal() {

        ### package ###
        $package = self::getCurrentPackage();

        ### add-on ###
        $addons = self::getCurrentAddons();

        ### shampoo ###
        $shampoo = self::getCurrentShampoo();

        return self::get_sub_total_by_pet($package, $addons, $shampoo);
    }

    public static function get_sub_total_by_pet($package, $addons, $shampoo) {
        $sub_total = 0;

        ### package ###
        if (!empty($package)) {
            $sub_total += $package->denom;
        }

        ### add-on ###
        if (count($addons) > 0) {
            foreach ($addons as $o) {
                $sub_total += $o->denom;
            }
        }

        ### shampoo ###
        if (!empty($shampoo)) {
            $sub_total += $shampoo->denom;
        }

        return $sub_total;
    }

    public static function clearCurrentSelection() {
        self::setCurrentPackage(null);
        self::setCurrentShampoo(null);
        self::setCurrentAddons(null);
        self::setCurrentSize(null);
        self::setCurrentPet(null);
        self::setAddress(null);
    }

    public static function removeFromPets(Pet $pet) {
        if (empty($pet)) {
            throw new \Exception('Empty pet provided');
        }

        $current_pet_type = self::getCurrentPetType();
        if (!empty($current_pet_type) && $current_pet_type != $pet->type) {
            throw new \Exception('Invalid pet type');
        }

        $current_pets = self::getPets();
        $idx = 0;
        if (count($current_pets) > 0) {
            foreach ($current_pets as $o) {
                if ($o->pet_id == $pet->pet_id) {
                    unset($current_pets[$idx]);
                    $current_pets = array_values($current_pets);
                    break;
                }
                $idx++;
            }
        }

        Session::put('schedule.pets', $current_pets);
    }

    public static function removePetByID($pet_id) {
        $current_cleared = false;

        if (empty($pet_id) || self::isCurrentPet($pet_id)) {
            self::clearCurrentSelection();
            $current_cleared = true;
        }

        $current_pets = self::getPets();
        $idx = 0;
        if (count($current_pets) > 0) {
            if (!empty($pet_id)) {
                foreach ($current_pets as $o) {
                    if ($o->pet_id == $pet_id) {
                        unset($current_pets[$idx]);
                        $current_pets = array_values($current_pets);
                        break;
                    }
                    $idx++;
                }
            }

            if ($current_cleared && count($current_pets) > 0) {
                foreach ($current_pets as $o) {
                    self::setCurrentSize($o->size);
                    self::setCurrentPackage($o->info->package);
                    self::setCurrentShampoo($o->info->shampoo);
                    self::setCurrentAddons($o->info->add_ons);
                    $pet = Pet::find($o->pet_id);
                    self::setCurrentPet($pet);
                    break;
                }
            }
        }

        Session::put('schedule.pets', $current_pets);
    }

    public static function getRebook() {
        return Session::get('schedule.rebook');
    }

    public static function setRebook($rebook) {
        Session::put('schedule.rebook', $rebook);
    }

    public static function getPlace() {
        return Session::get('schedule.place');
    }

    public static function setPlace($place) {
        Session::put('schedule.place', $place);
    }

    public static function getPlaceOther() {
        return Session::get('schedule.place_other');
    }

    public static function setPlaceOther($place_other) {
        Session::put('schedule.place_other', $place_other);
    }

    // FAV GROOMER
    public static function getFavGroomer() {
        return Session::get('schedule.fav-groomer');
    }
    public static function setFavGroomer($favGroomer) {
        Session::put('schedule.fav-groomer', $favGroomer);
    }

    public static function getFavGroomer_id() {
        return Session::get('schedule.fav-groomer-id');
    }
    public static function setFavGroomer_id($favGroomer_id) {
        Session::put('schedule.fav-groomer-id', $favGroomer_id);
    }
//    FAV GROOMER

    public static function getDate() {
        return Session::get('schedule.date');
    }

    public static function setDate(Carbon $date = null) {
        if (!empty($date)) {
            $date = $date->format('Y-m-d');
        }
        Session::put('schedule.date', $date);
    }

    public static function getTime() {
        return Session::get('schedule.time');
    }

    public static function setTime(stdClass $time = null) {

        if (!empty($time)) {
            $time_windows = Helper::get_time_windows();
            $found = false;
            foreach ($time_windows as $o) {
                if ($o->id == $time->id) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new \Exception('Invalid time object provided');
            }
        }

        Session::put('schedule.time', $time);
    }

    public static function getZip() {
        $zip = Session::get('schedule.zip');
        if (empty($zip)) {
            if (Auth::guard('user')->check()) {
                $address = Address::where('user_id', Auth::guard('user')->user()->user_id)
                    ->where('status', 'A')
                    ->orderBy('address_id', 'desc')
                    ->first();

                if (!empty($address)) {
                    $zip = $address->zip;
                    self::setZip($zip);
                } else {
                    $zip = Auth::guard('user')->user()->zip;
                    self::setZip($zip);
                }
            }
        }

        return Session::get('schedule.zip');
    }

    public static function setZip($zip = null) {
        if (!empty($zip)) {
            $allowed_zip = AllowedZip::where('zip', $zip)
                ->where('available', 'x')
                ->first();

            if (empty($allowed_zip)) {
                throw new \Exception('Service is not available in the area');
            }

//            $pet_type = self::getCurrentPetType();
//            if (!empty($pet_type) && $pet_type == 'cat' && $allowed_zip->state_abbr == 'NJ') {
//                throw new \Exception('Cat service is not available in New Jersey');
//            }
        }

        Session::put('schedule.zip', $zip);
    }

    public static function setAddress1($address1 = null) {
        Session::put('schedule.address1', $address1);
    }
    public static function setCity($city = null) {
        Session::put('schedule.city', $city);
    }
    public static function setState($state = null) {
        Session::put('schedule.state', $state);
    }

    public static function getAddress1() {
        return Session::get('schedule.address1');
    }
    public static function getCity() {
        return Session::get('schedule.city');
    }
    public static function getState() {
        return Session::get('schedule.state');
    }

    public static function getPaymentId() {
        $payment = self::getPayment();
        if (empty($payment)) {
            return null;
        }

        return $payment->billing_id;
    }

    public static function getPayment() {
        return Session::get('schedule.payment');
    }

    public static function setPayment(UserBilling $payment = null) {
        if (!empty($payment) && $payment->status != 'A') {
            throw new \Exception('Payment status is not active');
        }

        Session::put('schedule.payment', $payment);
    }

    public static function getPromoCode() {
        return Session::get('schedule.promo');
    }

    public static function setPromoCode(PromoCode $code = null) {
        if (!empty($code)) {
            $pets = self::getPets();

            $package_list = [];
            foreach ($pets as $pet) {
                $package_list[] = isset($pet->info->package) ? $pet->info->package->prod_id : '';
            }

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Please login first');
            }

            $msg = PromoCodeProcessor::checkIfUsed($user->user_id, $code, null, $package_list);
            if (!empty($msg)) {
                throw new \Exception($msg);
            }
        }

        Session::put('schedule.promo', $code);
    }

    public static function getUseCredit() {
        return Session::get('schedule.use-credit');
    }

    public static function setUseCredit($use_credit) {
        Session::put('schedule.use-credit', $use_credit);
    }

    public static function getAvailableCredit($user_id = null) {
        if (empty($user_id)) {
            if (!Auth::guard('user')->check()) {
                return 0;
            }

            $user = Auth::guard('user')->user();
            return CreditProcessor::getAvailableCredit($user->user_id);
        } else {
            return CreditProcessor::getAvailableCredit($user_id);
        }
    }

    public static function getTotal() {

        ### pets ###
        $pets = self::getPets();

        ### promo ###
        $promo = self::getPromoCode();

        ### zip ###
        $zip = self::getZip();

        ### use or not credit ###
        $use_credit = self::getUseCredit();


        ### SAMEDAY BOOKING ###
        $sameday_booking = 0;
        $today = Carbon::now()->format('Y-m-d');
        $service_req_date = self::getDate();

        if( $service_req_date == $today) {
            $sameday_booking = env('SAMEDAY_BOOKING');

            if( !empty($promo) && !empty($promo->type) && ($promo->type == 'S') ) { //In case of Membership, no sameday_booking
                $sameday_booking = 0;
            }
        }

        ### Favorite Groomer Fee, fav_Fee ###
        $fav_type = self::getFavGroomer();
        $fav_groomer_id = self::getFavGroomer_id() ;
        $fav_fee = 0;
        if($fav_type == 'F') {
            if( !is_null($fav_groomer_id) && $fav_groomer_id > 0) {
                $fav_fee = env('FAV_GROOMER_FEE');
            }else {
                return null;
            }
        }
//
//        if( $today >= '2019-09-18' ){
//            $service_req_date = self::getDate();
//
//            if( $service_req_date == $today) {
//                $sameday_booking = env('SAMEDAY_BOOKING');
//            }
//        }
        //Helper::send_mail('jun@jjonbp.com', $today, $service_req_date . '[' . $sameday_booking . ']');


        return self::get_total_price($pets, $promo, $zip, $use_credit,0, null, $sameday_booking, $fav_fee );
        //Credit amt of $0 for existing appt  only to apply new promo code. Not used at new appt.
        //That's because to return existing credit/new_credit amt for the existing appt, not to calculate both.
    }

    public static function get_total_price($pets, $promo, $zip, $use_credit, $credit_in_appt=0, $user_id = null, $sameday_booking = 0 , $fav_fee = 0 ) {
        //credit_in_appt : will be set only when CS to apply new promo_code, so credit amt used should be the same, not calculate from the beginning.

        $sub_total = 0;
        $sub_total_products_only = 0;
        $highest_addon_price = 0;
        $highest_product_price = 0;

        if (count($pets) > 0) {
            foreach ($pets as $pet) {
                if (!isset($pet->info)) {
                    Helper::send_mail('tech@groomit.me', '[GROOMIT][DESKTOP][' . getenv('APP_ENV') . '] Empty $pet->info while getTotal()', var_export($pets, true));
                    throw new \Exception('Invalid pet data setup');
                }

                ### package ###
                $package = isset($pet->info->package) ? $pet->info->package : null;
                if (empty($package)) {
                    Helper::send_mail('tech@groomit.me', '[GROOMIT][DESKTOP][' . getenv('APP_ENV') . '] Empty $pet->info->package while getTotal()', var_export($pets, true));
                    throw new \Exception('Pet does not have package information');
                }

                $sub_total += $package->denom;
                $sub_total_products_only += $package->denom;
                if ($highest_product_price <  $package->denom) {
                    $highest_product_price =  $package->denom;
                }

                ### shampoo ###
                $shampoo = isset($pet->info->shampoo) ? $pet->info->shampoo : null;
                if (empty($shampoo)) {
                    Helper::send_mail('tech@groomit.me', '[GROOMIT][DESKTOP][' . getenv('APP_ENV') . '] Empty $pet->info->shampoo while getTotal()', var_export($pets, true));
                    throw new \Exception('Pet does not have shampoo information: ' . var_export($pet->info, true));
                }

                $sub_total += $shampoo->denom;

                ### add-ons ###
                $addons = isset($pet->info->add_ons) ? $pet->info->add_ons : null;
                if (count($addons) > 0) {
                    foreach ($addons as $addon) {
                        $sub_total += $addon->denom;

                        if ($highest_addon_price < $addon->denom) {
                            $highest_addon_price = $addon->denom;
                        }
                    }
                }

            }
        }

        ### safety insurance ###
        $safety_insurance = env('SAFETY_INSURANCE');
        ### promotion amount ###
        $promo_amt = 0;

        if (!empty($promo)) {
            $for_cal_promo_amt = 0;
//            if (!empty($promo->package_id)) {
//                foreach ($pets as $pet) {
//                    if ($pet->info->package->prod_id == $promo->package_id) {
//                        $for_cal_promo_amt += $package->denom;
//                    }
//                }
//            } else {
                $for_cal_promo_amt = $sub_total;
//            }

            $safety_insurance = $promo->no_insurance == 'Y' ? 0 : $safety_insurance ;

            switch ($promo->amt_type) {
                case 'R':
                    $promo_amt = round($for_cal_promo_amt * $promo->amt / 100, 2);
                    break;
                case 'A':
                    if( ($highest_product_price < $promo->amt) &&
                        in_array($promo->type, ['G','T'] )) {
                        $promo_amt = $highest_product_price; //Limit up to Max of Product for Groupon/Gilt at PA area, for example.
                    }else if( $promo->type == 'S') { //Membership for Main product only, excluding Add-ons.
                        $promo_amt = $sub_total_products_only + $safety_insurance ; //+ $sameday_booking + $fav_fee ;
                    }else {
                        $promo_amt = $promo->amt;
                    }

                    break;
                case 'H':
                    $promo_amt = $highest_addon_price;
                    break;
            }
        }

        $new_credit = 0;

        //if ($promo_amt > ($sub_total + $safety_insurance + $sameday_booking)) {
            //$taxable_promo_amt = $sub_total + $safety_insurance + $sameday_booking ; //Not include same day booking & fav_fee
        if ($promo_amt > ($sub_total + $safety_insurance )) {
            $taxable_promo_amt = $sub_total + $safety_insurance;
        }else {
            $taxable_promo_amt = $promo_amt;
        }


        ### tax ###
        $taxable_promo_amt = empty($promo) ? 0 : ($promo->include_tax == 'N' ? 0 : $taxable_promo_amt);
        $tax = AppointmentProcessor::get_tax($zip, $sub_total, $safety_insurance, $taxable_promo_amt, 0, $sameday_booking, $fav_fee );

        $total = $sub_total + $safety_insurance  + $sameday_booking + $fav_fee + $tax;

        if ($promo_amt > 0) {
            if ($total >= $promo_amt) {
                $total = $total - $promo_amt;
            } else {
                if ($promo->type == 'K') {
                    $new_credit = $promo_amt - $total;
                } else {
                    $promo_amt = $total;
                }
                $total = 0;
            }
        }

        ### credit amount ###
        $credit_amt = 0;
        if($credit_in_appt == 0 ) { //In normal cases.
            $available_credit = self::getAvailableCredit($user_id);
        }else {                     //In 'apply new promo code by CS.
            $available_credit = $credit_in_appt;
        }

        if ($use_credit == 'Y') {
            if ($total > 0) {
                $total = $total - $available_credit;

                if ($total > 0) {
                    $credit_amt = $available_credit;
                    $available_credit = 0;
                } else {
                    $credit_amt = $available_credit + $total;
                    $available_credit = $available_credit - $credit_amt;
                    $total = 0;
                }
            }
        }

        Helper::log('### Middle of get_total_price ###', [
            'sub_total' => $sub_total,
            'safety_insurance' => $safety_insurance,
            'sameday_booking' => $sameday_booking,
            'fav_fee' => $fav_fee,
            'tax' => $tax,
            'promo_amt' => $promo_amt,
            'discount_applied' => $promo_amt - $new_credit,
            'credit_amt' => $credit_amt,
            'total' => $total,
            'new_credit' => $new_credit,
            'use_credit' => $use_credit,
            'available_credit' => $available_credit,
            'taxable_promo_amt' => $taxable_promo_amt
        ]);

        //Helper::send_mail('jun@jjonbp.com', 'sameday_booking', $sameday_booking ) ;
        return [
          'sub_total' => $sub_total,
          'safety_insurance' => $safety_insurance,
          'sameday_booking' => $sameday_booking,
          'fav_fee' => $fav_fee,
          'tax' => $tax,
          'promo_amt' => $promo_amt,
          'discount_applied' => $promo_amt - $new_credit,
          'credit_amt' => $credit_amt,
          'total' => $total,
          'new_credit' => $new_credit, //new_credit is generated with Voucher amount, when bigger than  appt amount.
          'use_credit' => $use_credit,
          'available_credit' => $available_credit
        ];
    }

    public static function getAddressId() {
        $address = self::getAddress();
        if (empty($address)) {
            return null;
        }

        return $address->address_id;
    }

    public static function getAddress() {
        return Session::get('schedule.address');
    }

    public static function setAddress(Address $address = null) {
        Session::put('schedule.address', $address);
    }


    ### END - schedule info ###

    ### BEGIN - helper ###

    public static function addonChecked($prod_id) {
        $addons = self::getCurrentAddons();
        if (count($addons) > 0) {
            foreach ($addons as $o) {
                if ($o->prod_id == $prod_id) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function demattingSelected() {
        $addons = self::getCurrentAddons();
        if (count($addons) > 0) {
            foreach ($addons as $o) {
                if (in_array($o->prod_id, [14, 18])) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function petSelected($pet_id) {
        $pets = self::getPets();
        $i = 0;
        if (count($pets) > 0) {
            foreach ($pets as $o) {
                $i++;
                if ($o->pet_id == $pet_id) {
                    $current_pet = self::getCurrentPet();
                    if (!empty($current_pet) && $o->pet_id != $current_pet->pet_id) {
                        continue;
                    }
                    return true;
                }

            }
        }

        return false;
    }

    public static function getCurrentPetId() {
        $current_pet = self::getCurrentPet();
        if (empty($current_pet)) {
            return null;
        }

        return $current_pet->pet_id;
    }

    public static function getCurrentPet() {
        return Session::get('schedule.current-pet');
    }

    public static function setCurrentPet(Pet $pet = null) {
        Session::put('schedule.current-pet', $pet);
    }

    public static function setPets($pets) {
        Session::put('schedule.pets', $pets);
    }

    public static function isCurrentPet($pet_id) {
        $current_pet = self::getCurrentPet();
        if (empty($current_pet)) {
            return false;
        }

        return $current_pet->pet_id == $pet_id;
    }

    public static function is_available_package ($package) {
        if (empty($package)) {
            return false;
        }

        $pets = self::getPets();
        if (empty($pets) || count($pets) == 0) {
            return true;
        }

        foreach ($pets as $pet) {
            if (empty($pet->info) || empty($pet->info->package)) {
                continue;
            }

            ### package ###
            if ($pet->info->package->prod_id != $package->prod_id) {
                if ($package->no_mix == 'N' || $pet->info->package->no_mix == 'N') {
                    return false;
                }
            }
        }

        return true;
    }


    public static function is_allowed_pet($allowd_zip, $pet_type = null) {
        if (empty($pet_type)) {
            $pet_type = self::getCurrentPetType();
        }
        if (empty($pet_type)) return true;

        if (empty($allowd_zip)) return true;

        $exc = AllowedExceptionPet::where('pet_type', $pet_type)->where('status', 'A')
          ->whereRaw('( 
                zip = ? or 
                ((zip is null or zip = \'\') and county = ? and state = ?) or
                ((zip is null or zip = \'\') and (county is null or county = \'\') and state = ?)
             )', [$allowd_zip->zip, $allowd_zip->county_name, $allowd_zip->state_abbr, $allowd_zip->state_abbr])->first();

        if (!empty($exc)) return false;

        return true;
    }


    public static function is_allowed_package($allowd_zip, $package_id = null) {
        if (empty($package_id)) {
            $package_id = self::getCurrentPackageId();
        }
        if (empty($package_id)) return true;

        if (empty($allowd_zip)) return true;

        $exc = AllowedExceptionPackage::where('prod_id', $package_id)->where('status', 'A')
            ->whereRaw('( 
                zip = ? or 
                ((zip is null or zip = \'\') and county = ? and state = ?) or
                ((zip is null or zip = \'\') and (county is null or county = \'\') and state = ?)
             )', [$allowd_zip->zip, $allowd_zip->county_name, $allowd_zip->state_abbr, $allowd_zip->state_abbr])->first();

        if (!empty($exc)) return false;

        return true;
    }

    ### END - helper ###
}