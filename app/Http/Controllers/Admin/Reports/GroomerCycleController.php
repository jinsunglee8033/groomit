<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 8/28/18
 * Time: 10:16 AM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\AppointmentList;
use App\Model\Groomer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class GroomerCycleController extends Controller
{
    public function show(Request $request) {
        $month = $request->get('month', Carbon::today()->addDays(-90)->format('Y-m'));

        $month_date = Carbon::createFromFormat('Y-m-d H:i:s', $month . '-01 00:00:00');

        $repeat_query = '';
//        if (!empty($request->repeat)) {
//            if ($request->repeat == 'Y') {
//                $repeat_query = " and b.user_id in (select user_id from appointment_list where status = 'P' and cdate < '" . $month_date . "')";
//            } else {
//                $repeat_query = " and b.user_id not in (select user_id from appointment_list where status = 'P' and cdate < '" . $month_date . "')";
//            }
//        }

        if (!empty($request->repeat)) {
            if ($request->repeat == 'Y') {
                $repeat_query = " inner join appointment_cnt z on b.appointment_id = z.appointment_id and z.paid_cnt > 1 ";

            } else {
                $repeat_query = " inner join appointment_cnt z on b.appointment_id = z.appointment_id and z.paid_cnt = 1 ";

            }
        }

        $repeat_query1 = '';
        if (!empty($request->type)) {
            if ($request->type == 'D') { //dog, cat

                if(!empty($request->size)) {
                    $repeat_query1 = " inner join appointment_product ap on ap.appointment_id = b.appointment_id
                        inner join pet p on ap.pet_id = p.pet_id
                        and p.type = 'dog'
                        and p.size = $request->size ";

                } else {
                    $repeat_query1 = " inner join appointment_product ap on ap.appointment_id = b.appointment_id
                        inner join pet p on ap.pet_id = p.pet_id
                        and p.type = 'dog'
                        ";
                }

            } else {
                $repeat_query1 = " inner join appointment_product ap on ap.appointment_id = b.appointment_id
                    inner join pet p on ap.pet_id = p.pet_id
                    and p.type = 'cat'
                ";
            }
        }

        $query = "
           select 
                a.groomer_id,
                upper(a.first_name) first_name,
                upper(a.last_name) last_name,
                a.status,
                count(distinct b.appointment_id) as m1_qty,
                count(distinct c.appointment_id) as m2_qty,
                count(distinct d.appointment_id) as m3_qty,
                count(distinct e.appointment_id) as m4_qty,
                count(distinct f.appointment_id) as m5_qty,
                count(distinct g.appointment_id) as m6_qty,
                count(distinct h.appointment_id) as m7_qty,
                count(distinct i.appointment_id) as m8_qty,
                count(distinct j.appointment_id) as m9_qty,
                count(distinct k.appointment_id) as m10_qty,
                count(distinct l.appointment_id) as m11_qty,
                count(distinct m.appointment_id) as m12_qty
            from groomer a 
                left join appointment_list b on a.groomer_id = b.groomer_id
                    and b.cdate >= :month1
                    and b.cdate < :month2 + interval 1 month
                    and b.status = 'P' 
                    $repeat_query
                    $repeat_query1       
                left join appointment_list c on b.user_id = c.user_id
                    and c.cdate >= :month3 + interval 1 month
                    and c.cdate < :month4 + interval 2 month
                    and c.status = 'P'
                left join appointment_list d on b.user_id = d.user_id
                    and d.cdate >= :month5 + interval 2 month
                    and d.cdate < :month6 + interval 3 month
                    and d.status = 'P'
                left join appointment_list e on b.user_id = e.user_id
                    and e.cdate >= :month7 + interval 3 month
                    and e.cdate < :month8 + interval 4 month
                    and e.status = 'P'    
                left join appointment_list f on b.user_id = f.user_id
                    and f.cdate >= :month9 + interval 4 month
                    and f.cdate < :month10 + interval 5 month
                    and f.status = 'P'    
                left join appointment_list g on b.user_id = g.user_id
                    and g.cdate >= :month11 + interval 5 month
                    and g.cdate < :month12 + interval 6 month
                    and g.status = 'P'        
                left join appointment_list h on b.user_id = h.user_id
                    and h.cdate >= :month13 + interval 6 month
                    and h.cdate < :month14 + interval 7 month
                    and h.status = 'P'          
                left join appointment_list i on b.user_id = i.user_id
                    and i.cdate >= :month15 + interval 7 month
                    and i.cdate < :month16 + interval 8 month
                    and i.status = 'P'      
                left join appointment_list j on b.user_id = j.user_id
                    and j.cdate >= :month17 + interval 8 month
                    and j.cdate < :month18 + interval 9 month
                    and j.status = 'P'       
                left join appointment_list k on b.user_id = k.user_id
                    and k.cdate >= :month19 + interval 9 month
                    and k.cdate < :month20 + interval 10 month
                    and k.status = 'P'      
                left join appointment_list l on b.user_id = l.user_id
                    and l.cdate >= :month21 + interval 10 month
                    and l.cdate < :month22 + interval 11 month
                    and l.status = 'P'         
                left join appointment_list m on b.user_id = m.user_id
                    and m.cdate >= :month23 + interval 11 month
                    and m.cdate < :month24 + interval 12 month
                    and m.status = 'P'             
            where a.status is not null 
        ";

        if (!empty($request->groomer_id)) {
            $query .= " and a.groomer_id = " . $request->groomer_id . " ";
        }

        $query .= "
            group by 1, 2, 3, 4
            order by 4,2, 3
        ";

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
            'month24' => $month_date
        ]);

        if ($request->excel == 'Y') {
            Excel::create('groomer-cycle', function($excel) use($data, $month_date, $repeat_query ) {


                $excel->sheet('reports', function($sheet) use($data, $month_date, $repeat_query ) {

                    $reports = [];
                    foreach ($data as $o) {

                        $unix_qty = AppointmentList::where('status', 'P')
                          ->whereRaw('cdate >= \'' . $month_date . '\'  + interval 1 month')
                          ->whereRaw('user_id in (select b.user_id from appointment_list  b ' . $repeat_query . ' where b.groomer_id = ' . $o->groomer_id . ' and b.cdate >= \'' . $month_date . '\' and b.cdate < \'' . $month_date . '\' + interval 1 month)')
                          ->groupBy('user_id')
                          ->count();
                          //->get(['user_id']);

                        //$unix_qty = empty($unix_qty) ? 0 : count($unix_qty);
                        $unix_qty = $unix_qty ;

                        $reports[] = [
                            'Groomer.ID' => $o->groomer_id,
                            'Groomer.Name' => $o->first_name . ' ' . $o->last_name,
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
                            'UQY' => $unix_qty
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
        $muqy = 0;

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


            $unix_qty = AppointmentList::where('status', 'P')
                ->whereRaw('cdate >= \'' . $month_date . '\'  + interval 1 month')
                ->whereRaw('user_id in (select b.user_id from appointment_list b ' . $repeat_query . ' where b.groomer_id = ' . $o->groomer_id . ' and b.cdate >= \'' . $month_date . '\' and b.cdate < \'' . $month_date . '\' + interval 1 month)')
                ->groupBy('user_id')
                ->count();
                //->get(['user_id']);

//            Helper::log('### UQTY 1##' . $unix_qty);
//            Helper::log('### UQTY 2 empty##' . empty($unix_qty) );
//            Helper::log('### UQTY 2 isset##' . isset($unix_qty) );
//            Helper::log('### UQTY 2 is_array##' . is_array($unix_qty) );
            $o->unix_qty = $unix_qty;
            //$o->unix_qty = empty($unix_qty) ? 0 : ( is_array($unix_qty) ? count($unix_qty) : 0 ) ;
//            Helper::log('### UQTY 3##' . $o->unix_qty );
            $muqy += $o->unix_qty;
        }

        $data = $this->arrayPaginator($data, $request);

        $groomers = Groomer::orderBy('first_name', 'asc','last_name','asc')->get();
        //All groomer to see deactivated groomers too.

        return view('admin.reports.groomer-cycle', [
            'month' => $month,
            'groomer_id' => $request->groomer_id,
            'repeat' => $request->repeat,
            'groomers' => $groomers,
            'type'  => $request->type,
            'size'  => $request->size,
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
            'muqy'   => $muqy
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