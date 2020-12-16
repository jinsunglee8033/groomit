<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 3/5/18
 * Time: 2:54 PM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Model\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class AppointmentCycleController extends Controller
{
    public function show(Request $request) {
        $month = $request->get('month', Carbon::today()->addDays(-90)->format('Y-m'));

        $month_date = Carbon::createFromFormat('Y-m-d H:i:s', $month . '-01 00:00:00');

        $query = "
            select 
                a.user_id,
                case c.type 
                    when 'A' then 'Affiliate'
                    when 'B' then 'Affiliate'
                    when 'R' then 'Refer a Friend'
                    when 'N' then 'Normal'
                    when 'G' then 'Groupon'
                    when 'T' then 'Gilt'
                    when 'K' then 'Giftcard'
                    when 'S' then 'Membership'
                    else '-'
                end promo_code_type,
                ifnull(concat(a.first_name, ' ', a.last_name), '') as user_name,
                a.email,
                sum(if(b.appointment_id is not null and b.cdate >= :month1 and b.cdate < :month2 + interval 1 month, 1, 0)) as m1_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month3 + interval 1 month and b.cdate < :month4 + interval 2 month, 1, 0)) as m2_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month5 + interval 2 month and b.cdate < :month6 + interval 3 month, 1, 0)) as m3_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month7 + interval 3 month and b.cdate < :month8 + interval 4 month, 1, 0)) as m4_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month9 + interval 4 month and b.cdate < :month10 + interval 5 month, 1, 0)) as m5_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month11 + interval 5 month and b.cdate < :month12 + interval 6 month, 1, 0)) as m6_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month13 + interval 6 month and b.cdate < :month14 + interval 7 month, 1, 0)) as m7_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month15 + interval 7 month and b.cdate < :month16 + interval 8 month, 1, 0)) as m8_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month17 + interval 8 month and b.cdate < :month18 + interval 9 month, 1, 0)) as m9_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month19 + interval 9 month and b.cdate < :month20 + interval 10 month, 1, 0)) as m10_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month21 + interval 10 month and b.cdate < :month22 + interval 11 month, 1, 0)) as m11_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month23 + interval 11 month and b.cdate < :month24 + interval 12 month, 1, 0)) as m12_qty,
                sum(if(b.appointment_id is not null and b.cdate >= :month25 and b.cdate < :month26 + interval 12 month, 1, 0)) as total_qty,
                ifnull(min(b.cdate), '') as first_date,
                ifnull(max(b.cdate), '') as last_date,
                ifnull(datediff(max(b.cdate), min(b.cdate)), '') as date_diff,
                ifnull(datediff(max(b.cdate), min(b.cdate)) / if(sum(if(b.appointment_id is null, 0, 1)) > 1, sum(if(b.appointment_id is null, 0, 1)) - 1, sum(if(b.appointment_id is null, 0, 1))), '') as avg_days
            from user a
                left join appointment_list b on a.user_id = b.user_id
                    and b.status not in ('C', 'L')
                left join promo_code c on b.promo_code = c.code
            where a.user_id in ( select user_id from appointment_list where cdate >= :month99 and cdate < :month100 + interval 1 month and status not in ('C','L'))
        ";

        if (!empty($request->email)) {
            $query .= "and lower(a.email) like '%" . strtolower($request->email) . "%' ";
        }

        if (!empty($request->name)) {
            $query .= "and concat(lower(a.first_name), ' ', lower(a.last_name)) like '%" . strtolower($request->name) . "%' ";
        }

        if (!empty($request->promo_code_type)) {
            $promo_code_type = $request->promo_code_type;
            if ($promo_code_type == 'X') {
                $query .= " and ifnull(c.type, '') = '' ";
            }else if ($promo_code_type == 'A') {
                    $query .= " and ifnull(c.type, '') in ( 'A','B') ";
            } else {
                $query .= " and c.type = '" . $request->promo_code_type . "' ";
            }
        }

        $query .= " group by 1, 2, 3, 4 ";
        //$query .= " having min(b.cdate) >= :month27 and min(b.cdate) < :month28 + interval 1 month ";
        $query .= " order by 1, 3, 4 ";

        $data = DB::select($query, [
            'month1' => $month_date,
            'month2' => $month_date,
            'month3' => $month_date,
            'month4' => $month_date,
            'month5' => $month_date,
            'month6' => $month_date,
            'month7' => $month_date,
            'month8' => $month_date,
            'month9' => $month_date,
            'month10' => $month_date,
            'month11' => $month_date,
            'month12' => $month_date,
            'month13' => $month_date,
            'month14' => $month_date,
            'month15' => $month_date,
            'month16' => $month_date,
            'month17' => $month_date,
            'month18' => $month_date,
            'month19' => $month_date,
            'month20' => $month_date,
            'month21' => $month_date,
            'month22' => $month_date,
            'month23' => $month_date,
            'month24' => $month_date,
            'month25' => $month_date,
            'month26' => $month_date,
//            'month27' => $month_date,
//            'month28' => $month_date,
            'month99' => $month_date,
            'month100' => $month_date,
        ]);

        if ($request->excel == 'Y') {
            Excel::create('appointment-cycle', function($excel) use($data) {

                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                            'User.ID' => $o->user_id,
                            'Promo.Code.Type' => $o->promo_code_type,
                            'User.Name' => $o->user_name,
                            'Email' => $o->email,
                            'M1' => $o->m1_qty,
                            'M2' => $o->m2_qty,
                            'M3' => $o->m3_qty,
                            'M4' => $o->m4_qty,
                            'M5' => $o->m5_qty,
                            'M6' => $o->m6_qty,
                            'M7' => $o->m7_qty,
                            'M8' => $o->m8_qty,
                            'M9' => $o->m9_qty,
                            'M10' => $o->m10_qty,
                            'M11' => $o->m11_qty,
                            'M12' => $o->m12_qty,
                            'Total' => $o->total_qty,
                            'First.Date' => $o->first_date,
                            'Last.Date' => $o->last_date,
                            'Date.Diff' => $o->date_diff,
                            'Avg.Days' => empty($o->avg_days) ? '' : number_format($o->avg_days, 2)
                        ];
                    }


                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $m1_qty = 0;
        $m2_qty = 0;
        $m3_qty = 0;
        $m4_qty = 0;
        $m5_qty = 0;
        $m6_qty = 0;
        $m7_qty = 0;
        $m8_qty = 0;
        $m9_qty = 0;
        $m10_qty = 0;
        $m11_qty = 0;
        $m12_qty = 0;
        $total_qty = 0;
        $avg_days = 0;

        $total_avg_qty = 0;

        foreach ($data as $o) {
            $m1_qty += $o->m1_qty;
            $m2_qty += $o->m2_qty;
            $m3_qty += $o->m3_qty;
            $m4_qty += $o->m4_qty;
            $m5_qty += $o->m5_qty;
            $m6_qty += $o->m6_qty;
            $m7_qty += $o->m7_qty;
            $m8_qty += $o->m8_qty;
            $m9_qty += $o->m9_qty;
            $m10_qty += $o->m10_qty;
            $m11_qty += $o->m11_qty;
            $m12_qty += $o->m12_qty;
            $total_qty += $o->total_qty;
            $avg_days += $o->avg_days;

            if ($o->avg_days > 0) {
                $total_avg_qty++;
            }
        }

        $avg_days = $avg_days / ($total_avg_qty == 0 ? 1 : $total_avg_qty);


        $data = $this->arrayPaginator($data, $request);

        return view('admin.reports.appointment-cycle', [
            'month' => $month,
            'email' => $request->email,
            'name' => $request->name,
            'promo_code_type' => $request->promo_code_type,
            'data' => $data,
            'm1_qty' => $m1_qty,
            'm2_qty' => $m2_qty,
            'm3_qty' => $m3_qty,
            'm4_qty' => $m4_qty,
            'm5_qty' => $m5_qty,
            'm6_qty' => $m6_qty,
            'm7_qty' => $m7_qty,
            'm8_qty' => $m8_qty,
            'm9_qty' => $m9_qty,
            'm10_qty' => $m10_qty,
            'm11_qty' => $m11_qty,
            'm12_qty' => $m12_qty,
            'total_qty' => $total_qty,
            'avg_days' => empty($avg_days) ? '' : number_format($avg_days, 2)
        ]);
    }

    public function arrayPaginator($array, $request)
    {
        $page = Input::get('page', 1);
        $perPage = 100;
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]);
    }
}