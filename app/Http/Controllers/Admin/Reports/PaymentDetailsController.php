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

class PaymentDetailsController extends Controller
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

            $query_iseco = '';
            if (empty($request->iseco)) {
                $request->iseco = '';
            }
            else{
                $query_iseco = "and f_check_eco(a.appointment_id) >= 1 and category not in ( 'T' ) ";
            }


            $query = "
            select appointment_id, case type when 'S' then 'Sales' when 'V' then 'Refunds' else type end type,
            case category when 'S' then 'Appointment' when 'T' then 'Tip' when 'W' then 'Cancel Fee' when 'R' then 'Rescheduling Fee' else category end category,
            f_check_eco(a.appointment_id) IsEco,
            a.amt, error_name, a.cdate, orig_sales_id, void_date  
            from cc_trans a
            where a.cdate >= :sdate
            and a.cdate < :edate + interval 1 day
            and a.result = 0
            and a.amt != 0.01
            and a.type not in ('A')
            and a.category not in ( 'A' )
            $query_iseco
            ";


            Helper::log('### appointment_id ##' . $request->appointment_id);
            if(!empty($request->appointment_id)) {
                $query .= '  and a.appointment_id = :appointment_id ' ;
                $appointment_id =$request->appointment_id;
                $filter = array_merge($filter, compact('appointment_id'));
            }

            $query = $query . " order by a.cdate desc ";


            $result = DB::select($query,
                $filter
            );

            if ($request->excel == 'Y') {
                Excel::create('Payment', function ($excel) use ($result) {
                    $excel->sheet('reports', function ($sheet) use ($result) {
                        $data = [];
                        foreach ($result as $r) {
                            $row = [
                                'appointmentID' => $r->appointment_id,
                                'Eco?' => $r->IsEco,
                                'date' => $r->cdate,
                                'Type' => $r->type,
                                'Category' => $r->category,
                                'Amount' => $r-> amt,
                                'Error Name' => $r-> error_name,
                                'Original Sales ID' => $r-> orig_sales_id,
                                'Void Date' => $r-> void_date,
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
                if($r->type=='Sales') {
                    $total->amt_total += $r->amt;
                }
                if($r->type=='Refunds') {
                    $total-> amt_total -= ($r->amt);
                }

            }

            return view('admin.reports.paymentdetails', [
                'msg' => '',
                'results' => $result,
                'sdate' => $sdate,
                'edate' => $edate,
                'appointment_id' => $request -> appointment_id,
                'IsEco' => $request->iseco,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}