<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 3/29/18
 * Time: 3:01 PM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\Groomer;
use App\Model\VWGroomerEvaluation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

//use DB;

class EvaluationController extends Controller
{

    public function show(Request $request) {

        $sdate = Carbon::today()->addDays(-7);
        $edate = Carbon::today()->addDays(1)->addSeconds(-1);

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $groomers = //Groomer::whereIn('status', ['A','N'])->orderBy('first_name', 'asc','last_name','asc')->get();
            //remove status limiation, because needs to look up old groomers too.
            Groomer::orderBy('first_name', 'asc','last_name','asc')->get();

        $counties = DB::select("
            select distinct county_name, state_abbr
            from allowed_zip
            where lower(available) = 'x' 
            order by 2, 1
        ");

        $query_groomer = '';
        if (!empty ($request->groomer_id)) {
            $query_groomer = " and g.groomer_id = " . $request->groomer_id . " ";
        }
        $query_county = '';
        if (!empty($request->county)) {
            $query_county = "
                and g.zip in (  
                   select zip 
                   from allowed_zip
                   where concat(county_name, '/', state_abbr) = '" . $request->county . "'
                   and lower(available) = 'x'
                )
            ";
        }

        $query_accepted_by = '';
        $sum_query_accepted_by = '';
        if (empty($request->accepted_by)) {
            $request->accepted_by = '';
        }
        if($request->accepted_by == 'G'){
            $query_accepted_by = 'IfNull(accept.groomer_accept, 0 ) accept_total  ';
            $sum_query_accepted_by = ' sum( IfNull(groomer_accept,0) ) as accept_total_qty ';
        }else if($request->accepted_by == 'C'){
            $query_accepted_by = 'IfNull(accept.cs_accept, 0 ) accept_total ';
            $sum_query_accepted_by = ' sum( IfNull(cs_accept,0) ) as accept_total_qty ';
        }else {
            $query_accepted_by = 'IfNull(accept.groomer_accept, 0 ) + IfNull(accept.cs_accept, 0 )  accept_total';
            $sum_query_accepted_by = ' sum( IfNull(groomer_accept,0) + IfNull(cs_accept,0) ) as accept_total_qty ';
        }

        $data = DB::select("
             select g.groomer_id, 
                concat(g.first_name, ' ', g.last_name) as groomer_name,
                g.weekly_allowance,
                va.hours_total , 
                 $query_accepted_by ,
                sum(v.appointment_qty) appointment_qty, 
                sum(v.delayed_qty) delayed_qty, 
                sum(v.delayed_min) delayed_min,
                sum(v.sub_total) sub_total, 
                sum(v.tip) tip, 
                sum(v.adjust) adjust, 
                sum(v.groomer_fee) groomer_fee,
                sum(v.payout) payout, 
                sum(v.promo_amt) promo_amt,
                sum(v.fa_groomer_qty) fa_groomer_qty,
                sum(v.rate_score) rate_score,
                sum(v.rate_qty) rate_qty
               from groomer g
               left join vw_groomer_evaluation v on g.groomer_id = v.groomer_id and v.cdate >= :sdate and v.cdate < :edate
               left join (
                    select groomer_id, sum(1) hours_total
                      from groomer_availability
                     where `date` >= :sdate2 and `date` < :edate2
                     group by 1
               ) va on g.groomer_id = va.groomer_id
               left join (
                   select ac.groomer_id, sum(CASE ac.by_type when 'G' then 1 else 0 end ) groomer_accept, sum(CASE ac.by_type when 'C' then 1 else 0 end ) cs_accept
                      from groomer_accept_history ac inner join appointment_list ap on ac.appointment_id = ap.appointment_id and ac.groomer_id = ap.groomer_id and ac.accepted_date = ap.accepted_date and ap.status not in ( 'C','L' ) 
                     where ac.cdate >= :sdate3 and ac.cdate < :edate3 
                     group by 1
               ) accept on g.groomer_id = accept.groomer_id
              where g.status = 'A'
                $query_groomer
                $query_county
              group by 1,2,3,4,5
              order by 6 desc
        ", [
            'sdate' => $sdate,
            'edate' => $edate,
            'sdate2' => $sdate,
            'edate2' => $edate,
            'sdate3' => $sdate,
            'edate3' => $edate
        ]);

        $data_summary =  DB::select("
            select 
                sum(weekly_allowance) as weekly_allowance,
                sum(hours_total) hours_total,
                $sum_query_accepted_by,
                sum(appointment_qty) as appointment_qty, 
                sum(delayed_qty) delayed_qty, 
                sum(delayed_min) delayed_min,
                sum(sub_total) sub_total, 
                sum(tip) tip, 
                sum(adjust) adjust, 
                sum(groomer_fee) groomer_fee,
                sum(payout) payout, 
                sum(promo_amt) promo_amt,
                sum(fa_groomer_qty) fa_groomer_qty,
                sum(rate_score) rate_score,
                sum(rate_qty) rate_qty
              from (
             select g.groomer_id, 
                concat(g.first_name, ' ', g.last_name) as groomer_name,
                g.weekly_allowance,
                va.hours_total ,
                IfNull(accept.groomer_accept, 0 ) groomer_accept,
                IfNull(accept.cs_accept, 0 ) cs_accept,
                sum(ifnull(v.appointment_qty, 0)) appointment_qty, 
                sum(v.delayed_qty) delayed_qty, 
                sum(v.delayed_min) delayed_min,
                sum(v.sub_total) sub_total, 
                sum(v.tip) tip, 
                sum(v.adjust) adjust,
                sum(v.groomer_fee) groomer_fee, 
                sum(v.payout) payout, 
                sum(v.promo_amt) promo_amt,
                sum(v.fa_groomer_qty) fa_groomer_qty,
                sum(v.rate_score) rate_score,
                sum(v.rate_qty) rate_qty
               from groomer g
               left join vw_groomer_evaluation v on g.groomer_id = v.groomer_id and v.cdate > :sdate and v.cdate < :edate
               left join (
                    select groomer_id, sum(1) hours_total
                      from groomer_availability
                     where date >= :sdate2 and date < :edate2
                     group by groomer_id
               ) va on g.groomer_id = va.groomer_id
                left join (
                    select ac.groomer_id, sum(CASE ac.by_type when 'G' then 1 else 0 end ) groomer_accept, sum(CASE ac.by_type when 'C' then 1 else 0 end ) cs_accept
                      from groomer_accept_history ac inner join appointment_list ap on ac.appointment_id = ap.appointment_id and ac.groomer_id = ap.groomer_id and ac.accepted_date = ap.accepted_date and ap.status not in ( 'C','L' ) 
                     where ac.cdate >= :sdate3 and ac.cdate < :edate3 
                     group by 1
               ) accept on g.groomer_id = accept.groomer_id
              where g.status = 'A'
                $query_groomer
                $query_county
              group by 1,2,3,4,5
              ) t
        ", [
          'sdate' => $sdate,
          'edate' => $edate,
          'sdate2' => $sdate,
          'edate2' => $edate,
          'sdate3' => $sdate,
          'edate3' => $edate
        ]);


        if ($request->excel == 'Y') {
            Helper::log('########### EXCEL #############');

            Excel::create('groomer-evaluation', function($excel) use($data, $data_summary) {

                $excel->sheet('reports', function($sheet) use($data, $data_summary) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                          'Groomer.ID' => $o->groomer_id,
                          'Groomer.Name' => $o->groomer_name,
                          'Accept.Qty' => $o->accept_total,
                          'Complete.Qty' => $o->appointment_qty,
                          'Hours.Available' => $o->hours_total,
                          'Sub.Total' => $o->sub_total,
                          'Promo.Paid' => $o->promo_amt,
                          'Tip' => $o->tip,
                          'Profit.Adjustment' => $o->adjust,
                          'Groomer.Fee' => $o->groomer_fee,
                          'Payout' => $o->payout,
                          'Weekly.Allowance' => $o->weekly_allowance,
                          'P/L' => $o->weekly_allowance > 0 || $o->payout > 0 ? number_format($o->payout - $o->weekly_allowance, 2) : '-'
                        ];
                    }

                    foreach ($data_summary as $o) {
                        $reports[] = [
                          'Groomer.ID' => '',
                          'Groomer.Name' => 'Total',
                           'Accept.Qty' => $o->accept_total_qty,
                          'Complete.Qty' => $o->appointment_qty,
                          'Hours.Available' => $o->hours_total,
                          'Sub.Total' => $o->sub_total,
                          'Promo.Paid' => $o->promo_amt,
                          'Tip' => $o->tip,
                          'Profit.Adjustment' => $o->adjust,
                          'Groomer.Fee' => $o->groomer_fee,
                          'Payout' => $o->payout,
                          'Weekly.Allowance' => $o->weekly_allowance,
                          'P/L' => $o->weekly_allowance > 0 || $o->payout > 0 ? number_format($o->payout - $o->weekly_allowance, 2) : '-'
                        ];
                    }


                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $data = Helper::arrayPaginator($data, $request);

        return view('admin.reports.groomer-evaluation', [
            'groomer_id' => $request->groomer_id,
            'sdate' => $sdate,
            'edate' => $edate,
            'groomers' => $groomers,
            'counties' => $counties,
            'county' => $request->county,
            'accepted_by' => $request->accepted_by,
            'data' => $data,
            'data_summary' => $data_summary,
        ]);

    }

    public function rating_link($g_id, $u_id) {
        $sdate = Carbon::today()->modify('-5 years');
        $edate = Carbon::today()->addDays(1)->addSeconds(-1);

        $query = VWGroomerEvaluation::join('groomer', 'vw_groomer_evaluation.groomer_id', '=', 'groomer.groomer_id')
            ->where('vw_groomer_evaluation.rate_qty', '>', 0)
            ->where('vw_groomer_evaluation.cdate', '>', $sdate)
            ->where('vw_groomer_evaluation.cdate', '<', $edate)
            ->where('vw_groomer_evaluation.type', 'A')
            ->whereRaw('vw_groomer_evaluation.id not in (select original_id from profit_share where type = \'V\')');

        if (!empty($g_id)) {
            $query->where('vw_groomer_evaluation.groomer_id', $g_id);
        }

        if (!empty($u_id)) {
            $query->where('vw_groomer_evaluation.user_id', $u_id);
        }

        $total = new \stdClass();
        $total->qty = $query->count();
        $total->score = $query->sum('vw_groomer_evaluation.rate_score');
        $total->sub_total = $query->sum('sub_total');

        $data =  $query->orderBy('vw_groomer_evaluation.cdate', 'desc')
            ->paginate(20, [
                'vw_groomer_evaluation.cdate',
                'vw_groomer_evaluation.appointment_id',
                'vw_groomer_evaluation.groomer_id',
                DB::raw('concat(groomer.first_name, \' \', groomer.last_name) as groomer_name'),
                'vw_groomer_evaluation.user_id',
                'vw_groomer_evaluation.user_name',
                'vw_groomer_evaluation.rate_score',
                'vw_groomer_evaluation.app_pet_type',
                'vw_groomer_evaluation.sub_total',
            ]);

        $groomers = //Groomer::whereIn('status', ['A','N'])->orderBy('first_name', 'asc','last_name','asc')->get();
            Groomer::orderBy('first_name', 'asc','last_name','asc')->get();
        return view('admin.reports.groomer-rating')->with([
            'groomer_id' => $g_id,
            'sdate' => $sdate,
            'edate' => $edate,
            'pet_type' => '',
            'groomers' => '',
            'data' => $data,
            'total' => $total,
            'cust_id' => $u_id,
            'cust_name' => '',
            'groomers' => $groomers
        ]);

    }

    public function rating(Request $request) {

        $sdate = Carbon::today()->modify('-5 years');
        $edate = Carbon::today()->addDays(1)->addSeconds(-1);

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $groomers = Groomer::whereIn('status', ['A','N'])->orderBy('first_name', 'asc','last_name','asc')->get();

        $query = VWGroomerEvaluation::join('groomer', 'vw_groomer_evaluation.groomer_id', '=', 'groomer.groomer_id')
          ->where('vw_groomer_evaluation.rate_qty', '>', 0)
          ->where('vw_groomer_evaluation.cdate', '>', $sdate)
          ->where('vw_groomer_evaluation.cdate', '<', $edate)
          ->where('vw_groomer_evaluation.type', 'A')
          ->whereRaw('vw_groomer_evaluation.id not in (select original_id from profit_share where type = \'V\')');

        if (!empty($request->groomer_id)) {
            $query->where('vw_groomer_evaluation.groomer_id', $request->groomer_id);
        }

        if (!empty($request->pet_type)) {
            $query->where(DB::raw('lower(app_pet_type)'), strtolower($request->pet_type));
        }

        if (!empty($request->cust_id)) {
            $query->where('vw_groomer_evaluation.user_id', $request->cust_id);
        }

        if (!empty($request->cust_name)) {
            $query->where(DB::raw('lower(vw_groomer_evaluation.user_name)'), 'like', '%' . strtolower($request->cust_name) . '%');
        }

        $total = new \stdClass();
        $total->qty = $query->count();
        $total->score = $query->sum('vw_groomer_evaluation.rate_score');
        $total->sub_total = $query->sum('sub_total');
        if ($request->excel == 'Y') {
            Helper::log('########### EXCEL #############');

            $data =  $query->orderBy('vw_groomer_evaluation.cdate', 'desc')
              ->get([
                'vw_groomer_evaluation.cdate',
                'vw_groomer_evaluation.appointment_id',
                'vw_groomer_evaluation.groomer_id',
                DB::raw('concat(groomer.first_name, \' \', groomer.last_name) as groomer_name'),
                'vw_groomer_evaluation.user_id',
                'vw_groomer_evaluation.user_name',
                'vw_groomer_evaluation.rate_score',
                'vw_groomer_evaluation.app_pet_type',
                'vw_groomer_evaluation.sub_total',
              ]);

            Excel::create('groomer-rating', function($excel) use($data, $total) {

                $excel->sheet('reports', function($sheet) use($data, $total) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                          'Date' => $o->cdate,
                          'Appointment #' => $o->appointment_id,
                          'Groomer' => $o->groomer_id . ', ' . $o->groomer_name,
                          'Customer' => $o->user_id . ', ' . $o->user_name,
                          'Pet.Type' => $o->app_pet_type,
                          'Sub.Total' => $o->sub_total,
                          'Rating' => $o->rate_score
                        ];
                    }

                    $reports[] = [
                      'Date' => '',
                      'Appointment #' => '',
                      'Groomer' => '',
                      'Customer' => '',
                      'Pet.Type' => '',
                      'Sub.Total' => $o->sub_total,
                      'Rating' => $o->rate_score
                    ];


                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }


        $data =  $query->orderBy('vw_groomer_evaluation.cdate', 'desc')
            ->paginate(20, [
                'vw_groomer_evaluation.cdate',
                'vw_groomer_evaluation.appointment_id',
                'vw_groomer_evaluation.groomer_id',
                DB::raw('concat(groomer.first_name, \' \', groomer.last_name) as groomer_name'),
                'vw_groomer_evaluation.user_id',
                'vw_groomer_evaluation.user_name',
                'vw_groomer_evaluation.rate_score',
                'vw_groomer_evaluation.app_pet_type',
                'vw_groomer_evaluation.sub_total',
            ]);

        return view('admin.reports.groomer-rating')->with([
            'groomer_id' => $request->groomer_id,
            'sdate' => $sdate,
            'edate' => $edate,
            'pet_type' => $request->pet_type,
            'groomers' => $groomers,
            'data' => $data,
            'total' => $total,
            'cust_id' => $request->cust_id,
            'cust_name' => $request->cust_name
        ]);
    }

}