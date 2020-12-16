<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 11/1/18
 * Time: 4:06 AM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Model\CreditMemo;

use Excel;

class SpecialPromotionController extends Controller
{

    public function show(Request $request) {
        $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('sdate', Carbon::today()->addDays(-7)->format('Y-m-d')) . ' 00:00:00');
        $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('edate', Carbon::today()->addDay()->format('Y-m-d')) . ' 00:00:00');

        $data = CreditMemo::join('user', 'user.user_id', '=', 'credit_memo.user_id')
          ->join('appointment_list', 'appointment_list.appointment_id', '=', 'credit_memo.appointment_id')
          ->where('credit_memo.type', 'C')
          ->where('credit_memo.ref_type', 'P')
          ->where('credit_memo.ref', strtoupper($request->spcode))
          ->where('appointment_list.cdate', '>=', $sdate)
          ->where('appointment_list.cdate', '<=', $edate)
          ->select(
            'credit_memo.*',
            //'credit.user_id',
            DB::raw("concat(user.first_name, ' ', user.last_name) as user_name"),
            //'credit.appointment_id',
            'user.email',
            'appointment_list.total',
            'appointment_list.cdate as order_date',
            'appointment_list.accepted_date as service_date',
            'credit_memo.amt as credit_amt'
          )->orderBy('appointment_list.cdate', 'desc');

        if ($request->excel == 'Y') {
            $data = $data->get();
            Excel::create($request->spcode, function($excel) use($data) {


                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                          'User.ID'         => $o->user_id,
                          'User.Name'       => $o->user_name,
                          'User.Email'      => $o->email,
                          'Appointment.ID'  => $o->appointment_id,
                          'Amount($)'       => $o->total,
                          'Order.Date'      => $o->order_date,
                          'Service.Date'    => $o->service_date,
                          'Credit.Amount($)' => $o->credit_amt,
                          'Expire.Date'     => $o->expire_date
                        ];
                    }


                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $data = $data->paginate();

        return view('admin.reports.special-promotion', [
            'data' => $data,
            'sdate' => $sdate,
            'edate' => $edate,
            'spcode' => $request->spcode
        ]);
    }

}