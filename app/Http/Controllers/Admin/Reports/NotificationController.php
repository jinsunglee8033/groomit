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

class NotificationController extends Controller
{

    public function show(Request $request) {

        $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('sdate', Carbon::today()->addDays(-1)->format('Y-m-d')) . ' 00:00:00');
        $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->get('edate', Carbon::today()->addDay()->format('Y-m-d')) . ' 00:00:00');

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d', $request->sdate);
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d', $request->edate);
        }

        $query = GroomerOpens::leftJoin('groomer', function($join){
                    $join->on('groomer_opens.groomer_id', '=', 'groomer.groomer_id');
                })
            ->where('groomer_opens.cdate', '>=', $sdate)
            ->where('groomer_opens.cdate', '<=', $edate);

        if (!empty ($request->groomer_id)) {
            $query = $query->where('groomer_opens.groomer_id', $request->groomer_id);
        }

        if (!empty ($request->appointment_id)) {
            $query = $query->where('groomer_opens.appt_id', $request->appointment_id);
        }

        if ($request->excel == 'Y') {
            $data = $query->orderBy('groomer_opens.appt_id', 'desc')->orderBy('groomer_opens.cdate', 'desc')->get([
                'groomer_opens.appt_id',
                'groomer_opens.county',
                'groomer_opens.prod_ids',
                'groomer_opens.stage',
                'groomer_opens.groomer_id',
                'groomer.first_name',
                'groomer.last_name',
                'groomer_opens.notified',
                'groomer_opens.removed',
                'groomer_opens.cdate'
            ]);

            Excel::create('Notification', function($excel) use($data) {

                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                          'Appt.ID' => $o->appt_id,
                          'County' => $o->county,
                          'State' => $o->stage,
                          'Groomer.ID' => $o->groomer_id,
                          'Notified' => $o->notified,
                          'Removed' => $o->removed,
                          'Cdate' => $o->cdate,
                        ];
                    }
                    $sheet->fromArray($reports);
                });
            })->export('xlsx');
        }

        $data = $query->orderBy('groomer_opens.appt_id', 'desc')->orderBy('groomer_opens.cdate', 'desc')->paginate(50, [
          'groomer_opens.appt_id',
          'groomer_opens.county',
          'groomer_opens.prod_ids',
          'groomer_opens.stage',
          'groomer_opens.groomer_id',
          'groomer.first_name',
          'groomer.last_name',
          'groomer_opens.notified',
          'groomer_opens.removed',
          'groomer_opens.cdate'
        ]);

        $groomers = Groomer::orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->get();

        return view('admin.reports.notification', [
            'data'      => $data,
            'sdate'     => $sdate,
            'edate'     => $edate,
            'groomers'  => $groomers,
            'groomer_id' => $request->groomer_id,
            'appointment_id' => $request->appointment_id
        ]);
    }

}