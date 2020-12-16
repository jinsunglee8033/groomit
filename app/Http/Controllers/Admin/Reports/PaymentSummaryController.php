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

class PaymentSummaryController extends Controller
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

            $query = "
            select date_format(cdate,'%Y-%m-%d') dt, sum(case type when 'S' then amt when 'V' then -amt end ) amt
            from cc_trans a
            where a.cdate >= :sdate 
            and a.cdate < :edate + interval 1 day
            and a.result = 0
            and a.amt != 0.01
            and a.type not in ('A')
            and a.category not in ( 'A' )
            group by 1
            order by 1 desc";


            $result = DB::select($query, [
                'sdate' => $sdate,
                'edate' => $edate
            ]);

            if ($request->excel == 'Y') {
                Excel::create('Payment', function ($excel) use ($result) {
                    $excel->sheet('reports', function ($sheet) use ($result) {
                        $data = [];
                        foreach ($result as $r) {
                            $row = [

                                'Date' => $r->dt,
                                'Amount' => $r-> amt,

                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');


            }

            $total = new \stdClass();
            $total->amt_total = 0;


            foreach ($result as $r) {
                $total->amt_total += $r->amt;
            }

            return view('admin.reports.paymentsummary', [
                'msg' => '',
                'results' => $result,
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

}