<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 5/9/19
 * Time: 7:26 AM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Model\Credit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;

class UserCreditController extends Controller
{

    public function show(Request $request) {
        $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('sdate', Carbon::today()->addDays(-7)->format('Y-m-d')) . ' 00:00:00');
        $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('edate', Carbon::today()->addDay()->format('Y-m-d')) . ' 00:00:00');

        $data = Credit::join('user', 'user.user_id', '=', 'credit.user_id')
          ->leftjoin('appointment_list', 'appointment_list.appointment_id', '=', 'credit.appointment_id')
          ->where('credit.cdate', '>=', $sdate)
          ->where('credit.cdate', '<=', $edate)
          ->select(
                'credit.*',
            DB::raw("concat(user.first_name, ' ', user.last_name) as user_name"),
            'user.email',
            'appointment_list.total',
            'appointment_list.cdate as order_date',
            'appointment_list.accepted_date as service_date'
          )->orderBy('credit.cdate', 'desc');

        if ($request->excel == 'Y') {
            $data = $data->get();
            Excel::create('user-credit', function($excel) use($data) {


                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                          'User.ID' => $o->user_id,
                          'User.Name' => $o->user_name,
                          'User.Email' => $o->email,
                          'Type' => $o->type == 'C' ? 'Credit' : 'Debit',
                          'Category' => Credit::get_category_name($o->category),
                          'Amount($)' => $o->amt,
                          'Created.Date' => $o->cdate,
                          'Expire.Date' => $o->expire_date,
                          'Comment' => $o->notes,
                          'Appointment.ID' => $o->appointment_id,
                          'Order.Date' => $o->order_date
                        ];
                    }


                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $data = $data->paginate();

        return view('admin.reports.user_credit', [
          'data' => $data,
          'sdate' => $sdate,
          'edate' => $edate
        ]);
    }

}