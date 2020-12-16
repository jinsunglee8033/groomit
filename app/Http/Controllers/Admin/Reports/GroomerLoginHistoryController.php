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

class GroomerLoginHistoryController extends Controller
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
                    select a.login_id,
                    case a.log_inout when 'I' then 'Log In' else 'Log Out' end log_inout, 
                    a.groomer_id, b.first_name, b.last_name, 
                    case a.result when 'S' then 'Success' else 'Failure' end result,  
                    a.ip_addr, a.cdate
                    from groomer_login_history a
                    inner join groomer b on a.groomer_id = b.groomer_id
                    where a.cdate >= :sdate 
                    and a.cdate < :edate + interval 1 day";

            Helper::log('### login_id ##' . $request->login_id);
            if (!empty($request->login_id)) {
                $query = $query . "and a.login_id = :login_id ";
                $login_id =$request->login_id;
                $filter = array_merge($filter, compact('login_id'));
            }

            Helper::log('### log_inout ##' . $request->log_inout);
            if (!empty($request->log_inout)) {
                $query = $query . "and a.log_inout = :log_inout ";
                $log_inout =$request->log_inout;
                $filter = array_merge($filter, compact('log_inout'));
            }

            Helper::log('### groomer_id ##' . $request->groomer_id);
            if (!empty($request->groomer_id)) {
                $query = $query . " and a.groomer_id = :groomer_id ";
                $groomer_id =$request->groomer_id;
                $filter = array_merge($filter, compact('groomer_id'));
            }
//
            Helper::log('### result ##' . $request->result);
            if (!empty($request->result)) {
                $query = $query . " and a.result = :result ";
                $result =$request->result;
                $filter = array_merge($filter, compact('result'));
            }

            Helper::log('### ip_addr ##' . $request->ip_addr);
            if (!empty($request->ip_addr)) {
                $query = $query . "and a.ip_addr = :ip_addr " ;
                $ip_addr = $request->ip_addr;

                $filter = array_merge($filter, compact('ip_addr'));
            }



            $query = $query . " order by a.cdate desc ";

            $result = DB::select($query,
                                 $filter
                              );



            if ($request->excel == 'Y') {
                Excel::create('Login History', function ($excel) use ($result) {
                    $excel->sheet('reports', function ($sheet) use ($result) {
                        $data = [];
                        foreach ($result as $r) {
                            $row = [
                                'Login ID' => $r->login_id,
                                'Log In or Log Out' => $r->log_inout,
                                'Groomer ID' => $r->groomer_id,
                                'First Name' => $r->first_name,
                                'Last Name' => $r->last_name,
                                'Result' => $r->result,
                                'IP Address' => $r->ip_addr,
                                'Confirm Date' => $r->cdate
                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');


            }

//            $total = new \stdClass();
//            $total->ov_total = 0;
//            $total->sc_total = 0;
//            $total->gq_total = 0;
//            $total->cl_total = 0;
//            $total->va_total = 0;
//            $total->cs_total = 0;
//
//            foreach ($result as $r) {
//                $total->ov_total += $r->ov;
//                $total->sc_total += $r->sc;
//                $total->gq_total += $r->gq;
//                $total->cl_total += $r->cl;
//                $total->va_total += $r->va;
//                $total->cs_total += $r->cs;
//            }

            return view('admin.reports.groomer-login-history', [
                'msg' => '',
                'results' => $result,
                'sdate' => $sdate,
                'edate' => $edate,
                'login_id' => $request->login_id,
                'log_inout' => $request->log_inout,
                'groomer_id' => $request->groomer_id,
                'result' => $request->result,
                'ip_addr' => $request->ip_addr


            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}