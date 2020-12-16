<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/30/17
 * Time: 4:09 PM
 */

namespace App\Lib;

use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\AppointmentList;
use App\Model\AppointmentModified;
use App\Model\AppointmentPet;
use App\Model\AppointmentPhoto;
use App\Model\AppointmentProduct;
use App\Model\Survey;
use App\Model\Breed;
use App\Model\CCTrans;
use App\Model\Credit;
use App\Model\CreditMemo;
use App\Model\GroomerArrived;
use App\Model\GroomerOpens;
use App\Model\GroomerServiceArea;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\Product;
use App\Model\ProductDenom;
use App\Model\Size;
use App\Model\TaxZip;
use App\Model\UserBilling;
use App\Model\User;
use App\Model\Message;
use App\Model\Constants;
use App\Model\Groomer;
use App\Model\PromoCode;
use App\Model\UserBlockedGroomer;
use App\Model\UserFavoriteGroomer;
use App\Model\VWAppointmentPet;
use App\Lib\UserProcessor;
use Auth;
use Carbon\Carbon;
use DateTime;
use DB;


class AppointmentProcessor
{
    //Called from both Desktop/App.
    public static function add_appointment($ar, $user, $order_from = 'A') {

        try {
            if (empty($user->phone)) {
                return [
                  'msg' => 'Please update your phone number.'
                ];
            }

            if (empty($user->first_name)) {
                return [
                  'msg' => 'Please update your first name.'
                ];
            }

            if (empty($user->last_name)) {
                return [
                  'msg' => 'Please update your last name.'
                ];
            }

            if ($ar->total < 0) {
                return [
                    'msg' => 'Invalid total amount. Please try again.'
                ];
            }

            # reserved at
            if (empty($ar->datetime)) {
                return [
                    'msg' => 'Please select service date and time'
                ];
            } else {


//                Helper::log('### add_appointment:datetime ###', [
//                                'datetime' => $ar->datetime
//                            ]);
                // reserved date : show start time window
                $date_array = explode(" ", $ar->datetime);  //2020-03-20 08:00am - 09:00am, 2020-08-12 04:00pm - 08:00pm
                $reserved_date = new DateTime($date_array[0] . ' ' . $ar->time->start);  //DateTime of starting date.
                $reserved_date2 = new DateTime($date_array[0] . ' ' . $ar->time->start);
                $reserved_datetime = $reserved_date->format('l, F j Y, h:i A');
                $reserved_date = $reserved_date->format('Y-m-d H:i:s');
                $reserved_day = $reserved_date2->format('Y-m-d');

//                if ($reserved_date >= '2020-06-10 15:30:00' && $reserved_date < '2020-06-10 18:00:00') {
//                    return [
//                        'msg' => 'We are sorry but we cannot accept appointments from 3:30 PM to 6:00 PM at 06/10.'
//                    ];
//                }

                $address = Address::where('user_id', $user->user_id)
                    ->where('status', '!=', 'D')
                    ->where(DB::raw("ifnull(zip, '')"), '!=', '')
                    ->first();
//                if (!empty($address) && $address->state == 'NJ') {
//                    //Allow NJ area.
//                }else {
//                    if ($reserved_day >= '2020-03-22' && $reserved_day <= '2020-05-15') {
//                        return [
//                            'msg' => 'Please schedule after May 15th.'
//                        ];
//                    }
//                }

                $now = Carbon::now();
                $r_date = Carbon::parse($reserved_date);
                //$time_diff = $r_date->diffInMinutes($now);
                //$time_diff = $now->diffInMinutes($r_date, false);

                //if ($r_date->lt($now)) {
                //if ($r_date->diffInMinutes($now) < 30) {

                if( $now->diffInMinutes($r_date,false) < 60){   // it returns r_date - now, with plus/minus
//                    return [
//                        'msg' => $date_array[1] .$date_array[3]
//                    ];
                    if( !in_array($date_array[1] . $date_array[3] , [ '08:00am12:00pm', '12:00pm04:00pm', '04:00pm08:00pm' ] ) ) { //Specific times only, not flexible times.
                        return [
                            'msg' =>'Please choose at least 1 hour in advance for your service time.'
                        ];
                    }

                }

                if ($now->diffInDays($r_date, false) > 30) {
                    return [
                      'msg' => 'You can make an appointment within 30 days only.'
                    ];
                }
            }

            # negative credit amt check
            if ($ar->credit_amt < 0) {
                return [
                    'msg' => 'Negative credit amount is not allowed'
                ];
            }

            # set applied promo code for sms
            # P.A(Promo code Applied)
            # R.A(Referal code Applied)
            # A.A(Affiliate code Applied)
            # G.A(Groupon code Applied)
            # B.A(Affiliate code Generated by affilite user Applied)
            # K.A(Voucher code Applied)
            # T.A(Gilt code Applied)
            $applied_code = '';

            if (empty($ar->promo_code) && $ar->promo_amt > 0) {
                return [
                    'msg' => 'Your promotion code seems to have wrong configuration. Please contact our Customer care.'
                ];
            }

            if (!empty($ar->promo_code)) {
                $promo_code = PromoCode::find(strtoupper($ar->promo_code));
                if (empty($promo_code)) {
                    return [
                        'msg' => 'Invalid promotion code provided'
                    ];
                }

                $package_list = [];
                foreach ($ar->pet as $pet) {
                    $prod_id = isset($pet->info->package) ? $pet->info->package->prod_id : '';

                    $package_list[] = $prod_id;
                }

                $msg = PromoCodeProcessor::checkIfUsed($user->user_id, $promo_code, null, $package_list);
                if (!empty($msg)) {
                    return [
                        'msg' => $msg
                    ];
                }

                if($promo_code->type =='S' ){ //In case of Membership, check if the new appointments over the limitations by products.
                    $msg = PromoCodeProcessor::checkSubscription($user->user_id, $promo_code, $ar->pet );
                    if (!empty($msg)) {
                        return [
                            'msg' => $msg
                        ];
                    }
                }

                $applied_code = ($promo_code->type == 'N' ? ', P.A' : ', ' . $promo_code->type . '.A');
            }


            ### SAMEDAY BOOKING ###
            $sameday_booking = 0;
            $today = Carbon::now()->format('Y-m-d');
            if( $reserved_day == $today) {
                $sameday_booking = env('SAMEDAY_BOOKING');
                if( !empty($ar->promo_code) && !empty($promo_code->type) && ($promo_code->type == 'S') ) { //In case of Membership, no sameday_booking
                    $sameday_booking = 0;
                }
            }
//                if( $today >= '2019-09-18' ){
//                    if( $reserved_day == $today ) {
//                        $sameday_booking = env('SAMEDAY_BOOKING');
//                    }
//                }

            ### FAV FEE, Favorite Groomer fee
            $fav_type = $ar->fav_type;
            $fav_groomer_id = isset($ar->fav_groomer_id)? $ar->fav_groomer_id: 0;
            $fav_fee = 0;
            if( $fav_type == 'F' ) {
                if($fav_groomer_id > 0 ) {
                    $fav_fee = env('FAV_GROOMER_FEE');
                }else {
                    return [
                        'msg' => 'No Groomer is selected. Please start over your appointment.'
                    ];
                }
            }

            ## total
            $total = 0;
            $tax = 0;
            $pet_ids = array();
            $ar->is_prepaid = false;

            if (is_array($ar->pet)) {
                foreach ($ar->pet as $pet) {
                    $pet_ids[] = $pet->pet_id;

                    if ($pet->type == 'cat') {
                        ### NJ check for cat => Removed, so it could be skipped as of 01/01/2020. ####
                        $msg = AppointmentProcessor::check_if_cats_allowed($user);
                        if (!empty($msg)) {
                            return [
                                'msg' => $msg
                            ];
                        }
                    }

                    # package
                    $pkg = $pet->info->package;

                    $pkg_o = Product::where('prod_id', $pkg->prod_id)
                        ->where('prod_type', 'P')
                        ->where('pet_type', $pet->type)
                        ->first();

                    if (empty($pkg_o)) {
                        return [
                            'msg' => 'Invalid package ID provided'
                        ];
                    }

                    if (in_array($pkg->prod_id, [28, 29])) {
                        $min_date = Carbon::today()->addDays(7)->format('Y-m-d');

                        if ($reserved_date < $min_date) {
                            return [
                                'msg' => 'ECO package is allowed to book 7 days in advance.'
                            ];
                        }
                    }

                    if ($pkg_o->is_prepaid == 'Y') {
                        $ar->is_prepaid = true;
                    }

                    $query = ProductDenom::where('prod_id', $pkg->prod_id);
                    if ($pet->type == 'dog') {
                        $query = $query->where('size_id', $pet->size);
                    }
                    $pkg_denom = $query->first();

                    if (empty($pkg_denom)) {
                        return [
                            'msg' => 'Invalid package amount: ' . $pkg->prod_id . ' / ' . $pet->size . ' / ' . $pkg->denom
                        ];
                    }

                    $price = Helper::get_price($pkg->prod_id, $pet->size, $ar->address->zip);
                    $total += doubleVal($price);

                    # shampoo
                    $shp_o = null;
                    $shp = $pet->info->shampoo;
                    if (empty($shp)) {
                        $shp_o = Product::where('pet_type', $pet->type)
                            ->where('prod_type', 'S')
                            ->orderBy('prod_id', 'asc')
                            ->first();

                        if (empty($shp_o)) {
                            return [
                                'msg' => 'No available shampoo'
                            ];
                        }

                        $shp = new \stdClass();
                        $shp->prod_id = $shp_o->prod_id;
                        $shp->prod_name = $shp_o->prod_name;
                        $shp->denom = 0;
                        $pet->info->shampoo = $shp;
                    } else {
                        $shp_o = Product::where('prod_id', $shp->prod_id)
                            ->where('pet_type', $pet->type)
                            ->where('prod_type', 'S')
                            ->first();

                        if (empty($shp_o)) {
                            return [
                                'msg' => 'Invalid shampoo ID provided'
                            ];
                        }
                    }

                    $shp_denom = ProductDenom::where('prod_id', $shp->prod_id)->first();
                    if (empty($shp_denom)) {
                        return [
                            'msg' => 'Invalid shampoo amount: ' . $shp->prod_id . ' / ' . $pet->size . ' / ' . $shp->denom
                        ];
                    }

                    $price = Helper::get_price($shp->prod_id, $pet->size, $ar->address->zip);
                    $total += doubleVal($price);

                    # add-ons
                    if (is_array($pet->info->add_ons)) {
                        foreach ($pet->info->add_ons as $ao) {

                            $ao_o = Product::where('prod_id', $ao->prod_id)->first();
                            if (empty($ao_o)) {
                                return [
                                    'msg' => 'Invalid add-on ID provided'
                                ];
                            }

                            $ao_denom = ProductDenom::where('prod_id', $ao->prod_id)
                                ->first();
                            if (empty($ao_denom)) {
                                return [
                                    'msg' => 'Invalid add-on amount: ' . $ao->prod_id . ' / ' . $pkg->denom
                                ];
                            }

                            $price = Helper::get_price($ao->prod_id, $pet->size, $ar->address->zip);

                            $total += doubleVal($price);
//                            Helper::log('### ADD_APPOINTMENT BEFORE TOTAL ###', [
//                                'Total of a pet:' => $total
//                            ]);
                        }
                    }
                }
            }

            Helper::log('### ADD_APPOINTMENT BEFORE TOTAL ###', [
                'sub_total' => $total,
                'tax(No Tax yet)' => $tax,
                'safety_insurance' => $ar->safety_insurance,
                'sameday_booking' => $sameday_booking,
                'fav_fee' => $fav_fee,
                'promo_amt' => $ar->promo_amt,
                'credit_amt' => $ar->credit_amt,
                'user_id' => $user->user_id,
                'ar' => $ar
            ]);

            // $total = $total + $ar->tax + $ar->safety_insurance - $ar->promo_amt - $ar->credit_amt;
            if ($total < 0) {
                return [
                    'msg' => 'Total amount is less than 0'
                ];
            }

            if (doubleval($total) != doubleval($ar->sub_total)) {
                return [
                    'msg' => 'Your total amount is not correct. Please start over from the beginning: [' . $total. ' / ' . $ar->sub_total . ']'
                ];
            }

            # check already same appointment exist or not #
            $same_app_pets = DB::select("
                select
                    a.pet_id
                from appointment_pet a 
                    left join appointment_list b on a.appointment_id = b.appointment_id
                where ((b.accepted_date is not null and b.accepted_date = :accepted_date) 
                        or (b.accepted_date is null and b.reserved_date = :reserved_date))
                and b.address_id = :address_id
                and b.user_id = :user_id
                and b.status not in ('C', 'L' )
                and b.status != 'P'
            ", [
                'accepted_date' => $reserved_date,
                'reserved_date' => $reserved_date,
                'address_id' => $ar->address->address_id,
                'user_id' => $user->user_id
            ]);

            if (!empty($same_app_pets)) {
                foreach($same_app_pets as $p) {

                    if (in_array($p->pet_id, $pet_ids)) {

                        return [
                            'msg' => 'You already have another appointment at this date & time. Please check your existing appointments.'
                        ];
                    }
                }
            }

            DB::beginTransaction();

            $appointment = new AppointmentList;
            $appointment->address_id = $ar->address->address_id;
            $appointment->payment_id = $ar->payment->billing_id;
            $appointment->reserved_at = $ar->datetime;
            $appointment->reserved_date = $reserved_date;
            $appointment->user_id = $user->user_id;
            $appointment->sub_total = $ar->sub_total;
            $appointment->promo_code = isset($ar->promo_code) ? strtoupper($ar->promo_code) : '';
            $appointment->promo_amt = $ar->promo_amt;
            $appointment->credit_amt = $ar->credit_amt;
            $appointment->new_credit = isset($ar->new_credit) ? $ar->new_credit : 0;
            $appointment->tax = $ar->tax;
            $appointment->safety_insurance = $ar->safety_insurance;
            $appointment->sameday_booking = $sameday_booking;
            $appointment->fav_groomer_fee = $fav_fee;
            $appointment->total = $ar->total;
            $appointment->status = 'N';
            $appointment->cdate = Carbon::now();
            $appointment->order_from = $order_from; # A: app ( default ), D: desktop
            $appointment->fav_type = $ar->fav_type;
            $appointment->my_favorite_groomer = isset($ar->fav_groomer_id)? $ar->fav_groomer_id: null;
            $appointment->fav_groomer_fee = $fav_fee;
            $appointment->save();

            ### authorize amount first ###
            if ($ar->total > 0) {

                $user_billing = UserBilling::find($ar->payment->billing_id);
                if (empty($user_billing)) {
                    $msg = 'Credit card not found. Please select or register your credit card first.';
                    DB::rollback();

                    return [
                        'msg' => $msg
                    ];
                }

                if (empty($user_billing->card_token)) {
                    $msg = 'Some of your credit card data is missing. Please update your credit card information again.';
                    DB::rollback();

                    return [
                        'msg' => $msg
                    ];
                }

//                $prev_app_cnt = AppointmentList::where('user_id', $user->user_id)->where('status', '<>', 'C')->count();
//                //Hold temporarily. 07142019
//
//                if($prev_app_cnt <= 1) {

                    if ($ar->is_prepaid) {
                        $ret = Converge::sales($user_billing->card_token, $ar->total, $appointment->appointment_id, 'S');

                        if (!empty($ret['error_msg'])) {
                            $appointment->status = 'F';
                            $appointment->save();

                            $msg = 'Credit card processing failed : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                            DB::rollback();

                            return [
                                'msg' => $msg
                            ];
                        }
                    } else {
                        ### auth only 1 cent to validate credit card ###
                        $validation_amt = 0.01;
                        $ret = Converge::auth_only($user_billing->card_token, $validation_amt, $appointment->appointment_id, 'A');
                        if (!empty($ret['error_msg'])) {
                            //$msg = 'Validating credit card has failed : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                            $msg = 'Payment Declined : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                            Helper::send_mail('tech@groomit.me', '[Groomit][' . env('APP_ENV') . '] New appointment error : ' . $appointment->appointment_id, $msg);

                            DB::rollback();

                            return [
                                'msg' => $msg
                            ];
                        }

                        ### void 1 cent if validation pass. ###
                        $void_ref = $ret['void_ref'];
                        $ret = Converge::void($appointment->appointment_id, 'A', $void_ref, 'A'); //Full voids of $0.01 auth.
                        if (!empty($ret['error_msg'])) {
                            $msg = ' - appointment ID: ' . $appointment->appointment_id . '<br/>';
                            $msg .= ' - void_ref: ' . $void_ref . '<br/>';
                            $msg .= ' - error : Voiding validation amount $0.01 has failed : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']<br/>';

                            Helper::send_mail('tech@groomit.me', '[Groomit][' . env('APP_ENV') . '] New appointment error : ' . $appointment->appointment_id, $msg);

                            DB::rollback();

                            return [
                                'msg' => $msg
                            ];
                        }
                    }
//                }
            }

            if ($appointment->new_credit > 0) {
                $expire_date = Carbon::today()->addDays(90);

                $c = new Credit;
                $c->user_id = $appointment->user_id; // code owner
                $c->type = 'C';
                $c->category = 'T';
                $c->amt = $appointment->new_credit;
                $c->referral_code = '';
                $c->appointment_id = $appointment->appointment_id;
                $c->expire_date = Carbon::today()->addDays(365);
                $c->status = 'A';
                $c->cdate = Carbon::now();
                $c->save();

                ### create_memo($user_id, $type, $amt, $expire_date, $ref_type, $ref, $appointment_id, $created_by)
                CreditMemo::create_memo($appointment->user_id, 'C', $appointment->new_credit, $expire_date, 'P', 'Promotion',  $appointment->appointment_id, 'system');
            }

            // Set De-Matting info for sending Terms //
            $deMatting = 'N';
            $segment_products = [];

            if (is_array($ar->pet)) {
                foreach ($ar->pet as $pet) {

                    // appointment pet
                    $apet = new AppointmentPet;
                    $apet->appointment_id = $appointment->appointment_id;
                    $apet->pet_id = $pet->pet_id;
                    $apet->size_id = $pet->size;
                    $apet->sub_total = $pet->info->sub_total;
                    $apet->tax = $pet->info->tax;
                    $apet->total = $pet->info->total;
                    $apet->save();

                    // package
                    $app = new AppointmentProduct;
                    $pkg = $pet->info->package;
                    $app->appointment_id = $appointment->appointment_id;
                    $app->pet_id = $pet->pet_id;
                    $app->prod_id = $pkg->prod_id;
                    $app->amt = $pkg->denom;
                    $app->save();

                    // shampoo
                    $aps = new AppointmentProduct;
                    $shp = $pet->info->shampoo;
                    $aps->appointment_id = $appointment->appointment_id;
                    $aps->pet_id = $pet->pet_id;
                    $aps->prod_id = $shp->prod_id;
                    $aps->amt = $shp->denom;
                    $aps->save();


                    $segment_product = [
                        'product_id' => $pkg->prod_id,
                        'price' => $pkg->denom,
                        'pet_id' => $pet->pet_id,
                        'size_id' => $pet->size,
                        'quantity' => 1
                    ];
                    $segment_products[] = $segment_product;

                    // add-ons
                    if (is_array($pet->info->add_ons)) {
                        foreach ($pet->info->add_ons as $ao) {
                            $apo = new AppointmentProduct;
                            $apo->appointment_id = $appointment->appointment_id;
                            $apo->pet_id = $pet->pet_id;
                            $apo->prod_id = $ao->prod_id;
                            if($apo->prod_id == 14){
                                $deMatting = 'Y';
                            }
                            $apo->amt = $ao->denom;
                            $aps->created_by = $user->first_name;
                            $aps->cdate = Carbon::now();
                            $apo->save();


                            $segment_product = [
                                'product_id' => $ao->prod_id,
                                'price' =>  $ao->denom,
                                'pet_id' => $pet->pet_id,
                                'size_id' => $pet->size,
                                'quantity' => 1
                            ];
                            $segment_products[] = $segment_product;
                        }
                    }
                }
            }

            ### if credit is used, make debit type credit record ###
            if ($appointment->credit_amt > 0) {
                $msg = CreditProcessor::useCredit($user->user_id, $appointment->credit_amt, $appointment->appointment_id);
                if (!empty($msg)) {
                    DB::rollback();
                    return [
                        'msg' => $msg
                    ];
                }
            }

            ### mark promo code has been used for groupon code ###
            if (!empty($appointment->promo_code)) {
                $msg = PromoCodeProcessor::markAsUsed($appointment->promo_code, $user->user_id);
                if (!empty($msg)) {
                    DB::rollback();
                    return [
                        'msg' => $msg
                    ];
                }
            }

            if (!empty($ar->place_id) && trim($ar->place_id) != '' ) {
                $ret = DB::insert("
                insert into appointment_place (appointment_id, place_id, other_name, cdate)
                values (:appointment_id, :place_id, :other_name, :cdate )
            ", [
                    'appointment_id' =>$appointment->appointment_id,
                    'place_id' =>$ar->place_id,
                    'other_name' => empty($ar->place_other_name)? '': $ar->place_other_name,
                    'cdate' => Carbon::now(),
                ]);
            }


            DB::commit();

            //Update order_cnt at appointment_cnt & user_stat.
            $user_stat = DB::select("
                select book_cnt
                from user_stat
                where user_id = :user_id
            ", [
                'user_id' => $appointment->user_id
               ]);

            $book_cnt = 1;
            if (!empty($user_stat)) {
                $book_cnt = $user_stat[0]->book_cnt + 1;

                DB::update("
                update user_stat
                  set book_cnt  = book_cnt + 1
                 where user_id = :user_id
                ", [
                    'user_id' => $appointment->user_id
                ]);

            }else {
                //Not last_groomer_id & names because no groomer is assigned yet.
                $ret = DB::insert("
                insert into user_stat( user_id, book_cnt, last_appt_id, last_appt_date)
                values ( :user_id, :book_cnt, :appointment_id, :cdate )
            ", [
                    'user_id' =>$appointment->user_id,
                    'book_cnt' => $book_cnt,
                    'appointment_id' =>$appointment->appointment_id,
                    'cdate' => Carbon::now()
                ]);
            }

            $ret = DB::insert("
                insert into appointment_cnt (appointment_id, book_cnt, cdate )
                values (:appointment_id, :book_cnt, :cdate )
            ", [
                'appointment_id' =>$appointment->appointment_id,
                'book_cnt' =>$book_cnt,
                'cdate' => Carbon::now(),
            ]);

            ## send email to user ##
            $address ='';
            $city_zip = '';
            $addr = $ar->address;
            if (!empty($addr)) {
                if( !empty($addr->address2) && ($addr->address2 != '' ) ) {
                    $address  =$addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state;
                }else {
                    $address  =$addr->address1 .  ', ' . $addr->city . ', ' . $addr->state;
                }

                $city_zip = "\nZip " . $addr->zip . ", " . $addr->city . "\n";
            }

            $subject = "Your Groomit Appointment has been received";

            $pets = DB::select("
                    select 
                        a.pet_id,
                        c.name as pet_name,
                        c.dob as pet_dob,
                        b.prod_name as package_name,
                        a.amt as price
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                ", [
                'appointment_id' => $appointment->appointment_id
            ]);

            $data = [];
            $data['email'] = $user->email;
            $data['name'] = $user->first_name;
            $data['subject'] = $subject;
            $data['address'] = $address;
            $data['referral_code'] = $user->referral_code;

            foreach ($pets as $k=>$v) {
                $data['pet'][$k]['pet_name'] = $v->pet_name;
                $data['pet'][$k]['package_name'] = $v->package_name;
            }

            $data['reserved_date'] =$ar->datetime;


//            $ret = Helper::send_html_mail('new_appointment', $data);
//
//            if (!empty($ret)) {
//                $msg = 'Failed to send new appointment email to user';
//
//                Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . ']' . $msg, ' - appointment : ' . $appointment->appointment_id . '<br> - error : ' . $ret);
//            }

            ## end send email to user ##

            ### Send Terms*Condition email, right after appointments are created, once Demat is selected. ###
            if($deMatting == 'Y') {
                $data = [];
                $data['email'] = $user->email;
                $data['name'] = '';
                $data['subject'] = 'GROOMIT TERMS AND CONDITIONS';
                $ret = Helper::send_html_mail('terms', $data);

                if (!empty($ret)) {
                    $msg = 'Failed to send Groomit Terms And Conditions email to User';
                    Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . ']' . $msg, ' - appointment : ' . $appointment->appointment_id . '<br> - error : ' . $ret);
                }
            }

            ### send text ###

            $prev_app_cnt = AppointmentList::where('user_id', $user->user_id)->whereNotIn('status', ['C', 'L'])->count();

            switch ($prev_app_cnt) {
                case 1:
                    $how_many = ' (1st Order';
                    break;
                case 2:
                    $how_many = ' (2nd Order';
                    break;
                case 3:
                    $how_many = ' (3rd Order';
                    break;
                default:
                    $how_many = ' (' . $prev_app_cnt. 'th Order';
                    break;
            }

            $how_many .= ', UID: ' . $user->user_id . ')';
            $order_from = $appointment->order_from == 'D' ? 'Web' : 'App';

            $msg = 'There is new ' . $order_from . ' appointment for appointment ID: ' . $appointment->appointment_id . ' ' . $city_zip . 'at ' . $reserved_datetime .', Total $' . $appointment->total . $applied_code . $how_many;
            if (getenv('APP_ENV') == 'production') {
                Helper::send_sms_to_admin($msg, true);
            }

            ### send groomer notification ###
            //self::send_groomer_notification($appointment); //OLD version.
            self::send_groomer_notification2($appointment);  //New since 02/04/2020



            ### set groom hour pet package/size in appointment_pet
            self::update_pet_groom_hour($appointment->appointment_id);

            ### Reapply promo code when promo_amt is 0
            if (!empty($appointment->promo_code)) {

                // Update for influencer with promo code influencer = Y
                $promo = PromoCode::whereRaw("code = '" . strtoupper($appointment->promo_code) . "'")
                    ->where('influencer', 'Y')
                    ->first();

                if (!empty($promo)) {
                    $user = User::find($appointment->user_id);
                    $user->influencer = 'Y';
                    $user->save();
                }


                //Not sure why it exist here , but removed it here at 02142020, for simplicity of the function.
//                if ($appointment->promo_amt == 0) {
//                    self::apply_promo_code($appointment, null);
//                }

                //Automatically add as a Fav, when appointments, not payments completed.
                if (!empty($appointment->promo_code) && !empty($promo_code) && !empty($promo_code->groomer_id) ) {
                    $favorite_groomer = UserFavoriteGroomer::where('user_id', $user->user_id)
                        ->where('groomer_id', $promo_code->groomer_id)
                        ->first();
                    if ( empty($favorite_groomer) ) {
                        $fav = new UserFavoriteGroomer();
                        $fav->user_id = $user->user_id;
                        $fav->groomer_id = $promo_code->groomer_id;
                        $fav->save();
                    }

                    //Make it mandatory when groomer referal code has been used.
                    $appointment->fav_type = 'F';
                    $appointment->save();
                }
            }


//            $segment = new \Segment();
//            $segment->init("5Ve8XWVizx6obmb2aunTqyQ89tta5a0c");
//
//            $segment->identify( [
//                    "userId" =>  empty($user->user_id)? 'SessionTimeOut': $user->user_id,
//                    "billing_email" =>  $user->email,
//                    "billing_address" =>  $addr->address1 ,
//                    "billing_city" =>  $addr->city ,
//                    "billing_state" => $addr->state,
//                    "billing_zip" => $addr->zip,
//                    "device_type" => $order_from ,
//                    "user_id" => $user->user_id
//                ]
//            );
//
//
//            $segment->track([
//                    "userId" =>  empty($user->user_id)? 'SessionTimeOut': $user->user_id,
//                    "event" => 'Order Completed',
//                    "device_type" => $order_from ,
//                    "billing_email" => $user->email,
//                    "device_type" => $order_from ,
//                    "first_name" => $user->first_name,
//                    "last_name" => $user->last_name,
//                    "order_id" =>  $appointment->appointment_id,
//                    "sub_total" => $appointment->sub_total,
//                    "promo_amt" => $appointment->promo_amt,
//                    "coupon" => $appointment->promo_code,
//                    "tax" => $appointment->tax,
//                    "revenue" => $appointment->total,
//                    "currency" => 'USD',
//                    "payment_method" => 'credit card',
//                    "user_id" => $user->user_id,
//                    "billing_address" =>  $addr->address1 ,
//                    "billing_city" =>  $addr->city ,
//                    "billing_state" => $addr->state,
//                    "billing_zip" => $addr->zip,
//                    "order_count" =>  count($ar->pet),
//                    "products" => $segment_products
//                ]
//            );


            return [
                'msg' => '',
                'appointment_id' => $appointment->appointment_id
            ];

        } catch (\Exception $ex) {
            DB::rollback();

            return [
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ];
        }
    }

    //notify_level : 0 => At appointments
    //               1 => 20 mins later
    //               2 => 35 mins later, no, stopped at 12/30/2019.
    public static function send_groomer_notification(AppointmentList $app, $notify_level = 0) {
        try {

            //$msg = '[' . $app->appointment_id . '] Here\'s new appointment request';

            $address = Address::find($app->address_id);
            $zip = '';
            $city = '';
            if (!empty($address)) {
                $zip = $address->zip;
                $city = $address->city;
            }

            $package = '';
            $msg = '';
            try{
                $pet_type = '';
                $qty_cat_gold = 0;
                $qty_cat_silver = 0;
                $qty_cat_eco = 0;
                $qty_gold = 0;
                $qty_silver = 0;
                $qty_eco = 0;

                $pets = AppointmentPet::where('appointment_id', $app->appointment_id)->get();

                foreach ($pets as $p) {
                    $pet = Pet::where('pet_id', $p->pet_id)->first();
                    $pet_type = $pet->type;
                    if ($pet->type == 'cat') {
                        $product = AppointmentProduct::where('appointment_id', $app->appointment_id)
                          ->where('pet_id', $p->pet_id)
                          ->whereIn('prod_id', ['16', '27', '29'])
                          ->first();

                        switch ($product->prod_id) {
                            case '16':
                                $qty_cat_gold = $qty_cat_gold + 1;
                                break;
                            case '27':
                                $qty_cat_silver = $qty_cat_silver + 1;
                                break;
                            case '29':
                                $qty_cat_eco = $qty_cat_eco + 1;
                                break;
                        }
                    } else {
                        $product = AppointmentProduct::where('appointment_id', $app->appointment_id)
                            ->where('pet_id', $p->pet_id)
                            ->whereIn('prod_id', ['1', '2', '28'])
                            ->first();

                        switch ($product->prod_id) {
                            case '1':
                                $qty_gold = $qty_gold + 1;
                                break;
                            case '2':
                                $qty_silver = $qty_silver + 1;
                                break;
                            case '28':
                                $qty_eco = $qty_eco + 1;
                                break;
                        }
                    }
                }

                if ($qty_cat_gold + $qty_cat_silver + $qty_cat_eco + $qty_gold + $qty_silver + $qty_eco == 1) {
                    if ($qty_cat_gold == 1) {
                        $package = 'CAT Gold';
                        $msg = '[CAT] Gold';
                    }

                    if ($qty_cat_silver == 1) {
                        $package = 'CAT Silver';
                        $msg = '[CAT] Silver';
                    }

                    if ($qty_cat_eco == 1) {
                        $package = 'CAT ECO';
                        $msg = '[CAT] ECO';
                    }

                    if ($qty_gold == 1) {
                        $package = 'DOG Gold';
                        $msg = '[DOG] Gold';
                    }

                    if ($qty_silver == 1) {
                        $package = 'DOG Silver';
                        $msg = '[DOG] Silver';
                    }

                    if ($qty_eco == 1) {
                        $package = 'DOG ECO';
                        $msg = '[DOG] ECO';
                    }
                } else {
                    if ($qty_cat_gold > 0) {
                        $msg = ',' . $qty_cat_gold . ' Cat-Gold' . ($qty_cat_gold == 1 ? '' : 's');
                    }
                    if ($qty_cat_silver > 0) {
                        $msg = ',' . $qty_cat_silver . ' Cat-Silver' . ($qty_cat_silver == 1 ? '' : 's');
                    }
                    if ($qty_cat_eco > 0) {
                        $msg = ',' . $qty_cat_eco . ' Cat-ECO' . ($qty_cat_eco == 1 ? '' : 's');
                    }
                    if ($qty_silver > 0) {
                        $msg .= ',' . $qty_silver . ' Dog-Silver' . ($qty_silver == 1 ? '' : 's');
                    }
                    if ($qty_gold > 0) {
                        $msg .= ',' . $qty_gold . ' Dog-Gold' . ($qty_gold == 1 ? '' : 's');
                    }
                    if ($qty_eco > 0) {
                        $msg .= ',' . $qty_eco . ' Dog-ECO' . ($qty_eco == 1 ? '' : 's');
                    }
                    $package = substr($msg, 1);

                    $msg = 'Multiple pets[' . $package . ']';
                }

                $msg = "[" . $app->appointment_id . "] " . $msg. " appointment.";

            } catch (\Exception $ex) {
                $msg = "[" . $app->appointment_id . "] New appointment.";
            }

            $allowed_zip = AllowedZip::where('zip', $zip)->first();
            $county = empty($allowed_zip) ? null : ($allowed_zip->county_name . '.' . $allowed_zip->state_abbr);

            if ($pet_type == 'dog') {
                $available_groomer_query = "(select groomer_id from groomer where dog = 'Y')";
            } else {
                $available_groomer_query = "(select groomer_id from groomer where cat = 'Y')";
            }

            ### ECO no favorite groomer ###
            //if ($qty_eco + $qty_cat_eco == 0) { //no limitation since 06/22/2020 ?
                if (empty($allowed_zip)) { //This is not possible, because we accept from allowed zip areas only.
                    $fav_groomers = UserFavoriteGroomer::where('user_id', $app->user_id)
                        ->whereRaw('groomer_id in ' . $available_groomer_query)
                        ->get();
                } else {
                    $fav_groomers = UserFavoriteGroomer::where('user_id', $app->user_id)
                        ->whereRaw('groomer_id in (select groomer_id from groomer_service_area where county = \'' . $county . '\' and status = \'A\')')
                        ->whereRaw('groomer_id in ' . $available_groomer_query)
                        ->get();
                }
            //}

            $reserved_date = new DateTime($app->reserved_date );
            $reserved_datetime = $reserved_date->format('l, F j Y, h:i A');
            $reserved_day = $reserved_date->format('l');

            $user = User::find($app->user_id);

            $sms_message = 'Your request for a Groomit appointment on ' . $reserved_datetime . '  has been received. We are now attempting to locate the closest available groomer.';

            ### SMS to user only when at appointment only, not 1st/2nd level notification.
            if (!empty($user->phone) && ($notify_level < 1) )  {
                $ret = Helper::send_sms($user->phone, $sms_message);

                if (!empty($ret)) {
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $sms_message);
                }

                Message::save_sms_to_user($sms_message, $user, $app->appointment_id);
            }


            if (empty($fav_groomers) || count($fav_groomers) == 0) {  //In case of no Fav, or ECO .
                $msg = $msg . " $city $county $zip, $app->reserved_at, $reserved_day. Please visit groomer app to accept";
                $notify_level = $notify_level + 1;

            } else {
                if ($notify_level > 0) { //In case of Fav Groomer exist, send it only when at appointment only, which means notify_level = 0.
                    return;
                }
                $msg = "You have favorite customer $user->first_name $user->last_name, [$package] appointment at $address->address1 $city $zip, $app->reserved_at, $reserved_day. Please visit groomers app to accept";
            }


            $notify_groomers = null;

            if ($notify_level == 0) {  //This means fav groomer exist only.
                $notify_groomers = $fav_groomers;
            } else {
                if ($notify_level <= 2) {
                    if ($pet_type == 'dog') {
                        $available_groomer_query2 = "dog = 'Y'";
                    } else {
                        $available_groomer_query2 = "cat = 'Y'";
                    }

                    if (empty($county)) { //Not possible.
                        $notify_groomers = Groomer::where('level', $notify_level)
                            ->whereRaw($available_groomer_query2)
                            ->where('status', 'A')
                            ->whereNotNull('device_token')
                            ->where('device_token', '!=', '')
                            ->whereRaw('groomer_id not in (select groomer_id from user_blocked_groomer where user_id = \'' . $app->user_id . '\' )')
                            ->get();
                    } else {
                        $notify_groomers = Groomer::where('level', $notify_level)
                            ->whereRaw($available_groomer_query2)
                            ->where('status', 'A')
                            ->whereNotNull('device_token')
                            ->where('device_token', '!=', '')
                            ->whereRaw('groomer_id in (select groomer_id from groomer_service_area where county = \'' . $county . '\' and status = \'A\')')
                            ->whereRaw('groomer_id not in (select groomer_id from user_blocked_groomer where user_id = \'' . $app->user_id . '\' )')
                            ->get();
                    }
                }
            }

            if (!empty($notify_groomers) && count($notify_groomers) > 0) {
                foreach ($notify_groomers as $o) {
                    $groomer = Groomer::where('groomer_id', $o->groomer_id)
                      ->where('status', 'A')
                      ->first();
                    if (!empty($groomer)) {
                        Helper::send_sms($groomer->mobile_phone, $msg);

                        ### Send push notification.
                        if (!empty($groomer->device_token)) {
                            Helper::send_notification("", $msg, $groomer->device_token, 'New Appointment', "");
                        }
                    }
                }

                $app->groomer_notified = 'Y';
                $app->save();
            }else {
                Helper::send_mail('jun@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Groomer Notification Failed:No groomers to send Notification on this appointment', $msg);
            }

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ' ]: ' . $ex->getTraceAsString();
            Helper::send_mail('tech@groomit.me', '[GROOMIT.ME][' . getenv('APP_ENV') . '] Groomer Notification Failed', $msg);
        }
    }

    public static function check_if_cats_allowed(User $user) {
        // check if there is address for the user
        $address = Address::where('user_id', $user->user_id)
            ->where('status', '!=', 'D')
            ->where(DB::raw("ifnull(zip, '')"), '!=', '')
            ->first();

        if (empty($address)) {
            return 'Unable to find address';
        }

        $allowed_zip = AllowedZip::where('zip', $address->zip)->first();
        if (empty($allowed_zip)) {
            return 'Invalid zip code found';
        }

        if ($allowed_zip->available != 'x') {
            return 'Groomit service is not available at this location';
        }

//        if ($allowed_zip->state_abbr == 'NJ') {
//            return 'Cat service is not available in New Jersey';
//        }

        return '';
    }

    //Can be called from groomer_on_the_way(), cc_hold() hourly, change_status() by CS. For S or A type both.
    //Not used any longer from 08/06/2020. instead use holdvoid_appointment().
    public function hold_appointment(AppointmentList $app) {

        try {
            if (empty($app)) {
                throw new \Exception('Invalid appointment ID provided', -1);
            }

            ### only groomer assigned and time accepted is allowed ###
            if (!in_array($app->status, ['D', 'O'])) {
                throw new \Exception('Invalid appointment status provided: ' . $app->status, -2);
            }

            if ($app->total == 0) {
                return [
                    'error_code' => '',
                    'error_msg' => ''
                ];
            }

            ### find credit card ###
            $user_billing = UserBilling::find($app->payment_id);
            if (empty($user_billing)) {
                throw new \Exception('Failed to find user credit card', -3);
            }

            if (empty($user_billing->card_token)) {
                throw new \Exception('Credit card token is empty', -4);
            }

            ### 1. Total A/S amount already match w/  total amt, no need to hold again ###
            $total_trans = CCTrans::where('appointment_id', $app->appointment_id)
                ->whereIn('type', ['A', 'S', 'V'])
                ->where('category', 'S')
                ->where('result', 0)
                //->whereNull('void_date')
                ->where('amt', '!=', 0.01)
                ->where( DB::raw('IfNull(error_name,"")'), '!=', 'cccomplete' )
                ->sum( DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );

            if  (($total_trans == $app->total) || ($app->total == 0) ) {
                return [
                    'error_code' => '',
                    'error_msg' => ''
                ];
            }

            ### 2.  total amount is bigger than holding/charged amount.###
            if( $app->total > $total_trans) {
                $ret = Converge::auth_only($user_billing->card_token, ($app->total - $total_trans) , $app->appointment_id, 'S');
                if (!empty($ret['error_msg'])) {

                    $app->status = 'R';
                    $app->note = $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                    $app->save();

                    $msg = 'Holding amount with credit card has failed : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']' . "<br/>";
                    $msg .= ' - appointment_id : ' . $app->appointment_id;
                    Helper::send_mail('tech@groomit.me', '[GROOMIT][' . env('APP_ENV') . '] Failed to holding amount', $msg);

                    return $ret;
                }
            }else if($app->total < $total_trans){
                $first_auth_only_trans = CCTrans::where('appointment_id', $app->appointment_id)
                    ->whereIn('type', ['A', 'S'])
                    ->where('category', 'S')
                    ->where('result', 0)
                    ->whereNull('void_date')
                    ->where('amt', '!=', 0.01)
                    ->orderBy('amt','desc')
                    ->first();

                $ret = Converge::void( $app->appointment_id, 'S', $first_auth_only_trans->void_ref,$first_auth_only_trans->type,$total_trans - $app->total  );

                if (!empty($ret['error_msg'])) {
                    Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to refund CC trans when hold_appointment : ' . $app->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
                }
                return $ret;
            }


            return [
                'error_code' => '',
                'error_msg' => ''
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    //Can be called from groomer_on_the_way(), cc_hold() hourly, change_status() by CS. For S or A type both.
    public function holdvoid_appointment(AppointmentList $app) {

        try {
            if (empty($app)) {
                throw new \Exception('Invalid appointment ID provided', -1);
            }

            ### only groomer assigned and time accepted is allowed ###
            if (!in_array($app->status, ['D', 'O'])) {
                throw new \Exception('Invalid appointment status provided: ' . $app->status, -2);
            }

            if ($app->total == 0) {
                return [
                    'error_code' => '',
                    'error_msg' => ''
                ];
            }

            ### find credit card ###
            $user_billing = UserBilling::find($app->payment_id);
            if (empty($user_billing)) {
                throw new \Exception('Failed to find user credit card', -3);
            }

            if (empty($user_billing->card_token)) {
                throw new \Exception('Credit card token is empty', -4);
            }

            ### 1. Total A/S amount already match w/  total amt, no need to hold again ###
            $total_trans = CCTrans::where('appointment_id', $app->appointment_id)
                ->whereIn('type', ['A', 'S', 'V'])
                ->where('category', 'S')
                ->where('result', 0)
                //->whereNull('void_date')
                ->where('amt', '!=', 0.01)
                ->where( DB::raw('IfNull(error_name,"")'), '!=', 'cccomplete' )
                ->sum( DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );

            if  (($total_trans == $app->total) || ($app->total == 0) ) {
                return [
                    'error_code' => '',
                    'error_msg' => ''
                ];
            }

            ### 2.  total amount is bigger than holding/charged amount.###
            if( $app->total > $total_trans) {
                $ret = Converge::auth_only($user_billing->card_token, 0.01 , $app->appointment_id, 'A');
                if (!empty($ret['error_msg'])) {
                    $app->status = 'R';
                    $app->note = $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                    $app->save();

                    $msg = 'Holding amount of $0.01 , has failed : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']' . "<br/>";
                    $msg .= ' - appointment_id : ' . $app->appointment_id;
                    Helper::send_mail('tech@groomit.me', '[GROOMIT][' . env('APP_ENV') . '] Failed to holding $0.01 at last stage', $msg);
                    Helper::send_mail('help@groomit.me', '[GROOMIT][' . env('APP_ENV') . '] Failed to holding $0.01 at last stage', $msg);
                    return $ret;
                }

                ### void 1 cent if validation pass. ###
                $void_ref = $ret['void_ref'];
                $ret2 = Converge::void($app->appointment_id, 'A', $void_ref, 'A'); //Full voids of $0.01 auth.
                if (!empty($ret['error_msg'])) {
                    $msg = ' - appointment ID: ' . $app->appointment_id . '<br/>';
                    $msg .= ' - void_ref: ' . $void_ref . '<br/>';
                    $msg .= ' - error : Voiding validation amount of $0.01 has failed : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']<br/>';

                    Helper::send_mail('jun@jjonbp.com', '[Groomit][' . env('APP_ENV') . '] New appointment error : ' . $app->appointment_id, $msg);

                    return $ret2;
                }


            }else if($app->total < $total_trans){
                //Do not refund remaining amount at this time, because those will be refunded when appointment is completed.
//                $first_auth_only_trans = CCTrans::where('appointment_id', $app->appointment_id)
//                    ->whereIn('type', ['A', 'S'])
//                    ->where('category', 'S')
//                    ->where('result', 0)
//                    ->whereNull('void_date')
//                    ->where('amt', '!=', 0.01)
//                    ->orderBy('amt','desc')
//                    ->first();
//
//                $ret = Converge::void( $app->appointment_id, 'S', $first_auth_only_trans->void_ref,$first_auth_only_trans->type,$total_trans - $app->total  );
//
//                if (!empty($ret['error_msg'])) {
//                    Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to refund CC trans when hold_appointment : ' . $app->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
//                }
//                return $ret;
            }


            return [
                'error_code' => '',
                'error_msg' => ''
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    //Convert from Auth into Sales. if shortage of amount, charge the remaining amount. if overcharged, refund the difference.
    public function charge_appointment(AppointmentList $app) {
        $void_ref = '';

        try {
            if (empty($app)) {
                throw new \Exception('Invalid appointment ID provided', -1);
            }

            if ($app->status != 'S') {
                throw new \Exception('Invalid appointment status provided: ' . $app->status, -2);
            }

            $user = User::findOrFail($app->user_id);

            ### charge credit card ###
            $payment = UserBilling::find($app->payment_id);
            if (empty($payment)) {
                $app->status = 'F';
                $app->mdate = Carbon::now();

                $u = Auth::guard('admin')->user();
                $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;

                $app->save();

                Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty payment info of appointment : ' . $app->appointment_id);
                Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty payment info of appointment : ' . $app->appointment_id);
                Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty payment info of appointment : ' . $app->appointment_id);

                self::send_failure_email($app, $user);

                $msg = 'Payment information cannot be found for the appointment';
                throw new \Exception($msg);
            }

            if (empty($payment->card_token)) {
                $app->status = 'F';

                $app->mdate = Carbon::now();

                $u = Auth::guard('admin')->user();
                $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;

                $app->save();

                Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty card token for appointment : ' . $app->appointment_id);
                Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty card token for appointment : ' . $app->appointment_id);
                Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty card token for appointment : ' . $app->appointment_id);


                $this->send_failure_email($app, $user);

                $msg = 'Payment card token is empty.';
                throw new \Exception($msg);
                //return Redirect::route('admin.appointment', array('id' => $request->id))->with('alert', $msg);
            }

            Helper::log('### Before converge sales ###');
            $void_ref = '';
            if ($app->total > 0) {
                 ## First, complete auth only transactions
                $all_auth_only_trans = CCTrans::where('appointment_id', $app->appointment_id)
                            ->where('type', 'A')
                            ->where('category', 'S')
                            ->where('result', 0)
                            ->whereNull('void_date')
                            ->where('amt', '!=', 0.01)
                            ->get();

                foreach( $all_auth_only_trans as $auth_only_trans) {
                    //This create a new record of 'S' type at cc_trans
                    $ret = Converge::complete($auth_only_trans->void_ref, $auth_only_trans->token, $auth_only_trans->amt, $app->appointment_id, 'S');
                    if (!empty($ret['error_msg'])) {
                        ### notify tech@groomit.me ###
                        $msg = $ret['error_msg'] . ' [ ' . $ret['error_code'] . ']';
                        Helper::send_mail('tech@groomit.me', '[GROOMIT][' . getenv('APP_ENV') . '] Auth-Complete Failed : ' . $app->appointment_id, $msg);
                        Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Auth-Complete Failed : ' . $app->appointment_id, $msg);
                        ### auth complete has failed so we need sales ###
                        throw new \Exception($msg, -4);
                    }
                }

                ### Second : find total paid(Sales) transaction.
                $total_paid_trans = 0;
                $total_paid_trans = CCTrans::where('appointment_id', $app->appointment_id)
                    ->whereIn('type', ['S','V'])
                    ->where('category', 'S')
                    ->where('result', 0)
                    ->whereNull('void_date')
                    ->sum( DB::raw("case when type = 'S' then amt else -amt end") );
                    //->sum('amt');

                //Charge the difference amount only.
                if ($total_paid_trans < $app->total ) {
                    $ret = Converge::sales($payment->card_token, ($app->total - $total_paid_trans), $app->appointment_id, 'S');

                    if (!empty($ret)) {
                        if (!empty($ret['error_msg'])) {
                            $app->status = 'F';
                            $app->mdate = Carbon::now();
                            $u = Auth::guard('admin')->user();
                            $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
                            $app->save();

                            Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
                            Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
                            Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing of Sales of difference from Auth failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);

                            $this->send_failure_email($app, $user);

                            $msg = 'Credit card processing failed : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                            throw new \Exception($msg, -5);
                            //return Redirect::route('admin.appointment', array('id' => $request->id))->with('alert', $msg);
                        }

                        $void_ref = $ret['void_ref'];
                    }
                }else if($total_paid_trans > $app->total){ //refund the difference.

                    $first_paid_trans = CCTrans::where('appointment_id', $app->appointment_id)
                        ->where('type', 'S')
                        ->where('category', 'S')
                        ->where('result', 0)
                        ->whereNull('void_date')
                        ->orderBy('amt', 'desc')
                        ->first();
                    //Partial refunds.
                    $ret = Converge::void($app->appointment_id,'S', $first_paid_trans->void_ref,$first_paid_trans->type, $total_paid_trans - $app->total );
                    if (!empty($ret['error_msg'])) {
                        Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to refund CC trans when charge_appointment appointment : ' . $app->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
                        /*return response()->json([
                            'msg' => 'Failed to void auth only credit card transaction'
                        ]);*/
                    }
                }
            }

            $app->status = 'P';
            $app->mdate = Carbon::now();

            $u = Auth::guard('admin')->user();
            $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
            $app->save();

            ### Send email ###

            Helper::log('### After converge sales ###', $app->satus);

            ## Payment Success ##
            if ($app->status == 'P') {
                ########
                # Send Push Notification / SMS HERE!!
                ########

                if (empty($app->rating) || empty($app->tip)) {
                    $user = User::find($app->user_id);
                    $payload = [
                        'type' => 'T',
                        'id' => $app->appointment_id
                    ];

                    $message = Constants::$message_app['ThankYou'];
                    ## Send message ##
                    $r = New Message;
                    $r->send_method = 'B'; // for now both
                    $r->sender_type = 'A'; // admin user
                    $r->sender_id = isset($u) ? $u->admin_id : null;
                    $r->receiver_type = 'A'; // an end user
                    $r->receiver_id = $app->user_id;
                    $r->message_type = 'D';
                    $r->appointment_id = $app->appointment_id;
                    $r->subject = '';
                    $r->message = $message;
                    $r->cdate = Carbon::now();
                    $r->save();

                    if (!empty($user->device_token)) {
                        $error = Helper::send_notification('groomit', $r->message, $user->device_token, $r->subject, $payload);
                        if (!empty($error)) {
                            //throw new \Exception($error);
                            Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $app->appointment_id, $error);
                        }
                    }

                    ### SMS to user
                    if (!empty($user->phone)) {
                        $sms_message = 'Your pets grooming has been completed. You have been charged for [$' . number_format($app->total, 2) . '].';

                        $ret = Helper::send_sms($user->phone, $sms_message);

                        if (!empty($ret)) {
                            Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $sms_message);
                        }
                    }

                }

                ### Success Email to end-user ###
                $ret = $this->send_success_email($app, $user, $payment);
                if (!empty($ret)) {
                    //throw new \Exception($ret);
                    Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $app->appointment_id, $ret);
                }

                //Helper::log('### Success email return ###', $ret);

                ### if the user has signup credit, activate reserved credit
                $msg = CreditProcessor::activateReservedReferralCredit($user->user_id, $app->appointment_id);
                if (!empty($msg)) {
                    //throw new \Exception($msg);
                    Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $app->appointment_id, $msg);
                }

                ### if promo code is used and the type is referral, give referral credit directly for code owner ###
                if (!empty($app->promo_code)) {
                    $promo_code = PromoCode::whereRaw('code = ?', [strtoupper($app->promo_code)])->first();
                    if (empty($promo_code)) {
                        //throw new \Exception('Something is wrong. Promo code used could not be found in our system');
                        Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $app->appointment_id, ' promo code not found');
                    }

                    if (!empty($promo_code) && $promo_code->type == 'R') {
                        $msg = CreditProcessor::giveReferralCredit($app->promo_code, $app->appointment_id);
                        if (!empty($msg)) {
                            //throw new \Exception($msg);
                            Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error in giveReferralCredit: ' . $app->appointment_id, $msg);
                        }
                    }
                }

                ### share profit ###
                $msg = ProfitSharingProcessor::shareProfit($app);
                if (!empty($msg)) {
                    Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $app->appointment_id, $msg);
                }

                ### process promotion if any ###
                //$msg = CreditProcessor::give_march_2018_promo($app);
//                $msg = CreditProcessor::process_promotion($app);
//                if (!empty($msg)) {
//                    Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $app->appointment_id, $msg);
//                }
            }
            ### End Send email ###

            return [
                'error_code' => '',
                'error_msg' => ''
            ];

        } catch (\Exception $ex) {

            Helper::log('#### EXCEPTIOn ####', $ex->getTraceAsString());

//            if (!empty($void_ref)) {
//                $ret = Converge::void($app->appointment_id, 'S', $void_ref,'S');
//                if (!empty($ret['error_msg'])) {
//                    Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Sales auto void failed', ' - id: ' . $app->appointment_id . '<br/> - error_msg : ' . $ret['error_msg'] . '<br/> - error_code: ' . $ret['error_code']);
//                }
//            }

            Helper::send_mail('it@jjonbp.com', '[GroomIt][' . getenv('APP_ENV') .'] Sales charge proces failed', ' - id: ' . $app->appointment_id . '<br/> - error_msg : ' . $ex->getMessage() . '<br/> - error_code: ' . $ex->getCode());
            Helper::send_mail('help@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Sales charge proces failed', ' - id: ' . $app->appointment_id . '<br/> - error_msg : ' . $ex->getMessage() . '<br/> - error_code: ' . $ex->getCode());

            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    public function refund_holding_amounts(AppointmentList $app) {
        $void_ref = '';

        try {
            if (empty($app)) {
                throw new \Exception('Invalid appointment ID provided', -1);
            }

            $void_ref = '';


            $all_auth_only_trans = CCTrans::where('appointment_id', $app->appointment_id)
                    ->where('type', 'A')
                    ->where('category', 'S')
                    ->where('result', 0)
                    ->whereNull('void_date')
                    ->where('amt', '!=', 0.01)
                    ->get();

            foreach( $all_auth_only_trans as $auth_only_trans) {
                    //This create a new record of 'S' type at cc_trans
                    $ret = Converge::void($app->appointment_id, $auth_only_trans->category, $auth_only_trans->void_ref, $auth_only_trans->type );
                    if (!empty($ret['error_msg'])) {
                        ### notify tech@groomit.me ###
                        $msg = $ret['error_msg'] . ' [ ' . $ret['error_code'] . ']';
                        Helper::send_mail('tech@groomit.me', '[GROOMIT][' . getenv('APP_ENV') . '] Refund Holding amount Failed : ' . $app->appointment_id, $msg);
                        Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Refund Holding amount  Failed : ' . $app->appointment_id, $msg);
                        ### auth complete has failed so we need sales ###
                        throw new \Exception($msg, -4);

                    }
            }

            return [
                'error_code' => '',
                'error_msg' => ''
            ];

        } catch (\Exception $ex) {

            Helper::log('#### EXCEPTIOn ####', $ex->getTraceAsString());
            Helper::send_mail('it@jjonbp.com', '[GroomIt][' . getenv('APP_ENV') .'] Refund Holding amount failed', ' - id: ' . $app->appointment_id . '<br/> - error_msg : ' . $ex->getMessage() . '<br/> - error_code: ' . $ex->getCode());
            Helper::send_mail('help@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Refund Holding amount failed', ' - id: ' . $app->appointment_id . '<br/> - error_msg : ' . $ex->getMessage() . '<br/> - error_code: ' . $ex->getCode());

            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }
    }

    public function send_success_email(AppointmentList $app, User $user, UserBilling $payment) {
        if ($app->groomer_id) {
            $groomer = Groomer::findOrFail($app->groomer_id);
            $groomer_name = $groomer->first_name . ' ' . $groomer->last_name;;
        } else {
            $msg = 'Please select a groomer first.';
            return $msg;
        }

        $pets = DB::select("
            select 
                a.pet_id, 
                p.sub_total,
                p.tax, 
                p.total, 
                c.name as pet_name,
                c.dob as pet_dob,
                timestampdiff(month, c.dob, curdate()) as age,
                b.prod_id as package_id,
                b.prod_name as package_name,
                a.amt as price
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

        $subject = "Your Groomit Appointment has been completed";

        $data = [];
        $data['appointment_id'] = $app->appointment_id;
        $data['email'] = $user->email;
        $data['name'] = $user->first_name;
        $data['subject'] = $subject;
        $data['groomer'] = $groomer_name;
        $data['card_holder'] = $payment->card_holder;
        //$data['card_type'] = Constants::$card_type[$payment->card_type];
        $data['card_number'] = substr($payment->card_number, -4);
        $data['safety_insurance'] = $app->safety_insurance;
        $data['sameday_booking'] = $app->sameday_booking;
        $data['fav_fee'] = $app->fav_groomer_fee;
        $data['sub_total'] = $app->sub_total;
        $data['promo_code'] = $app->promo_code;
        $data['promo_amt'] = $app->promo_amt;
        $data['credit_amt'] = $app->credit_amt;
        $data['tax'] = $app->tax;
        $data['total'] = $app->total;
        $data['payment_date'] = Carbon::now()->toDayDateTimeString();

        $address = Address::find($app->address_id);
        if( !empty( $address->address2) && ( $address->address2 != '' ) ) {
            $data['address'] = $address->address1 .' # ' . $address->address2 . ', ' . $address->city;
        }else {
            $data['address'] = $address->address1 .  ', ' . $address->city;
        }



        foreach ($pets as $k=>$v) {
            $data['pet'][$k]['pet_name'] = $v->pet_name;
            $data['pet'][$k]['package_name'] = $v->package_name;
            $data['pet'][$k]['sub_total'] = $v->sub_total;
        }

        $data['accepted_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $app->accepted_date)->format('l, F j Y, h:i A');

        $referral_arr = UserProcessor::get_referral_code($user->user_id);
        $data['referral_code'] = $referral_arr['referral_code'];
        $data['referral_amount'] = $referral_arr['referral_amount'];

        $ret = Helper::send_html_mail('service_completion', $data);

        if (!empty($ret)) {
            $msg = 'Failed to send service completion email: ' . $ret;
            return $msg;
        }


//        //Send Survey request email
        $data = [] ;
        $data['subject'] = "How did we do ?";
        $data['email'] = $user->email;
        $data['name'] = $user->first_name;
        if (getenv('APP_ENV') == 'production') {
            $data['links'] = 'https://groomit.page.link/survey';
        } else{
            $data['links'] = 'https://groomit.page.link/survey-demo';
        }
        $ret = Helper::send_html_mail('survey-email', $data);


        return '';
    }

    public function send_failure_email(AppointmentList $app, User $user) {
        $subject = "Your payment was unsuccessful. Please update your payment method within the App";

        $data = [];
        $data['appointment_id'] = $app->appointment_id;
        $data['email'] = $user->email;
        $data['name'] = $user->first_name;
        $data['subject'] = $subject;

        $referral_arr = UserProcessor::get_referral_code($user->user_id);
        $data['referral_code'] = $referral_arr['referral_code'];
        $user['referral_amount'] = $referral_arr['referral_amount'];

        $ret = Helper::send_html_mail('payment_failure', $data);

        if (!empty($ret)) {
            $msg = 'Failed to send service completion email' . $ret;
            //return Redirect::route('admin.appointment', array('id' => $request->id))->with('alert', $msg);
            return $msg;
        }
    }

    //Get Appointments details to show /user/home page
    public static function get_info(AppointmentList $ap) {
        $ap->status_name = '';
        if (array_key_exists($ap->status, Constants::$appointment_status)) {
            $ap->status_name = Constants::$appointment_status[$ap->status];
        }

        $ap->earning = ProfitSharingProcessor::getProfit($ap->appointment_id);
        $ap->estimated_earning = ProfitSharingProcessor::getEstimatedProfit($ap->appointment_id);
        $ap->estimated_bonus = ProfitSharingProcessor::getEstimatedBonus($ap->appointment_id);

        $place = DB::select("
                    select b.place_id, b.place_name, a.other_name
                    from appointment_place a inner join place b on a.place_id = b.place_id
                    where a.appointment_id = :appointment_id
                ", [
            'appointment_id' => $ap->appointment_id
        ]);
        if (!empty($place)) {
            $ap->place_id = $place[0]->place_id;
            $ap->place_name = $place[0]->place_name;
            $ap->other_place_name = $place[0]->other_name;
        }

        $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name', 'profile_photo', 'phone', 'bio', 'dog', 'cat' )->first();
        if (!empty($ap->groomer)) {
//            if (!empty($ap->groomer->profile_photo)) {
//                $ap->groomer->profile_photo = $ap->groomer->profile_photo;
//            }

            $ret = DB::select("
                        select avg(rating) avg_rating, count(*) total_appts
                        from appointment_list
                        where groomer_id = :groomer_id
                        and status = 'P'
                    ", [
                'groomer_id' => $ap->groomer_id
            ]);

            $ap->groomer->overall_rating = 0;
            $ap->groomer->total_appts = 0;
            if (count($ret) > 0) {
                $ap->groomer->overall_rating = $ret[0]->avg_rating;
                $ap->groomer->total_appts = $ret[0]->total_appts;
            }

            $ap->groomer->completed_qty = AppointmentList::where('groomer_id', $ap->groomer_id)
                ->where('status', 'P')
                ->count();

            $fav = DB::select("
                        select groomer_id
                        from user_favorite_groomer
                        where user_id = :user_id
                        and groomer_id = :groomer_id
                    ", [
                'user_id' => $ap->user_id,
                'groomer_id' => $ap->groomer_id
            ]);

            if (count($fav) > 0) {
                $ap->groomer->favorite = true;
            } else {
                $ap->groomer->favorite = false;
            }
        }

        $addr = Address::find($ap->address_id);
        if (!empty($addr)) {
            if( !empty( $addr->address2) && ( $addr->address2 != '' ) ) {
                $ap->address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
            }else {
                $ap->address = $addr->address1 . ', '  . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
            }

            $ap->address_info = $addr;
        }

        $ap->pets = DB::select("
                    select 
                        a.pet_id, 
                        p.size_id, 
                        p.sub_total, 
                        p.tax, 
                        p.total, 
                        c.name as pet_name,
                        c.dob as pet_dob,
                        timestampdiff(month, c.dob, curdate()) as age,
                        b.prod_id as package_id,
                        b.prod_name as package_name,
                        a.amt as price,
                        c.type,
                        c.special_note as user_note,
                        c.coat_type,
                        c.temperament,
                        d.breed_id,
                        d.breed_name
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                        left join breed d on c.breed = d.breed_id 
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                ", [
            'appointment_id' => $ap->appointment_id
        ]);

        foreach ($ap->pets as $p) {

            $p->info = Pet::find($p->pet_id);
            if (!empty($p->info->vaccinated_image)) {
                try{
                    $p->info->vaccinated_image = base64_encode($p->info->vaccinated_image);
                } catch (\Exception $ex) {
                    $p->info->vaccinated_image= $p->info->vaccinated_image;
                }
            }


            $p->age = PetProcessor::get_age($p->pet_id);
            $p->groomer_note = self::get_groomer_note($p->pet_id);

            $p->addons = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = :pet_id
                        and b.prod_type = 'A'
                        and b.pet_type = :pet_type
                    ", [
                'appointment_id' => $ap->appointment_id,
                'pet_id' => $p->pet_id,
                'pet_type' => $p->type
            ]);

            $p->shampoo = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = :pet_id
                        and b.prod_type = 'S'
                        and b.pet_type = :pet_type
                    ", [
                'appointment_id' => $ap->appointment_id,
                'pet_id' => $p->pet_id,
                'pet_type' => $p->type
            ]);

            $p->photos = PetPhoto::where('pet_id', $p->pet_id)->get();
            foreach ($p->photos as $photo) {
                //$photo->photo = base64_encode($photo->photo);
                try{
                    $photo->photo = base64_encode($photo->photo);
                } catch (\Exception $ex) {
                    $photo->photo = $photo->photo ;
                }
            }

            if (count($p->photos) > 0) {
                $p->photo = $p->photos[0]->photo;
            }

            $p->before_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $p->pet_id)
                ->where('type', 'B')
                ->select('image')
                ->first();

            if (!empty($p->before_image)) {
                $p->before_image = base64_encode($p->before_image->image);
            }

            $p->after_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $p->pet_id)
                ->where('type', 'A')
                ->select('image')
                ->first();

            if (!empty($p->after_image)) {
                $p->after_image = base64_encode($p->after_image->image);
            }
        }

        if (count($ap->pets) > 0) {
            $ap->first_pet = $ap->pets[0];
        }

        $user = User::find($ap->user_id);
        if (!empty($user)) {
            $ap->owner_name = $user->first_name . ' ' . $user->last_name;
        } else {
            $ap->owner_name = '';
        }

        $ap->status_name = Constants::$appointment_status[$ap->status];

        $arrived = false;
        if( !empty($ap->groomer_id) && ($ap->groomer_id > 0)  ){
            $ga = GroomerArrived::where('appointment_id', $ap->appointment_id)
                ->where('groomer_id', $ap->groomer_id)
                ->where('result', 'Y')
                ->first();
            if (!empty($ga)) {
                $arrived = true;
            }
        }
        $ap->arrived = $arrived;

        return $ap;
    }

    public static function get_info_v3(AppointmentList $ap) {
        $ap->status_name = '';
        if (array_key_exists($ap->status, Constants::$appointment_status)) {
            $ap->status_name = Constants::$appointment_status[$ap->status];
        }

        $ap->earning = ProfitSharingProcessor::getProfit($ap->appointment_id);
        $ap->estimated_earning = ProfitSharingProcessor::getEstimatedProfit($ap->appointment_id);
        $ap->estimated_bonus = ProfitSharingProcessor::getEstimatedBonus($ap->appointment_id);

        $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name', 'profile_photo', 'phone', 'bio')->first();
        if (!empty($ap->groomer)) {
            if (!empty($ap->groomer->profile_photo)) {
                $ap->groomer->profile_photo = $ap->groomer->profile_photo;
            }

            $ret = DB::select("
                        select avg(rating) avg_rating, count(*) total_appts
                        from appointment_list
                        where groomer_id = :groomer_id
                        and status = 'P'
                    ", [
                'groomer_id' => $ap->groomer_id
            ]);

            $ap->groomer->overall_rating = 0;
            $ap->groomer->total_appts = 0;
            if (count($ret) > 0) {
                $ap->groomer->overall_rating = round($ret[0]->avg_rating);
                $ap->groomer->total_appts = round($ret[0]->total_appts);
            }

            $fav = DB::select("
                        select groomer_id
                        from user_favorite_groomer
                        where user_id = :user_id
                        and groomer_id = :groomer_id
                    ", [
                'user_id' => $ap->user_id,
                'groomer_id' => $ap->groomer_id
            ]);

            if (count($fav) > 0) {
                $ap->groomer->favorite = 'Y';
            } else {
                $ap->groomer->favorite = 'N';
            }
        }

        $addr = Address::find($ap->address_id);
        if (!empty($addr)) {
            $ap->address = $addr->address1 . ', ' . $addr->address2 . ' ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
            $ap->address_info = $addr;
        }

        $ap->pets = DB::select("
                    select 
                        a.pet_id, 
                        p.size_id, 
                        p.sub_total, 
                        p.tax, 
                        p.total, 
                        c.name as pet_name,
                        c.dob as pet_dob,
                        timestampdiff(month, c.dob, curdate()) as age,
                        b.prod_id as package_id,
                        b.prod_name as package_name,
                        a.amt as price,
                        c.type,
                        c.special_note as user_note,
                        d.breed_id,
                        d.breed_name
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                        left join breed d on c.breed = d.breed_id 
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                ", [
            'appointment_id' => $ap->appointment_id
        ]);

        foreach ($ap->pets as $p) {

            $p->info = Pet::find($p->pet_id);
            try{
                $p->info->vaccinated_image = base64_encode($p->info->vaccinated_image);
            } catch (\Exception $ex) {
                $p->info->vaccinated_image= $p->info->vaccinated_image;
            }

            $p->age = PetProcessor::get_age($p->pet_id);
            $p->groomer_note = self::get_groomer_note($p->pet_id);

            $p->addons = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = :pet_id
                        and b.prod_type = 'A'
                        and b.pet_type = :pet_type
                    ", [
                'appointment_id' => $ap->appointment_id,
                'pet_id' => $p->pet_id,
                'pet_type' => $p->type
            ]);

            $p->shampoo = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = :pet_id
                        and b.prod_type = 'S'
                        and b.pet_type = :pet_type
                    ", [
                'appointment_id' => $ap->appointment_id,
                'pet_id' => $p->pet_id,
                'pet_type' => $p->type
            ]);

            $p->photos = PetPhoto::where('pet_id', $p->pet_id)->get();
            foreach ($p->photos as $photo) {
                //$photo->photo = base64_encode($photo->photo);
                try{
                    $photo->photo = base64_encode($photo->photo);
                } catch (\Exception $ex) {
                    $photo->photo = $photo->photo ;
                }
            }

            if (count($p->photos) > 0) {
                $p->photo = $p->photos[0]->photo;
            }

            $p->before_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $p->pet_id)
                ->where('type', 'B')
                ->select('image')
                ->first();

            if (!empty($p->before_image)) {
                $p->before_image = base64_encode($p->before_image->image);
            }

            $p->after_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $p->pet_id)
                ->where('type', 'A')
                ->select('image')
                ->first();

            if (!empty($p->after_image)) {
                $p->after_image = base64_encode($p->after_image->image);
            }
        }

        if (count($ap->pets) > 0) {
            $ap->first_pet = $ap->pets[0];
        }

        $user = User::find($ap->user_id);
        if (!empty($user)) {
            $ap->owner_name = $user->first_name . ' ' . $user->last_name;
        } else {
            $ap->owner_name = '';
        }

        $ap->status_name = Constants::$appointment_status[$ap->status];
        return $ap;
    }

    public static function get_groomer_note($pet_id) {
        $app_pets = DB::select("
            select
                a.appointment_id,
                a.groomer_id, 
                c.first_name as groomer_first_name,
                c.last_name as groomer_last_name,
                b.pet_id, 
                b.groomer_note,
                a.accepted_date
            from appointment_list a
                inner join appointment_pet b on a.appointment_id = b.appointment_id 
                    and b.pet_id = :pet_id 
                inner join groomer c on a.groomer_id = c.groomer_id
            where a.status = 'P'
            and a.accepted_date >= :date
            order by a.accepted_date desc       
        ", [
            'pet_id' => $pet_id,
            'date' => Carbon::today()->subYear()
        ]);

        return $app_pets;
    }

    public static function recalc_sub_total($pet, $ap, $zip, $shampoo, $add_ons) {
        $sub_total = 0;

        if ($pet->type != 'cat') {
            # package
            $package = AppointmentProduct::join('product', 'product.prod_id', '=', 'appointment_product.prod_id')
                ->where('appointment_product.appointment_id', $ap->appointment_id)
                ->where('appointment_product.pet_id', $pet->pet_id)
                ->where('product.prod_type', 'P')
                ->where('product.pet_type', $pet->type)
                ->first();

            if (empty($ap_prod)) {
                throw new \Exception('Unable to find package for the pet');
            }

            $pkg_o = Product::where('prod_id', $package->prod_id)
                ->where('prod_type', 'P')
                ->where('pet_type', $pet->type)
                ->first();

            if (empty($pkg_o)) {
                throw new \Exception('Invalid package ID provided');
            }

            $pkg_denom = ProductDenom::where('prod_id', $package->prod_id)
                ->where('size_id', $pet->size)
                ->first();
            if (empty($pkg_denom)) {
                throw new \Exception('Invalid package amount');
            }

            $price = Helper::get_price($package->prod_id, $pet->size, $zip);
            $sub_total += doubleVal($price);

            # shampoo
            $shp_o = Product::where('prod_id', $shampoo)
                ->where('prod_type', 'S')
                ->where('pet_type', $pet->type)
                ->first();
            if (empty($shp_o)) {
                throw new \Exception('Invalid shampoo ID provided');
            }

            $shp_denom = ProductDenom::where('prod_id', $shp_o->prod_id)->first();
            if (empty($shp_denom)) {
                throw new \Exception('Invalid shampoo amount');
            }

            $price = Helper::get_price($shp_o->prod_id, $pet->size, $zip);

            $sub_total += doubleVal($price);

            # add-ons
            if (is_array($add_ons)) {
                foreach ($add_ons as $ao) {

                    $ao_o = Product::where('prod_id', $ao->prod_id)->first();
                    if (empty($ao_o)) {
                        throw new \Exception('Invalid add-on ID provided');
                    }

                    $ao_denom = ProductDenom::where('prod_id', $ao->prod_id)
                        ->first();
                    if (empty($ao_denom)) {
                        throw new \Exception('Invalid add-on amount:');
                    }

                    $price = Helper::get_price($ao->prod_id, $pet->size, $zip);

                    $sub_total += doubleVal($price);
                }
            }

        } else {

            # cat service
            $package = AppointmentProduct::join('product', 'product.prod_id', '=', 'appointment_product.prod_id')
                ->where('appointment_product.appointment_id', $ap->appointment_id)
                ->where('appointment_product.pet_id', $ap->pet_id)
                ->where('product.prod_type', 'P')
                ->where('product.pet_type', $pet->type)
                ->first();
            if (empty($ap_prod)) {
                throw new \Exception('Unable to find package for the pet');
            }

            $pkg_o = Product::where('prod_id', $package->prod_id)
                ->where('prod_type', 'P')
                ->where('pet_type', $pet->type)
                ->first();
            if (empty($pkg_o)) {
                throw new \Exception('Invalid cat service ID provided');
            }

            $pkg_denom = ProductDenom::where('prod_id', $package->prod_id)->first();
            if (empty($pkg_denom)) {
                throw new \Exception('Invalid cat service amount');
            }

            $price = Helper::get_price($package->prod_id, 2, $zip); // for now, all cats are size 2
            $sub_total += doubleVal($price);
        }

        ### for other pets
        $others_pets_sub_total = AppointmentProduct::where('appointment_id', $ap->appointment_id)
            ->where('pet_id', '!=', $pet->pet_id)
            ->sum('amt');

        $sub_total += $others_pets_sub_total;

        return $sub_total;
    }

    public static function get_tax($zip, $sub_total, $safety_insurance, $promo_amt, $credit_amt, $sameday_booking = 0 ,$fav_fee = 0) {

        $tax_zip = TaxZip::where("zip", $zip)->first();
        $rates = 7.75;
        if (!empty($tax_zip)) {
            $rates = $tax_zip->rates;
        }

        $taxable_amt = $sub_total + $safety_insurance - $promo_amt - $credit_amt + $sameday_booking + $fav_fee ;
        $tax = ceil($taxable_amt * $rates) / 100;

        //Helper::send_mail('jun@jjonbp.com', $rates,   "[$sub_total][$safety_insurance][$promo_amt][$credit_amt][$sameday_booking]:[$taxable_amt]][$rates][$tax]" );

        return $tax;
    }

    public static function update_service($appointment_id, $pet_id, $size_id, $package_id, $shampoo_id, $addons, $modified_by) {

        $ap = AppointmentList::findOrFail($appointment_id);
        if (empty($ap)) {
            throw new \Exception('Wrong appointment!');
        }

        $address = Address::find($ap->address_id);
        if (empty($address)) {
            throw new \Exception('Wrong address!');
        }

        $pet = Pet::find($pet_id);
        if (empty($pet)) {
            throw new \Exception('Wrong pet!');
        }

        #### Get total ####
        ### 1. Update current pet service info and price.

        $pet_total = 0;

        ## Check Pet Size ##
        $ap_pet = AppointmentPet::where('appointment_id', $ap->appointment_id)
            ->where('pet_id', $pet_id)
            ->first();

        if ($pet->type == 'dog' && $ap_pet->size_id != $size_id) {
            $pet = Pet::findOrFail($pet_id);
            $pet->size = $size_id;
            $pet->save();
        }

        ## package ##
        # a. Calculate package price
        $pkg = Product::where('prod_id', $package_id)
            ->where('prod_type', 'P')
            ->where('pet_type', $pet->type)
            ->first(); // dog or cat

        if (empty($pkg)) {
            throw new \Exception('Invalid package ID provided');
        }

        $pkg_denom = ProductDenom::where('prod_id', $package_id);
        if ($pet->type == 'dog') {
            $pkg_denom = $pkg_denom->where('size_id', $size_id);
        }

        $pkg_denom = $pkg_denom->first();
        if (empty($pkg_denom)) {
            throw new \Exception('Invalid package amount : ' . $package_id . ' / ' . $size_id);
        }

        $pkg_price = Helper::get_price($pkg->prod_id, $pkg_denom->size_id, $address->zip);
        $pet_total += doubleVal($pkg_price);


        # b. Update package info
        $pkg_products = Product::select('prod_id')
            ->where('prod_type', 'P')
            ->where('pet_type', $pet->type)
            ->get();
        if (empty($pkg_products)) {
            throw new \Exception('Cannot find package products. Please try again.');
        }

        foreach ($pkg_products as $pp) {
            $ap_pkg = AppointmentProduct::where('prod_id', $pp->prod_id)
                ->where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $pet_id)
                ->first();

            if (!empty($ap_pkg)) {
                $ap_pkg->prod_id = $pkg->prod_id;
                $ap_pkg->amt = $pkg_price;
                $ap_pkg->save();
                break;
            }
        }

        ## shampoo ##
        # a. Calculate shampoo price

        if ($pet->type == 'dog') {  //only 1 shampoo only for cat, as of now.
            $shp = Product::where('prod_id', $shampoo_id)
                ->where('prod_type', 'S')
                ->where('pet_type', $pet->type)
                ->first();
            if (empty($shp)) {
                throw new \Exception('Invalid shampoo ID provided');
            }

            $shp_denom = ProductDenom::where('prod_id', $shp->prod_id)->first();
            if (empty($shp_denom)) {
                throw new \Exception('Invalid shampoo amount');
            }

            $shp_price = Helper::get_price($shp->prod_id, $shp_denom->size_id, $address->zip);
            $pet_total += doubleVal($shp_price);

            # b. Update shampoo info
            $shp_products = Product::select('prod_id')
                ->where('prod_type', 'S')
                ->where('pet_type', $pet->type)
                ->get();
            if (empty($shp_products)) {
                throw new \Exception('Cannot find shampoo products. Please try again.');
            }

            foreach ($shp_products as $sp) {
                $ap_shp = AppointmentProduct::where('prod_id', $sp->prod_id)
                    ->where('appointment_id', $ap->appointment_id)
                    ->where('pet_id', $pet_id)
                    ->first();

                if (!empty($ap_shp)) {
                    $ap_shp->prod_id = $shp->prod_id;
                    $ap_shp->amt = $shp_price;
                    $ap_shp->save();
                    break;
                }
            }
        }

        ## add-ons ##
        ## Delete all current pet's add-ons first ##
        $addon_products = Product::select('prod_id')
            ->where('prod_type', 'A')
            ->where('pet_type', $pet->type)
            ->get();

        if (empty($addon_products)) {
            throw new \Exception('Cannot find add-on products. Please try again.');
        }

        foreach ($addon_products as $aop) {
            $ao_shp = AppointmentProduct::where('prod_id', $aop->prod_id)
                ->where('appointment_id', $ap->appointment_id)
                ->where('pet_id', $pet_id)
                ->first();

            if (!empty($ao_shp)) {
                $ao_shp->delete();
            }
        }


        if (is_array($addons)) {
            foreach ($addons as $addon) {

                # a. Calculate add-on price
                $ao = Product::where('prod_id', $addon)->first();
                if (empty($ao)) {
                    throw new \Exception('Invalid add-on ID provided');
                }

                $ao_denom = ProductDenom::where('prod_id', $addon)
                    ->first();
                if (empty($ao_denom)) {
                    throw new \Exception('Invalid add-on amount');
                }

                $ao_price = Helper::get_price($addon, $ao_denom->size_id, $address->zip);
                $pet_total += doubleVal($ao_price);

                //Delete existing ADD-ON, if exist, in case multiple duplicated ADD-ON in the same array.
                AppointmentProduct::where('prod_id', $ao->prod_id )
                    ->where('appointment_id', $ap->appointment_id)
                    ->where('pet_id', $pet_id)
                    ->delete();

                # b. Add new add-ons
                $np = new AppointmentProduct;
                $np->appointment_id = $ap->appointment_id;
                $np->pet_id = $pet_id;
                $np->prod_id = $ao->prod_id;
                $np->amt = $ao_price;
                $np->created_by = $modified_by;
                $np->cdate = Carbon::now();
                $np->save();

            }
        }


        ## Update appointment pet's info : cannot use Eloquent since it doesn't allow multiple Primary Key ##
//            $ap_pet->sub_total = $pet_total;
//            $ap_pet->total = $pet_total;
//            $ap_pet->save();

        //Ignore sameday_booking intentionally, becasue this part is by Pets, not by appointments.
        $tax = self::get_tax($address->zip, $pet_total, 0, 0, 0,0);

        DB::table('appointment_pet')
            ->where('appointment_id',  $ap->appointment_id)
            ->where('pet_id', $pet_id)
            ->update(['sub_total' => $pet_total, 'tax' => $tax, 'total'=> $pet_total + $tax, 'size_id' => $size_id]);


        ### 2. Update Sub total & Total for this appointment.
        $sub_total = $pet_total;

        # a. Check another pet total
        $other_ap = AppointmentPet::where('pet_id', '<>', $pet_id)->where('appointment_id', $ap->appointment_id)->get();
        if (!empty($other_ap)) {
            foreach ($other_ap as $o) {
                $sub_total += doubleVal($o->sub_total);
            }
        }


        $safety_insurance = env('SAFETY_INSURANCE');
        $sameday_booking = $ap->sameday_booking;
        $fav_fee = $ap->fav_groomer_fee;
        $promo_amt = 0;
        $org_promo_amt = 0;
        $applied_promo_amt = 0;
        $promo_amt_type = null;
        $credit_amt = $ap->credit_amt;

        # b. Check Promo Code :
        $promo = PromoCode::whereRaw("code = '" . strtoupper($ap->promo_code) . "'")->first();
        if (!empty($promo)) {
            $promo_amt_type = $promo->amt_type;
            $org_promo_amt = $promo->amt;

            $safety_insurance = $promo->no_insurance == 'Y' ? 0 : env('SAFETY_INSURANCE');
        }

        $ap->safety_insurance = $safety_insurance;

        switch ($promo_amt_type) {
            case 'A':
                $promo_amt = $org_promo_amt;
                break;
            case 'R':
                $promo_amt = $sub_total * $org_promo_amt * 0.01;
                break;
            case 'H':
                $add_ons = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and b.prod_type = 'A'
                    ", [
                    'appointment_id' => $ap->appointment_id
                ]);

                $highest_addon_price = 0;
                if (count($add_ons) > 0) {
                    foreach ($add_ons as $a) {
                        if ($highest_addon_price <= $a->price) {
                            $highest_addon_price = $a->price;
                        }
                    }
                }

                $promo_amt = $highest_addon_price;
                break;
            default:
                $promo_amt = 0;
                break;
        }

        $taxable_promo_amt = 0;
        if (!empty($promo)) {
            $taxable_promo_amt = $promo_amt;
            if ($promo_amt > ($sub_total + $safety_insurance )) {
                $taxable_promo_amt = $sub_total + $safety_insurance;
            }
            $taxable_promo_amt = $promo->include_tax == 'N' ? 0 : $taxable_promo_amt;
        }

        $tax = self::get_tax($address->zip, $sub_total, $safety_insurance, $taxable_promo_amt, 0,$sameday_booking, $fav_fee );
        //$tax = self::get_tax($address->zip, $sub_total, $safety_insurance, $taxable_promo_amt, $credit_amt, $sameday_booking, $fav_fee ); //Not sure but This look correct one.
        $ap->tax = $tax;

        $temp_total = $sub_total - $credit_amt + $safety_insurance + $sameday_booking + $fav_fee + $tax;

        if ($promo_amt >= $temp_total) {
            $applied_promo_amt = $temp_total;
        } else {
            $applied_promo_amt = $promo_amt;
        }

        $total =  $temp_total - $promo_amt;

        if ($total > 0) {
            $grand_total = $total;
            $ap->new_credit = 0;
        } else {
            $grand_total = 0;
            if (!empty($promo) && $promo->type == 'K') {
                $ap->new_credit = abs($total);
            } else {
                $ap->new_credit = 0;
            }
        }

        $old_total = $ap->total;

        $ap->promo_amt = $applied_promo_amt;
        $ap->sub_total = $sub_total;
        $ap->total = $grand_total;
        $ap->mdate = Carbon::now();

        $ap->modified_by = $modified_by;

        $ap->save();

        ### void auth only if total changed ###
        if ($ap->status != 'P' && $old_total != $grand_total) {
            //Do nothing at updating services, but use 'Re-Holding/charges/refunds' button, instead.

            //When a payment is completed or repayment is executed, it should refund or charge more amount.


            ### void auth only if there's any ###
//            $auth_only_trans = CCTrans::where('appointment_id', $ap->appointment_id)
//                ->where('type', 'A')
//                ->where('category', 'S')
//                ->where('result', 0)
//                ->whereNull('void_date')
//                ->where('amt', '!=', 0.01)
//                ->first();

            //Stop automatic voids
//            if (!empty($auth_only_trans)) {
//                $ret = Converge::void($ap->appointment_id, 'S', $auth_only_trans->void_ref, 'A');
//                if (!empty($ret['error_msg'])) {
//                    Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void auth only CC trans when updating appointment : ' . $ap->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
//                    /*return response()->json([
//                        'msg' => 'Failed to void auth only credit card transaction'
//                    ]);*/
//                }
//            }

            ### void 'Sales' only if there's any ###
//            $paid_trans = CCTrans::where('appointment_id', $ap->appointment_id)
//              ->where('type', 'S')
//              ->where('category', 'S')
//              ->where('result', 0)
//              ->whereNull('void_date')
//              ->first();

            //Stop automatic voids
//            if (!empty($paid_trans)) {
//                $ret = Converge::void($ap->appointment_id, 'S', $paid_trans->void_ref, 'S');
//                if (!empty($ret['error_msg'])) {
//                    Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void sales CC trans when cancelling appointment : ' . $ap->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
//                    /*return response()->json([
//                        'msg' => 'Failed to void auth only credit card transaction'
//                    ]);*/
//                }
//            }
        }

        ### remove new credit record first ###
        $credit = Credit::where('appointment_id', $ap->appointment_id)
            ->where('user_id', $ap->user_id)
            ->where('type', 'C')
            ->where('category', 'T')
            ->where('status', 'A')
            ->first();

        if (!empty($credit)) {
            $credit->delete();
        }

        ### now give new credit if it's required ###
        if ($ap->new_credit > 0) {
            $c = new Credit;
            $c->user_id = $ap->user_id; // code owner
            $c->type = 'C';
            $c->category = 'T';
            $c->amt = $ap->new_credit;
            $c->referral_code = '';
            $c->appointment_id = $ap->appointment_id;
            $c->expire_date = Carbon::today()->addDays(365);
            $c->status = 'A';
            $c->cdate = Carbon::now();
            $c->save();
        }

        if ($ap->status == 'P') {
            ### share profit ###
            $msg = ProfitSharingProcessor::shareProfit($ap);
            if (!empty($msg)) {
                Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $ap->appointment_id, $msg);
            }

            ### give 03/2018 promo ###
//            $msg = CreditProcessor::give_march_2018_promo($ap);
//            if (!empty($msg)) {
//                Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $ap->appointment_id, $msg);
//            }
        }
    }

    public static function groomer_on_the_way($appointment_id, $user_name, $user_id, $sender_type) {
        try {

            $ap = AppointmentList::findOrFail($appointment_id);
            if (empty($ap->groomer_id) || empty($ap->reserved_date)) {
                throw new \Exception('Please select a groomer and confirm the appointment date first.');
            }

            $groomer = Groomer::findOrFail($ap->groomer_id);
            if (empty($groomer)) {
                throw new \Exception('Invalid groomer ID assigned to the appointment');
            }

            /*
            ### if groomer has another appointment in status of O/W, block it ###
            $working_ap = AppointmentList::where('groomer_id', $groomer->groomer_id)
                ->whereIn('status', ['O', 'W'])
                ->where('appointment_id', '!=', $ap->appointment_id)
                ->first();
            if (!empty($working_ap)) {
                throw new \Exception('The groomer is working on another appointment already: ' . $working_ap->appointment_id);
            }
            */

            $ap->status = 'O';
            $ap->mdate = Carbon::now();

            $ap->modified_by = $user_name . '(' . $user_id . ')';

            $ap->save();

            $groomer_name = $groomer->first_name . ' ' . $groomer->last_name;
            $accepted_date = new DateTime($ap->accepted_date);
            $accepted_date = $accepted_date->format('l, F j Y, h:i A');

            $message = Constants::$message_app['GroomerOnWay'];
            $message = str_replace('GROOMER_NAME', $groomer_name, $message);
            $message = str_replace('SERVICE_TIME', $accepted_date, $message);

            ## Send message ##
            $r = New Message;
            $r->send_method = 'B'; // for now both
            $r->sender_type = $sender_type; // admin user
            $r->sender_id = $user_id;
            $r->receiver_type = 'A'; // an end user
            $r->receiver_id = $ap->user_id;
            $r->message_type = $ap->status;
            $r->appointment_id = $ap->appointment_id;
            $r->subject = '';
            $r->message = $message;
            $r->cdate = Carbon::now();
            $r->save();

            //DB::commit();

            ### groomer on the way ###
            if ($ap->status == 'O' && $ap->total > 0) {
                ### if $0.01 holding/void exist withing 1 day, skip hold/void process ###

                $total_trans = null;
                $total_trans = CCTrans::where('appointment_id', $ap->appointment_id)
                    ->whereIn('type', ['A', 'S', 'V'])
                    ->where('category', 'S')
                    ->where('result', 0)
                    ->where('amt', '!=', 0.01)
                    ->where( DB::raw('IfNull(error_name,"")'), '!=', 'cccomplete' )
                    ->sum( DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );

                if( empty($total_trans) ) {
                    $total_trans = 0;
                }

                if ( $total_trans != $ap->total ) { //In case of ECO, don't have to hold/void of $0.01.
                    $hold_1cent_trans = CCTrans::where('appointment_id', $ap->appointment_id)
                        ->where('type', 'A')
                        ->where('category', 'A')
                        ->where('result', 0)
                        ->where('amt', '=', 0.01)
                        ->where('cdate', '>=',  Carbon::today()->subDays(1) )
                        ->first();

                    if( empty($hold_1cent_trans)){
                        $proc = new AppointmentProcessor();
                        $ret = $proc->holdvoid_appointment($ap);
                        if (!empty($ret['error_msg'])) {
                            $ap->status = 'R';
                            $ap->note = $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                            $ap->save();

                            throw new \Exception('Fail to hold 1 cent. Please be sure to notify Customer Care.', $ret['error_code']);
                        }
                    }
                }
            }

//            if ($ap->status == 'O' && $ap->total > 0) {
//                ### if no holding CC found, try to do it ###
//                $total_trans = CCTrans::where('appointment_id', $ap->appointment_id)
//                    ->whereIn('type', ['A', 'S', 'V'])
//                    ->where('category', 'S')
//                    ->where('result', 0)
//                    ->where('amt', '!=', 0.01)
//                    ->where( DB::raw('IfNull(error_name,"")'), '!=', 'cccomplete' )
//                    ->sum( DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );
//
//                if ($total_trans != $ap->total ) {
//                    $proc = new AppointmentProcessor();
//                    $ret = $proc->hold_appointment($ap);
//                    if (!empty($ret['error_msg'])) {
//                        $ap->status = 'R';
//                        $ap->note = $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
//                        $ap->save();
//
//                        throw new \Exception('Fail to hold 1 cent. Please be sure to notify Customer Care.', $ret['error_code']);
//                    }
//                }
//            }

            ### send text to user ###
            $user = User::findOrFail($ap->user_id);
            if (!empty($user->phone)) {
                $phone = $user->phone;
                $ret = Helper::send_sms($phone, $message);

                if (!empty($ret)) {
                    //throw new \Exception($ret);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message . '/ Appointment ID:' . $ap->appointment_id);
                }
            }
            ### end send text to user ###

            ### send push to user ###
            if (!empty($user->device_token)) {
                $payload = [
                    'type' => 'A',
                    'id' => $ap->appointment_id
                ];

                $error = Helper::send_notification('groomit', $r->message, $user->device_token, $r->subject, $payload);
                if (!empty($error)) {
                    //throw new \Exception('Push Notfication Error: ' . $error);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Push Notfication Error:' . $error, 'Appointment ID:' . $ap->appointment_id);
                }
            } else {
                //throw new \Exception('Push Notfication Error: No device token found');
                Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Push Notfication Error: No device token found', 'Appointment ID:' . $ap->appointment_id);
            }
            ### end send push to user ###


            ### send email to groomer ###
            $address = '';
            $addr = Address::find($ap->address_id);
            if (!empty($addr)) {
                $address  =$addr->address1 . ' ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
            }

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
                        c.special_note as note
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                ", [
                'appointment_id' => $ap->appointment_id
            ]);

            $subject = "Your Groomit Appointment Information";

            $data = [];
            $data['email'] = $groomer->email;
            $data['name'] = $groomer->first_name;
            $data['subject'] = $subject;
            $data['address'] = $address;
            $data['user'] = $user->first_name . ' ' . $user->last_name; // temp.

            foreach ($pets as $k=>$v) {
                $data['pet'][$k]['pet_name'] = $v->pet_name;
                $data['pet'][$k]['package_name'] = $v->package_name;
                $data['pet'][$k]['breed_name'] = empty($v->breed) ? '' : Breed::findOrFail($v->breed)->breed_name;
                $data['pet'][$k]['size_name'] = empty($v->size) ? '' : Size::findOrFail($v->size)->size_name;

                if (!empty($v->pet_age)) {
                    $data['pet'][$k]['age'] = ($v->pet_age > 0 ? $v->pet_age . ' year' : ''). ($v->pet_age > 1 ? 's old' : ' old');
                } else {
                    $dob = Carbon::parse($v->pet_dob);
                    $data['pet'][$k]['age'] = $dob->age < 2 ? $dob->diffInMonths(Carbon::now()) . " months old" : $dob->age . " years old";
                }

                $data['pet'][$k]['note'] = $v->note;
                $data['pet'][$k]['shampoo'] = '';
                $data['pet'][$k]['addon'] = '';

                $shampoo = DB::select("
                            select
                                b.prod_name
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                                inner join pet c on a.pet_id = c.pet_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'S'
                            and b.pet_type = c.type
                        ", [
                    'appointment_id' => $ap->appointment_id,
                    'pet_id' => $v->pet_id
                ]);

                if (!empty($shampoo)) {
                    foreach ($shampoo as $a) {
                        $data['pet'][$k]['shampoo'] .= $a->prod_name;
                    }
                }

                $addon = DB::select("
                            select
                                b.prod_name
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                                inner join pet c on a.pet_id = c.pet_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'A'
                            and b.pet_type = c.type
                        ", [
                    'appointment_id' => $ap->appointment_id,
                    'pet_id' => $v->pet_id
                ]);

                if (!empty($addon)) {
                    foreach ($addon as $a) {
                        $data['pet'][$k]['addon'] .= '[' . $a->prod_name . '] ';
                    }
                }
            }

            $ap->accepted_date = new DateTime($ap->accepted_date);
            $data['accepted_date'] = $ap->accepted_date->format('l, F j Y, h:i A');

            $ret = Helper::send_html_mail('groomer_on_the_way_for_groomer', $data);

            if (!empty($ret)) {
                $msg = 'Failed to send groomer on the way email to groomer';
                throw new \Exception($msg);
            }
            ## end send email to groomer ##


            ### send email to USER ###
            $data['email'] = $user->email;
            $data['name'] = $user->first_name;
            $data['groomer'] = $groomer_name;

            $referral_arr = UserProcessor::get_referral_code($user->user_id);
            $data['referral_code'] = $referral_arr['referral_code'];
            $data['referral_amount'] = $referral_arr['referral_amount'];

            $ret = Helper::send_html_mail('groomer_on_the_way', $data);

            if (!empty($ret)) {
                $msg = 'Failed to send groomer on the way email to user';
                throw new \Exception($msg);
            }
            ### end send email to USER ###


            ### send SMS to groomer ###
            if (!empty($groomer->mobile_phone)) {
                $phone = $groomer->mobile_phone;

                $message = "Your Groomit appointment information. \n\n";

                $message .= $user->first_name . ' ' . $user->last_name . "\n";
                $message .= $data['accepted_date'] . "\n";
                $message .= $data['address'] . "\n\n";
                $message .= "Appointment ID: " . $ap->appointment_id . "\n";

                foreach($data['pet'] as $p) {
                    $message .= "\n" . "Pet Name: " . $p['pet_name'] . "\n";
                    $message .= "Pet Age: " . $p['age'] . " \n" ;

                    if ($p['breed_name'] != '' && $p['size_name'] != '') {
                        $message .= $p['breed_name'] . " / " . $p['size_name'] . "\n";
                    }

                    $message .= "Package: " . $p['package_name'] . "\n";
                    $message .= "Shampoo: " . $p['shampoo'] . "\n";

                    if ($p['addon'] != '') {
                        $message .= "Add-ons: " . $p['addon'] . "\n";
                    }

                    if ($p['note'] != '') {
                        $message .= "Special Note: " . $p['note'] . "\n";
                    }
                }

                ## Save message ##
                $r = New Message;
                $r->send_method = 'B'; // for now both
                $r->sender_type = $sender_type; // admin user
                $r->sender_id = $user_id;
                $r->receiver_type = 'B'; // groomer
                $r->receiver_id = $ap->groomer_id;
                $r->message_type = 'O';
                $r->appointment_id = $ap->appointment_id;
                $r->subject = '';
                $r->message = $message;
                $r->cdate = Carbon::now();
                $r->save();

                ## send SMS ##
                $ret = Helper::send_sms($phone, $message);
                if (!empty($ret)) {
                    //throw new \Exception('Groomer SMS Error: ' . $ret);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message . '/ Appointment ID:' . $ap->appointment_id);
                }
            }

            ### end send SMS to groomer ###

            return [
                'error_code' => '',
                'error_msg' => ''
            ];

        } catch (\Exception $ex) {
            return [
                'error_code' => $ex->getCode(),
                'error_msg' => $ex->getMessage()
            ];
        }

    }

    public static function get_recent($user_id, $take = 3) {
        ### status list ###
        # - N : New
        # - D : Groomer assigned
        # - W : Work in progress
        # - C : Cancelled
        # - S : Work Completed
        # - F : Failed ( Maybe payment failure ? )

        ### list of information to be returned ###
        # - appointment general
        # - groomer information
        # - pet images ( before / after )

        $query = AppointmentList::where('user_id', $user_id)
            ->whereIn('status', ['P'])
            //->where('accepted_date', '<', Carbon::now()->toDateTimeString())
            ->where('accepted_date', '<>', 'null')
            /*->select([
                'appointment_id',
                'groomer_id',
                'reserved_at',
                'reserved_date',
                'accepted_date',
                'sub_total',
                'tax',
                'total',
                'status',
                'rating',
                'rating_comments',
                'address_id',
                'tip'
            ])*/->orderBy('accepted_date', 'desc');

        if ($take == 1) {
            $app = $query->first();
            if (empty($app)) {
                return null;
            }
            return AppointmentProcessor::get_info($app);
        }

        if (!is_null($take)) {
            $appointments = $query->take($take)->get();
        } else {
            $appointments = $query->get();
        }

        $arr_appointments = [];
        if (count($appointments) > 0) {
            foreach ($appointments as $ap) {
                $arr_appointments[] = AppointmentProcessor::get_info($ap);
            }
        }

        return $arr_appointments;
    }

    public static function get_upcoming($user_id, $take = 3) {
        ### status list ###
        # - N : Groomer not assigned yet
        # - G : Groomer assigned
        # - W : Work in progress
        # - C : Cancelled
        # - S : Work Completed
        # - F : Failed ( Maybe payment failure ? )

        ### list of information to be returned ###
        # - appointment general
        # - groomer information
        # - pet images ( before / after )

        $query = AppointmentList::where('user_id', $user_id)
            ->whereNotIn('status', ['C', 'F', 'S', 'P', 'L'])
            //->whereRaw("((accepted_date is not null and accepted_date >= '" . Carbon::today() ."') or (accepted_date is null))")
            ->whereRaw("((accepted_date is not null and accepted_date >= '" . Carbon::today() ."') or (accepted_date is null and reserved_date >= '" . Carbon::today() . "'))")
            ->orderByRaw("IfNull(accepted_date, reserved_date)");
            //->orderBy('accepted_date', 'asc','reserved_date','asc');

        if ($take == 1) {
            $app = $query->first();
            if (empty($app)) {
                return null;
            }
            return AppointmentProcessor::get_info($app);
        }

        if (!is_null($take)) {
            $appointments = $query->take($take)->get();
        } else {
            $appointments = $query->get();
        }

        $arr_appointments = [];
        if (count($appointments) > 0) {
            foreach ($appointments as $ap) {
                $arr_appointments[] = AppointmentProcessor::get_info($ap);
            }
        }
        return $arr_appointments;
    }

    public static function rate($user_id, $appointment_id, $rating) {
        try {

            $user = User::find($user_id);
            if (empty($user)) {
                throw new \Exception('Invalid user ID provided');
            }

            ### find appointment ###
            $ap = AppointmentList::where('user_id', $user_id)
                ->where('appointment_id', $appointment_id)
                ->first();

            if (empty($ap)) {
                throw new \Exception('Invalid user ID or appointment ID provided.');
            }

            if ($ap->status != 'P') {
                throw new \Exception('Invalid appointment status');
            }

            $ap->rating = $rating;
            $ap->modified_by = $user->email . ' (' . $user_id . ')';
            $ap->mdate = Carbon::now();
            $ap->save();

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function survey($user_id, $appointment_id, $sc, $gq, $cl, $va,$cs, $su) {
        try {

            $survey = Survey::find($appointment_id);
            if (!empty($survey)) {
                //return as if survey is successful, if it already exist.
                return '';
            }

            $user = User::find($user_id);
            if (empty($user)) {
                throw new \Exception('Invalid user ID provided');
            }

            ### find appointment ###
            $ap = AppointmentList::where('user_id', $user_id)
                ->where('appointment_id', $appointment_id)
                ->first();

            if (empty($ap)) {
                throw new \Exception('Invalid user ID or appointment ID provided.');
            }

            if ($ap->status != 'P') {
                throw new \Exception('Invalid appointment status');
            }


            $survey = new Survey();
            $survey->appointment_id = $appointment_id;
            $survey->ov =  round( ($sc + $gq + $cl + $va + $cs)/5 ,2 ) ;
            $survey->sc =  $sc ;
            $survey->gq =  $gq ;
            $survey->cl =  $cl ;
            $survey->va =  $va ;
            $survey->cs =  $cs ;
            $survey->su =  $su ;
            $survey->cdate      = Carbon::now();
            $survey->save();


//            $ap->rating = ( $gq + $cl )/2 ;
//            $ap->modified_by = $user->email . ' (' . $user_id . ')';
//            $ap->mdate = Carbon::now();
//            $ap->save();

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function survey_get( $appointment_id) {
        try {

            $survey = Survey::find($appointment_id);
            if (!empty($survey)) {
                //return as if survey is successful, if it already exist.
                return $survey;
            }

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function tip($user_id, $appointment_id, $tip) {
        try {

            $user = User::find($user_id);
            if (empty($user)) {
                throw new \Exception('Invalid user ID provided');
            }

            $ap = AppointmentList::where('user_id', $user_id)
                ->where('appointment_id', $appointment_id)->first();
            if (empty($ap)) {
                throw new \Exception('Invalid appointment ID provided');
            }

            if (!is_null($ap->tip)) {
                throw new \Exception('Tip already given');
            }

            $payment = null;
            //$payment = UserBilling::find($ap->payment_id); //Use the latest registered one, not the one at the appointment.
            $payment = UserBilling::where('user_id', $ap->user_id) //Use the default_card first.
                ->where('status', 'A')
                ->where('default_card', 'Y')
                ->orderBy('cdate', 'desc')
                ->first();

            if (empty($payment) ) { //Use the latest one, if no default_card exist.
                $payment = UserBilling::where('user_id', $ap->user_id)
                    ->where('status', 'A')
                    ->orderBy('cdate', 'desc')
                    ->first();
            }

            if (empty($payment)) {
                throw new \Exception('Unable to find user credit card information');
            }

            if (empty($payment->card_token)) {
                throw new \Exception('Invalid credit card setup found');
            }

            $tip = (empty($tip)) ? 0 : $tip;
            $tip_amt = $tip; // $ap->sub_total * $tip / 100;

            Helper::log('######## Give TIP ######## (' . $appointment_id . ') $' . $tip . ' by user ' . $user_id,'');

            if ($tip > 0) {

                $ret = Converge::sales($payment->card_token, $tip_amt, $ap->appointment_id, 'T');
                if (!empty($ret['error_msg'])) {
                    ### send failure tip email to user ###
                    self::send_tip_email($ap, false);

                    ### notify tech as well  ###
                    Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] TIP credit card processing failed', ' - appointment : ' . $ap->appointment_id . '<br> - error : ' . $ret['error_msg']);
                    Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] TIP credit card processing failed', ' - appointment : ' . $ap->appointment_id . '<br> - error : ' . $ret['error_msg']);

                    throw new \Exception($ret['error_msg'], $ret['error_code']);
                }
            }

            $ap->tip = $tip_amt;
            $ap->mdate = Carbon::now();
            $ap->modified_by = $user->email . '(' . $user->user_id . ')';
            $ap->save();

            ## generate tip profit ###
            if ($ap->tip > 0) {
                $ret = ProfitSharingProcessor::generateTipProfit($ap);
                if (!empty($ret)) {
                    Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] TIP profit generation failed', ' - appointment : ' . $ap->appointment_id . '<br> - error : ' . $ret);
                }
            }

            ### send success tip email to user ###
            self::send_tip_email($ap, true);


            ## send SMS to User ##
            if (!empty($user->phone)) {
                $groomer = Groomer::find($ap->groomer_id);
                $g_name = $groomer->first_name . ' ' . $groomer->last_name;
                $user_message = 'Your tip of $' . $tip_amt . ' to ' . $g_name . ' has been processed.';
                $ret = Helper::send_sms($user->phone, $user_message);
                if (!empty($ret)) {
                    //throw new \Exception('Groomer SMS Error: ' . $ret);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $user_message . '/ Appointment ID:' . $ap->appointment_id);
                }

                Message::save_sms_to_user($user_message, $user, $ap->appointment_id);
            }

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    private static function send_tip_email($ap, $tip_payment) {
        $groomer = Groomer::where('groomer_id', $ap->groomer_id)->first();
        if (empty($groomer)) {
            $msg = 'Send Tip Email - Groomer was not found.';
            Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . ']' . $msg, ' - appointment : ' . $ap->appointment_id);
            return;
        }

        $groomer_name = $groomer->first_name . ' ' . $groomer->last_name;

        $address = '';
        $addr = Address::find($ap->address_id);
        if (!empty($addr)) {
            $address = $addr->address1 . ' ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state;
        }

        $user = User::find($ap->user_id);
        $payment = UserBilling::find($ap->payment_id);

        $pets = DB::select("
            select 
                a.pet_id,
                c.name as pet_name,
                c.dob as pet_dob,
                b.prod_name as package_name,
                a.amt as price
            from appointment_pet p 
                inner join appointment_product a on p.appointment_id = a.appointment_id
                inner join product b on a.prod_id = b.prod_id
                inner join pet c on p.pet_id = c.pet_id
            where a.appointment_id = :appointment_id
            and a.pet_id = p.pet_id
            and b.prod_type = 'P'
            and b.pet_type = c.type
        ", [
            'appointment_id' => $ap->appointment_id
        ]);

        $data = [];
        $data['appointment_id'] = $ap->appointment_id;

        if (!empty($payment)) {
            $data['card_holder'] = $payment->card_holder;
            $data['card_number'] = substr($payment->card_number, -4);
        } else {
            $data['card_holder'] = 'Unknown';
            $data['card_number'] = 'Unknown';
        }

        $data['total'] = $ap->total;
        $data['tip'] = $ap->tip;
        $data['payment_date'] = Carbon::now()->toDayDateTimeString();

        $data['groomer'] = $groomer_name;
        $data['address'] = $address;
        $data['referral_code'] = $user->referral_code;

        foreach ($pets as $k => $v) {
            $data['pet'][$k]['pet_name'] = $v->pet_name;
            $data['pet'][$k]['package_name'] = $v->package_name;
        }

        $data['accepted_date'] = Carbon::parse($ap->accepted_date)->toDayDateTimeString();
        $data['email'] = $user->email;
        $data['name'] = $user->first_name;
        $data['user_name'] = $user->first_name . ' ' . $user->last_name;

        $referral_arr = UserProcessor::get_referral_code($user->user_id);
        $data['referral_code'] = $referral_arr['referral_code'];
        $data['referral_amount'] = $referral_arr['referral_amount'];

        if ($tip_payment !== true) {
            //DB::rollback();

            $data['subject'] = "Your tip payment was unsuccessful.";
            $ret = Helper::send_html_mail('tip_failure', $data);

            $msg = 'Failed to send tip failure email';

        } else {
            //DB::commit();
            $data['subject'] = "Your tip payment was successful.";
            $ret = Helper::send_html_mail('tip_success', $data);

            $msg = 'Failed to send tip success email';
        }

        if (!empty($ret)) {
            Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . ']' . $msg, ' - appointment : ' . $ap->appointment_id . '<br> - error : ' . $ret);
        }

        ## end send email ##
    }

    //Modified by End Users(D,A)
    public static function edit($user, $appointment_id, $datetime, $time, $fav_type=null, $fav_groomer_id=null ) {
        try {

            if (empty($user)) {
                return 'Invalid user provided';
            }

            # reserved at
            if (empty($datetime)) {
                return 'Please select service date and time';
            }

            if (empty($time) || !isset($time->start)) {
                return 'Invalid time provided';
            }

            // reserved date : show start time window
            $date_array = explode(" ", $datetime);
            $reserved_date = new DateTime($date_array[0] .' ' . $time->start);
            $reserved_date = $reserved_date->format('Y-m-d H:i:s');

            $now = Carbon::now();
            $r_date = Carbon::parse($reserved_date);

            //$time_diff = $now->diffInMinutes($r_date,false); //it returns r_date  - now, including plus/minus

            $ap = AppointmentList::where('appointment_id', $appointment_id)
                ->where('user_id', $user->user_id)
                ->first();
            if (empty($ap)) {
                return 'Invalid appointment';
            }

            if ( !in_array($ap->status , [ 'N','D' ] ) ) {
                return "Our Groomer is currently on the way to you. We can't modify your appointment. Please contact our support.";
            }

            //check if we allow it or not.
            if ($ap->accepted_date) {
                $app_date = $ap->accepted_date;
            } else {
                $app_date = $ap->reserved_date;
            }

            $package = AppointmentProduct::where('appointment_id', $ap->appointment_id)->first();
            if (in_array($package->prod_id, [28, 29])) {
                return 'ECO Package cannot be rescheduled. Please contact Customer Center.' ;
            }

//            else {
//                $app_date = Carbon::parse($app_date) ;
//                $prev_date = Carbon::createFromFormat('Y-m-d H:i:s', $app_date->addDays(-1)->format('Y-m-d') . ' 18:00:00') ;
//                if (Carbon::now() > $prev_date ) {
//                    if( $ap->status != 'N') {
//                        return "You cannot reschedule an appointment after 6 PM a day before. Please contact Customer Center.";
//                    }
//                }
//            }


            //Reschedule fee, if it's after 6pm , get the amount before data is updated , and actually charge the amount at last stage.
            $reschedule_ret = AppointmentProcessor::get_fee_amount('R', $ap) ;

            $prev_accepted_date = null;
            if (!empty($ap->accepted_date)) {
                Carbon::parse($ap->accepted_date)->format('m/d/Y h:i A');
            }
            $old_groomer_id = $ap->groomer_id;

            // Allow only Groomer not assigned, or Groomer assigned case
            if ($ap->status == 'D' || $ap->status == 'N') {
                $ap->status = 'N';
                $ap->groomer_id = null;
                if( !empty($old_groomer_id) &&  ($old_groomer_id > 0 )) {
                    $ap->prefer_groomer_id = $old_groomer_id;
                }

                $ap->accepted_date = null;
            }

//Removed this part when deploy new reschedule policy of 6 pm a day before.
//            if (!empty($ap->accepted_date)) {
//                $prev_accepted_date = Carbon::parse($ap->accepted_date);
//                //$pre_time_diff = $prev_accepted_date->diffInMinutes($now); //it returns now - prev_accepted_date
//                $pre_time_diff = $now->diffInMinutes($prev_accepted_date, false); //it returns prev_accepted_date - now including plus/minus.
//
//                $modified_qty = AppointmentModified::where('appointment_id', $ap->appointment_id)->count();
//                $package = AppointmentProduct::where('appointment_id', $ap->appointment_id)->first();
//                if (!empty($package) && in_array($package->prod_id, [28, 29])) {
//                    // allow to reschedule st least 24 hours left
//                    if ($time_diff < 60 * 24 || $pre_time_diff < 60 * 24) {
//                        return 'Appointment can’t be modified within 24h prior service time';
//                    }
//
//                    if ($modified_qty > 0) {
//                        return 'You can modify the appointment up to 1 time.';
//                    }
//
//                } else {
//                    // allow to reschedule st least 4 hours left
//                    if ($time_diff < 240 || $pre_time_diff < 240) {
//                        return 'Appointment can’t be modified within 4h prior service time.';
//                    }
//
//                    if ($modified_qty > 2) {
//                        return 'You can modify the appointment up to 3 times.';
//                    }
//                }
//
//                ## Record Modified history
//                AppointmentModified::add_record($ap->appointment_id, $modified_qty + 1, $ap->reserved_date);
//            }

            ### SAMEDAY BOOKING ###
            $sameday_booking = 0;
            $today = Carbon::now()->format('Y-m-d');
            $service_req_date = $date_array[0];
            if( $service_req_date == $today) {
                $sameday_booking = env('SAMEDAY_BOOKING');
                if( !empty($ap->promo_code) ) {
                    $promo_code = PromoCode::find(strtoupper($ap->promo_code));
                    if (empty($promo_code)) {
                        return  'Invalid promotion code provided' ;
                    }
                    if( !empty($promo_code->type) && ($promo_code->type == 'S') ) { //In case of Membership, no sameday_booking
                        $sameday_booking = 0;
                    }
                }
            }
//            if( $today >= '2019-09-18' ){
//                $service_req_date = $date_array[0];
//                if( $service_req_date == $today) {
//                    $sameday_booking = 20;
//                }
//            }

            $old_total = $ap->total ;
            if($sameday_booking > 0) {
                if( $ap->sameday_booking == 0 ) {
                    return 'You can not change your appointment into today';
                }else {
                    //No changes in prices into today's appointment
                }
            }else {
                if( $ap->sameday_booking == 0 ) {
                    //No changes in prices into future's appointment
                }else {
                    //change from Today into Future. Need to recalculate sameday_booking & Tax & Total amounts, and then void the authentication.

                    $addr = Address::find($ap->address_id);
                    $zip = $addr->zip;

                    $promo_amt = $ap->promo_amt ;
                    $sub_total = $ap->sub_total;
                    $safety_insurance = $ap->safety_insurance ;
                    $credit_amt = $ap->credit_amt;
                    $fav_fee = $ap->fav_groomer_fee ;

                    if ($promo_amt > ($sub_total + $safety_insurance )) {
                        $taxable_promo_amt = $sub_total + $safety_insurance ;
                    }else {
                        $taxable_promo_amt = $promo_amt;
                    }

                    ### tax ###
                    if( !empty($ap->promo_code) && $ap->promo_code != '' ){ //recalculate
                        $promo = PromoCode::whereRaw("code = '" . $ap->promo_code . "'")->first();
                        $taxable_promo_amt = empty($promo) ? 0 : ($promo->include_tax == 'N' ? 0 : $taxable_promo_amt);
                    }
                    $tax = AppointmentProcessor::get_tax($zip, $sub_total, $safety_insurance, $taxable_promo_amt, 0, $sameday_booking, $fav_fee ); //Do not consider credit amount,

                    $total = $sub_total + $safety_insurance  + $sameday_booking  + $fav_fee + $tax - $promo_amt - $credit_amt ;

                    $ap->tax = $tax;
                    $ap->sameday_booking = $sameday_booking; //$0
                    //No change in fav_groomer_fee
                    $ap->total = $total;

                    //It'll be charge more or refund partially when an appointment is at holding or completed.
                    //No automatic voids any longer
                    //Void Authentications
                    ### void auth only if there's any ###
                    //$auth_only_trans = CCTrans::where('appointment_id', $ap->appointment_id)
                    //    ->where('type', 'A')
                    //    ->where('category', 'S')
                    //    ->where('result', 0)
                    //    ->whereNull('void_date')
                    //    ->where('amt', '!=', 0.01)
                    //    ->first();

                    //if (!empty($auth_only_trans)) {
                    //    $ret = Converge::void($ap->appointment_id, 'S', $auth_only_trans->void_ref, 'A');
                    //    if (!empty($ret['error_msg'])) {
                    //        Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void auth only CC trans when updating appointment : ' . $ap->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
                    //    }
                    //}
                }
            }

            ## change to a new appointment ##
            $ap->reserved_at = $datetime;
            $ap->reserved_date = $reserved_date;

            //Should be no change at rescheduling. or we can open it again if we allow to change fav.groomer IDs out of multiple fav.groomers.
            //$ap->fav_type = !empty($fav_type) ? $fav_type : null ;
            //$ap->my_favorite_groomer = ( !empty($fav_groomer_id) &&  ($fav_groomer_id>0 ) ) ? $fav_groomer_id : null ;

            $ap->mdate = Carbon::now();
            $ap->modified_by = $user->email . '(' . $user->user_id . ')';


            //get the rescheduling fee in advance, so system could get the correct amount.
            //This is because this process remove groomer_id assigned, so need to get the data in advance.
            if( is_array($reschedule_ret) ) {
                if(  $reschedule_ret['msg'] == '') {
                    if(  $reschedule_ret['fee_amount']> 0 ) {
                        $estimated_earning = ProfitSharingProcessor::getEstimatedProfit($ap->appointment_id); //Estimated profit, based on SubTotal only, excluding Sameday/Bonus/FavGroomerFee.

//                        Helper::log('#### estimated earning ####', $estimated_earning);
//                        Helper::log('#### estimated earning ####', $reschedule_ret['fee_amount']);
//                        Helper::log('#### estimated earning ####', $reschedule_ret['tax_amount']);
//                        Helper::log('#### estimated earning ####', $ap->sub_total);

                        $groomer_commission_amt = round( ($reschedule_ret['fee_amount'] - $reschedule_ret['tax_amount']) * ($estimated_earning/$ap->sub_total), 2);
                        //Helper::log('#### estimated earning ####', $groomer_commission_amt);

                        //$groomer_commission_amt = round($estimated_earning *  $reschedule_ret['fee_rates'], 2) ;
                        //$groomer_commission_amt = round($reschedule_ret['fee_amount'] - $reschedule_ret['tax_amount'] , 2) ;
                        $msg = AppointmentProcessor::reschedule_appointment_with_fee($ap, $reschedule_ret['fee_amount'] - $reschedule_ret['tax_amount'],
                            $groomer_commission_amt, $reschedule_ret['tax_amount']);
                        if (!empty($msg)) {
                            return [
                                'msg' => $msg
                            ];
                        }
                    } //else, no rescheduling fee, so go proceed.
                }else {
                    return [
                        'msg' => 'Error:' . $reschedule_ret['msg']
                    ];
                }
            }else {
                return [
                    'msg' => 'Fail to get Rescheduling Fee'
                ];
            }

            //Save new data after credit card(Reschedule Fee) is completed.
            $ap->save();

            $groomer = Groomer::where('groomer_id', $old_groomer_id)->first();

            # send notification if groomer has been assigned.

//            $msg = "Customer : " . $user->first_name . " has cancelled appointment with you reserved at " . $ar->reserved_at;
//            if (!empty($groomer) && !empty($groomer->device_token)) {
//                $ret = Helper::send_notification('groomer', $push_msg, $groomer->device_token);
//                if (!empty($ret)) {
//                    return response()->json([
//                        'msg' => 'Failed to notify groomer with cancellation'
//                    ]);
//                }
//            }

            ### send SMS to groomer ###
            if (!empty($groomer)) {
                if (!empty($groomer->mobile_phone)) {
                    $phone = $groomer->mobile_phone;
                    $msg_groomer =  "Your appointment has been rescheduled, because the customer changed the appointment date/time.\n\n";
                    $msg_groomer .= "ID:" . $ap->appointment_id . "\n";
                    $msg_groomer .= "Customer:" . $user->first_name . "\n";
                    //$msg_groomer .= "Before:" . $prev_accepted_date . "\n";
                    $msg_groomer .= "New Date/Time:" . $datetime . "\n";
                    $msg_groomer .= "Please contact our Groomit Customer Center if you are not available at " . $datetime;

                    $ret = Helper::send_sms($phone, $msg_groomer);

                    if (!empty($ret)) {
                        Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Groomer SMS Error:' . $ret, $msg_groomer);
                    }

                    # save message #
                    $message = new Message;
                    $message->send_method = 'S';
                    $message->sender_type = 'A';
                    $message->message_type = 'UC';
                    $message->receiver_type = 'B';
                    $message->appointment_id = $ap->appointment_id;
                    $message->sender_id = 19; // system admin
                    $message->receiver_id = $groomer->groomer_id;
                    $message->message = $msg_groomer;
                    $message->cdate = Carbon::now();
                    $message->save();
                    # end save message #

                    self::send_groomer_notification2($ap); // Send TEXT again, in order to make record at groomer_opens.
                }

                $msg = "The appointment[" . $ap->appointment_id . "] has been updated by the customer. \nThe groomer was reallocated automatically, so please allocate a new one";
            } else {
                $msg = "The appointment[" . $ap->appointment_id . "] has been updated by the customer. \nPlease assign a groomer.";
            }

            ### send text ###
            if (getenv('APP_ENV') == 'production') {
                Helper::send_sms_to_admin($msg);
            }

            # save message #
            $message = new Message;
            $message->send_method = 'S';
            $message->sender_type = 'A';
            $message->message_type = 'UC';
            $message->receiver_type = 'C';
            $message->appointment_id = $ap->appointment_id;
            $message->sender_id = 19; // system admin
            $message->receiver_id = 19;
            $message->message = $msg;
            $message->cdate = Carbon::now();
            $message->save();
            # end save message #


            ## send SMS to User ##
            try{
                if (!empty($user->phone)) {
                    $a_date = Carbon::parse($ap->reserved_date)->format('m/d/Y h:i A');
                    $user_message = 'Your upcoming Groomit appointment has been changed. Your appointment is now scheduled for ' . $a_date;
                    $ret = Helper::send_sms($user->phone, $user_message);
                    if (!empty($ret)) {
                        //throw new \Exception('Groomer SMS Error: ' . $ret);
                        Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $user_message . '/ Appointment ID:' . $ap->appointment_id);
                    }

                    Message::save_sms_to_user($user_message, $user, $ap->appointment_id);
                }
            } catch (\Exception $ex) {
                Helper::log('### EXCEPTION ####', $ex->getTraceAsString());
            }

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function mark_as_favorite($user_id, $appointment_id, $add_to_favorite) {
        try {

            $ap = AppointmentList::where('user_id', $user_id)
                ->where('appointment_id', $appointment_id)->first();
            if (empty($ap)) {
                return 'Invalid appointment ID provided';
            }

            # favorite settings
            DB::statement("
                delete from user_favorite_groomer
                where user_id = :user_id
                and groomer_id = :groomer_id
            ", [
                'user_id' => $user_id,
                'groomer_id' => $ap->groomer_id
            ]);

            if ($add_to_favorite == 'Y') {
                $ret = DB::insert("
                    insert into user_favorite_groomer (user_id, groomer_id)
                    values (:user_id, :groomer_id)
                ", [
                    'user_id' => $user_id,
                    'groomer_id' => $ap->groomer_id
                ]);
                if ($ret < 1) {
                    return 'Failed to add user favorite groomer';
                }
            }

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function notify_groomer_assignment(AppointmentList $app, Groomer $groomer) {
        try {

            $user = User::findOrFail($app->user_id);

            # to new assigned groomer
            $data = [];

            # address
            $addr = Address::find($app->address_id);
            $address = '';
            if (!empty($addr)) {
                $address  =$addr->address1 . ' ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
            }
            $data['address'] = $address;
            $data['referral_code'] = $user->referral_code;

            $data['email'] = $groomer->email;
            $data['name'] = $groomer->first_name;
            $data['subject'] = "You have an assigned Groomit appointment.";
            $data['groomer'] = $user->first_name . ' ' . $user->last_name; // temp.

            $data['accepted_date'] = $app->accepted_date->format('l, F j Y, h:i A');

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

            foreach ($pets as $k=>$v) {
                $data['pet'][$k]['pet_name'] = $v->pet_name;
                $data['pet'][$k]['package_name'] = $v->package_name;
                $data['pet'][$k]['breed_name'] = empty($v->breed) ? '' : Breed::findOrFail($v->breed)->breed_name;
                $data['pet'][$k]['size_name'] = empty($v->size) ? '' : Size::findOrFail($v->size)->size_name;

                if (!empty($v->pet_age)) {
                    $data['pet'][$k]['age'] = ($v->pet_age > 0 ? $v->pet_age . ' year' : ''). ($v->pet_age > 1 ? 's old' : ' old');
                } else {
                    $dob = Carbon::parse($v->pet_dob);
                    $data['pet'][$k]['age'] = $dob->age < 2 ? $dob->diffInMonths(Carbon::now()) . " months old" : $dob->age . " years old";
                }

                $data['pet'][$k]['note'] = $v->note;
                $data['pet'][$k]['shampoo'] = '';
                $data['pet'][$k]['addon'] = '';

                $shampoo = DB::select("
                            select
                                b.prod_name
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'S'
                            and b.pet_type = :pet_type
                        ", [
                    'appointment_id' => $app->appointment_id,
                    'pet_id' => $v->pet_id,
                    'pet_type' => $v->type
                ]);

                if (!empty($shampoo)) {
                    foreach ($shampoo as $a) {
                        $data['pet'][$k]['shampoo'] .= $a->prod_name;
                    }
                }

                $addon = DB::select("
                            select
                                b.prod_name
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'A'
                            and b.pet_type = :pet_type
                        ", [
                    'appointment_id' => $app->appointment_id,
                    'pet_id' => $v->pet_id,
                    'pet_type' => $v->type
                ]);

                if (!empty($addon)) {
                    foreach ($addon as $a) {
                        $data['pet'][$k]['addon'] .= '[' . $a->prod_name . '] ';
                    }
                }
            }

            $ret = Helper::send_html_mail('groomer_assigned_for_groomer', $data);

            if (!empty($ret)) {
                $msg = 'Failed to send appointment confirmation email: ' . $ret;
                throw new \Exception($msg);
            }

            ### send SMS to groomer ###
            if (!empty($groomer->mobile_phone)) {
                $phone = $groomer->mobile_phone;

                $message = "You have a new Groomit appointment. \n\n";

                $message .= $user->first_name . ' ' . $user->last_name . "\n";
                $message .= $data['accepted_date'] . "\n";
                $message .= $data['address'] . "\n\n";
                $message .= "Appointment ID: " . $app->appointment_id . "\n";

                foreach ($data['pet'] as $p) {
                    $message .= "\n" . "Pet Name: " . $p['pet_name'] . "\n";
                    $message .= "Pet Age: " . $p['age'] . " \n";

                    if ($p['breed_name'] != '' && $p['size_name'] != '') {
                        $message .= $p['breed_name'] . " / " . $p['size_name'] . "\n";
                    }

                    $message .= "Package: " . $p['package_name'] . "\n";
                    $message .= "Shampoo: " . $p['shampoo'] . "\n";

                    if ($p['addon'] != '') {
                        $message .= "Add-ons: " . $p['addon'] . "\n";
                    }

                    if ($p['note'] != '') {
                        $message .= "Special Note: " . $p['note'] . "\n";
                    }
                }

                ## Save message ##
                $r = New Message;
                $r->send_method = 'B'; // for now both
                $r->sender_type = 'A'; // admin user
                $r->sender_id = null;//Auth::guard('admin')->user()->admin_id;
                $r->receiver_type = 'B'; // groomer
                $r->receiver_id = $app->groomer_id;
                $r->message_type = 'D';
                $r->appointment_id = $app->appointment_id;
                $r->subject = '';
                $r->message = $message;
                $r->cdate = Carbon::now();
                $r->save();

                ## send SMS ##
                $ret = Helper::send_sms($phone, $message);
                if (!empty($ret)) {
                    //throw new \Exception('Groomer SMS Error: ' . $ret);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message . '/ Appointment ID:' . $app->appointment_id);
                }

                ### send SMS to C/S ###
                $ret = Helper::send_sms_to_cs($message);
                if (!empty($ret)) {
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message . '/ Appointment ID:' . $app->appointment_id);
                }
            }

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function get_open_appointments($groomer_id) {
        $appids_obj = DB::select("
			select appointment_id, max(prod_id) prod_id
              from appointment_product
             where prod_id in (select prod_id from product where prod_type = 'P')
               and prod_id in (select prod_id from groomer_service_package where groomer_id = :groomer_id and status = 'A')
               and appointment_id in (select appointment_id from appointment_list where status = 'N')
             group by appointment_id
            ", [
            'groomer_id' => $groomer_id
        ]);

        if (empty($appids_obj) || count($appids_obj) == 0) return null;

        $app_ids_query = '';
        foreach ($appids_obj as $aid) {
            Helper::log('#### FOREACH APPT_ID ###', $aid->appointment_id);

            $app = AppointmentList::find($aid->appointment_id);

            if (!empty($app)) {

                $blocked_groomer = UserBlockedGroomer::where('user_id', $app->user_id)->where('groomer_id', $groomer_id)->first();
                if (!empty($blocked_groomer)) continue;

                $address = Address::find($app->address_id);
                if (empty($address)) continue;

                $allowed_zip = AllowedZip::where('zip', $address->zip)->first();
                $county = empty($allowed_zip) ? null : ($allowed_zip->county_name . '.' . $allowed_zip->state_abbr);

                if (!empty($county)) {
                    $service_area = GroomerServiceArea::where('groomer_id', $groomer_id)->whereRaw("lower(county) = '" . strtolower($county) . "'")->first();
                    if (empty($service_area)) continue;
                }

                ### ECO no favorite groomer ###
                $fav_groomers = null;
                if (!in_array($aid->prod_id, [28, 29])) {
                    if (empty($county)) {
                        $fav_groomers = UserFavoriteGroomer::where('user_id', $app->user_id)->get();
                    } else {
                        $fav_groomers = UserFavoriteGroomer::where('user_id', $app->user_id)->whereRaw('groomer_id in (select groomer_id from groomer_service_area where county = \'' . $county . '\' and status = \'A\')')->get();
                    }
                }

                Helper::log('#### FAV GROOMERS EXIST or NOT ###', $fav_groomers);

                if (!empty($fav_groomers) && count($fav_groomers) > 0) {
                    foreach ($fav_groomers as $f) {
                        if ($f->groomer_id == $groomer_id) {
                            $app_ids_query .= ',' . $aid->appointment_id;
                            continue;
                        }
                    }
                } else {  //When the user does not have Fav groomer, it opens to all groomers.
                    Helper::log('#### APP FAV EMPTY ###', $aid->appointment_id);
                    $app_ids_query .= ',' . $aid->appointment_id;
                }
                Helper::log('#### APP LIST as of now ###', $app_ids_query);
            }
        }

        Helper::log('#### APP LIST FINAL ###', $app_ids_query);

        if (empty($app_ids_query)) return null;

        $app_ids_query = substr($app_ids_query, 1);

        $groomer = Groomer::find($groomer_id);

        $query_minpass = '1=1';
        if ($groomer->level == 2) {
            $query_minpass = 't.min_pass >= 20';
        }else if ($groomer->level >= 3) {  //Do not show open appointments to more than Level 3.
            $query_minpass = 't.min_pass >= 35000';
        }
        $data = DB::select("
            select 
                t.appointment_id,
                t.reserved_date,
                t.reserved_time,
                t.pet_type,
                t.package_name,
                t.address,
                t.address_id,
                t.pet_qty,
                t.accepted_date,
                t.min_pass,
                t.place_id, t.place_name, t.other_name other_place_name
              from (
                select
                    a.appointment_id,
                    a.reserved_date,
                    substr(trim(a.reserved_at), -17) as reserved_time,
                    f_get_pet_type(a.appointment_id) as pet_type,
                    f_get_package_name(a.appointment_id) as package_name,
                    concat(c.city, ',', c.zip) as address,
                    a.address_id,
                    (select count(*) from appointment_pet where appointment_id = a.appointment_id) as pet_qty,
                    a.accepted_date,
                    a.cdate,
                    TIMESTAMPDIFF(MINUTE,a.cdate, :now1) as min_pass,
                    e.place_id,  e.place_name,d.other_name
                from appointment_list a 
                left join address c on a.address_id = c.address_id
                left join appointment_place d on a.appointment_id = d.appointment_id
                left join place e on d.place_id = e.place_id
                where a.status = 'N'
                  and a.appointment_id in (" . $app_ids_query . ")
                  and a.reserved_date >= :now2 - interval 1800 minute
              ) t
            where " . $query_minpass . "
            order by 2 asc
            ", [
            'now1' => Carbon::now(),
            'now2' => Carbon::now()
        ]);


        if (count($data) > 0) {
            foreach ($data as $o) {
                $address = Address::find($o->address_id);
                $o->address_info = $address;

                //$o->earning = ProfitSharingProcessor::getProfit($o->appointment_id);
                $o->estimated_earning = ProfitSharingProcessor::getEstimatedProfit($o->appointment_id);
                $o->estimated_bonus = ProfitSharingProcessor::getEstimatedBonus($o->appointment_id);

                $o->pet_list = DB::select("
                    select type, breed_name, package_name , size
                      from vw_appointment_pet 
                     where appointment_id = :appointment_id
                     ", [
                    'appointment_id' => $o->appointment_id
                ]);

                if ($o->pet_qty > 1) {
                    $o->package_name = 'Multiple Package';
                }
            }
        }

        return $data;
    }

    public static function get_open_appointments2($groomer_id, $distance_type='', $x=0, $y=0 ) {
        $data = DB::select("
                select
                   distinct a.appointment_id,
                    a.reserved_date,
                    substr(trim(a.reserved_at), -17) as reserved_time,
                    f_get_pet_type(a.appointment_id) as pet_type,
                    f_get_package_name(a.appointment_id) as package_name,
                    concat(c.city, ',', c.zip) as address,
                    a.address_id,
                    (select count(*) from appointment_pet where appointment_id = a.appointment_id) as pet_qty,
                    a.accepted_date,
                    TIMESTAMPDIFF(MINUTE,a.cdate, :now1) as min_pass,
                    e.place_id,  e.place_name,d.other_name as other_place_name
                from appointment_list a  
                inner join groomer_opens z on z.appt_id = a.appointment_id  and z.groomer_id = :groomer_id and z.removed != 'Y'
                left join address c on a.address_id = c.address_id and c.status = 'A'
                left join appointment_place d on a.appointment_id = d.appointment_id
                left join place e on d.place_id = e.place_id
                where a.status = 'N'
            order by 2 asc
            ", [
            'now1' => Carbon::now(),
            'groomer_id' => $groomer_id
        ]);


        if (count($data) > 0) {
            foreach ($data as $o) {
                $address = Address::find($o->address_id);
                $o->address_info = $address;

                //$o->earning = ProfitSharingProcessor::getProfit($o->appointment_id);
                $o->estimated_earning = ProfitSharingProcessor::getEstimatedProfit($o->appointment_id);
                $o->estimated_bonus = ProfitSharingProcessor::getEstimatedBonus($o->appointment_id);

                $o->pet_list = DB::select("
                    select type, breed_name, package_name , size
                      from vw_appointment_pet 
                     where appointment_id = :appointment_id
                     ", [
                    'appointment_id' => $o->appointment_id
                ]);

                if ($o->pet_qty > 1) {
                    $o->package_name = 'Multiple Package';
                }

                $o->distance = 'N/A';
                $o->distance_next = 'N/A';
                if( in_array($distance_type, ['C','H','P'] ) ){
                    //Get Distance in miles
                    $dist = Helper::get_distance_to_groomer( $groomer_id, $o->appointment_id, $distance_type, $x, $y );
                    $dist_next = Helper::get_distance_to_groomer( $groomer_id, $o->appointment_id, 'N', $x, $y ); //Distance to next.

                    $o->distance = ($dist == 'N/A') ? $dist : $dist . ' miles';
                    $o->distance_next = ($dist_next == 'N/A') ? $dist_next : $dist_next . ' miles';
                }
            }
        }

        return $data;
    }
    public static function update_pet_groom_hour($appointment_id) {
        try{
            DB::update("
                update appointment_pet appp
                  join appointment_product apppd on appp.appointment_id = apppd.appointment_id and appp.pet_id = apppd.pet_id
                  join product pd on apppd.prod_id = pd.prod_id and pd.prod_type = 'P' 
                  join pet p on appp.pet_id = p.pet_id
                  join pet_groom_hour pgh on p.type = pgh.pet_type and apppd.prod_id = pgh.prod_id 
                  set appp.hour = pgh.hour
                 where (appp.size_id is null or appp.size_id = pgh.size_id)
                   and appp.appointment_id = :appointment_id
                ", [
                'appointment_id' => $appointment_id
            ]);
        } catch (Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            Helper::send_mail('it@jjonbp.com', '[groomit][' . getenv('APP_ENV') . '] PET GROOM HOUR SETTING ERROR', $msg . '/ Appointment ID:' . $appointment_id);
        }
    }

    public static function send_notification_to_user_for_groomer_confirm($ap, $user, $groomer, $address, $pets) {
        try {

            ## send email to user ##

            $subject = "Your Groomit Appointment has been confirmed";

            $data = [];
            $data['email'] = $user->email;
            $data['name'] = $user->first_name;
            $data['subject'] = $subject;
            $data['groomer'] = $groomer->first_name . ' ' . $groomer->last_name;
            $data['address'] = $address->address1 . ' ' . $address->address2 . ', ' . $address->city . ', ' . $address->state . ' ' . $address->zip;
//            $data['referral_code'] = $user->referral_code;

            foreach ($pets as $k=>$v) {
                $data['pet'][$k]['pet_name'] = $v->pet_name;
                $data['pet'][$k]['package_name'] = $v->package_name;
                $data['pet'][$k]['breed_name'] = empty($v->breed) ? '' : Breed::findOrFail($v->breed)->breed_name;
                $data['pet'][$k]['size_name'] = empty($v->size) ? '' : Size::findOrFail($v->size)->size_name;

                if (!empty($v->pet_age)) {
                    $data['pet'][$k]['age'] = ($v->pet_age > 0 ? $v->pet_age . ' year' : ''). ($v->pet_age > 1 ? 's old' : ' old');
                } else {
                    $dob = Carbon::parse($v->pet_dob);
                    $data['pet'][$k]['age'] = $dob->age < 2 ? $dob->diffInMonths(Carbon::now()) . " months old" : $dob->age . " years old";
                }

                $data['pet'][$k]['note'] = $v->note;
                $data['pet'][$k]['shampoo'] = '';
                $data['pet'][$k]['addon'] = '';

                $shampoo = DB::select("
                            select
                                b.prod_name
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'S'
                            and b.pet_type = :pet_type
                        ", [
                    'appointment_id' => $ap->appointment_id,
                    'pet_id' => $v->pet_id,
                    'pet_type' => $v->type
                ]);

                if (!empty($shampoo)) {
                    foreach ($shampoo as $a) {
                        $data['pet'][$k]['shampoo'] .= $a->prod_name;
                    }
                }

                $addon = DB::select("
                            select
                                b.prod_name
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'A'
                            and b.pet_type = :pet_type
                        ", [
                    'appointment_id' => $ap->appointment_id,
                    'pet_id' => $v->pet_id,
                    'pet_type' => $v->type
                ]);

                if (!empty($addon)) {
                    foreach ($addon as $a) {
                        $data['pet'][$k]['addon'] .= '[' . $a->prod_name . '] ';
                    }
                }
            }

            $data['accepted_date'] = $ap->accepted_date->format('l, F j Y, h:i A');

            $referral_arr = UserProcessor::get_referral_code($user->user_id);
            $data['referral_code'] = $referral_arr['referral_code'];
            $data['referral_amount'] = $referral_arr['referral_amount'];

            $ret = Helper::send_html_mail('appointment_confirmation', $data);

            if (!empty($ret)) {
                $msg = 'Failed to send appointment confirmation email: ' . $ret;
                throw new \Exception($msg);
            }

            ## end send email to user ##



            ### send SMS to end user ###
            $message = Constants::$message_app['Confirmation'];
            if (Helper::is_favorite_groomer($user->user_id, $groomer->groomer_id)) {
                $message = str_replace('GROOMER_NAME', 'your favorite groomer ' . $data['groomer'], $message);
            } else {
                $message = str_replace('GROOMER_NAME', $data['groomer'], $message);
            }
            $message = str_replace('DATETIME', $data['accepted_date'], $message);
            $message = str_replace('ADDRESS', $data['address'], $message);

            ## Save message ##
            $r = New Message;
            $r->send_method = 'B'; // for now both
            $r->sender_type = 'A'; // admin user
            $r->sender_id = $groomer->groomer_id;
            $r->receiver_type = 'A'; // end user
            $r->receiver_id = $ap->user_id;
            $r->message_type = 'D';
            $r->appointment_id = $ap->appointment_id;
            $r->subject = '';
            $r->message = $message;
            $r->cdate = Carbon::now();
            $r->save();

            ## send SMS ##
            if (!empty($user->phone)) {
                $phone = $user->phone;
                $ret = Helper::send_sms($phone, $message);
                if (!empty($ret)) {
                    //throw new \Exception('End-User SMS Error: ' . $ret);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message . '/ Appointment ID:' . $ap->appointment_id);
                }
            }
            ### end send SMS to end user ###


            ### send push ###
            if (!empty($user->device_token)) {
                /*
                $payload = [
                    'type' => 'G',
                    'id' => $ap->groomer_id
                ];
                */
                $payload = [
                    'type' => 'A',
                    'id' => $ap->appointment_id
                ];

                $error = Helper::send_notification('groomit', $r->message, $user->device_token, $r->subject, $payload);
                if (!empty($error)) {
                    //throw new \Exception('Push Notfication Error: ' . $error);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Push Notfication Error:' . $error, 'Appointment ID:' . $ap->appointment_id);
                }
            } else {
                //throw new \Exception('Push Notfication Error: No device token found');
                Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Push Notfication Error: No device token found', 'Appointment ID:' . $ap->appointment_id);
            }

            ### end send push
        } catch (Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            Helper::send_mail('it@jjonbp.com', '[groomit][' . getenv('APP_ENV') . '] PET GROOM HOUR SETTING ERROR', $msg . '/ Appointment ID:' . $ap->appointment_id);
        }
    }

    public static function cancel_appointment_with_fee($ap, $charge_amt, $groomer_commission_amt, $tax_amt=0) {

        try {
            $payment = UserBilling::find($ap->payment_id);
            if (empty($payment)) {
                Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Failed to charge cancellation fee.', ' - id: ' . $ap->appointment_id . '<br/> - error_msg : No billing information.');

                return response()->json([
                    'msg' => 'Failed to charge cancellation fee. No billing information.'
                ]);
            }

            $address = Address::find($ap->address_id);

            if (empty($address)) {
                Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Failed to charge cancellation fee.', ' - id: ' . $ap->appointment_id . '<br/> - error_msg : No address information.');

                return response()->json([
                    'msg' => 'Failed to charge cancellation fee. No address information.'
                ]);
            }

            $ap->promo_code = 'CANCELWITHFEE';

            $ap->credit_amt = 0;
            $ap->new_credit = 0;
            $ap->safety_insurance = 0;
            $ap->sameday_booking = 0;
            $ap->fav_groomer_fee = 0;

            $ap->promo_amt  = $ap->sub_total - $charge_amt;
            $ap->tax = $tax_amt;
            $ap->total = $charge_amt + $tax_amt;

            $ret = Converge::sales($payment->card_token, $ap->total, $ap->appointment_id, 'W');
            if (!empty($ret['error_msg'])) {
                Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Failed to charge cancellation fee. Credit card processing failed', ' - id: ' . $ap->appointment_id . '<br/> - error_msg : ' . $ret['error_msg']);
                Helper::send_mail('help@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Failed to charge cancellation fee. Credit card processing failed', ' - id: ' . $ap->appointment_id . '<br/> - error_msg : ' . $ret['error_msg']);

                return 'Failed to charge cancellation fee. Credit card processing failed. ' . $ret['error_msg'];
            }

            $ap->save();

            ProfitSharingProcessor::create_cancel_fee($ap, $charge_amt , $groomer_commission_amt, $tax_amt );

            return '';
        } catch (Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            Helper::send_mail('it@jjonbp.com', '[groomit][' . getenv('APP_ENV') . '] PET GROOM HOUR SETTING ERROR', $msg . '/ Appointment ID:' . $ap->appointment_id);

            return $msg;
        }
    }

    public static function reschedule_appointment_with_fee($ap, $charge_amt, $groomer_commission_amt, $tax_amt=0) {

        try {
            $payment = UserBilling::find($ap->payment_id);
            if (empty($payment)) {
                Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Failed to charge reschedule fee.', ' - id: ' . $ap->appointment_id . '<br/> - error_msg : No billing information.');

                return response()->json([
                    'msg' => 'Failed to charge reschedule fee. No billing information.'
                ]);
            }

            $address = Address::find($ap->address_id);

            if (empty($address)) {
                Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Failed to charge reschedule fee.', ' - id: ' . $ap->appointment_id . '<br/> - error_msg : No address information.');

                return response()->json([
                    'msg' => 'Failed to reschedule cancellation fee. No address information.'
                ]);
            }

//No, must not update existing appointment_list
//            $ap->promo_code = 'RESCHEDULEWITHFEE';
//            $ap->credit_amt = 0;
//            $ap->new_credit = 0;
//            $ap->safety_insurance = 0;
//            $ap->sameday_booking = 0;
//
//            $ap->promo_amt  = $ap->sub_total - $charge_amt;
//            $ap->tax = $tax_amt;
//            $ap->total = $charge_amt + $tax_amt;

            $ret = Converge::sales($payment->card_token, $charge_amt + $tax_amt, $ap->appointment_id, 'R');
            if (!empty($ret['error_msg'])) {
                Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Failed to charge rescheduling fee. Credit card processing failed', ' - id: ' . $ap->appointment_id . '<br/> - error_msg : ' . $ret['error_msg']);
                Helper::send_mail('help@groomit.me', '[GroomIt][' . getenv('APP_ENV') .'] Failed to charge rescheduling fee. Credit card processing failed', ' - id: ' . $ap->appointment_id . '<br/> - error_msg : ' . $ret['error_msg']);

                return 'Failed to charge rescheduling fee. Credit card processing failed. ' . $ret['error_msg'];
            }

//            $ap->save();

            //Use the same one for Cancel & Reschedule. : generate profit_share tx.
            ProfitSharingProcessor::create_cancel_fee($ap, $charge_amt , $groomer_commission_amt, $tax_amt );

            return '';
        } catch (Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            Helper::send_mail('jun@jjonbp.com', '[groomit][' . getenv('APP_ENV') . '] PET GROOM HOUR SETTING ERROR', $msg . '/ Appointment ID:' . $ap->appointment_id);

            return $msg;
        }
    }

    //Called by Desktop/CA both.
    public static function cancel($app, $user, $note = null) {

        if ($app->accepted_date) {
            $app_date = $app->accepted_date;
        } else {
            $app_date = $app->reserved_date;
        }

        $package = AppointmentProduct::where('appointment_id', $app->appointment_id)->first();
        if (in_array($package->prod_id, [28, 29])) {
            return [
                'msg' => 'ECO Package cannot be cancelled. Please contact Customer Center.'
            ];
        }

        //Cancel fee, if it's after 6pm.
        $cancel_ret = AppointmentProcessor::get_fee_amount('C', $app) ;

        if( is_array($cancel_ret) ) {
            if(  $cancel_ret['msg'] == '') {
                if(  $cancel_ret['fee_amount']> 0 ) {
                    $estimated_earning = ProfitSharingProcessor::getEstimatedProfit($app->appointment_id); //Estimated profit, based on SubTotal only, excluding Sameday/Bonus/FavGroomerFee.
                    $groomer_commission_amt = round($estimated_earning *  $cancel_ret['fee_rates'], 2) ;
                    $msg = AppointmentProcessor::cancel_appointment_with_fee($app, $cancel_ret['fee_amount'] - $cancel_ret['tax_amount'],
                        $groomer_commission_amt, $cancel_ret['tax_amount']);
                    if (!empty($msg)) {
                        return [
                            'msg' => $msg
                        ];
                    }
                } //else, no cancelling fee, so go proceed.

            }else {
                return [
                    'msg' => 'Error:' . $cancel_ret['msg']
                ];
            }
        }else {
            return [
                'msg' => 'Fail to get Cancelling Fee'
            ];
        }

//        else {
//            $app_date = Carbon::parse($app_date) ;
//            $prev_date = Carbon::createFromFormat('Y-m-d H:i:s', $app_date->addDays(-1)->format('Y-m-d') . ' 18:00:00') ;
//            if (Carbon::now() > $prev_date ) {
//                if( $app->status != 'N') {
//                    return [
//                        'msg' => 'You cannot cancel an appointment after 6 PM a day before. Please contact Customer Center.'
//                    ];
//                }

            //if ($app_date < Carbon::now()->addHours(4)) {
            //    if( $app->status != 'N') {
            //        return [
            //            'msg' => 'You cannot be cancelled within 4 hours. Please contact Customer Center.'
            //        ];
            //    }//Allow cancel withing 4 hours, if Groomer is not assigned yet.
//            }
//        }



        ### Recycle Promo code
        if (!empty($app->promo_code)) {
            PromoCodeProcessor::recycle($app->promo_code);
        }

        $msg = CreditProcessor::cancelCreditUsage($app->appointment_id);
        if (!empty($msg)) {
            return [
                'msg' => $msg
            ];
        }

        # Deactivate credit of canceled appointment
        $credit = Credit::where('user_id', $app->user_id)
            ->where('appointment_id', $app->appointment_id)
            ->first();
        if (!empty($credit)) {
            $credit->status = 'C';
            $credit->save();
        }

        ### void auth only if there's any ###
        $all_auth_only_trans = DB::select("
                select a.id, a.appointment_id, a.type, a.category, a.token, a.amt - IfNull(b.amt,0) as amt, a.cdate, a.void_ref, b.orig_sales_id 
                from cc_trans a left join cc_trans b on a.id = b.orig_sales_id and b.appointment_id = :appointment_id1 and b.type ='V' and b.result = 0 and b.error_name = 'Partial Void' 
                where a.appointment_id = :appointment_id2
                and a.type in ('A','S')
                and a.category = 'S'
                and a.result = 0
                and a.void_date is null 
            ", [
            'appointment_id1' => $app->appointment_id,
            'appointment_id2' => $app->appointment_id
            ]);

        foreach($all_auth_only_trans as $auth_only_trans) {
            if( !empty($auth_only_trans->orig_sales_id) && $auth_only_trans->orig_sales_id > 0  ){
                $ret = Converge::void($app->appointment_id, 'S', $auth_only_trans->void_ref, $auth_only_trans->type,  $auth_only_trans->amt ); //Partial voids, if there existed 'Partial Void
            }else {
                $ret = Converge::void($app->appointment_id, 'S', $auth_only_trans->void_ref, $auth_only_trans->type ); //Full voids, not partial voids.
            }

            if (!empty($ret['error_msg'])) {
                Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void CC trans when cancelling appointment : ' . $app->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
            }
        }

        $app->status = 'C';
        $app->note = !empty($note) ? $note : '';
        $app->mdate = Carbon::now();
        $app->modified_by = $user->name . '(' . $user->user_id . ')';
        $app->update();


        $send_msg = Helper::send_appointment_msg($app);
        if (!empty($send_msg)) {
            return [
                'msg' => $send_msg
            ];
        }


        return [
            'msg' => ''
        ];
    }

    //Rescheduling fee or Cancelling fee
    public static function get_fee_amount( $fee_type, $app ) {

        if ($app->accepted_date) {
            $app_date = $app->accepted_date;
        } else {
            $app_date = $app->reserved_date;
        }

        $fee_amount = 0;
        $fee_rates = 0;
        $tax_amount = 0;

        $app_date = Carbon::parse($app_date) ;
        $prev_date = Carbon::createFromFormat('Y-m-d H:i:s', $app_date->addDays(-1)->format('Y-m-d') . ' 18:00:00') ;
        if (Carbon::now() > $prev_date ) {
            if ($app->status != 'N') {
                if($fee_type == 'R') {          //Rescheduling Fee
                    $fee_rates = round(25/$app->sub_total,2);
                    $fee_amount = round(25.00, 2) ; //$25, not 25%
                } else if ( $fee_type == 'C') { //Cancel Rates
                    $fee_rates = 0.5;
                    $fee_amount = round(($app->sub_total ) * $fee_rates, 2) ;
                }else {
                    return [
                        'msg' => 'Wrong Fee Type'
                    ];
                }
                //$fee_amount = round(($app->sub_total ) * $fee_rates, 2) ;
                //$fee_amount = round(($app->sub_total - $app->promo_amt + $app->sameday_booking) * $fee_rates, 2) ;

                $address = Address::find($app->address_id);
                if (empty($address)) {
                    return [
                        'msg' => 'No Address information'
                    ];
                }
                $tax_amount = AppointmentProcessor::get_tax($address->zip, $app->sub_total*$fee_rates, 0,
                    0, 0,0 );
                //Get Tax w/ $0 insurance, and no credits redeemed, because it'll be recovered even if it exist.
//                $tax = AppointmentProcessor::get_tax($address->zip, $app->sub_total*$fee_rates, 0,
//                    $app->promo_amt*$cancel_rates, 0,
//                    $app->sameday_booking*$cancel_rates );
                $fee_amount = $fee_amount + $tax_amount;
            }
        }

        Helper::log('### get_fee_amount ###', [
            'fee_amount' => $fee_amount,
            'fee_rates' => $fee_rates,
            'tax_amount' => $tax_amount
        ]);
        return [
            'msg' => '',
            'fee_amount' => $fee_amount,
            'fee_rates' => $fee_rates,
            'tax_amount' => $tax_amount
        ];
    }
    //Seems not to be used by End Users.
    public static function update($app, $user) {

        if ($app->accepted_date) {
            $app_date = $app->accepted_date;
        } else {
            $app_date = $app->reserved_date;
        }

        if ($app_date < Carbon::now()->addHours(12) && !empty($app->groomer_id)) {
            return [
                'msg' => 'Appointment can be cancelled only within 12 hours of reserved date'
            ];
        }

        $app->status = 'C';
        $app->mdate = Carbon::now();
        $app->modified_by = $user->name . '(' . $user->user_id . ')';
        $app->update();


        ### void auth only if there's any ###
//        $auth_only_trans = CCTrans::where('appointment_id', $app->appointment_id)
//            ->where('type', 'A')
//            ->where('category', 'S')
//            ->where('result', 0)
//            ->whereNull('void_date')
//            ->where('amt', '!=', 0.01)
//            ->first();
//
//        if (!empty($auth_only_trans)) {
//            $ret = Converge::void($app->appointment_id, 'S', $auth_only_trans->void_ref, 'A');
//            if (!empty($ret['error_msg'])) {
//                Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void auth only CC trans when cancelling appointment : ' . $app->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
//            }
//        }

        ### Recycle Promo code
        if (!empty($app->promo_code)) {
            PromoCodeProcessor::recycle($app->promo_code);
        }

        $msg = CreditProcessor::cancelCreditUsage($app->appointment_id);
        if (!empty($msg)) {
            return [
                'msg' => $msg
            ];
        }

        # Deactivate credit of canceled appointment
        $credit = Credit::where('user_id', $app->user_id)
            ->where('appointment_id', $app->appointment_id)
            ->first();
        if (!empty($credit)) {
            $credit->status = 'C';
            $credit->save();
        }

        $send_msg = Helper::send_appointment_msg($app);
        if (!empty($send_msg)) {
            return [
                'msg' => $send_msg
            ];
        }

        return [
            'msg' => ''
        ];
    }

    //called from  apply_promo from Admin : This is to apply new promo code, not reset it.
    public static function apply_promo_code($ap, $promo) {

        try {
            if (empty($promo)) {
                if (empty($ap->promo_code)) {
                    return [
                        'msg' =>  'Please enter valid promo code.'
                    ];
                }
                $promo = PromoCode::whereRaw("code = '" . strtoupper($ap->promo_code) . "'")->first();
            }

            $old_total = $ap->total;

            $address = Address::findOrFail($ap->address_id);
            if (empty($address)) {
                return [
                    'msg' =>  'Wrong address!'
                ];
            }


            $pets = [];

            $pet_data =  DB::select("
                        select a.pet_id, case  b.prod_type when 'P' then 1 when 'A' then 2 when 'S' then 3 else 4 end  prod_sort,  b.prod_type, a.prod_id, a.amt as denom, b.prod_name
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        order by 1,2, 4
                    ", [
                'appointment_id' => $ap->appointment_id
            ]);

            if (!empty($pet_data)) {
                $prev_pet_id = 0;
                $prev_prod_type = 0;
                $info = null;

                foreach ($pet_data as $pet) {
                    if( $pet->pet_id != $prev_pet_id) {
                        if($prev_pet_id != 0 ){
                            $new_pet  = new \stdClass();
                            $new_pet->info = $info;
                            $pets[] = $new_pet ;
                        }

                        if( $pet->prod_type == 'P' ){ //Need it only when Packages.
                            $info =new \stdClass();
                        }
                    }
                    if ($pet->prod_type == 'P' ) {
                        $obj = new \stdClass();
                        $obj->prod_id = $pet->prod_id;
                        $obj->prod_name = $pet->prod_name;
                        $obj->denom =  $pet->denom;

                        $info->package = $obj;
                    }else if ($pet->prod_type == 'S' ){
                        $obj = new \stdClass();
                        $obj->prod_id = $pet->prod_id;
                        $obj->prod_name = $pet->prod_name;
                        $obj->denom =  $pet->denom;

                        $info->shampoo = $obj;

                    }else if ($pet->prod_type == 'A' ){
                        $obj = new \stdClass();
                        $obj->prod_id = $pet->prod_id;
                        $obj->prod_name = $pet->prod_name;
                        $obj->denom =  $pet->denom;

                        $info->add_ons[] = $obj; //In case of Add-on, it could be multiples.
                    }

                    $prev_pet_id = $pet->pet_id;
                    $prev_prod_type = $pet->prod_type;
                }

                $new_pet  = new \stdClass();
                $new_pet->info = $info;
                $pets[] = $new_pet ;
            }

//            Helper::log('### pet info ###', [
//                'pets' => $pets
//            ]);

            ### SAMEDAY BOOKING ###
            $sameday_booking = 0;
            //$today = Carbon::now()->format('Y-m-d');
            $app_cdate = Carbon::parse($ap->cdate)->format('Y-m-d'); //it should compare cdate & reserv_date, not today.
            $service_req_date = Carbon::parse($ap->reserved_date)->format('Y-m-d');
            if( $service_req_date == $app_cdate) {
                $sameday_booking = env('SAMEDAY_BOOKING');
                if( !empty($promo) && !empty($promo->type) && ($promo->type == 'S') ) { //In case of Membership, no sameday_booking
                    $sameday_booking = 0;
                }
            }

            $fav_fee = $ap->fav_groomer_fee;

            $use_credit = 'N';
            if($ap->credit_amt > 0) { //Once used it before, regard the customer wanted to use credit.
                $use_credit = 'Y';
            }

            $ret = ScheduleProcessor::get_total_price($pets, $promo, $address->zip, $use_credit, $ap->credit_amt, null, $sameday_booking, $fav_fee );
//            return [
//                'sub_total' => $sub_total,
//                'safety_insurance' => $safety_insurance,
//                'sameday_booking' => $sameday_booking,
//                'tax' => $tax,
//                'promo_amt' => $promo_amt,
//                'discount_applied' => $promo_amt - $new_credit,
//                'credit_amt' => $credit_amt,
//                'total' => $total,
//                'new_credit' => $new_credit, //new_credit is generated with Voucher amount, when bigger than  appt amount.
//                'use_credit' => $use_credit,
//                'available_credit' => $available_credit
//            ];

            $ap->sub_total = $ret['sub_total'];
            $ap->promo_code = empty($promo) ? "" : strtoupper($promo->code);
            $ap->promo_amt = $ret['promo_amt'];
            $ap->credit_amt = $ret['credit_amt'];
            $ap->new_credit = $ret['new_credit'];
            $ap->safety_insurance = $ret['safety_insurance'];
            $ap->sameday_booking = $ret['sameday_booking'];
            $ap->fav_groomer_fee = $ret['fav_fee'];
            $ap->tax = $ret['tax'];
            $ap->total = $ret['total'];


            if (Auth::check()) {
                $u = Auth::guard('admin')->user();
                if (!empty($u)) {
                    $ap->mdate = Carbon::now();
                    $ap->modified_by = $u->name . '(' . $u->admin_id . ')';
                }
            }
            $ap->save();

            ### void auth only if total changed ###
            if ($ap->status != 'P' && $old_total != $ap->total ) {
                // New amount should be appliend at repayments or completion.

                //How about comment this out, so the final would be done at last stage ?
                // => No, because worry about just complete with existing auth_tx_id, not checking price difference.
                // => If check price difference, yes, we could comment it out.
                ### void auth only if there's any ###
//                $auth_only_trans = CCTrans::where('appointment_id', $ap->appointment_id)
//                    ->where('type', 'A')
//                    ->where('category', 'S')
//                    ->where('result', 0)
//                    ->whereNull('void_date')
//                    ->where('amt', '!=', 0.01)
//                    ->orderBy('cdate','asc')
//                    ->first();
//
//                if (!empty($auth_only_trans)) {
//                    $ret = Converge::void($ap->appointment_id, 'S', $auth_only_trans->void_ref, 'A');
//                    if (!empty($ret['error_msg'])) {
//                        Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void auth only CC trans when cancelling appointment : ' . $ap->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
//                        /*return response()->json([
//                            'msg' => 'Failed to void auth only credit card transaction'
//                        ]);*/
//                    }
//                }
            }

            ### remove new credit record first ###
            $credit = Credit::where('appointment_id', $ap->appointment_id)
                ->where('user_id', $ap->user_id)
                ->where('type', 'C')
                ->where('category', 'T')
                ->where('status', 'A')
                ->first();

            if (!empty($credit)) {
                $credit->delete();
            }

            ### now give new credit if it's required ###
            if ($ap->new_credit > 0) {
                $c = new Credit;
                $c->user_id = $ap->user_id; // code owner
                $c->type = 'C';
                $c->category = 'T';
                $c->amt = $ap->new_credit;
                $c->referral_code = '';
                $c->appointment_id = $ap->appointment_id;
                $c->expire_date = Carbon::today()->addDays(365);
                $c->status = 'A';
                $c->cdate = Carbon::now();
                $c->save();
            }

            if ($ap->status == 'P') {
                ### share profit ###
                $msg = ProfitSharingProcessor::shareProfit($ap);
                if (!empty($msg)) {
                    Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $ap->appointment_id, $msg);
                }

                ### give 03/2018 promo ###
//                $msg = CreditProcessor::give_march_2018_promo($ap);
//                if (!empty($msg)) {
//                    Helper::send_mail('tech@groomit.me', '[GroomIt][' . getenv('APP_ENV') . '] Appointment Processor Error: ' . $app->appointment_id, $msg);
//                }
            }

//            Helper::log('### END of apply_promo ###', [
//                'promo' => $promo
//            ]);

            return [
                'msg' => ''
            ];

        } catch (\Exception $ex) {
            return [
                'msg' =>  $ex->getMessage() . ' [' . $ex->getCode() . ']' . ' [' . $ex->getFile() . ']' . ' [' . $ex->getLine() . ']'
            ];
        }
    }

    public static function is_first_appointment($app) {
        if (!empty($app)) {
            $first_app = AppointmentList::where('user_id', $app->user_id)->where('status', 'P')->orderBy('cdate', 'asc')->first();

            if (empty($first_app) ||
                ($first_app->appointment_id == $app->appointment_id)
               ) {
                return true;
            }
        }

        return false;
    }

    public static function cancel_by_user($app) {

        $user = Auth::guard('user')->user();
        if (empty($user)) {
            return [
                'msg' => 'Session expired. Please login again!'
            ];
        }

        if ($app->accepted_date) {
            $app_date = $app->accepted_date;
        } else {
            $app_date = $app->reserved_date;
        }

        $package = AppointmentProduct::where('appointment_id', $app->appointment_id)->first();

        if (in_array($package->prod_id, [28, 29])) {
            return [
                'msg' => 'ECO Package cannot be cancelled. Please contact Customer Center.'
            ];
        } else {
            if ($app_date < Carbon::now()->addHours(4)) {
                return [
                    'msg' => 'Appointment can be cancelled only within 4 hour of reserved date'
                ];
            }
        }

        $app->status = 'C';
        $app->mdate = Carbon::now();
        $app->modified_by = $user->name . '(' . $user->user_id . ')';
        $app->update();

        if (!in_array($package->prod_id, [28, 29])) {
            ### void auth only if there's any ###
            $all_auth_only_trans = CCTrans::where('appointment_id', $app->appointment_id)
                ->whereIn('type', ['A', 'S'])
                ->where('category', 'S')
                ->where('result', 0)
                ->whereNull('void_date')
                ->get();

            foreach($all_auth_only_trans as $auth_only_trans) {
                $ret = Converge::void($app->appointment_id, 'S', $auth_only_trans->void_ref, $auth_only_trans->type); //Full voids, not partial voids.
                if (!empty($ret['error_msg'])) {
                    Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void auth only CC trans when cancelling appointment : ' . $app->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
                }
            }

            ### Recycle Promo code
            if (!empty($app->promo_code)) {
                PromoCodeProcessor::recycle($app->promo_code);
            }

            $msg = CreditProcessor::cancelCreditUsage($app->appointment_id);
            if (!empty($msg)) {
                return ['msg' => $msg];
            }

            # Deactivate credit of canceled appointment
            $credit = Credit::where('user_id', $app->user_id)->where('appointment_id', $app->appointment_id)->first();
            if (!empty($credit)) {
                $credit->status = 'C';
                $credit->save();
            }
        }

        $send_msg = Helper::send_appointment_msg($app);
        if (!empty($send_msg)) {
            return [
                'msg' => $send_msg
            ];
        }

        return [
            'msg' => ''
        ];
    }

    //Send notification, based on previous notification status, not based on Level.
    public static function send_groomer_notification2(AppointmentList $app) {
        try {

            $max_stage = 0;
            $msg = '';
            $county = '';
            $breed_ids = '' ; //Breeds to check blocked breeds
            $prod_ids = ''  ; //Prod_id to check available packages for a groomer

            $available_groomers = null;
            $found_groomers = false;

            $package = '';
            $zip = '';
            $city = '';

            $groomer_opens = DB::select("
                select stage, county, prod_ids, breed_ids, msg
                from groomer_opens
                where appt_id = :appt_id
                order by stage desc 
                limit 0,1 ",
                [
                    'appt_id' => $app->appointment_id
                ]);


            //max_stage :
            // 0  => First time notification

            // 10 => When the user has Fav. groomers
            // 13 => Exclusive area of customers(New market area with limited area ) => Not used at all, so can ignore it.
            // 21 Influencer
            // 16 => Over $200 orders group.   => 2x after 3 minutes
            // 15 => AutoAssign Groomer group. => 2x after 3 minutes
            // 2x => When Customer Type of Groomer exists :
            //   21 => Level 1 of Influencer customers , 26 : Level 2 of Influence customers. Do not use 221, because 21/26will be used step by step. will not go w/ 30, because 26/27/28 will be the last stage.
            //   22 => 1st time customers only         , 27 : Level 2 of 1st time customers
            //   23 => Repeated customers only         , 28 : Level 2 of Repeated customers only
            //   29 => Dummy stage after Needs new notifications , by unassign a groomer, change request date/time, etc. Add it, after 'removed' previous ones.
            // 30 => Level 1
            // 40 => Level 2
            // 50 => Level 3

            //Exceptional cases :
            // 700 => When appointment_list.prefer_groomer_id exist, when appt is delayed by a customer(Not by CS), after a groomer is already assigned.
            //       Provide 12 minutes preference to the groomer.
            // 1000 => No groomers are notified at all, because none are qualified.

            if ( empty($groomer_opens) || (count($groomer_opens) == 0))  {
                //First time notification
                $max_stage = 0;

            } else {
                $groomer_opens2 = DB::select("
                select stage, county, prod_ids, breed_ids, msg
                from groomer_opens
                where appt_id = :appt_id
                and removed != 'Y'
                order by stage desc 
                limit 0,1 ",
                    [
                        'appt_id' => $app->appointment_id
                    ]);
                if ( empty($groomer_opens2) || (count($groomer_opens2) == 0))  {
                     //If CS use 'Re-Send Notification', it's already 'remvoed=Y ' for all old notification, so will come here.
                    //$groomer_opens = $groomer_opens[0];

                    $max_stage = 29; //Ignore all old notifications. send level 1 , from the beginning.

                }else {
                    $groomer_opens = $groomer_opens2[0];    //Get groomers after 'Excluding 'removed''.
                    $max_stage = $groomer_opens->stage ;
                    $msg = $groomer_opens->msg ;
                    $county = $groomer_opens->county ;
                    $prod_ids = $groomer_opens->prod_ids ;
                    $breed_ids = $groomer_opens->breed_ids ;
                }


                if( !in_array($max_stage, [ 10, 13,  15,16, 21,22,23,  26,27,28,  29, 30,40,50,   700, 1000] ) ) {
                    Helper::send_mail('jun@jjonbp.com', '[groomit][' . getenv('APP_ENV') . '] Invalid stage value in send_groomer_notification2:' . $max_stage, $msg );
                }

                if( !empty($app->prefer_groomer_id) && ($app->prefer_groomer_id > 0) ) {
                    //In this case, need to send prefer_groomer_id only, for 12 minutes,
                    // even when when it's previous status is 10/15/16/21/22/23,
                    // because the app was delayed by a customer.
                }else {
                    if (in_array($max_stage, [10, 13, 26, 27, 28 ])) {
                        //Do not notify next stage. just stop.  But, need to go next in case of 21,22,23 to execute 26,27,28 each.
                        return;
                    } else if ($max_stage == 1000) {
                        //Need to send CS.
                        Helper::send_mail('jun@jjonbp.com', '[groomit][' . getenv('APP_ENV') . '] No groomer found in send_groomer_notification2:' . $max_stage, $msg );
                        return;
                    }
                }
            }

            $user = User::leftjoin('user_stat', 'user_stat.user_id', '=', 'user.user_id')
                    ->where('user.user_id', $app->user_id)
                    ->get( [ 'user.user_id' ,
                             'user.phone' ,
                             'user.first_name' ,
                             'user.last_name' ,
                             'user.influencer',
                             'user_stat.book_cnt'
                           ]);

            if( empty($user) ||  count($user) != 1 ){
                Helper::send_mail('jun@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Cannot find User at Notification at appointment', $app->appointment_id . ":" . $app->user_id  );
                return;
            }
            $user = $user[0];

            //$max_stage : Last stage before, not new stage, until available_groomer is found.
            // Once found,it means current stage.
            if( in_array($max_stage, [0, 10, 13,  29,    700] ) ){ //10, 13 can be removed here, I think, because it's returned above step.
                //First time notification, 10:Fav Notification, 29:After removed, or 700 => Needs to generate message again.
                //Send notification to Customer at first.
                $reserved_date = new DateTime($app->reserved_date );
                $reserved_datetime = $reserved_date->format('l, F j Y, h:i A');
                $reserved_day = $reserved_date->format('l');

                if( $max_stage == 0 ) { //Send TEXT at the first time only.
                    $sms_message = 'Your request for a Groomit appointment on ' . $reserved_datetime . '  has been received. We are now attempting to locate the closest available groomer.';

                    ### SMS to user only when at appointment only.
                    if (!empty($user->phone)  )  {
                        $ret = Helper::send_sms($user->phone, $sms_message);

                        if (!empty($ret)) {
                            Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Fails to end user at appointment:' . $ret, $sms_message);
                        }

                        Message::save_sms_to_user($sms_message, $user, $app->appointment_id);
                    }
                }


                //Generate Groomer Message :

                try{
                    //$pet_type = '';
                    $qty_cat_gold = 0;
                    $qty_cat_silver = 0;
                    $qty_cat_eco = 0;
                    $qty_gold = 0;
                    $qty_silver = 0;
                    $qty_eco = 0;

                    $pets = AppointmentPet::where('appointment_id', $app->appointment_id)->get();
                    $inx = 0;
                    foreach ($pets as $p) {
                        $pet = Pet::where('pet_id', $p->pet_id)->first();
                        $pet_type = $pet->type;
                        if ($pet->type == 'cat') {
                            $product = AppointmentProduct::where('appointment_id', $app->appointment_id)
                                ->where('pet_id', $p->pet_id)
                                ->whereIn('prod_id', ['16', '27', '29'])
                                ->first();

                            switch ($product->prod_id) {
                                case '16':
                                    $qty_cat_gold = $qty_cat_gold + 1;
                                    break;
                                case '27':
                                    $qty_cat_silver = $qty_cat_silver + 1;
                                    break;
                                case '29':
                                    $qty_cat_eco = $qty_cat_eco + 1;
                                    break;
                            }

                            //Nothing for cat
                            //$breed_ids .= $pet->breed  . ' , ' ;
                            if($inx == 0) {
                                $prod_ids =  " '" . $product->prod_id  . "' " ;
                            }else {
                                $prod_ids .= " , '" . $product->prod_id  . "' ";
                            }

                        } else {
                            $product = AppointmentProduct::where('appointment_id', $app->appointment_id)
                                ->where('pet_id', $p->pet_id)
                                ->whereIn('prod_id', ['1', '2', '28'])
                                ->first();

                            switch ($product->prod_id) {
                                case '1':
                                    $qty_gold = $qty_gold + 1;
                                    break;
                                case '2':
                                    $qty_silver = $qty_silver + 1;
                                    break;
                                case '28':
                                    $qty_eco = $qty_eco + 1;
                                    break;
                            }

                            if($inx == 0) {
                                $breed_ids = " '" . $pet->breed  . "' " ;
                                $prod_ids = " '" . $product->prod_id  . "' "  ;
                            }else {
                                $breed_ids .= " , '"  .  $pet->breed  . "' ";
                                $prod_ids .= " , '" . $product->prod_id . "' ";
                            }

                        }

                        if( empty($breed_ids) || trim($breed_ids) == '') { //In order to avoid syntax error when Cat, that has no breed
                            $breed_ids = " ' ' " ;
                        }

                        $inx++;;
                    }

                    if ($qty_cat_gold + $qty_cat_silver + $qty_cat_eco + $qty_gold + $qty_silver + $qty_eco == 1) {
                        if ($qty_cat_gold == 1) {
                            $package = 'CAT Gold';
                            $msg = '[CAT] Gold';
                        }

                        if ($qty_cat_silver == 1) {
                            $package = 'CAT Silver';
                            $msg = '[CAT] Silver';
                        }

                        if ($qty_cat_eco == 1) {
                            $package = 'CAT ECO';
                            $msg = '[CAT] ECO';
                        }

                        if ($qty_gold == 1) {
                            $package = 'DOG Gold';
                            $msg = '[DOG] Gold';
                        }

                        if ($qty_silver == 1) {
                            $package = 'DOG Silver';
                            $msg = '[DOG] Silver';
                        }

                        if ($qty_eco == 1) {
                            $package = 'DOG ECO';
                            $msg = '[DOG] ECO';
                        }
                    } else {
                        if ($qty_cat_gold > 0) {
                            $msg = ',' . $qty_cat_gold . ' Cat-Gold' . ($qty_cat_gold == 1 ? '' : 's');
                        }
                        if ($qty_cat_silver > 0) {
                            $msg = ',' . $qty_cat_silver . ' Cat-Silver' . ($qty_cat_silver == 1 ? '' : 's');
                        }
                        if ($qty_cat_eco > 0) {
                            $msg = ',' . $qty_cat_eco . ' Cat-ECO' . ($qty_cat_eco == 1 ? '' : 's');
                        }
                        if ($qty_silver > 0) {
                            $msg .= ',' . $qty_silver . ' Dog-Silver' . ($qty_silver == 1 ? '' : 's');
                        }
                        if ($qty_gold > 0) {
                            $msg .= ',' . $qty_gold . ' Dog-Gold' . ($qty_gold == 1 ? '' : 's');
                        }
                        if ($qty_eco > 0) {
                            $msg .= ',' . $qty_eco . ' Dog-ECO' . ($qty_eco == 1 ? '' : 's');
                        }
                        $package = substr($msg, 1);

                        $msg = 'Multiple pets[' . $package . ']';
                    }

                    $msg = "[" . $app->appointment_id . "] " . $msg. " appointment.";

                } catch (\Exception $ex) {
                    $msg = "[" . $app->appointment_id . "] New appointment.";
                }

                $address = Address::find($app->address_id);
                if (!empty($address)) {
                    $zip = $address->zip;
                    $city = $address->city;
                }

                //Get Available area of the appointment.
                $allowed_zip = AllowedZip::where('zip', $zip)->first();
                $county = empty($allowed_zip) ? null : ($allowed_zip->county_name . '.' . $allowed_zip->state_abbr);

                ### Lookup Fav. features only when the first notification with non ECO only && requested 'F' by a customer only.
                //Lookup Fav always.
                if ( ( $max_stage == 0 ) &&
//                     ($qty_eco + $qty_cat_eco == 0 )  && //ECO can select a FAV groomer since 06/22/2020 ?
                     (empty($app->fav_type) || ($app->fav_type == 'F') || ( trim($app->fav_type == '')) )  ) {

                        if( $app->my_favorite_groomer > 0 ) {
                            $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone , a.device_token, 'Y' as notified, a.text_appt
                                 FROM groomer a inner join user_favorite_groomer b on a.groomer_id = b.groomer_id AND b.user_id = :user_id  
                                 WHERE a.status = 'A'
                                 AND a.level <= 3
                                 AND a.groomer_id = :groomer_id
                                  ",
                                [
                                    'user_id' => $app->user_id,
                                    'groomer_id' => $app->my_favorite_groomer
                                ]);
                        }else {
                            $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone , a.device_token, 'Y' as notified, a.text_appt
                                 FROM groomer a inner join user_favorite_groomer b on a.groomer_id = b.groomer_id AND b.user_id = :user_id  
                                 WHERE a.status = 'A'
                                 AND a.level <= 3
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area b WHERE b.groomer_id = a.groomer_id and b.status = 'A' and b.county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in ( " . $prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND  concat(breed_id,'') in ( " . $breed_ids  . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id2 and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                                [
                                    'user_id' => $app->user_id,
                                    'county_state' => $county,
                                    'appointment_id' => $app->appointment_id,
                                    //'prod_ids' => $prod_ids,
                                    //'breed_ids' => $breed_ids,
                                    'user_id2' => $app->user_id,
                                    'user_id3' => $app->user_id
                                ]);
                        }

                }

                if (empty($available_groomers) || count($available_groomers) == 0) {  //In case of no Fav :
                    $msg = $msg . " $city $county $zip, $app->reserved_at, $reserved_day. Please visit groomer app to accept";
                }else {
                    $max_stage = 10; //Fav. Groomers exist
                    $found_groomers = true;
                    $msg = "You have favorite customer $user->first_name $user->last_name, [$package] appointment at $address->address1 $city $zip, $app->reserved_at, $reserved_day. Please visit groomers app to accept";
                }
            } //End of generation of Message at 0, 10, 700

            //No, this feature has been removed.
            //Exclusive area appointments to limited groomers only.
//            if( !$found_groomers && ($max_stage == 0)) {
//                    $available_groomers = DB::select("
//                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token, case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
//                                 FROM groomer a
//                                 WHERE a.status = 'A'
//                                 AND a.level in (1,2,3)
//                                 AND a.groomer_id in ( select gea.groomer_id from exclusive_area_detail ead inner join groomer_exclusive_area gea on ead.alias_id = gea.alias_id where ead.zip = :zip )
//                                 AND EXISTS ( SELECT groomer_id FROM groomer_notification_types  WHERE groomer_id = a.groomer_id and notification_id = 1 and status  = 'A' )
//                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
//                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) =
//                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in ( " .$prod_ids . " ) )
//                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'')  in ( " . $breed_ids . " ) )
//                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id )
//                                  ",
//                        [   'zip' => $zip ,
//                            'county_state' => $county,
//                            'appointment_id' => $app->appointment_id,
//                            'user_id' => $app->user_id
//                        ]);
//                    if (!empty($available_groomers) || count($available_groomers) > 0) {
//                        foreach ($available_groomers as $o) {
//                            if( $o->notified == 'Y' ) {
//                                $max_stage = 13; //send notifications to groomers for limited area(new market area, exclusive area)
//                                $found_groomers = true;
//                            }else {
//                                $go = new GroomerOpens();
//                                $go->appt_id = $app->appointment_id;
//                                $go->county = $county;
//                                $go->prod_ids = $prod_ids;
//                                $go->breed_ids = $breed_ids;
//                                $go->stage = '13';
//                                $go->groomer_id = $o->groomer_id;
//                                $go->msg  = $msg;
//                                $go->notified  = $o->notified;
//                                $go->cdate   = Carbon::now();
//                                $go->save();
//                            }
//                        }
//                        if( !$found_groomers ) {
//                            $available_groomers = null;
//                        }
//                    }
//            }

            //The same logic regardless of it's the first time notification or not.
            //Check if the app is delayed by end user.
            if( !$found_groomers ) { //No groomer found yet
                if (!empty($app->prefer_groomer_id) && ($app->prefer_groomer_id > 0 ) && ($max_stage != 700) ) {
                    //This would be possible after delaying appointment by a customer after a groomer is assigned, which means later 10/20 later at least.
                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone , a.device_token, 'Y' as notified, a.text_appt
                                 FROM groomer a  
                                 WHERE a.groomer_id = :groomer_id
                                  ",
                        [
                            'groomer_id' => $app->prefer_groomer_id
                        ]);
                    if (!empty($available_groomers) || count($available_groomers) > 0) {
                        $max_stage = 700; //Prefer Groomer exist, by cancelling by a customer.
                        $found_groomers = true;

                        //Invalidate existing notifications, so it cannot be shown in Open Appointments for existing groomers.
                        $ret2 = \Illuminate\Support\Facades\DB::statement("
                            update groomer_opens
                            set removed = 'Y'
                            where appt_id = :appointment_id
                        ", [ 'appointment_id' => $app->appointment_id
                        ]);
                    }
                }
            }

            if( !$found_groomers && ($max_stage == 700)) {
                //This means already notified to to the prefer_groomer before,
                // so need to send notification to all Level 1/2.
                $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone , a.device_token, case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level in ( 1, 2 )
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area b WHERE b.groomer_id = a.groomer_id and b.status = 'A' and b.county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'')  in (" . $prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'') in (" .$breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                    [
                        'county_state' => $county,
                        'appointment_id' => $app->appointment_id,
                        //'prod_ids' => $prod_ids,
                        //'breed_ids' => $breed_ids,
                        'user_id' => $app->user_id,
                        'user_id3' => $app->user_id
                    ]);
                if (!empty($available_groomers) || count($available_groomers) > 0) {
                    $inx = 0;
                    foreach ($available_groomers as $o) {
                        if( $o->notified == 'Y' ) {
                            if( $inx == 0 ) {  //Need to set it only once, do not update 'removed='Y' repeatedly.
                                $max_stage = 40; //send notifications up to Level 1,2
                                $found_groomers = true;
                                //Need to update 700 as Removed to be ensure.
                                DB::update(" update groomer_opens
                                               set removed = 'Y'
                                             where appt_id = :appointment_id",
                                    [
                                        'appointment_id'    => $app->appointment_id
                                    ]);
                            }
                            $inx++;
                        }else {
                            $go = new GroomerOpens();
                            $go->appt_id = $app->appointment_id;
                            $go->county = $county;
                            $go->prod_ids = $prod_ids;
                            $go->breed_ids = $breed_ids;
                            $go->stage = '40';
                            $go->groomer_id = $o->groomer_id;
                            $go->msg  = $msg;
                            $go->notified  = $o->notified ;
                            $go->cdate   = Carbon::now();
                            $go->save();
                        }
                    }
                    if( !$found_groomers ) {
                        $available_groomers = null;
                    }else {
                        //Reset to normal, after sending notifications to Level 1/2.
                        $app->prefer_groomer_id = null; //Reset afer sent prefer groomer once.
                        $app->save();

                    }
                }
            }

            //Influencer
            if( !$found_groomers && ($max_stage == 0)) {
                if( !empty($user->influencer) && ($user->influencer == 'Y') ) { //Send Level 1 first.
                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token, case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level = 1
                                 AND EXISTS ( SELECT groomer_id FROM groomer_notification_types  WHERE groomer_id = a.groomer_id and notification_id = 1 and status  = 'A' )
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in ( " .$prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'')  in ( " . $breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                        [
                            'county_state' => $county,
                            'appointment_id' => $app->appointment_id,
                            //'prod_ids' => $prod_ids,
                            //'breed_ids' => $breed_ids,
                            'user_id' => $app->user_id,
                            'user_id3' => $app->user_id
                        ]);
                    if (!empty($available_groomers) || count($available_groomers) > 0) {
                        foreach ($available_groomers as $o) {
                            if( $o->notified == 'Y' ) {
                                $max_stage = 21; //send notifications to groomers for influencer
                                $found_groomers = true;
                            }else {
                                $go = new GroomerOpens();
                                $go->appt_id = $app->appointment_id;
                                $go->county = $county;
                                $go->prod_ids = $prod_ids;
                                $go->breed_ids = $breed_ids;
                                $go->stage = '21';
                                $go->groomer_id = $o->groomer_id;
                                $go->msg  = $msg;
                                $go->notified  = $o->notified;
                                $go->cdate   = Carbon::now();
                                $go->save();
                            }
                        }
                        if( !$found_groomers ) {
                            $available_groomers = null;
                        }
                    }
                }
            }

            //Over $200 group
            if( !$found_groomers && ( in_array($max_stage, [0] ) ) ) {
                if( $app->sub_total >= 200 ) {
                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token,case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level <= 3
                                 AND EXISTS ( SELECT groomer_id FROM groomer_notification_types  WHERE groomer_id = a.groomer_id and notification_id = 16 and status  = 'A' )
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in (" .$prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'') in (" . $breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                        [
                            'county_state' => $county,
                            'appointment_id' => $app->appointment_id,
                            //'prod_ids' => $prod_ids,
                            //'breed_ids' => $breed_ids,
                            'user_id' => $app->user_id,
                            'user_id3' => $app->user_id
                        ]);
                    if (!empty($available_groomers) || count($available_groomers) > 0) {
                        foreach ($available_groomers as $o) {
                            if( $o->notified == 'Y' ) {
                                $max_stage = 16;  //send notifications to groomers for First time customers
                                $found_groomers = true;
                            }else {
                                $go = new GroomerOpens();
                                $go->appt_id = $app->appointment_id;
                                $go->county = $county;
                                $go->prod_ids = $prod_ids;
                                $go->breed_ids = $breed_ids;
                                $go->stage = '16';
                                $go->groomer_id = $o->groomer_id;
                                $go->msg  = $msg;
                                $go->notified  = $o->notified;
                                $go->cdate   = Carbon::now();
                                $go->save();
                            }
                        }
                        if( !$found_groomers ) {
                            $available_groomers = null;
                        }
                    }
                }
            }

            //AutoAssign group
            if( !$found_groomers && ( in_array($max_stage, [0] ) ) ) {

                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token,case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level <= 3
                                 AND EXISTS ( SELECT groomer_id FROM groomer_notification_types  WHERE groomer_id = a.groomer_id and notification_id = 15 and status  = 'A' )
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in (" .$prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'') in (" . $breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                        [
                            'county_state' => $county,
                            'appointment_id' => $app->appointment_id,
                            //'prod_ids' => $prod_ids,
                            //'breed_ids' => $breed_ids,
                            'user_id' => $app->user_id,
                            'user_id3' => $app->user_id
                        ]);
                    if (!empty($available_groomers) || count($available_groomers) > 0) {
                        foreach ($available_groomers as $o) {
                            if( $o->notified == 'Y' ) {
                                $max_stage = 15;  //send notifications to groomers for First time customers
                                $found_groomers = true;
                            }else {
                                $go = new GroomerOpens();
                                $go->appt_id = $app->appointment_id;
                                $go->county = $county;
                                $go->prod_ids = $prod_ids;
                                $go->breed_ids = $breed_ids;
                                $go->stage = '15';
                                $go->groomer_id = $o->groomer_id;
                                $go->msg  = $msg;
                                $go->notified  = $o->notified;
                                $go->cdate   = Carbon::now();
                                $go->save();
                            }
                        }
                        if( !$found_groomers ) {
                            $available_groomers = null;
                        }
                    }

            }

            if( !$found_groomers && ( in_array($max_stage, [0, 15, 16] ) ) ) {
                if( empty($user->book_cnt) || $user->book_cnt <= 1 ) { //book_cnt is inserted, right after an appointment is created.
                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token,case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level = 1
                                 AND EXISTS ( SELECT groomer_id FROM groomer_notification_types  WHERE groomer_id = a.groomer_id and notification_id = 2 and status  = 'A' )
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in (" .$prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'') in (" . $breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                        [
                            'county_state' => $county,
                            'appointment_id' => $app->appointment_id,
                            //'prod_ids' => $prod_ids,
                            //'breed_ids' => $breed_ids,
                            'user_id' => $app->user_id,
                            'user_id3' => $app->user_id
                        ]);
                    if (!empty($available_groomers) || count($available_groomers) > 0) {
                        foreach ($available_groomers as $o) {
                            if( $o->notified == 'Y' ) {
                                $max_stage = 22;  //send notifications to groomers for First time customers
                                $found_groomers = true;
                            }else {
                                $go = new GroomerOpens();
                                $go->appt_id = $app->appointment_id;
                                $go->county = $county;
                                $go->prod_ids = $prod_ids;
                                $go->breed_ids = $breed_ids;
                                $go->stage = '22';
                                $go->groomer_id = $o->groomer_id;
                                $go->msg  = $msg;
                                $go->notified  = $o->notified;
                                $go->cdate   = Carbon::now();
                                $go->save();
                            }
                        }
                        if( !$found_groomers ) {
                            $available_groomers = null;
                        }
                    }
                }
            }

            if( !$found_groomers && ( in_array($max_stage, [0, 15, 16] ) ) ) {
                if( !empty($user->book_cnt) && $user->book_cnt >= 2 ) {
                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token, case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level = 1
                                 AND EXISTS ( SELECT groomer_id FROM groomer_notification_types  WHERE groomer_id = a.groomer_id and notification_id = 3 and status  = 'A' )
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in (" . $prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'')  in (" .$breed_ids ." ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                        [
                            'county_state' => $county,
                            'appointment_id' => $app->appointment_id,
                            //'prod_ids' => $prod_ids,
                            //'breed_ids' => $breed_ids,
                            'user_id' => $app->user_id,
                            'user_id3' => $app->user_id
                        ]);

                    if (!empty($available_groomers) || count($available_groomers) > 0) {
                        foreach ($available_groomers as $o) {
                            if( $o->notified == 'Y' ) {
                                $max_stage = 23;  //send notifications to groomers for Repeated customers
                                $found_groomers = true;
                            }else {
                                $go = new GroomerOpens();
                                $go->appt_id = $app->appointment_id;
                                $go->county = $county;
                                $go->prod_ids = $prod_ids;
                                $go->breed_ids = $breed_ids;
                                $go->stage = '23';
                                $go->groomer_id = $o->groomer_id;
                                $go->msg  = $msg;
                                $go->notified  = $o->notified ;
                                $go->cdate   = Carbon::now();
                                $go->save();
                            }
                        }
                        if( !$found_groomers ) {
                            $available_groomers = null;
                        }
                    }
                }
            }

            //Send notifications to level 2 of 21/22/23.
            if( !$found_groomers && ( in_array($max_stage, [0, 15, 16] ) ) ) {
                if( !empty($user->influencer) && ($user->influencer == 'Y') ) { //Send Level 2 first.
                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token, case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level = 2
                                 AND EXISTS ( SELECT groomer_id FROM groomer_notification_types  WHERE groomer_id = a.groomer_id and notification_id = 1 and status  = 'A' )
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in ( " .$prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'')  in ( " . $breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                        [
                            'county_state' => $county,
                            'appointment_id' => $app->appointment_id,
                            //'prod_ids' => $prod_ids,
                            //'breed_ids' => $breed_ids,
                            'user_id' => $app->user_id,
                            'user_id3' => $app->user_id
                        ]);
                    if (!empty($available_groomers) || count($available_groomers) > 0) {
                        foreach ($available_groomers as $o) {
                            if( $o->notified == 'Y' ) {
                                $max_stage = 26; //send notifications to Level 2 groomers for influencer
                                $found_groomers = true;
                            }else {
                                $go = new GroomerOpens();
                                $go->appt_id = $app->appointment_id;
                                $go->county = $county;
                                $go->prod_ids = $prod_ids;
                                $go->breed_ids = $breed_ids;
                                $go->stage = '26';
                                $go->groomer_id = $o->groomer_id;
                                $go->msg  = $msg;
                                $go->notified  = $o->notified;
                                $go->cdate   = Carbon::now();
                                $go->save();
                            }
                        }
                        if( !$found_groomers ) {
                            $available_groomers = null;
                        }
                    }
                }
            }


            if( !$found_groomers && ( in_array($max_stage, [0, 15, 16] ) ) ) {
                if( empty($user->book_cnt) || $user->book_cnt <= 1 ) {
                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token,case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level = 2
                                 AND EXISTS ( SELECT groomer_id FROM groomer_notification_types  WHERE groomer_id = a.groomer_id and notification_id = 2 and status  = 'A' )
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in (" .$prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'') in (" . $breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                        [
                            'county_state' => $county,
                            'appointment_id' => $app->appointment_id,
                            //'prod_ids' => $prod_ids,
                            //'breed_ids' => $breed_ids,
                            'user_id' => $app->user_id,
                            'user_id3' => $app->user_id
                        ]);
                    if (!empty($available_groomers) || count($available_groomers) > 0) {
                        foreach ($available_groomers as $o) {
                            if( $o->notified == 'Y' ) {
                                $max_stage = 27;  //send notifications to Level 2 groomers for First time customers
                                $found_groomers = true;
                            }else {
                                $go = new GroomerOpens();
                                $go->appt_id = $app->appointment_id;
                                $go->county = $county;
                                $go->prod_ids = $prod_ids;
                                $go->breed_ids = $breed_ids;
                                $go->stage = '27';
                                $go->groomer_id = $o->groomer_id;
                                $go->msg  = $msg;
                                $go->notified  = $o->notified;
                                $go->cdate   = Carbon::now();
                                $go->save();
                            }
                        }
                        if( !$found_groomers ) {
                            $available_groomers = null;
                        }
                    }
                }
            }

            if( !$found_groomers && ( in_array($max_stage, [0, 15, 16] ) ) ) {
                if( !empty($user->book_cnt) && $user->book_cnt >= 2 ) {
                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token, case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level = 2
                                 AND EXISTS ( SELECT groomer_id FROM groomer_notification_types  WHERE groomer_id = a.groomer_id and notification_id = 3 and status  = 'A' )
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in (" . $prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'')  in (" .$breed_ids ." ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                        [
                            'county_state' => $county,
                            'appointment_id' => $app->appointment_id,
                            //'prod_ids' => $prod_ids,
                            //'breed_ids' => $breed_ids,
                            'user_id' => $app->user_id,
                            'user_id3' => $app->user_id
                        ]);

                    if (!empty($available_groomers) || count($available_groomers) > 0) {
                        foreach ($available_groomers as $o) {
                            if( $o->notified == 'Y' ) {
                                $max_stage = 28;  //send notifications to Level 2 groomers for Repeated customers
                                $found_groomers = true;
                            }else {
                                $go = new GroomerOpens();
                                $go->appt_id = $app->appointment_id;
                                $go->county = $county;
                                $go->prod_ids = $prod_ids;
                                $go->breed_ids = $breed_ids;
                                $go->stage = '28';
                                $go->groomer_id = $o->groomer_id;
                                $go->msg  = $msg;
                                $go->notified  = $o->notified ;
                                $go->cdate   = Carbon::now();
                                $go->save();
                            }
                        }
                        if( !$found_groomers ) {
                            $available_groomers = null;
                        }
                    }
                }
            }

            //Send to Level 1 if not found in previous logics.
            if( !$found_groomers && ($max_stage < 30 ) ) {
                    $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone ,a.device_token, case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level = 1
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in ( " . $prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'') in ( " . $breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                        [
                            'county_state' => $county,
                            'appointment_id' => $app->appointment_id,
                            //'prod_ids' => $prod_ids,
                            //'breed_ids' => $breed_ids,
                            'user_id' => $app->user_id,
                            'user_id3' => $app->user_id
                        ]);

                if (!empty($available_groomers) || count($available_groomers) > 0) {
                    foreach ($available_groomers as $o) {
                        if( $o->notified == 'Y' ) {
                            $max_stage = 30;   //send notifications to groomers Level 1
                            $found_groomers = true;
                        }else {
                            $go = new GroomerOpens();
                            $go->appt_id = $app->appointment_id;
                            $go->county = $county;
                            $go->prod_ids = $prod_ids;
                            $go->breed_ids = $breed_ids;
                            $go->stage = '30';
                            $go->groomer_id = $o->groomer_id;
                            $go->msg  = $msg;
                            $go->notified  = $o->notified;
                            $go->cdate   = Carbon::now();
                            $go->save();
                        }
                    }
                    if( !$found_groomers ) {
                        $available_groomers = null;
                    }
                }
            }

            if( !$found_groomers && ($max_stage < 40 ) ) {
                $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token, case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level = 2
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id,'') in ( " . $prod_ids . ") )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND  concat(breed_id,'')  in ( " . $breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                    [
                        'county_state' => $county,
                        'appointment_id' => $app->appointment_id,
                        //'prod_ids' => $prod_ids,
                        //'breed_ids' => $breed_ids,
                        'user_id' => $app->user_id,
                        'user_id3' => $app->user_id
                    ]);

                if (!empty($available_groomers) || count($available_groomers) > 0) {
                    foreach ($available_groomers as $o) {
                        if($o->notified == 'Y' ) {
                            $max_stage = 40;   //send notifications to groomers Level 2
                            $found_groomers = true;
                        }else {
                            $go = new GroomerOpens();
                            $go->appt_id = $app->appointment_id;
                            $go->county = $county;
                            $go->prod_ids = $prod_ids;
                            $go->breed_ids = $breed_ids;
                            $go->stage = '40';
                            $go->groomer_id = $o->groomer_id;
                            $go->msg  = $msg;
                            $go->notified  = $o->notified ;
                            $go->cdate   = Carbon::now();
                            $go->save();
                        }
                    }
                    if( !$found_groomers ) {
                        $available_groomers = null;
                    }
                }
            }
            if( !$found_groomers && ($max_stage < 50 ) ) {
                $available_groomers = DB::select(" 
                                 SELECT  a.groomer_id, a.mobile_phone, a.device_token, case IfNull(a.device_token,'') when '' then 'N' else 'Y' end as notified, a.text_appt
                                 FROM groomer a   
                                 WHERE a.status = 'A'
                                 AND a.level = 3
                                 AND EXISTS ( SELECT groomer_id FROM groomer_service_area WHERE groomer_id = a.groomer_id and status = 'A' and county = :county_state )
                                 AND ( SELECT count(distinct prod_id) from appointment_product where appointment_id = :appointment_id and prod_id in ( select prod_id from product where prod_type='P' ) ) = 
                                     (  SELECT count(distinct prod_id) FROM groomer_service_package WHERE groomer_id =  a.groomer_id and status = 'A'  and concat(prod_id ,'') in ( " . $prod_ids . " ) )
                                 AND NOT EXISTS ( SELECT breed_id from groomer_blocked_breeds WHERE groomer_id = a.groomer_id AND status ='A' AND concat(breed_id,'')  in ( "  .$breed_ids . " ) )
                                 AND NOT EXISTS ( SELECT user_id  from user_blocked_groomer WHERE user_id = :user_id and groomer_id = a.groomer_id ) 
                                 AND EXISTS ( SELECT IfNull(groomer_prefer,'') from user where user_id = :user_id3 and IfNull(groomer_prefer,'') in ('', IfNull(a.sex,'') ) )
                                  ",
                    [
                        'county_state' => $county,
                        'appointment_id' => $app->appointment_id,
                        //'prod_ids' => $prod_ids,
                        //'breed_ids' => $breed_ids,
                        'user_id' => $app->user_id,
                        'user_id3' => $app->user_id
                    ]);
                if (!empty($available_groomers) || count($available_groomers) > 0) {
                    foreach ($available_groomers as $o) {
                        if( $o->notified == 'Y' ) {
                            $max_stage = 50;   //send notifications to groomers Level 3
                            $found_groomers = true;
                        }else {
                            $go = new GroomerOpens();
                            $go->appt_id = $app->appointment_id;
                            $go->county = $county;
                            $go->prod_ids = $prod_ids;
                            $go->breed_ids = $breed_ids;
                            $go->stage = '50';
                            $go->groomer_id = $o->groomer_id;
                            $go->msg  = $msg;
                            $go->notified  = $o->notified ;
                            $go->cdate   = Carbon::now();
                            $go->save();
                        }
                    }
                    if( !$found_groomers ) {
                        $available_groomers = null;
                    }
                }
            }


            //Save first, before sending TEXTs, because it could take more than 3 mins in sending TEXTs.
            if (!empty($available_groomers) && count($available_groomers) > 0) {
                foreach ($available_groomers as $o) {
                    //Save to log only 'notified=Y', because the other cases were already saved.
                    if ($o->notified == 'Y') {
                        $go = new GroomerOpens();
                        $go->appt_id = $app->appointment_id;
                        $go->county = $county;
                        $go->prod_ids = $prod_ids;
                        $go->breed_ids = $breed_ids;
                        $go->stage = $max_stage;
                        $go->groomer_id = $o->groomer_id;
                        $go->msg = $msg;
                        $go->notified = $o->notified;
                        $go->cdate = Carbon::now();
                        $go->save();
                    }
                }
                foreach ($available_groomers as $o) {

                    if ($o->notified == 'Y') {
                        if ($o->text_appt == 'Y') { //Send Text only when it's on.
                            Helper::send_sms($o->mobile_phone, $msg);

//                            $r = New Message; //Hide from the report, requested by CS.
//                            $r->send_method = 'B'; // for now both
//                            $r->sender_type = 'A'; // admin user
//                            $r->sender_id = 19 ;
//                            $r->receiver_type = 'B'; // groomers
//                            $r->receiver_id = $o->groomer_id;
//                            $r->message_type = 'UC';
//                            $r->appointment_id = $app->appointment_id;
//                            $r->subject = '';
//                            $r->message = $msg;
//                            $r->cdate = Carbon::now();
//                            $r->save();
                        }
                         ### Send push notification.
                        if (!empty($o->device_token)) {
                            Helper::send_notification("", $msg, $o->device_token, 'New Appointment', "");
                        }
                    }else {
                        if( in_array($max_stage, [10, 13] )) { //In case of Fav.groomer, or exclusive area groomer, send TEXT only, even when no PUSH.
                                                               //Because it's not repeatedly sent at next time in both cases.
                            if ($o->text_appt == 'Y') { //Send Text only when it's on.
                                Helper::send_sms($o->mobile_phone, $msg);
                                //$r = New Message; //Hide from the report, requested by CS.
                                //....
                            }
                        }
                    }
                }
            } else { //When no available groomer found.

                $go = new GroomerOpens();
                $go->appt_id = $app->appointment_id;
                $go->county = $county;
                $go->prod_ids = $prod_ids;
                $go->breed_ids = $breed_ids;
                $go->stage = 1000;
                //$go->groomer_id = $o->groomer_id;
                $go->msg  = $msg;
                $go->cdate   = Carbon::now();
                $go->save();

                //no more used.
//              $app->groomer_notified = 'N';
//              $app->save();

                Helper::send_mail('jun@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Groomer Notification Failed:No groomers to send Notification on this appointment', $msg);
            }


        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ' ]: ' . $ex->getTraceAsString();
            Helper::send_mail('tech@groomit.me', '[GROOMIT.ME][' . getenv('APP_ENV') . '] Groomer Notification Failed', $msg);
        }
    }
}