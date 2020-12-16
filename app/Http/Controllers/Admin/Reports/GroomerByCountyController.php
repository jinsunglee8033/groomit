<?php
/**
 * Created by PhpStorm.
 * User: Jin
 * Date: 2/13/20
 * Time: 05:42 PM
 */

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\Groomer;
use App\Model\GroomerOpens;
use Carbon\Carbon;
use Carbon\Laravel\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
//use stdClass;

class GroomerbyCountyController extends Controller
{

    public function show(Request $request)
    {
        try {

            $sdate = Carbon::createFromFormat('!Y-m-d', $request->get('sdate', Carbon::today()->addDays(-6)->format('Y-m-d')));
            $edate = Carbon::createFromFormat('!Y-m-d', $request->get('edate', Carbon::today()->addDay()->format('Y-m-d')));

            if (!empty($request->sdate)) {
                $sdate = Carbon::createFromFormat('!Y-m-d', $request->sdate);
            }

            if (!empty($request->edate)) {
                $edate = Carbon::createFromFormat('!Y-m-d', $request->edate);
            }

            $filter = [
                'sdate' => $sdate,
                'edate' => $edate
            ] ;



            $query = "
            
            select no_groomer.county, no_groomer.groomer_cnt, no_user.user_cnt, no_revenue.appt_cnt, no_revenue.appt_total
            from 
            (
            select county, count(*) groomer_cnt
            from groomer_service_area a left join groomer b on  a.groomer_id = b.groomer_id and b.status = 'A'
            where a.status ='A'
            and county in (  select  concat(county_name , '.' , state_abbr) from allowed_zip where available = 'x')
            group by 1
            ) no_groomer 
            inner join
            (
            select  concat(a.county_name , '.' , a.state_abbr) county, count(*) user_cnt
            from (
            select distinct a.user_id, b.county_name, b.state_abbr
            from user a inner join allowed_zip b on a.zip = b.zip
            where a.cdate >= :sdate
            and  a.cdate < :edate
            ) a
            group by 1
            ) no_user on no_groomer.county = no_user.county
            inner join
            (
            select  concat(b.county_name , '.' , b.state_abbr) county, count(*) appt_cnt, sum(total) appt_total
            from (
            select distinct a.appointment_id, a.total, c.county_name, c.state_abbr
            from appointment_list a inner join address b on a.address_id = b.address_id
                        inner join allowed_zip c on b.zip = c.zip
            where a.accepted_date >= :sdate
            and  a.accepted_date < :edate + interval 1 day
            and a.status = 'P'
            ) b
            group by 1
            ) no_revenue on no_groomer.county = no_revenue.county
            order by 5 desc

                    ";


            $result = DB::select($query,$filter
            );

            $query2 = "
            select a.county, a.groomer_id, b.first_name, b.last_name
            from groomer_service_area a left join groomer b on a.groomer_id = b.groomer_id and b.status = 'A'
            where a.status ='A'
            and county in (  select  concat(county_name , '.' , state_abbr) from allowed_zip where available = 'x')
            ";

            $details = DB::select($query2,$filter
            );

            if ($request->excel == 'Y') {
                Excel::create('Groomers by county', function ($excel) use ($result, $details) {
                    $excel->sheet('reports', function ($sheet) use ($result, $details) {
                        $data = [];
                        foreach ($result as $r) {
                            $groomers = '';
                            foreach ($details as $d) {
                                if( $r->county  == $d->county ) {
                                    $groomers = $groomers . $d->first_name . ' ' . $d->last_name . ', ';
                                }
                            }
                            $row = [
                                'County' => $r->county,
                                'Number of Groomers' => $r->groomer_cnt,
                                'Number of Users' => $r->user_cnt,
                                'Number of appointments' => $r->appt_cnt,
                                'Revenue' => $r->appt_total,
                                'Groomer List' => $groomers
                            ];

                            $data[] = $row;
                        }

                        $sheet->fromArray($data);
                    });
                })->export('xlsx');
            }



            $total = new \stdClass();
            $total->num_total = 0;
            $total->user_total = 0;
            $total->appt_total = 0;
            $total->rev_total = 0;


            foreach ($result as $r) {
                $total->num_total += $r->groomer_cnt;
                $total->user_total += $r->user_cnt;
                $total->appt_total += $r->appt_cnt;
                $total->rev_total += $r->appt_total;

            }



            return view('admin.reports.groomers-by-countysummary', [
                'msg' => '',
                'results' => $result,
                'details' => $details,
                'sdate' => $sdate,
                'edate' => $edate,
                'total' => $total


            ]);



        }

        catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }


    public function showdetails(Request $request)
    {
        try {

            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('sdate', Carbon::today()->addDays(-6)->format('Y-m-d')) . ' 00:00:00');
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('edate', Carbon::today()->addDay()->format('Y-m-d')) . ' 00:00:00');

            if (!empty($request->sdate)) {
                $sdate = Carbon::createFromFormat('Y-m-d', $request->sdate);
            }

            if (!empty($request->edate)) {
                $edate = Carbon::createFromFormat('Y-m-d', $request->edate);
            }

            $filter = [
                'sdate' => $sdate,
                'edate' => $edate
            ] ;

            $query = "
                    select a.county, a.groomer_id, b.first_name, b.last_name, a.cdate
                    from groomer_service_area a inner join groomer b on a.groomer_id = b.groomer_id and b.status = 'A'
                    where a.status = 'A'
                    ";

            Helper::log('### county ##' . $request->county);
            if (!empty($request->county)) {
                $query = $query . "and a.county = :county ";
                $county =$request->county;
                $filter = array_merge($filter, compact('county'));
            }

            $query = $query . " order by 3 asc ";

            $result = DB::select($query, $filter);




            if ($request->excel == 'Y') {
                Excel::create('Login History', function ($excel) use ($result) {
                    $excel->sheet('reports', function ($sheet) use ($result) {
                        $data = [];
                        foreach ($result as $r) {
                            $row = [
                                'County' => $r->county,
                                'Number of Groomers' => $r->groomer_cnt,
                                'Number of Users' => $r->user_cnt,
                                'Number of appointments' => $r->appt_cnt,
                                'Revenue' => $r -> appt_total,
                                'Groomer First Name' => $r->first_name,
                                'Groomer Last Name' => $r->last_name,

                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');


            }

//            $total = new \stdClass();
//            $total->num_total = 0;
//
//
//            foreach ($result as $r) {
//                $total->num_total += $r->num;
//
//            }

            return view('admin.reports.groomers-by-countydetails', [
                'msg' => '',
                'results' => $result,
                'county' => $request->county,


            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}