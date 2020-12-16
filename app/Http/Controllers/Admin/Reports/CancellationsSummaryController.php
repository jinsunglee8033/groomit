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

class CancellationsSummaryController extends Controller
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
            if (!empty($request->reason)) {
                $reason =  $request->reason;
            }else{
                $reason = '';
            }





            $query = "
            select lower(note) note, count(*) num
            from appointment_list
            where status in ( 'C' )
            and IfNull(note,'') != ''
            and mdate >= :sdate
            and mdate < :edate + interval 1 day
            group by 1
            order by 2 desc
            ";



            if(!empty($request->reason)) {
                $query .= ' and lower(note)= lower( :reason ) ' ;
                $result = DB::select($query, [
                    'sdate' => $sdate,
                    'edate' => $edate,
                    'reason' => $reason
                ]);

            }else {
                $result = DB::select($query, [
                    'sdate' => $sdate,
                    'edate' => $edate
                ]);

            }





            if ($request->excel == 'Y') {
                Excel::create('Cancellation Summary', function ($excel) use ($result) {
                    $excel->sheet('reports', function ($sheet) use ($result) {
                        $data = [];
                        foreach ($result as $a) {
                            $row = [

                                'Reason' => $a->note,
                                'Number of Times Cited' => $a->num,
                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');
            }

            return view('admin.reports.cancellationsummary', [
                'msg' => '',
                'results' => $result,
                'sdate' => $sdate,
                'edate' => $edate,
                'reason' => $reason
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function showgroomer(Request $request)
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
            if (!empty($request->reason)) {
                $reason =  $request->reason;
            }else{
                $reason = '';
            }

            $groomers = Groomer::whereIn('status', ['A'])->orderBy('first_name', 'asc','last_name','asc')->get();

            $filter = [
                'sdate' => $sdate,
                'edate' => $edate
            ] ;



            $query = "
            
            ";

            Helper::log('### groomer_id ##' . $request->groomer_id);
            if (!empty($request->groomer_id)) {
                $query = "  select a.groomer_id, b.first_name, b.last_name, lower(note) note, count(*) num
                            from appointment_list a
                            left join groomer b on a.groomer_id = b.groomer_id
                            where a.status in ( 'C' )
                            and IfNull(note,'') != ''
                            and a.mdate >= :sdate
                            and a.mdate < :edate + interval 1 day
                            and a.groomer_id = :groomer_id 
                            group by 1, 2, 3, 4
                            order by 5 desc";
                $groomer_id =$request->groomer_id;
                $filter = array_merge($filter, compact('groomer_id'));
            }


            if (empty($request->groomer_id)) {
                $query = "select 0 groomer_id, '' first_name, '' last_name,  lower(note) note, count(*) num
                                    from appointment_list a
                                    where a.status in ( 'C' )
                                    and IfNull(note,'') != ''
                                    and a.mdate >= :sdate
                                    and a.mdate < :edate + interval 1 day
                                   group by 1, 2, 3, 4
                                   order by 5 desc";
                $filter = $filter;
            }


            $result = DB::select($query,
                $filter
            );



            if ($request->excel == 'Y') {
                Excel::create('Cancellations Groomer', function ($excel) use ($result) {
                    $excel->sheet('reports', function ($sheet) use ($result) {
                        $data = [];
                        foreach ($result as $a) {
                            $row = [
                                'Reason' => $a-> note,
                                'Number of times Cited' => $a->num
                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');
            }

            return view('admin.reports.cancellationsummarygroomer', [
                'msg' => '',
                'results' => $result,
                'sdate' => $sdate,
                'edate' => $edate,
                'groomers' => $groomers,
                'groomer_id' => $request->groomer_id
            ]);

        } catch (\Exception $ex) {
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
            if (!empty($request->reason)) {
                $reason =  $request->reason;
            }else{
                $reason = '';
            }
            $groomers = Groomer::whereIn('status', ['A'])->orderBy('first_name', 'asc','last_name','asc')->get();

            $filter = [
                'sdate' => $sdate,
                'edate' => $edate
            ] ;


            $query = "
            select a.appointment_id,  a.groomer_id, b.first_name, b.last_name, a.mdate, a.note
            from appointment_list a
            left join groomer b on a.groomer_id = b.groomer_id
            where a.status in ( 'C' )
            and IfNull(a.note,'') != ''
            and a.mdate >= :sdate
            and a.mdate <=  :edate
            ";


            Helper::log('### groomer_id ##' . $request->groomer_id);
            if (!empty($request->groomer_id)) {
                $query = $query . "and a.groomer_id = :groomer_id ";
                $groomer_id =$request->groomer_id;
                $filter = array_merge($filter, compact('groomer_id'));
            }

            Helper::log('### reason ##' . $request->reason);
            if(!empty($request->reason)) {
                $query .= ' and lower(note)= lower( :reason ) ' ;
                $reason =$request->reason;
                $filter = array_merge($filter, compact('reason'));
            }

            $query = $query . " order by a.cdate desc ";

            $result = DB::select($query,
                $filter
            );



            if ($request->excel == 'Y') {
                Excel::create('Cancellation Details', function ($excel) use ($result) {
                    $excel->sheet('reports', function ($sheet) use ($result) {
                        $data = [];
                        foreach ($result as $a) {
                            $row = [
                                'Appointment ID' => $a-> appointment_id,
                                'Groomer' => $a->groomer_id,
                                'Groomer Name' => $a->first_name . ' ' . $a->last_name,
                                'Cancelled Date' => $a->mdate,
                                'Reason' => $a->note,
                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');
            }

            return view('admin.reports.cancellationsdetails', [
                'msg' => '',
                'results' => $result,
                'groomer_id' => $request->groomer_id,
                'sdate' => $sdate,
                'edate' => $edate,
                'groomers' => $groomers,
                'reason' => $reason
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}