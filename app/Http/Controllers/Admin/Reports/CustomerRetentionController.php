<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 4/22/19
 * Time: 9:31 AM
 */

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRetentionController extends Controller
{
    public function show(Request $request)
    {
        try {

            $sdate = Carbon::today()->addDays(-30);
            $edate = Carbon::today()->addDays(1)->addSeconds(-1);

            if (!empty($request->sdate)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }
//            $days = 14;
//
//            if (!empty($request->days) && empty($request->id)) {
//                $days = $request->days;
//            }
//            $query = "
//            select a.user_id, a.first_name, a.last_name, a.email, a.phone, c.cnt as c1, count(*) as c2, max(b.cdate) as cdate
//            from user  a
//            inner join appointment_list b on a.user_id = b.user_id
//            inner join ( select z.user_id, count(*) cnt
//                        from user z, appointment_list y
//                        where z.user_id = y.user_id
//                        and y.status not in ('C','L')
//                        group by 1
//                        ) c on a.user_id = c.user_id
//            where b.cdate >= curdate() - interval :days day
//            and b.status not in ('C', 'L')
//            and a.user_id in ( select  user_id from appointment_list where status not in ('C','L') )
//            group by 1,2,3,4,5,6
//            order by a.user_id desc";

            $query = "
            select a.user_id, a.first_name, a.last_name, a.email, a.phone, c.book_cnt as c1, 
            date_format(a.cdate,'%m/%d/%Y') cdate, c.last_appt_date last_date,  count(b.appointment_id) as c2
            from user  a
            left join appointment_list b on a.user_id = b.user_id and b.cdate >= :sdate and b.cdate <= :edate and b.status not in ('C', 'L')
            left join user_stat c on a.user_id = c.user_id
            where a.status ='A'
            group by 1,2,3,4,5,6, 7, 8
            order by 1 desc";

            $result = DB::select($query, [
                'sdate' => $sdate,
                'edate' => $edate,
            ]);

            if ($request->excel == 'Y') {

                Excel::create('users', function ($excel) use ($result) {

                    $excel->sheet('reports', function ($sheet) use ($result) {

                        $data = [];
                        foreach ($result as $a) {
                            $row = [
                                'User ID' => $a->user_id,
                                'Name' => $a->first_name . ' ' . $a->last_name,
                                'Phone' => $a->phone,
                                'Email' => $a->email,
                                'Booked.For.Entire.Period' => $a->c1,
                                'Booked.For.Above.Period' => $a->c2,
                                'Reg.Order' => $a->cdate,
                                'Last.Order' => $a->last_date
                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');
            }

            $total = new \stdClass();
            $total->all_users = 0;
            $total->all_total = 0;
            $total->period_total = 0;

            foreach ($result as $o) {
                $total->all_users += 1;
                $total->all_total += $o->c1;
                $total->period_total += $o->c2;
            }

            $result = $this->arrayPaginator($result, $request);

            return view('admin.reports.customer_retention', [
                'msg' => '',
                'users' => $result,
                'sdate' => $sdate,
                'edate' => $edate,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
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