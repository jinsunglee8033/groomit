<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 4/9/18
 * Time: 10:51 AM
 */

namespace App\Lib;


use App\Model\AppointmentList;
use App\Model\ProfitShare;
use App\Model\ProfitSharing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EarningProcessor
{

    public static function getHistory($type, $groomer_id, $from, $to) {
        switch ($type) {
            case 'W':
                $query = "
                        select 
                            concat(year(sdate), 'W', lpad(week(sdate, 7), 2, '0')) as category, 
                            sdate period_from,
                            DATE_ADD(sdate, interval 6 day) period_to,
                            min(cdate) as min_date,
                            max(cdate) as max_date,
                            sum(0) as promo,
                            sum(groomer_profit_amt) as earning,
                            sum(groomer_fee) as fee,
                            sum(case type when 'A' then 1 when 'W' then 1 when 'V' then -1 else 0 end) as appt,
                            sum(case type when 'C' then 1 else 0 end) as credit,
                            sum(case type when 'D' then 1 else 0 end) as debit,
                            sum(if(type = 'T', 1, 0)) as tip,
                            sum(case type when 'A' then groomer_profit_amt when 'W' then groomer_profit_amt when 'V' then -groomer_profit_amt else 0 end) as amt_appt,
                            sum(case type when 'C' then groomer_profit_amt else 0 end) as amt_credit,
                            sum(case type when 'D' then groomer_profit_amt else 0 end) as amt_debit,
                            sum(if(type = 'T', groomer_profit_amt, 0)) as amt_tip 
                        from (select cast(DATE_ADD(cdate, INTERVAL 1-wd DAY) as date) sdate, t.*
                              from (
                            select 
                                case 
                                    when DAYOFWEEK(cdate) > 1 then DAYOFWEEK(cdate) -1
                                    else 7
                                end wd, 
                                profit_share.*
                            from profit_share
                            ) t) t
                        where cdate >= :from
                        and cdate < :to + interval 1 day
                        and groomer_id = :groomer_id
                        group by 1, 2, 3
                        order by 1 desc
                    ";
                break;
            case 'M':
                $query = "
                        select 
                            concat(year(cdate), 'M', lpad(month(cdate), 2, '0')) as category, 
                            str_to_date(concat(year(cdate), lpad(month(cdate), 2, '0'), '01'), '%Y%m%d') as period_from,
                            str_to_date(concat(year(cdate), lpad(month(cdate) + 1, 2, '0'), '01'), '%Y%m%d') - interval 1 day as period_to,
                            min(cdate) as min_date,
                            max(cdate) as max_date,
                            sum(0) as promo,
                            sum(groomer_profit_amt) as earning,
                            sum(groomer_fee) as fee,
                            sum(case type when 'A' then 1 when 'W' then 1 when 'V' then -1 else 0 end) as appt,
                            sum(case type when 'C' then 1 else 0 end) as credit,
                            sum(case type when 'D' then 1 else 0 end) as debit,
                            sum(if(type = 'T', 1, 0)) as tip,
                            sum(case type when 'A' then groomer_profit_amt when 'W' then groomer_profit_amt when 'V' then -groomer_profit_amt else 0 end) as amt_appt,
                            sum(case type when 'C' then groomer_profit_amt else 0 end) as amt_credit,
                            sum(case type when 'D' then groomer_profit_amt else 0 end) as amt_debit,
                            sum(if(type = 'T', groomer_profit_amt, 0)) as amt_tip 
                        from profit_share
                        where cdate >= :from
                        and cdate < :to + interval 1 day
                        and groomer_id = :groomer_id
                        group by 1, 2, 3
                        order by 1 desc
                    ";
                break;
            case 'Y':
                $query = "
                        select 
                            year(cdate) as category, 
                            str_to_date(concat(year(cdate), '0101'), '%Y%m%d') as period_from,
                            str_to_date(concat(year(cdate) + 1, '0101'), '%Y%m%d') - interval 1 day as period_to,
                            #DATE_ADD(STR_TO_DATE( CONCAT(YEAR(cdate), month(cdate), ' Sunday'), '%X%V %W'), interval 6 day) period_to,
                            min(cdate) as min_date,
                            max(cdate) as max_date,
                            sum(0) as promo,
                            sum(groomer_profit_amt) as earning,
                            sum(groomer_fee) as fee ,
                            sum(case type when 'A' then 1 when 'W' then 1 when 'V' then -1 else 0 end) as appt,
                            sum(case type when 'C' then 1 else 0 end) as credit,
                            sum(case type when 'D' then 1 else 0 end) as debit,
                            sum(if(type = 'T', 1, 0)) as tip,
                            sum(case type when 'A' then groomer_profit_amt when 'W' then groomer_profit_amt when 'V' then -groomer_profit_amt else 0 end) as amt_appt,
                            sum(case type when 'C' then groomer_profit_amt else 0 end) as amt_credit,
                            sum(case type when 'D' then groomer_profit_amt else 0 end) as amt_debit,
                            sum(if(type = 'T', groomer_profit_amt, 0)) as amt_tip 
                        from profit_share
                        where cdate >= :from
                        and cdate < :to + interval 1 day
                        and groomer_id = :groomer_id
                        group by 1, 2, 3
                        order by 1 desc;
                    ";
                break;
            default:
                return response()->json([
                    'msg' => 'Invalid type provided'
                ]);
        }

        $data = DB::select($query, [
            'from' => $from,
            'to' => $to,
            'groomer_id' => $groomer_id
        ]);

        return $data;
    }

    public static function getCurrentEarning($groomer_id) {

        $current_week = ProfitShare::where('groomer_id', $groomer_id)
            ->where('cdate', '>=', Carbon::today()->startOfWeek())
            ->where('cdate', '<', Carbon::tomorrow())
            ->get();

        $app_cnt = 0;
        $total_rating = 0;
        $total_tip_cnt = 0;
        $total_earning = 0;
        foreach ($current_week as $o) {
            if ($o->type == 'A') {
                $app_cnt++;
            }

            if ($o->type == 'T') {
                ### get rating ###
                $ap = AppointmentList::find($o->appointment_id);
                $total_rating += is_null($ap->rating) ? 0 : $ap->rating;
                $total_tip_cnt++;
            }

            //$total_earning += ($o->type == 'D' ? -1 : 1) * $o->groomer_profit_amt;
            $total_earning += $o->groomer_profit_amt;
        }

        $rating = $total_rating / ($total_tip_cnt == 0 ? 1 : $total_tip_cnt);
        $current = new \stdClass();
        $current->appointments = $app_cnt;
        $current->promotion = 0;
        $current->rating = $rating;
        $current->earning = $total_earning;
        $current->tip = $total_tip_cnt;

        return $current;
    }

    public static function getLast3WeeksEarning($groomer_id) {
        $last_3_week = ProfitShare::where('groomer_id', $groomer_id)
            //->where('cdate', '<', Carbon::today()->startOfWeek())
            //->where('cdate', '>=', Carbon::today()->startOfWeek()->subDays(21))
            ->where('cdate', '>=', Carbon::today()->startOfWeek()->subDays(14))
            ->where('cdate', '<', Carbon::tomorrow())
            ->get();

        $app_cnt = 0;
        $total_rating = 0;
        $total_tip_cnt = 0;
        $total_earning = 0;
        foreach ($last_3_week as $o) {
            if ($o->type == 'A') {
                $app_cnt++;
            }

            if ($o->type == 'T') {
                ### get rating ###
                $ap = AppointmentList::find($o->appointment_id);
                $total_rating += is_null($ap->rating) ? 0 : $ap->rating;
                $total_tip_cnt++;
            }

            //$total_earning += ($o->type == 'D' ? -1 : 1) * $o->groomer_profit_amt;
            $total_earning += $o->groomer_profit_amt;
        }

        $rating = $total_rating / ($total_tip_cnt == 0 ? 1 : $total_tip_cnt);
        $last_3_week = new \stdClass();
        $last_3_week->appointments = $app_cnt;
        $last_3_week->promotion = 0;
        $last_3_week->rating = $rating;
        $last_3_week->earning = $total_earning;
        $last_3_week->tip = $total_tip_cnt;

        return $last_3_week;
    }

    public static function get_earning_detail($groomer_id, $from, $to) {
        $query = "
            select 
                cdate,
                appointment_id,
                type,
                case type 
                  when 'A' then 'Appointment'
                  when 'W' then 'Appointment'
                  when 'V' then 'Reverse Appointment'
                  when 'T' then 'Tip'
                  when 'C' then 'Credit'
                  when 'D' then 'Debit'
                  when 'J' then 'Adjust'
                  when 'R' then 'Referal'
                  when 'L' then 'Reverse Referal'
                  else type 
                end type_name,
                groomer_profit_amt as earning
            from profit_share
            where cdate >= :from
            and cdate < :to + interval 1 day
            and groomer_id = :groomer_id
            group by 1, 2, 3
            order by 1 desc
            ";

        $data = DB::select($query, [
          'from' => $from,
          'to' => $to,
          'groomer_id' => $groomer_id
        ]);

        return $data;
    }
}