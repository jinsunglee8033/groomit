<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/15/17
 * Time: 4:35 PM
 */

namespace App\Lib;

use App\Model\AffiliateRedeemHistory;
use App\Model\AppointmentList;
use App\Model\AppointmentPet;
use App\Model\AppointmentProduct;
use App\Model\Product;
use App\Model\ProfitSharing;
use App\Model\ProfitSharingDetail;
use App\Model\ProfitSharingExceptionGroomer;
use App\Model\ProfitSharingExceptionUser;
use App\Model\ProfitSharingSetup;
use App\Model\ProfitShare;
use App\Model\ProfitShareDetail;
use App\Model\PromoCode;
use App\Model\VWGroomerAssignLog;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ProfitSharingProcessor
{

    public static function generateTipProfit(AppointmentList $app) {
        try {
            ### Tip always goes to groomer 100% ###
            $groomer_profit_ratio = 97;

            $groomer_profit_amt = $app->tip * $groomer_profit_ratio / 100;

            $ps = new ProfitSharing;
            $ps->appointment_id = $app->appointment_id;
            $ps->sub_total = $app->tip;
            $ps->type = 'T';
            $ps->groomer_id = $app->groomer_id;
            $ps->groomer_profit_ratio = $groomer_profit_ratio;
            $ps->groomer_profit_amt = $groomer_profit_amt;
            $ps->remaining_amt = $ps->sub_total - $ps->groomer_profit_amt;
            $ps->created_by = $app->user_id;
            $ps->cdate = Carbon::now();
            $ps->save();

            ProfitShare::create_tip($app);

            return '';

        } catch (\Exception $ex) {
            Helper::log('### EXCEPTION ###', $ex->getTraceAsString());
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    //Used by Cancel Fee & Reschedule Fee together.
    public static function create_cancel_fee($app, $charge_amt, $groomer_commission_amt, $tax=0) {
        try {

            $groomer_profit_amt         = $groomer_commission_amt;
            $groomer_fee                = 0;

            $ps = new ProfitShare();
            $ps->appointment_id         = $app->appointment_id;
            $ps->sub_total              = $charge_amt;
            $ps->type                   = 'W'; //Cancel & Reschedule Fee for both.
            $ps->groomer_id             = empty($app->groomer_id) ? $app->prefer_groomer_id : $app->groomer_id ; //In case of Reschedul, groomer_id is gone already.
            $ps->groomer_profit_ratio   = $groomer_commission_amt / $charge_amt * 100;
            $ps->groomer_fee            = $groomer_fee;
            $ps->groomer_sameday_earning = 0;
//            $now = Carbon::now();
//            if ($now >= '2020-03-01') {
//                $ps->groomer_sameday_earning = $app->sameday_booking * 0.50;
//            }else {
//                $ps->groomer_sameday_earning = $app->sameday_booking * 0.65; // changed since 12/05/2019 0.75;
//            }

            $ps->groomer_fav_earning = 0; // ( ($app->fav_groomer_fee > 0) && ($app->groomer_id == $app->my_favorite_groomer)) ? $app->fav_groomer_fee * 0.50 : 0 ; //$5 for the groomer, $5 for Groomit.

            $ps->groomer_profit_amt     = $groomer_profit_amt ; // - $ps->groomer_fee + $ps->groomer_sameday_earning + $ps->groomer_fav_earning ;
            $ps->remaining_amt          = $charge_amt  - $ps->groomer_profit_amt; //+ $tax
            $ps->app_pet_qty            = 0;
            $ps->app_package_type       = 'Cancel/ReSchedule Fee';
            $ps->app_safety_insurance   = 0 ; //$app->safety_insurance;
            $ps->app_sameday_booking   = 0 ;  //$app->sameday_booking;
            $ps->app_fav_groomer_fee   = 0 ;  //$app->fav_groomer_fee;
            $ps->app_total              = $charge_amt + $tax ; //$app->total;
            $ps->app_tax                = $tax ; //$app->tax;

            $ps->created_by             = $app->user_id;
            $ps->cdate                  = Carbon::now();

//            Helper::log('### EXCEPTION ### of ps',$ps->appointment_id  );
//            Helper::log('### EXCEPTION ### of ps',$ps->sub_total  );
//            Helper::log('### EXCEPTION ### of ps',$ps->type  );
//            Helper::log('### EXCEPTION ### of ps',$ps->groomer_id  );
//            Helper::log('### EXCEPTION ### of ps',$ps->groomer_profit_ratio  );
//            Helper::log('### EXCEPTION ### of ps',$ps->groomer_fee  );
//            Helper::log('### EXCEPTION ### of ps',$ps->groomer_sameday_earning  );
//            Helper::log('### EXCEPTION ### of ps',$ps->groomer_fav_earning  );
//            Helper::log('### EXCEPTION ### of ps',$ps->groomer_profit_amt  );
//            Helper::log('### EXCEPTION ### of ps',$ps->remaining_amt  );
//            Helper::log('### EXCEPTION ### of ps',$ps->app_pet_qty  );
//            Helper::log('### EXCEPTION ### of ps',$ps->app_package_type  );
//            Helper::log('### EXCEPTION ### of ps',$ps->app_safety_insurance  );
//            Helper::log('### EXCEPTION ### of ps',$ps->app_sameday_booking  );
//            Helper::log('### EXCEPTION ### of ps',$ps->app_fav_groomer_fee  );
//            Helper::log('### EXCEPTION ### of ps',$ps->app_total  );
//            Helper::log('### EXCEPTION ### of ps',$ps->app_tax  );
//            Helper::log('### EXCEPTION ### of ps',$ps->created_by  );
//            Helper::log('### EXCEPTION ### of ps',$ps->cdate  );

            $ps->save();

        } catch (\Exception $ex) {
            Helper::log('### EXCEPTION ###', $ex->getTraceAsString());
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function getProfit($appointment_id) {
//        return ProfitSharing::where('appointment_id', $appointment_id)
//            ->sum('groomer_profit_amt');
        return ProfitShare::where('appointment_id', $appointment_id)
              ->sum('groomer_profit_amt');
    }

    public static function getEstimatedProfit($appointment_id) {

        try {

            $app = AppointmentList::findOrFail($appointment_id);

            $appointment_products = AppointmentProduct::where('appointment_id', $app->appointment_id)
                ->whereRaw("prod_id in (select prod_id from product where prod_type = 'P')")
                ->get();

            $total_groomer_profit_amt = 0;
            $is_eco = false;

            foreach ($appointment_products as $o) {
                if (in_array($o->prod_id, [28, 29])) {
                    $is_eco = true;
                }

                $ret = self::getProfitSharingRatio($o->prod_id, $app->user_id, $app->groomer_id);
                if (!empty($ret['msg'])) {
                    return 0;
                }

                $groomer_profit_ratio = $ret['ratio'];
                $sub_total = AppointmentProduct::where('appointment_id', $app->appointment_id)
                    ->where('pet_id', $o->pet_id)
                    ->sum('amt');

                $groomer_profit_amt = $sub_total * $groomer_profit_ratio / 100;

                $total_groomer_profit_amt += $groomer_profit_amt;

            }

//            $sameday_booking_earning = 0;
//            $now = Carbon::now();
//            $p_sdate2 = '2019-06-22';
//            $p_edate2 = '2020-01-01';
//
//            if ($app->cdate >= $p_sdate2 && $app->cdate < $p_edate2) {
//
//                if ($app->cdate>= '2020-03-01') {
//                    $sameday_booking_earning =  $app->sameday_booking *  0.50; // changed since 12/05/2019 0.75;
//                }else {
//                    $sameday_booking_earning =  $app->sameday_booking *  0.65; // changed since 12/05/2019 0.75;
//                }
//
//            }else {
//                $sameday_booking_earning =  0;
//            }
//
//
//            $now = Carbon::now();
//            $p_sdate2 = '2019-11-21';
//            $p_edate2 = '2020-01-01';
//            $p_amt2 = 0;
//
//            if ($app->cdate > $p_sdate2 && $app->cdate < $p_edate2) {
//                $mins_diff = 100; //by default, not pay $5.
//                $vgl = VWGroomerAssignLog::where('appointment_id', $app->appointment_id)
//                    ->where('groomer_id', $app->groomer_id)
//                    ->first([
//                        'groomer_assign_date',
//                        DB::raw('TIMESTAMPDIFF(MINUTE, cdate, groomer_assign_date) as mins_diff')
//                    ]);
//                if (!empty($vgl)) {
//                    $mins_diff = $vgl->mins_diff ;
//                }
//
//                if ($mins_diff < 10 ) {
//                    //Pay $5 in case accept within 10 minutes.
//                    $p_amt2 = 5;
//                }
//            }

            //return $total_groomer_profit_amt + $sameday_booking_earning  + $p_amt2;   // +  $p_amt + $p_amt2 ; Show these as 'Bonus'

            return $total_groomer_profit_amt; //It returns profit by products only, not by sameday or fav.groomer.fee

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
            Helper::send_mail('it@perfectmobileinc.com', '[PM][' . getenv() . '] Failed to process getEstimatedProfit()', $msg);
            return 0;
        }
    }

    //Bonus of Sameday Commission, $5 on time arriving, Fav Groomer fee
    public static function getEstimatedBonus($appointment_id) {

        try {

            $app = AppointmentList::findOrFail($appointment_id);

            $appointment_products = AppointmentProduct::where('appointment_id', $app->appointment_id)
                ->whereRaw("prod_id in (select prod_id from product where prod_type = 'P')")
                ->get();


            $sameday_booking_earning = 0;
            $fav_fee_earning = 0;
            $p_amt2 = 0;
            $p_amt3 = 0;

            $now = Carbon::now();

            $p_sdate2 = '2019-11-21';
            $p_edate2 = '2020-01-01';
            if ($app->cdate >= $p_sdate2 && $app->cdate < $p_edate2) {
                $mins_diff = 100; //by default, not pay $5.
                $vgl = VWGroomerAssignLog::where('appointment_id', $app->appointment_id)
                    ->where('groomer_id', $app->groomer_id)
                    ->first([
                        'groomer_assign_date',
                        DB::raw('TIMESTAMPDIFF(MINUTE, cdate, groomer_assign_date) as mins_diff')
                    ]);
                if (!empty($vgl)) {
                    $mins_diff = $vgl->mins_diff ;
                }

                if ($mins_diff < 10 ) {
                    //Pay $5 in case accept within 10 minutes.
                    $p_amt2 = 5;
                }
            }

            $p_sdate2 = '2020-03-01';
            $p_edate2 = '2999-02-01';
            $p_amt3 = 0; //$5 on-time arrival, removed from 02/01/2020
            $sameday_booking_earning =  $app->sameday_booking *  0.65; // changed since 12/05/2019 0.75;
            if ($app->cdate >= $p_sdate2 && $app->cdate < $p_edate2) {
                $sameday_booking_earning =  $app->sameday_booking *  0.50; // changed since 12/05/2019 0.75;
                //$p_amt3 = 5; //$5 on-time arrival
                //$p_amt3 = 0; //$5 on-time arrival, removed since 02/01/2020
            }

            if(empty($app->groomer_id)) {
                $fav_fee_earning =  $app->fav_groomer_fee * 0.50;
            }else {
                $fav_fee_earning =  ( ($app->fav_groomer_fee > 0) && ($app->groomer_id == $app->my_favorite_groomer)) ? $app->fav_groomer_fee * 0.50 : 0 ;
            }

            return  $sameday_booking_earning + $fav_fee_earning ;
            //return  $sameday_booking_earning + $p_amt2 + $p_amt3 ;

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
            Helper::send_mail('it@perfectmobileinc.com', '[PM][' . getenv() . '] Failed to process getEstimatedBonus()', $msg);
            return 0;
        }
    }

    public static function shareProfit(AppointmentList $app) {

        DB::beginTransaction();

        try {

            ### delete old record first ###
            $ret = ProfitSharing::where('appointment_id', $app->appointment_id)
              ->where('type', 'A')
              ->delete();
            if ($ret < 0) {
                DB::rollback();
                return 'Failed to clear old profit sharing record';
            }

            $ret = ProfitSharingDetail::where('appointment_id', $app->appointment_id)
              ->where('type', 'A')
              ->delete();
            if ($ret < 0) {
                DB::rollback();
                return 'Failed to clear old profit sharing detail record';
            }

            $appointment_products = AppointmentProduct::where('appointment_id', $app->appointment_id)
                ->whereRaw("prod_id in (select prod_id from product where prod_type = 'P')")
                ->get();

            $user = Auth::guard('admin')->user();


            $created_by = isset($user) ? $user->admin_id : 'system';
            $total_groomer_profit_amt = 0;

            foreach ($appointment_products as $o) {
                $ret = self::getProfitSharingRatio($o->prod_id, $app->user_id, $app->groomer_id);
                if (!empty($ret['msg'])) {
                    DB::rollback();
                    return $ret['msg'];
                }

                $groomer_profit_ratio = $ret['ratio'];
                $ex_groomer_id = $ret['ex_groomer_id'];
                $ex_user_id = $ret['ex_user_id'];
                $orig_ratio = $ret['orig_ratio'];

                $sub_total = AppointmentProduct::where('appointment_id', $app->appointment_id)
                    ->where('pet_id', $o->pet_id)
                    ->sum('amt');

                $groomer_profit_amt = $sub_total * $groomer_profit_ratio / 100;

                $total_groomer_profit_amt += $groomer_profit_amt;

                $psd = new ProfitSharingDetail;
                $psd->appointment_id = $app->appointment_id;
                $psd->type = 'A';
                $psd->package_id = $o->prod_id;
                $psd->sub_total = $sub_total;
                $psd->groomer_id = $app->groomer_id;
                $psd->groomer_profit_ratio = $groomer_profit_ratio;
                $psd->groomer_profit_amt = $groomer_profit_amt;
                $psd->remaining_amt = $sub_total - $groomer_profit_amt;

                $psd->exception_groomer_id = $ex_groomer_id;
                $psd->exception_user_id = $ex_user_id;
                $psd->orig_profit_ratio = $orig_ratio;

                $psd->created_by = $created_by;
                $psd->cdate = Carbon::now();
                $psd->save();
            }

            $ps = new ProfitSharing;
            $ps->appointment_id = $app->appointment_id;
            $ps->type = 'A';
            $ps->sub_total = $app->sub_total;
            $ps->groomer_id = $app->groomer_id;
            $total_groomer_profit_ratio = $total_groomer_profit_amt / $app->sub_total * 100;
            $ps->groomer_profit_ratio = $total_groomer_profit_ratio;
            $ps->groomer_profit_amt = $total_groomer_profit_amt;
            $ps->remaining_amt = $ps->sub_total - $ps->groomer_profit_amt;
            $ps->created_by = $created_by;
            $ps->cdate = Carbon::now();
            $ps->save();

            DB::commit();

            self::share_profit($app);

            return '';

        } catch (\Exception $ex) {
            DB::rollback();
            return $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
        }
    }

    ### New Share Process
    public static function share_profit(AppointmentList $app, $is_mig = false) {

        DB::beginTransaction();

        try {
            $user = Auth::guard('admin')->user();

            $created_by = isset($user) ? $user->admin_id : 'system';

            if ($app->status != 'P') {
                ### delete old record first ###
                $ret = ProfitShare::where('appointment_id', $app->appointment_id)
                  ->where('type', 'A')
                  ->delete();

                if ($ret < 0) {
                    DB::rollback();
                    return 'Failed to clear old profit sharing record';
                }

                $ret = ProfitShareDetail::where('appointment_id', $app->appointment_id)
                  ->where('type', 'A')
                  ->delete();
                if ($ret < 0) {
                    DB::rollback();
                    return 'Failed to clear old profit sharing detail record';
                }

                ### Remove Affiliate
                AffiliateRedeemHistory::remove($app);

                ### Remove Groomer Referal : just Delete it.
                ProfitShare::remove_groomer_referral($app);
            } else {

                DB::insert("
                    insert into profit_share(
                        appointment_id,
                        type,
                        sub_total,
                        groomer_id,
                        groomer_profit_ratio,
                        groomer_fee,
                        groomer_sameday_earning,
                        groomer_fav_earning,
                        groomer_profit_amt,
                        remaining_amt,
                        exception_groomer_id,
                        exception_user_id,
                        orig_profit_ratio,
                        app_pet_type,
                        app_pet_qty,
                        app_package_type,
                        app_package_amt,
                        app_addon_amt,
                        app_sub_total,
                        app_promo_type,
                        app_promo_code,
                        app_promo_amt,
                        app_credit_amt,
                        app_new_credit,
                        app_safety_insurance,
                        app_sameday_booking,
                        app_fav_groomer_fee,
                        app_total,
                        app_tax,
                        app_tip,
                        app_groupon_amt,
                        app_number,
                        comments, 
                        created_by, 
                        cdate,
                        original_id)
                    select 
                        appointment_id,
                        'V',
                        -sub_total,
                        groomer_id,
                        groomer_profit_ratio,
                        -groomer_fee,
                        -groomer_sameday_earning,
                        -groomer_fav_earning,
                        -groomer_profit_amt,
                        -remaining_amt,
                        exception_groomer_id,
                        exception_user_id,
                        orig_profit_ratio,
                        app_pet_type,
                        -app_pet_qty,
                        app_package_type,
                        -app_package_amt,
                        -app_addon_amt,
                        -app_sub_total,
                        app_promo_type,
                        app_promo_code,
                        -app_promo_amt,
                        -app_credit_amt,
                        -app_new_credit,
                        -app_safety_insurance,
                        -app_sameday_booking,
                        -app_fav_groomer_fee,
                        -app_total,
                        -app_tax,
                        -app_tip,
                        -app_groupon_amt,
                        app_number,
                        'Reverse before update',
                        :created_by,
                        :cdate,
                        id
                      from profit_share
                     where appointment_id = :appointment_id
                       and type = 'A'
                       and (id not in (select original_id from profit_share where appointment_id = :v_appointment_id and type ='V'))
                ", [
                  'created_by' => $created_by,
                  'cdate' => Carbon::now(),
                  'appointment_id' => $app->appointment_id,
                  'v_appointment_id' => $app->appointment_id
                ]);

                ### Reverse sharing detail ###
                DB::insert("
                    insert into profit_share_detail(
                      appointment_id,
                      type,
                      package_id,
                      sub_total,
                      groomer_id,
                      groomer_profit_ratio,
                      groomer_fee,
                      groomer_profit_amt,
                      remaining_amt,
                      exception_groomer_id,
                      exception_user_id,
                      orig_profit_ratio,
                      created_by,
                      cdate,
                      original_id
                    )
                    select
                      appointment_id,
                      'V',
                      package_id,
                      -sub_total,
                      groomer_id,
                      groomer_profit_ratio,
                      -groomer_fee,
                      -groomer_profit_amt,
                      -remaining_amt,
                      exception_groomer_id,
                      exception_user_id,
                      orig_profit_ratio,
                      :created_by,
                      :cdate,
                      id
                      from profit_share_detail
                     where appointment_id = :appointment_id
                       and type = 'A'
                       and (id not in (select original_id from profit_share_detail where appointment_id = :v_appointment_id and type = 'V'))
                ", [
                  'created_by' => $created_by,
                  'cdate' => Carbon::now(),
                  'appointment_id' => $app->appointment_id,
                  'v_appointment_id' => $app->appointment_id
                ]);

                //Reverse Groomer Referals
                DB::insert("
                    insert into profit_share(
                        appointment_id,
                        type,
                        sub_total,
                        groomer_id,
                        groomer_profit_ratio,
                        groomer_fee,
                        groomer_sameday_earning,
                        groomer_fav_earning,
                        groomer_profit_amt,
                        remaining_amt,
                        exception_groomer_id,
                        exception_user_id,
                        orig_profit_ratio,
                        app_pet_type,
                        app_pet_qty,
                        app_package_type,
                        app_package_amt,
                        app_addon_amt,
                        app_sub_total,
                        app_promo_type,
                        app_promo_code,
                        app_promo_amt,
                        app_credit_amt,
                        app_new_credit,
                        app_safety_insurance,
                        app_sameday_booking,
                        app_fav_groomer_fee,
                        app_total,
                        app_tax,
                        app_tip,
                        app_groupon_amt,
                        app_number,
                        comments, 
                        created_by, 
                        cdate,
                        original_id)
                    select 
                        appointment_id,
                        'L',
                        -sub_total,
                        groomer_id,
                        groomer_profit_ratio,
                        -groomer_fee,
                        -groomer_sameday_earning,
                        -groomer_fav_earning,
                        -groomer_profit_amt,
                        -remaining_amt,
                        exception_groomer_id,
                        exception_user_id,
                        orig_profit_ratio,
                        app_pet_type,
                        -app_pet_qty,
                        app_package_type,
                        -app_package_amt,
                        -app_addon_amt,
                        -app_sub_total,
                        app_promo_type,
                        app_promo_code,
                        -app_promo_amt,
                        -app_credit_amt,
                        -app_new_credit,
                        -app_safety_insurance,
                        -app_sameday_booking,
                        -app_fav_groomer_fee,                        
                        -app_total,
                        -app_tax,
                        -app_tip,
                        -app_groupon_amt,
                        app_number,
                        'Reverse Referal before update',
                        :created_by,
                        :cdate,
                        id
                      from profit_share
                     where appointment_id = :appointment_id
                       and type = 'R'
                       and (id not in (select original_id from profit_share where appointment_id = :v_appointment_id and type ='L'))
                ", [
                    'created_by' => $created_by,
                    'cdate' => Carbon::now(),
                    'appointment_id' => $app->appointment_id,
                    'v_appointment_id' => $app->appointment_id
                ]);


            }



            $appointment_products = AppointmentProduct::where('appointment_id', $app->appointment_id)
              ->whereRaw("prod_id in (select prod_id from product where prod_type = 'P')")
              ->get();

            $total_groomer_profit_amt = 0;
            $total_groomer_fee = 0;
            $pet_type = '';
            $is_eco = false;

            foreach ($appointment_products as $o) {
                if (in_array($o->prod_id, [28, 29])) {
                    $is_eco = true;
                }

                $ret = self::getProfitSharingRatio($o->prod_id, $app->user_id, $app->groomer_id);
                if (!empty($ret['msg'])) {
                    DB::rollback();
                    return $ret['msg'];
                }

                $groomer_profit_ratio = $ret['ratio'];
                $ex_groomer_id = $ret['ex_groomer_id'];
                $ex_user_id = $ret['ex_user_id'];
                $orig_ratio = $ret['orig_ratio'];

//                ### GROOMER PROMOTION ### ADD 5% ### START #
//                # Increase Groomer ratios by 5% for this period of service date: 12/17 ~ 12/31
//                # --
//                if ($app->accepted_date >= '2018-12-17' && $app->accepted_date < '2019-01-01') {
//                    $groomer_profit_ratio = $groomer_profit_ratio + 5;
//                }
//                ### GROOMER PROMOTION ### ADD 5% ### END #
//
//                ### GROOMER PROMOTION ### SET FOR Soudeh Alimashrab(#39) ### START #
//                # 110% Groomer profit for Soudeh Alimashrab(#39) for Jan,2019
//                # 90% Groomer profit for Soudeh Alimashrab(#39) for Feb,2019
//                # --
//                if ($app->groomer_id == 39) {
//                    if ($app->accepted_date >= '2019-01-01' && $app->accepted_date < '2019-02-01') {
//                        $groomer_profit_ratio = 110;
//                    }
//
//                    if ($app->accepted_date >= '2019-02-01' && $app->accepted_date < '2019-03-01') {
//                        $groomer_profit_ratio = 90;
//                    }
//                }
//                ### GROOMER PROMOTION ### SET FOR Soudeh Alimashrab(#39) ### END #
//

                $sub_total = AppointmentProduct::where('appointment_id', $app->appointment_id)
                  ->where('pet_id', $o->pet_id)
                  ->sum('amt');

                $addon_cnt = AppointmentProduct::join('product', 'appointment_product.prod_id', '=', 'product.prod_id')
                    ->where('appointment_product.appointment_id', $app->appointment_id)
                    ->where('appointment_product.pet_id', $o->pet_id)
                    ->where('product.prod_type', 'A')
                    ->count();

                $groomer_profit_amt = $sub_total * $groomer_profit_ratio / 100;

                $total_groomer_profit_amt += $groomer_profit_amt;

                $psd = new ProfitShareDetail;
                $psd->appointment_id = $app->appointment_id;
                $psd->type = 'A';
                $psd->package_id = $o->prod_id;
                $psd->sub_total = $sub_total;
                $psd->groomer_id = $app->groomer_id;
                $psd->groomer_profit_ratio = $groomer_profit_ratio;
                $psd->groomer_fee = ($addon_cnt >= 1 ) ? 2 : 1;
                $psd->groomer_profit_amt = $groomer_profit_amt - $psd->groomer_fee;
                $psd->remaining_amt = $sub_total - $psd->groomer_profit_amt;

                $psd->exception_groomer_id = $ex_groomer_id;
                $psd->exception_user_id = $ex_user_id;
                $psd->orig_profit_ratio = $orig_ratio;

                $psd->created_by = $created_by;
                $psd->cdate = Carbon::now();
                $psd->save();

                $total_groomer_fee = $total_groomer_fee + $psd->groomer_fee;

                $product = Product::find($psd->package_id);
                $pet_type = $product->pet_type;
                $package_type = $product->prod_name;
            } //end foreach

            $pet_qty = AppointmentPet::where('appointment_id', $app->appointment_id)->count();
            if ($pet_qty > 1) {
                $package_type = "Multi";
            }

            $package_amt = AppointmentProduct::where('appointment_id', $app->appointment_id)
              ->whereRaw('prod_id in (select prod_id from product where prod_type = \'P\')')
              ->sum('amt');

            $addon_amt = AppointmentProduct::where('appointment_id', $app->appointment_id)
              ->whereRaw('prod_id in (select prod_id from product where prod_type = \'A\')')
              ->sum('amt');

            $b_app_cnt = AppointmentList::where('user_id', $app->user_id)
                ->where('status', 'P')
                ->where('accepted_date', '<', $app->accepted_date)
                ->count();

            if (empty($b_app_cnt)) {
                $b_app_cnt = 0;
            }

            $now = Carbon::now();
            $p_sdate = '2019-06-22';
            $p_edate = '2019-12-22';
            $p_ratio = 0;
            $p_amt = 0;

            if ($now > $p_sdate && $now < $p_edate) {
                # #1 ECO: 15% More
//                if ($is_eco) {
//                    if ($app->cdate >= $p_sdate) {
//                        $p_ratio += 15;
//                    }
//                }
//Ended at 11/21/2019
//                } else if ( ( ($now->dayOfWeek == 0) || ($now->dayOfWeek == 6) ) &&
//                              ($app->cdate >= $p_sdate) &&
//                            ( Carbon::parse($app->accepted_date)->format('Y-m-d') == Carbon::parse($app->cdate)->format('Y-m-d') )
//                          ){
//                    $p_ratio += 5;
//                }
//Not started
//              } else if ($now->dayOfWeek == 0 ) {
//                    $p_ratio += 5;
//              }else if (Carbon::now()->format('HH:mm') < '09:00' || Carbon::now()->format('HH:mm') > '19:00') {
//                    $p_ratio += 5;
//               }

                if ($p_ratio > 0) {
                    $p_amt = $app->sub_total * $p_ratio / 100;

                    $psd = new ProfitShareDetail;
                    $psd->appointment_id = $app->appointment_id;
                    $psd->type          = 'A';
                    $psd->package_id    = $o->prod_id;
                    $psd->sub_total     = $app->sub_total;
                    $psd->groomer_id    = $app->groomer_id;
                    $psd->groomer_profit_ratio = $p_ratio;
                    $psd->groomer_profit_amt = $p_amt;
                    $psd->remaining_amt = -$psd->groomer_profit_amt;

                    $psd->exception_groomer_id = $ex_groomer_id;
                    $psd->exception_user_id = $ex_user_id;
                    $psd->orig_profit_ratio = $orig_ratio;

                    $psd->created_by = $created_by;
                    $psd->cdate = Carbon::now();
                    $psd->save();
                }
            }


            $p_sdate2 = '2019-11-21';
            $p_edate2 = '2020-01-01';
            $p_ratio2 = 0;
            $p_amt2 = 0;

            if ($app->cdate > $p_sdate2 && $app->cdate < $p_edate2) {
                $mins_diff = 100; //by default, not pay $5.
                $vgl = VWGroomerAssignLog::where('appointment_id', $app->appointment_id)
                    ->where('groomer_id', $app->groomer_id)
                    ->first([
                        'groomer_assign_date',
                        DB::raw('TIMESTAMPDIFF(MINUTE, cdate, groomer_assign_date) as mins_diff')
                    ]);
                if (!empty($vgl)) {
                    $mins_diff = $vgl->mins_diff ;
                }

                if ($mins_diff < 10 ) {
                    $p_ratio2 = round(5/$app->sub_total * 100,2) ; //Pay $5 in case accept within 10 minutes.
                    $p_amt2 = 5;
                }

                if ($p_ratio2 > 0) {
                    $p_amt2 = 5 ;

                    $psd = new ProfitShareDetail;
                    $psd->appointment_id = $app->appointment_id;
                    $psd->type          = 'A';
                    $psd->package_id    = $o->prod_id;
                    $psd->sub_total     = $app->sub_total;
                    $psd->groomer_id    = $app->groomer_id;
                    $psd->groomer_profit_ratio = $p_ratio2;
                    $psd->groomer_profit_amt = $p_amt2;
                    $psd->remaining_amt = -$psd->groomer_profit_amt;

                    $psd->exception_groomer_id = $ex_groomer_id;
                    $psd->exception_user_id = $ex_user_id;
                    $psd->orig_profit_ratio = $orig_ratio;

                    $psd->created_by = $created_by;
                    $psd->cdate = Carbon::now();
                    $psd->save();
                }
            }

            $p_sdate2 = '2020-01-01';
            $p_edate2 = '2020-02-01'; //removed since 02/01/2020.
            $p_amt3 = 0;
            $p_ratio3 = 0;

            if ($app->cdate >= $p_sdate2 && $app->cdate < $p_edate2) {
                $checkin_datetime = $now;
                $accepted_date = Carbon::parse($app->accepted_date);
                if(!empty($app->check_in)){
                    $checkin_datetime = Carbon::parse($app->check_in);
                }

//                Helper::log('#### Time Diff ####', $checkin_datetime );
//                Helper::log('#### Time Diff ####', $app->accepted_date );
//                Helper::log('#### Time Diff ####', $accepted_date->diffInMinutes($checkin_datetime, false) );
                if( $accepted_date->diffInMinutes($checkin_datetime, false) < 5) {   // it returns r_date - now, with plus/minus
                    $p_ratio3 = round(5 / $app->sub_total * 100, 2); //Pay $5 in case arrive in 5 mins.
                    $p_amt3 = 5;

                    $psd = new ProfitShareDetail;
                    $psd->appointment_id = $app->appointment_id;
                    $psd->type = 'A';
                    $psd->package_id = $o->prod_id;
                    $psd->sub_total = $app->sub_total;
                    $psd->groomer_id = $app->groomer_id;
                    $psd->groomer_profit_ratio = $p_ratio3;
                    $psd->groomer_profit_amt = $p_amt3;
                    $psd->remaining_amt = -$psd->groomer_profit_amt;

                    $psd->exception_groomer_id = $ex_groomer_id;
                    $psd->exception_user_id = $ex_user_id;
                    $psd->orig_profit_ratio = $orig_ratio;

                    $psd->created_by = $created_by;
                    $psd->cdate = Carbon::now();
                    $psd->save();

                }
            }



            $ps = new ProfitShare;
            $ps->appointment_id = $app->appointment_id;
            $ps->type = 'A';
            $ps->sub_total          = $app->sub_total;
            $ps->groomer_id         = $app->groomer_id;
            $total_groomer_profit_ratio = $total_groomer_profit_amt / $app->sub_total * 100 + $p_ratio + $p_ratio2 + $p_ratio3;
            $ps->groomer_profit_ratio = $total_groomer_profit_ratio;
            $ps->groomer_fee        = $total_groomer_fee;

            $now = Carbon::now();
            if ($now >= '2020-03-01') {
                $ps->groomer_sameday_earning  = $app->sameday_booking * 0.50; // changed since 03/01/2020
            }else {
                $ps->groomer_sameday_earning  = $app->sameday_booking * 0.65; // changed since 12/05/2019 0.75; //$15 out of $20 of samedaybooking
            }
            $ps->groomer_fav_earning = ( ($app->fav_groomer_fee > 0) && ($app->groomer_id == $app->my_favorite_groomer)) ? $app->fav_groomer_fee * 0.50 : 0;
            
            //$ps->groomer_profit_amt = $total_groomer_profit_amt - $ps->groomer_fee + $p_amt + $p_amt2 +  $p_amt3 +  $ps->groomer_sameday_earning + $ps->groomer_fav_earning;
            $ps->groomer_profit_amt = $total_groomer_profit_amt - $ps->groomer_fee + $ps->groomer_sameday_earning + $ps->groomer_fav_earning ;
            $ps->remaining_amt      = $ps->sub_total - $ps->groomer_profit_amt;

            $ps->app_pet_type       = $pet_type;
            $ps->app_pet_qty        = $pet_qty;
            $ps->app_package_type   = $package_type;
            $ps->app_package_amt    = $package_amt;
            $ps->app_addon_amt      = $addon_amt;
            $ps->app_sub_total      = $app->sub_total;
            $ps->app_promo_code     = $app->promo_code;
            $ps->app_promo_amt      = $app->promo_amt;
            $ps->app_credit_amt     = $app->credit_amt;
            $ps->app_new_credit     = $app->new_credit;
            $ps->app_safety_insurance = $app->safety_insurance;
            $ps->app_sameday_booking = $app->sameday_booking;
            $ps->app_fav_groomer_fee = $app->fav_groomer_fee;
            $ps->app_total          = $app->total;
            $ps->app_tax            = $app->tax;
            $ps->app_tip            = $app->tip;

            // Count Numbering Appointment
            $ps->app_number         = $b_app_cnt + 1;

            $ps->app_promo_type = '';
            $ps->app_groupon_amt    = 0;
            if (!empty($ps->app_promo_code)) {
                $code = PromoCode::whereRaw("code = '" . strtoupper($ps->app_promo_code) . "'")->first();
                if (!empty($code)) {
                    $ps->app_promo_type = $code->type;
                    switch ($code->type) {
                        case 'G':
                            $ps->app_groupon_amt = $code->total_month > 1 ? $code->groupon_amt / $code->total_month : $code->groupon_amt;
                            break;
                        case 'T':
                            $ps->app_groupon_amt = $code->groupon_amt;
                            break;
                        case 'K':
                            if ($app->new_credit > 0) {
                                $ps->app_promo_amt = $code->amt + $app->credit_amt - $app->new_credit;
                            }
                            break;
                    }
                }
            }

            $ps->created_by = $created_by;
            $ps->cdate = $is_mig ? $app->accepted_date : Carbon::now();
            $ps->save();

            ### Create Affiliate
            AffiliateRedeemHistory::add($app);

            ### Create Groomer Affiliate
            //ProfitShare::create_groomer_referral($app);
            CreditProcessor::giveReferralGroomerCredit($app);
            DB::commit();

            return '';

        } catch (\Exception $ex) {
            Helper::log('#### EXCEPTION ####', $ex->getTraceAsString());

            DB::rollback();
            return $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
        }
    }

    private static function getProfitSharingRatio($package_id, $user_id, $groomer_id) {

        $setup = ProfitSharingSetup::find($package_id);
        if (empty($setup)) {
            return [
                'msg' => 'Something is wrong. Profit sharing setup record is empty'
            ];
        }

        ### find user exception if exists ###
        $user_ex = ProfitSharingExceptionUser::where('user_id', $user_id)
            ->where('groomer_id', $groomer_id)
            ->where('package_id', $package_id)
            ->first();

        if (!empty($user_ex)) {
            return [
                'msg' => '',
                'ratio' => $user_ex->groomer_profit,
                'ex_groomer_id' => $user_ex->groomer_id,
                'ex_user_id' => $user_ex->id,
                'orig_ratio' => $setup->groomer_profit
            ];
        }

        ### find groomer exception if exists ###
        $groomer_ex = ProfitSharingExceptionGroomer::where('groomer_id', $groomer_id)
            ->where('package_id', $package_id)
            ->first();
        if (!empty($groomer_ex)) {

            return [
                'msg' => '',
                'ratio' => $groomer_ex->groomer_profit,
                'ex_groomer_id' => $groomer_ex->groomer_id,
                'ex_user_id' => null,
                'orig_ratio' => $setup->groomer_profit
            ];
        }

        ### return normal setup ###
        return [
            'msg' => '',
            'ratio' => $setup->groomer_profit,
            'ex_groomer_id' => null,
            'ex_user_id' => null,
            'orig_ratio' => $setup->groomer_profit
        ];
    }

    public static function get_groomer_profit_ratio($package_id, $user_id, $groomer_id) {
        return self::getProfitSharingRatio($package_id, $user_id, $groomer_id);
    }

}