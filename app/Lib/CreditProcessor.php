<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/15/17
 * Time: 10:48 AM
 */

namespace App\Lib;

use App\Model\AppointmentList;
use App\Model\AppointmentProduct;
use App\Model\Credit;
use App\Model\CreditMemo;
use App\Model\ProfitShare;
use App\Model\PromoCode;
use App\Model\ReservedCredit;
use App\Model\CreditRedemption;
use App\Model\UserFavoriteGroomer;
use App\Model\VWAppointmentPhone;
use Carbon\Carbon;
use DB;

class CreditProcessor
{

    public static function process_promotion($app) {
        try {

            ### 08/03/2018 ~ 08/12/2018 promotion ###
            # 1. first time user only
            $app_qty = AppointmentList::where('user_id', $app->user_id)
                ->where('appointment_id', '!=', $app->appointment_id)
                ->where('status', 'P')
                ->count();

            $is_first = $app_qty == 0;
            if ($is_first) {
                # 2. gold get $50 credit after payment is completed for next appointment
                # - cat included
                # 3. silver get $25
                $gold_qty = AppointmentProduct::where('appointment_id', $app->appointment_id)
                    ->whereIn('prod_id', [1, 16])
                    ->count();

                $silver_qty = AppointmentProduct::where('appointment_id', $app->appointment_id)
                    ->whereIn('prod_id', [2, 27])
                    ->count();

                if ($gold_qty > 0) {
                    $credit_amt = 50;
                } else if ($silver_qty > 0) {
                    $credit_amt = 25;
                } else {
                    return '';
                }

                # 4. valid until 08/12/2018.
                # - appointment cdate
                $promo_start = Carbon::create('2018', 8, 3, 0, 0, 0);
                $promo_end = Carbon::create('2018', 8, 13, 0, 0, 0);
                $req_date = Carbon::createFromFormat('Y-m-d H:i:s', $app->cdate);
                if ($req_date->gte($promo_start) && $req_date->lt($promo_end)) {
                    $expire_date = Carbon::today()->addDays(90);

                    # 5. expire date : 90 days
                    $credit = new Credit;
                    $credit->user_id = $app->user_id;
                    $credit->appointment_id = $app->appointment_id;
                    $credit->type = 'C';
                    $credit->category = 'P';
                    $credit->amt = $credit_amt ;
                    $credit->expire_date = $expire_date;
                    $credit->status = 'A';
                    $credit->cdate = Carbon::now();
                    $credit->save();

                    ### create_memo($user_id, $type, $amt, $expire_date, $ref_type, $ref, $appointment_id, $created_by)
                    CreditMemo::create_memo($app->user_id, 'C', $credit_amt, $expire_date,'P', 'Promotion', $app->appointment_id, 'system');
                }
            }

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function give_march_2018_promo(AppointmentList $app) {

        try {

            if (empty($app)) {
                throw new \Exception('Empty appointment object provided', -1);
            }

            ### 03/2018 promotion ###
            ### no more check needed for march - give it all time ###
            # for all full paid appointment during 03/2018, give $50 credit with 30 days expire date.
            //$first_of_march = Carbon::create(2018, 3, 1, 0, 0, 0);
            $second_of_april = Carbon::create(2018, 4, 2, 0,0,0);
            $may_promo_start = Carbon::create(2018, 5, 15, 0, 0, 0);
            $app_cdate = Carbon::createFromFormat('Y-m-d H:i:s', $app->cdate);

            Helper::log('### app_cdate ###', [
                'cdate' => $app->cdate,
                'app_cdate' => $app_cdate
            ]);

            //$is_march_2018 = $app_cdate->between($first_of_march, $first_of_april, false);
            $made_before_april_second_2018 = $app_cdate->lt($second_of_april);
            $is_full_price = $app->promo_amt == 0 && $app->credit_amt == 0 && $app->new_credit == 0;

            ### count dog gold package ###
            $dog_golds_qty = AppointmentProduct::where('appointment_id', $app->appointment_id)
                ->where('prod_id', 1)
                ->count();

            ### count cat service ###
            $cat_qty = AppointmentProduct::where('appointment_id', $app->appointment_id)
                ->where('prod_id', 16)
                ->count();

            Helper::log('### give_march_2018_promo ###', [
                'appointment_id' => $app->appointment_id,
                'dog_golds_qty' => $dog_golds_qty,
                'cat_qty' => $cat_qty,
                'is_full_price' => $is_full_price,
                'made_before_april_second_2018' => $made_before_april_second_2018,
                'app->cdate' => $app->cdate
            ]);

            ### clear old credit first ###
            $ret = Credit::where('appointment_id', $app->appointment_id)
                ->where('type', 'C')
                ->where('category', 'G')
                ->where('status', 'A')
                ->delete();
            if ($ret < 0) {
                return 'Failed to remove old credit';
            }

            $is_gold_50_used = trim(strtoupper($app->promo_code)) == 'GOLD50';

            if ($is_gold_50_used && ($made_before_april_second_2018 || $app_cdate->gte($may_promo_start)) && $is_full_price && $dog_golds_qty > 0) {
                $credit = new Credit;
                $credit->user_id = $app->user_id;
                $credit->appointment_id = $app->appointment_id;
                $credit->type = 'C';
                $credit->category = 'G';
                $credit->amt = 50 * $dog_golds_qty ;
                $credit->expire_date = Carbon::today()->addDays(60);
                $credit->status = 'A';
                $credit->cdate = Carbon::now();
                $credit->save();
            }

            if ($is_gold_50_used && ($made_before_april_second_2018 || $app_cdate->gte($may_promo_start)) && $is_full_price && $cat_qty > 0) {
                $credit = new Credit;
                $credit->user_id = $app->user_id;
                $credit->appointment_id = $app->appointment_id;
                $credit->type = 'C';
                $credit->category = 'G';
                $credit->amt = 50 * $cat_qty ;
                $credit->expire_date = Carbon::today()->addDays(90);
                $credit->status = 'A';
                $credit->cdate = Carbon::now();
                $credit->save();
            }

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
        }
    }

    public static function getAvailableCredit($user_id) {

        ### 1. credit not expired -> total ###
        $remaining_credit_sum = Credit::where('type', 'C')
            ->where('user_id', $user_id)
            ->where('status', 'A')
            ->where('expire_date', '>', Carbon::today())
            ->sum('amt');

        ### 2. credit expired -> sum of debit to make credit - debit = 0.
        $ret = DB::select("
            select sum(b.amt) amt
            from credit a
                inner join credit_redemption b on a.credit_id = b.source_credit_id  
            where a.type = 'C'
            and a.user_id = :user_id
            and a.status = 'A'
            and b.status = 'A'
            and a.expire_date <= :today 
        ", [
            'user_id' => $user_id,
            'today' => Carbon::today()
        ]);
        if (count($ret) > 0) {
            $remaining_credit_sum += $ret[0]->amt;
        }

        $remaining_debit_sum = Credit::where('type', 'D')
            ->where('user_id', $user_id)
            ->where('status', 'A')
            ->sum('amt');

        return round($remaining_credit_sum - $remaining_debit_sum, 2);

    }

    public static function cancelCreditUsage($appointment_id) {
        try {

            ### find debit type credit for the appointment ###
            $c = Credit::where('type', 'D')
                ->where('appointment_id', $appointment_id)
                ->first();

            ### if not found, nothing to do here ###
            if (empty($c)) {
                return '';
            }

            ### if found, mark it as cancelled ###
            $c->status = 'C';
            $c->save();

            ### find all credit redemption records ###

            $credit_redemptions = CreditRedemption::where('credit_id', $c->credit_id)->get();
            $source_credit_ids = [];
            if (count($credit_redemptions) > 0) {
                foreach ($credit_redemptions as $o) {
                    ### mark them as cancelled ##
                    $o->status = 'C';
                    $o->save();

                    ### extract source credit IDs ###
                    if (!in_array($o->source_credit_id, $source_credit_ids)) {
                        $source_credit_ids[] = $o->source_credit_id;
                    }
                }
            }

            ### revaluate source credit IDs if they are fully redeemed ###
            if (count($source_credit_ids) > 0) {
                foreach ($source_credit_ids as $source_credit_id) {

                    $source_credit = Credit::where('credit_id', $source_credit_id)
                        ->where('type', 'C')
                        ->first();

                    if (empty($source_credit)) {
                        return 'Seomthing is wrong. Unable to find source credit';
                    }

                    $redeemed_amt = CreditRedemption::where('source_credit_id', $source_credit_id)
                        ->where('status', 'A')
                        ->sum('amt');

                    $source_credit->fully_redeemed = $redeemed_amt == $source_credit->amt ? 'Y' : 'N';
                    $source_credit->save();

                }
            }

            ### UNUSE CREDIT ###
            $app = AppointmentList::find($appointment_id);
            CreditMemo::unuse_credit($app);

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function useCredit($user_id, $amt, $appointment_id) {

        DB::beginTransaction();

        try {
            $total_credit = self::getAvailableCredit($user_id);
            if (doubleval($total_credit) < doubleval($amt)) {
                throw new \Exception('credit amount exceeds total available credit : ' . $total_credit . ' / ' . $amt);
            }

            ### make record for the credit usage ###
            $c = new Credit;
            $c->user_id = $user_id;
            $c->type = 'D';
            $c->category = 'N';
            $c->amt = $amt;
            $c->appointment_id = $appointment_id;
            $c->status = 'A';
            $c->cdate = Carbon::now();
            $c->save();

            ### make redemption history ###
            $credits = Credit::where('user_id', $user_id)
                ->where('type', 'C')
                ->where('fully_redeemed', 'N')
                ->where('expire_date', '>', Carbon::today())
                ->orderBy('cdate', 'asc')
                ->get();

            $total_redeemed = 0;
            if (count($credits) > 0) {
                foreach ($credits as $o) {

                    Helper::log('### o ###', $o);

                    ### get remaining unredeemed credit ###
                    $redeemed_total = CreditRedemption::where('source_credit_id', $o->credit_id)
                        ->where('status', 'A')
                        ->sum('amt');

                    if (empty($redeemed_total)) {
                        $redeemed_total = 0;
                    }

                    if ($o->amt < $redeemed_total) {
                        throw new \Exception('Something is wrong. Redeemed total is greater than source credit amount');
                    }

                    $remaining_credit = $o->amt - $redeemed_total;
                    if ($remaining_credit >= ($amt - $total_redeemed)) {
                        $amt_being_redeemed = ($amt - $total_redeemed);
                    } else {
                        $amt_being_redeemed = $remaining_credit;
                    }

                    Helper::log('### useCredit ###', [
                        'amt_being_redeemed' => $amt_being_redeemed,
                        'total_redeemed' => $total_redeemed,
                        'amt' => $amt,
                        'redeemed_total' => $redeemed_total,
                        'credit_amt' => $o->amt,
                        'remaining_credit' => $remaining_credit
                    ]);

                    $cr = new CreditRedemption;
                    $cr->credit_id = $c->credit_id;
                    $cr->source_credit_id = $o->credit_id;
                    $cr->amt = $amt_being_redeemed;
                    $cr->total_amt = $o->amt;
                    $cr->status = 'A';
                    $cr->cdate = Carbon::now();
                    $cr->save();

                    $redeemed_total = CreditRedemption::where('source_credit_id', $o->credit_id)
                        ->where('status', 'A')
                        ->sum('amt');

                    if ($redeemed_total == $o->amt) {
                        $o->fully_redeemed = 'Y';
                        $o->save();
                    }

                    $total_redeemed += $amt_being_redeemed;

                    if ($total_redeemed >= $amt) {
                        break;
                    }
                }
            }

            if ($total_redeemed != $amt) {
                throw new \Exception('Something is wrong total redeemed amount is not equal to debit type credit amount. ' . $total_redeemed . ' / ' . $amt);
            }

            DB::commit();

            CreditMemo::use_credit($user_id, $amt, $appointment_id);

            return "";

        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function giveReferralCredit($promo_code, $appointment_id ) {
        try {

            $code = PromoCode::whereRaw('code = ?' , [strtoupper($promo_code)])
                ->where('type', 'R')
                ->where('status', 'A')
                ->first();

            if (empty($code)) {
                return 'Invalid promo code provoided';
            }

            if ($code->type != 'R') {
                return 'Provided code is not referral code';
            }

            if ($code->amt_type == 'R') {
                return 'Referral code amount type is Ratio. Unable to reserve referral credit';
            }

            //Referal credit to end users.
            if( !empty($code->user_id) && ($code->user_id > 0 ) ) {
                $c = new Credit;
                $c->user_id = $code->user_id; // code owner
                $c->type = 'C';
                $c->category = 'R';
                $c->amt = $code->amt;
                $c->referral_code = $code->code;
                $c->appointment_id = $appointment_id;
                $c->expire_date = Carbon::today()->addDays(365);
                $c->status = 'A';
                $c->cdate = Carbon::now();
                $c->save();
            }

            return "";

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function giveReferralGroomerCredit(AppointmentList $app ) {
        try {

            if (empty($app) || $app->status != 'P')
                return;


            if( !empty($app->promo_code) ) {
                //When Referal code is used at appointment.
                $code_obj = PromoCode::whereRaw("code = '" . strtoupper($app->promo_code) . "'")
                    ->where('groomer_id' , $app->groomer_id)
                    ->where('type', 'R')
                    ->where('amt_type', 'A')
                    ->where('status', 'A')
                    ->first();
            }else {
                //When Referal code is used at Signup.
                //Find out Groomer referal code
                $code_obj = PromoCode::where('groomer_id' , $app->groomer_id)
                    ->where('type', 'R')
                    ->where('amt_type', 'A')
                    ->where('status', 'A')
                    ->first();

                //Helper::log('### PromoCode INFORMATION ###', $code_obj);

                if( empty( $code_obj ) ) {
                    return ;
                }

                //Find out a User who got Referal credit to the end user.
                $signup_credit = Credit::where('user_id', $app->user_id )
                    ->where('referral_code', $code_obj->code )
                    ->where('type', 'C')
                    ->where('category', 'S')
                    ->where('status', 'A')
                    ->first();
                //Helper::log('### signup credit INFORMATION ###', $signup_credit);
                if(empty($signup_credit)){
                    return ;
                }

                //If Referal code of the End user at Signup is different from The groomer's referral code, do nothing.
                if( $signup_credit->referral_code != $code_obj->code ){
                    Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . ']' . 'Referral code of End User at Signup does not match Groomer Referal code',
                        'app_id : ' . $app->appointment_id );

                    return;
                }
            }

            if(empty($code_obj)){
                return ;
            }

            //check first appt
            $is_first = AppointmentProcessor::is_first_appointment($app);
            Helper::log('### is_first_appointment ###', $is_first );
            if (!$is_first) {
                Helper::log('### Before return ###', $is_first );
                return;
            }

            //check if already paid.
            $profit_cnt =  ProfitShare::where('type', 'R')
                ->where('appointment_id', $app->appointment_id)
                ->whereRaw("id not in (select original_id from profit_share where type = 'L' )")
                ->count();


            Helper::log('### check if ProfitShare already exist ###', $profit_cnt );
            if($profit_cnt == 0 ) {
                ProfitShare::create_groomer_referral( $app, $code_obj->code);
            }else {
                //Already Groomer Referal was paid, so skipped.
            }

            return "";

        } catch (\Exception $ex) {
            Helper::log('### Exception in CreditProcessor ###', $ex );
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function activateReservedReferralCredit($user_id, $appointment_id) {
        try {

            ### check integrity ###
            # - there should be only one or none
            $cnt = ReservedCredit::where('signup_user_id', $user_id)
                ->count();

            if ($cnt > 1) {
                return 'Somthing is wrong. Reserved credit count for the signup user should not be more than 1';
            }

            ### find waiting reserved credit if exists ###
            $rc = ReservedCredit::where('signup_user_id', $user_id)
                ->where('status', 'W')
                ->first();

            ### if none exists, nothing to do here ###
            if (empty($rc)) {
                return '';
            }

            $expire_date = Carbon::today()->addDays(365);

            ### make reserved credit record ###
            $c = new Credit;
            $c->user_id = $rc->user_id;
            $c->type = 'C';
            $c->category = 'R';
            $c->amt = $rc->amt;
            $c->referral_code = $rc->referral_code;
            $c->reserved_credit_id = $rc->reserved_credit_id;
            $c->appointment_id = $appointment_id;
            $c->expire_date = $expire_date;
            $c->status = 'A';
            $c->cdate = Carbon::now();
            $c->fully_redeemed = 'N';
            $c->save();

            ### create_memo($user_id, $type, $amt, $expire_date, $ref_type, $ref, $appointment_id, $created_by, $orig_id = null)
            CreditMemo::create_memo($rc->user_id, 'C', $rc->amt, $expire_date, 'R', $rc->referral_code, $appointment_id, 'system');

            ### mark reserved credit as activated ###
            $rc->status = 'A'; // Applied
            $rc->applied_date = Carbon::now();
            $rc->appointment_id = $appointment_id;
            $rc->save();

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode(). ']';
        }
    }

    public static function reserveReferralCredit($signup_user_id, $promo_code) {
        try {

            $code = PromoCode::whereRaw('code = ?' , [strtoupper($promo_code)])
                ->where('type', 'R')
                ->where('status', 'A')
                ->first();

            if (empty($code)) {
                return 'Invalid promo code provoided';
            }

            if ($code->amt_type == 'R') {
                return 'Referral code amount type is Ratio. Unable to reserve referral credit';
            }

            $rc = ReservedCredit::where('signup_user_id', $signup_user_id)
                ->first();

            if (!empty($rc)) {
                return 'Somthing is wrong. There is reserved credit by the signup user already';
            }

            $rc = new ReservedCredit;
            $rc->user_id = $code->user_id;
            $rc->signup_user_id = $signup_user_id;
            $rc->referral_code = $code->code;
            $rc->amt = $code->amt;
            $rc->status = 'W'; // Waiting to be applied
            $rc->cdate = Carbon::now();
            $rc->save();

            return "";

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function giveSignupCredit($user, $promo_code) {
        try {
            $code = PromoCode::whereRaw('code = ?' , [strtoupper($promo_code)])
                ->where('type', 'R')
                ->where('status', 'A')
                ->first();

            if (empty($code)) {
                return 'Invalid promo code provoided';
            }

            if ($code->amt_type == 'R') {
                return 'Referral code amount type is Ratio. Unable to give signup credit';
            }

            //Check if the same phone has any existing appointments or not.
            $same_phone = VWAppointmentPhone::where('phone', $user->phone)->first();
            if (!empty($same_phone)) {
                return 'Your promo code is not valid. Please contact customer care for more information.';
            }

            ### give signup credit ###

            $expire_date = Carbon::today()->addDays(60);

            //Category :
            // S: Signup Credit with Referral code, N: Normal , R: Referral Credit ( to the owner of referral code ), T: Store Credit, G : $50 gold package credit
            //referal_code        : required when category = S ( Signup with Referral Code ) or R ( Referral credit ) and type = C ( Credit )
            //admin_id            : Required when category = T ( Store Credit )  and type = C ( Credit )
            //reserved_credit_id  : required when category = R ( Referral Credit ) and type = C
            //appointment_id      : required when category = N ( Normal ) or R ( Referral code ) and for both type = C & D
            //status              : A: Active, E: Expired, C: Cancelled : E might not work correctly.
            //fully_redeemed      : N by default, required only for type = 'C'. set to Y when it's fully redeemed.
            $credit = new Credit;
            $credit->user_id = $user->user_id;
            $credit->type = 'C';
            $credit->category = 'S';
            $credit->amt = $code->amt;
            $credit->referral_code = $code->code;
            $credit->expire_date = $expire_date;
            $credit->status = 'A';
            $credit->cdate = Carbon::now();
            $credit->save();


            //orig_id : required when category = R ( Referral Credit ) and type = C
            //ref_type : P: Promotion, R(Referal code), S(Signup credit with Referal code), NULL
            ### create_memo($user_id, $type, $amt, $expire_date, $ref_type, $ref, $appointment_id, $created_by, $orig_id = null)
            CreditMemo::create_memo($user->user_id, 'C', $code->amt, $expire_date, 'S', $code->code, null, 'system');

            ### reserve referral credit for code owner ###
            # - will be applied when the signup user make first appointment and paid in full

            if (!empty($code->groomer_id)) {
                $fav = new UserFavoriteGroomer();
                $fav->user_id = $user->user_id;
                $fav->groomer_id = $code->groomer_id;
                $fav->save();
            }

            if (empty($code->user_id)) {
                return "";
            }

            return self::reserveReferralCredit($user->user_id, $promo_code);

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode().  ']';
        }
    }

}