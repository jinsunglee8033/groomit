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

class SurveyController extends Controller
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
            $groomers = Groomer::orderByRaw("lower(trim(first_name)) asc, lower(trim(last_name)) asc ")->get();

            $filter = [
                'sdate' => $sdate,
                'edate' => $edate
            ] ;


                $query = "
                            select a.appointment_id, b.groomer_id, a.ov, a.sc, a.gq, a.cl, a.va, a.cs, a.su, a.cdate, concat(c.first_name, ' ',  c.last_name ) groomer_name
                            from survey a
                            inner join appointment_list b on a.appointment_id = b.appointment_id
                            inner join groomer c on b.groomer_id = c.groomer_id
                            where a.cdate >= :sdate
                            and a.cdate < :edate + interval 1 day
            ";

                Helper::log('### groomer_id ##' . $request->groomer_id);
                if (!empty($request->groomer_id)) {
                    $query = "select a.appointment_id, b.groomer_id, a.ov, a.sc, a.gq, a.cl, a.va, a.cs, a.su, a.cdate, concat(c.first_name, ' ',  c.last_name ) groomer_name
                                from survey a
                                inner join appointment_list b on a.appointment_id = b.appointment_id
                                inner join groomer c on b.groomer_id = c.groomer_id
                                where a.cdate >= :sdate
                                and a.cdate < :edate + interval 1 day
                                and b.groomer_id = :groomer_id
                                            ";
                    $groomer_id = $request->groomer_id;
                    $filter = array_merge($filter, compact('groomer_id'));
                }
//            Helper::log('### groomer_id ##' . $request->groomer_id);
//                if (empty($request->groomer_id)) {
//                    $query= "select a.appointment_id, 0 b.groomer_id, a.ov, a.sc, a.gq, a.cl, a.va, a.cs, a.su, a.cdate, '' groomer_name
//                            from survey a
//                            inner join appointment_list b on a.appointment_id = b.appointment_id
//                            inner join groomer c on b.groomer_id = c.groomer_id
//                            where a.cdate >= :sdate
//                            and a.cdate <= :edate";
//                    $groomer_id =$request->groomer_id;
//                    $filter = array_merge($filter, compact('groomer_id'));
//                }

//            Helper::log('### groomer_id ##' . $request->groomer_id);
//            if (empty($request->groomer_id)) {
//                $query = "select appointment_id, 0 groomer_id, ov, sc, gq, cl,va, cs, su, cdate, '' groomer_name
//                            from survey
//                            where cdate >= :sdate
//                            and cdate <= :edate ";
//                $groomer_id =$request->groomer_id;
//                $filter = array_merge($filter, compact('groomer_id'));
//            }

                $query = $query . "order by a.cdate desc ";
                $result = DB::select($query, $filter);




            if ($request->excel == 'Y') {
                Excel::create('Survey', function ($excel) use ($result) {
                    $excel->sheet('reports', function ($sheet) use ($result) {
                        $data = [];
                        foreach ($result as $r) {
                            $row = [
                                'appointmentID' => $r->appointment_id,
                                'Groomer' => $r->groomer_id,
                                'Overall' => $r->ov,
                                'Scheduling' => $r->sc,
                                'Groomer Quantity' => $r->gq,
                                'cleanliness' => $r->cl,
                                'Value' => $r->va,
                                'Customer Support' => $r->cs,
                                'Suggestion' => $r->su,
                                'Date' => $r->cdate
                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');


            }

            $total = new \stdClass();
            $total->ov_total = 0;
            $total->sc_total = 0;
            $total->gq_total = 0;
            $total->cl_total = 0;
            $total->va_total = 0;
            $total->cs_total = 0;

            foreach ($result as $r) {
                $total->ov_total += $r->ov;
                $total->sc_total += $r->sc;
                $total->gq_total += $r->gq;
                $total->cl_total += $r->cl;
                $total->va_total += $r->va;
                $total->cs_total += $r->cs;
            }

            return view('admin.reports.survey', [
                'msg' => '',
                'results' => $result,
                'sdate' => $sdate,
                'edate' => $edate,
                'groomer_id' => $request-> groomer_id,
                'groomers' => $groomers,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}