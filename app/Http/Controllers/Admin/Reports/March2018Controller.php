<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 3/5/18
 * Time: 1:20 PM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Model\Credit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;

class March2018Controller extends Controller
{

    public function show(Request $request) {
        $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('sdate', Carbon::today()->addDays(-7)->format('Y-m-d')) . ' 00:00:00');
        $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('edate', Carbon::today()->addDay()->format('Y-m-d')) . ' 00:00:00');

        $data = Credit::join('user', 'user.user_id', '=', 'credit.user_id')
            ->join('appointment_list', 'appointment_list.appointment_id', '=', 'credit.appointment_id')
            ->where('credit.type', 'C')
            ->where('credit.category', 'G')
            ->where('credit.status', 'A')
            ->where('appointment_list.cdate', '>=', $sdate)
            ->where('appointment_list.cdate', '<=', $edate)
            ->select(
                'credit.*',
                //'credit.user_id',
                DB::raw("concat(user.first_name, ' ', user.last_name) as user_name"),
                //'credit.appointment_id',
                'user.email',
                'appointment_list.total',
                'appointment_list.cdate as order_date',
                'appointment_list.accepted_date as service_date',
                'credit.amt as credit_amt',
                'credit.expire_date'
            )->orderBy('appointment_list.cdate', 'desc');

        if ($request->excel == 'Y') {
            $data = $data->get();
            Excel::create('march-2018', function($excel) use($data) {


                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                            'User.ID' => $o->user_id,
                            'User.Name' => $o->user_name,
                            'User.Email' => $o->email,
                            'Appointment.ID' => $o->appointment_id,
                            'Packages' => $o->appointment_type_name,
                            'Amount($)' => $o->total,
                            'Order.Date' => $o->order_date,
                            'Service.Date' => $o->service_date,
                            'Credit.Amount($)' => $o->credit_amt,
                            'Expire.Date' => $o->expire_date
                        ];
                    }


                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $data = $data->paginate();

        return view('admin.reports.march-2018', [
            'data' => $data,
            'sdate' => $sdate,
            'edate' => $edate
        ]);
    }

}