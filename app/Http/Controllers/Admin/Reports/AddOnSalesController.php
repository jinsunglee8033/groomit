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

class AddOnSalesController extends Controller
{

    public function show(Request $request)
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

            $query1 = "";
            $query2 = "";
            $query11 = "";
            $query22 = "";
            if (!empty($request->pet_type)) {
                $query1 = " and c.pet_type = '".$request->pet_type."' ";
                $query2 = " and lower(f_get_pet_type(appointment_id)) COLLATE utf8_general_ci  = '".$request->pet_type."' ";
            }else {
                $query1 = " ";
                $query2 = " ";
            }

            if (!empty($request->prod_type)) {
                $query11 = " and c.prod_type = '".$request->prod_type."' ";
                $query22 = " and 'Fees' = '".$request->prod_type."' ";
            }else {
                $query11 = " ";
                $query22 = " ";
            }


            $query = "  
            select c.pet_type  COLLATE utf8_general_ci as pet_type, c.prod_type  COLLATE utf8_general_ci as prod_type, c.prod_name  COLLATE utf8_general_ci as prod_name, count(*) as count, sum(b.amt) as sum
            from appointment_list a, appointment_product b, product c 
            where a.appointment_id = b.appointment_id
            and a.accepted_date >= :sdate
            and a.accepted_date < :edate + interval 1 day
            and a.status in ('P')
            and b.prod_id = c.prod_id 
            $query1
            $query11
            group by 1,2,3
            union 
            select lower(f_get_pet_type(appointment_id))  COLLATE utf8_general_ci as pet_type, 'Fees'  COLLATE utf8_general_ci as prod_type, 'Cancel&Rescheduling Fee'  COLLATE utf8_general_ci as prod_name, count(*) , sum(sub_total)
            from profit_share a
            where cdate >= :sdate
            and cdate < :edate  + interval 1 day
            and type in ('W') 
            $query2
            $query22
            group by 1,2,3
            union 
            select lower(f_get_pet_type(appointment_id))  COLLATE utf8_general_ci as pet_type, 'Fees'  COLLATE utf8_general_ci as prod_type , 'Safety Insurance Fee'  COLLATE utf8_general_ci as prod_name, count(*) , sum(safety_insurance)
            from appointment_list
            where cdate >= :sdate
            and cdate < :edate  + interval 1 day
            and status in ('P')
            and safety_insurance > 0
            $query2
            $query22
            group by 1,2,3
            union 
            select lower(f_get_pet_type(appointment_id))  COLLATE utf8_general_ci as pet_type, 'Fees'  COLLATE utf8_general_ci as prod_type, 'SameDay Booking Fee'   COLLATE utf8_general_ci as prod_name, count(*) , sum(sameday_booking) 
            from appointment_list
            where cdate >= :sdate
            and cdate < :edate  + interval 1 day
            and status in ('P')
            and sameday_booking > 0 
            $query2
            $query22
            group by 1, 2, 3
            union
            select lower(f_get_pet_type(appointment_id))  COLLATE utf8_general_ci as pet_type, 'Fees'  COLLATE utf8_general_ci as prod_type, 'Fav Groomer Fee'  COLLATE utf8_general_ci as prod_name, count(*) , sum(fav_groomer_fee)
            from appointment_list
            where cdate >= :sdate
            and cdate < :edate  + interval 1 day
            and status in ('P')
            and fav_groomer_fee > 0
            $query2
            $query22
            group by 1, 2, 3
            union 
            select c.pet_type  COLLATE utf8_general_ci as pet_type, 'Fees'  COLLATE utf8_general_ci as prod_type, 'Shampoo Fee'  COLLATE utf8_general_ci as prod_name, count(*) , count(*) 
            from appointment_list a, appointment_product b, product c
            where a.appointment_id = b.appointment_id
            and a.accepted_date >= :sdate
            and a.accepted_date < :edate  + interval 1 day
            and a.status in ('P')
            and b.prod_id = c.prod_id
            and c.prod_type ='S'
            $query1
            $query22
            group by 1,2,3
            ";

            $query = $query . " order by 1,2,4 desc ";

            $result = DB::select($query, [
                'sdate' => $sdate,
                'edate' => $edate
            ]);



            if ($request->excel == 'Y') {
                Excel::create('ProdSalesQuantity', function ($excel) use ($result) {
                    $excel->sheet('reports', function ($sheet) use ($result) {
                        $data = [];
                        foreach ($result as $a) {
                            $row = [
                                'Pet.Type' => $a->pet_type,
                                'Product.Type' => $a->prod_type,
                                'Product.Name' => $a->prod_name,
                                'Quantity' => $a->count,
                                'Amount' => $a->sum
                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');
            }

            return view('admin.reports.add_on_sales', [
                'msg' => '',
                'results' => $result,
                'sdate' => $sdate,
                'edate' => $edate,
                'pet_type' => $request->pet_type,
                'prod_type' => $request->prod_type
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}