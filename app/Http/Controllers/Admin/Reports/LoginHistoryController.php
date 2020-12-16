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

class LoginHistoryController extends Controller
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
                    select case a.log_inout when 'I' then 'Log In' else 'Log Out' end log_inout, a.user_id, b.first_name, b.last_name, case a.result when 'S' then 'Success' else 'Failure' end result, 
                    case a.login_channel when 'A' then 'Admin' when 'P' then 'App' when '3' then '3rd Party Login' when 'E' then 'Web' else a.login_channel end login_channel, a.ip_addr, a.cdate
                    from user_login_histsory a
                    inner join user b on a.user_id = b.user_id
                    where a.cdate >= :sdate
                    and a.cdate < :edate + interval 1 day";

            Helper::log('### log_inout ##' . $request->log_inout);
            if (!empty($request->log_inout)) {
                $query = $query . "and a.log_inout = :log_inout ";
                $log_inout =$request->log_inout;
                $filter = array_merge($filter, compact('log_inout'));
            }

            Helper::log('### user_id ##' . $request->user_id);
            if (!empty($request->user_id)) {
                $query = $query . " and a.user_id = :user_id ";
                $user_id =$request->user_id;
                $filter = array_merge($filter, compact('user_id'));
            }
//
            Helper::log('### result ##' . $request->result);
            if (!empty($request->result)) {
                $query = $query . " and a.result = :result ";
                $result =$request->result;
                $filter = array_merge($filter, compact('result'));
            }

            Helper::log('### login_channel ##' . $request->login_channel);
            if (!empty($request->login_channel)) {
                $query = $query . "and a.login_channel = :login_channel " ;
                $login_channel = $request->login_channel;

                $filter = array_merge($filter, compact('login_channel'));
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
                                'Log In/Out' => $r->log_inout,
                                'User ID' => $r->user_id,
                                'First Name' => $r->first_name,
                                'Last Name' => $r->last_name,
                                'Result' => $r->result,
                                'Login Channel' => $r->login_channel,
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

            return view('admin.reports.login-history', [
                'msg' => '',
                'results' => $result,
                'sdate' => $sdate,
                'edate' => $edate,
                'log_inout' => $request->log_inout,
                'user_id' => $request->user_id,
                'result' => $request->result,
                'login_channel' => $request->login_channel,
                'ip_addr' => $request->ip_addr


            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}