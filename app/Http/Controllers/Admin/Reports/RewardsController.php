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
use App\Model\GroomerPoint;
use Carbon\Carbon;
use Carbon\Laravel\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Redirect;
use Illuminate\Support\Facades\Auth;

class RewardsController extends Controller
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

        $query = GroomerPoint::leftJoin('groomer_point_reason_code','groomer_point_reason_code.reason_code' , '=', 'groomer_point.reason_code' )
                 ->leftJoin('groomer', 'groomer.groomer_id' , '=', 'groomer_point.groomer_id')
                 ->where('groomer_point.cdate', '>=', $sdate)
                ->where('groomer_point.cdate', '<', $edate);

        if (!empty ($request->groomer_id)) {
            $query = $query->where('groomer_point.groomer_id', $request->groomer_id);
        }

        if (!empty ($request->appointment_id)) {
            $query = $query->where('groomer_point.appointment_id', $request->appointment_id);
        }

        if ($request->excel == 'Y') {
            $data = $query->orderBy('groomer_point.cdate', 'desc')->get([
                'groomer_point.groomer_id',
                'groomer.first_name',
                'groomer.last_name',
                'groomer_point.point',
                'groomer_point.appointment_id',
                'groomer_point_reason_code.descrpt',
                'groomer_point.modified_by',
                'groomer_point.cdate',
                'groomer_point.comments'
            ]);

            Excel::create('Rewards', function($excel) use($data) {

                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                          'Groomer.ID' => $o->groomer_id,
                          'Groomer.Name' => $o->first_name . ' ' . $o->last_name,
                          'Point' => $o->point,
                          'Appt.ID' => $o->appointment_id,
                          'Reason' => $o->descrpt,
                          'BY' => $o->modified_by,
                          'Date' => $o->cdate,
                          'Notes' => $o->comments
                        ];
                    }
                    $sheet->fromArray($reports);
                });
            })->export('xlsx');
        }

        $data = $query->orderBy('groomer_point.cdate', 'desc')->paginate(100, [
            'groomer_point.groomer_id',
            'groomer.first_name',
            'groomer.last_name',
            'groomer_point.point',
            'groomer_point.appointment_id',
            'groomer_point_reason_code.descrpt',
            'groomer_point.modified_by',
            'groomer_point.cdate',
            'groomer_point.comments'
        ]);

        $groomers = Groomer::where('status', 'A')->orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->get();

        $reason_codes = DB::select("
            select reason_code, descrpt
            from groomer_point_reason_code
            order by 1
        ");

        return view('admin.reports.rewards', [
            'data'      => $data,
            'sdate'     => $sdate,
            'edate'     => $edate,
            'groomers'  => $groomers,
            'groomer_id' => $request->groomer_id,
            'appointment_id' => $request->appointment_id,
            'reason_codes' => $reason_codes
        ]);
    }

    public function adjust(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_id_adjust' => 'required',
                'reason_code_adjust' => 'required',
                'point_adjust' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.reports.rewards')->with('alert', $msg);
            }

            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                return Redirect::route('admin.reports.rewards')->with('alert', 'Your session was expired.Please login again.');
            }

            $gp = new GroomerPoint;
            $gp->groomer_id = $request->groomer_id_adjust;
            $gp->reason_code = $request->reason_code_adjust;
            $gp->point = $request->point_adjust;
            $gp->comments = $request->comments_adjust;
            $gp->appointment_id = $request->appointment_id_adjust;
            $gp->modified_by = $admin->name ;
            $gp->cdate = Carbon::now();

            $gp->save();

            return Redirect::route('admin.reports.rewards' );

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.reports.rewards')->with('alert', $msg );
        }
    }

}