<?php

namespace App\Http\Controllers\Admin;

use App\Model\PreApply;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Excel;


class GroomerPreApplyController extends Controller
{

    public function pre_apply(Request $request) {
        try {
            $sdate = Carbon::today()->subDays(14);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $query = PreApply::leftJoin('groomer_application', function($join) {
                $join->on('groomer_pre_apply.email', '=', 'groomer_application.email');
                    })->select('groomer_pre_apply.id',
                        'groomer_pre_apply.full_name',
                        'groomer_pre_apply.email',
                        'groomer_pre_apply.phone',
                        'groomer_pre_apply.zip',
                        'groomer_pre_apply.referred_by',
                        'groomer_pre_apply.cdate',
                        'groomer_application.id as ap_id'
            );

            if (!empty($sdate) && empty($request->id)) {
                $query = $query->where('groomer_pre_apply.cdate', '>=', $sdate);
            }

            if (!empty($edate) && empty($request->id)) {
                $query = $query->where('groomer_pre_apply.cdate', '<=', $edate);
            }

            if (!empty($request->full_name)) {
                $query = $query->whereRaw('LOWER(groomer_pre_apply.full_name) like \'%' . strtolower($request->full_name) . '%\'');
            }

            if (!empty($request->phone)) {
                $query = $query->where('groomer_pre_apply.phone', 'like', '%' . $request->phone . '%');
            }

            if (!empty($request->zip)) {
                $query = $query->where('groomer_pre_apply.zip', 'like', '%' . $request->zip . '%');
            }

            if (!empty($request->email)) {
                $query = $query->where('groomer_pre_apply.email', 'like', '%' .$request->email . '%');
            }

//            $query = $query->whereNull('hide');

            if ($request->excel == 'Y') {
                $preapplys = $query->orderBy('cdate', 'desc')->get();
                Excel::create('pre_apply', function($excel) use($preapplys) {

                    $excel->sheet('reports', function($sheet) use($preapplys) {

                        $data = [];
                        foreach ($preapplys as $a) {
                            $row = [
                                'Pre Apply ID'  => $a->id,
                                'Name'          => $a->full_name,
                                'email'         => $a->email,
                                'Phone'         => $a->phone,
                                'Zip'           => $a->zip,
                                'Referred By'   => $a->referred_by,
                                'Date'          => $a->cdate,
                                'Application ID' => $a->ap_id,
                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');
            }

            $total = $query->count();

            $preapplys = $query->orderBy('groomer_pre_apply.cdate', 'desc')
                ->paginate(20);

            return view('admin.pre_apply', [
                'msg'           => '',
                'preapplys'     => $preapplys,
                'sdate'         => $sdate->format('Y-m-d'),
                'edate'         => $edate->format('Y-m-d'),
                'full_name'     => $request->full_name,
                'phone'         => $request->phone,
                'zip'           => $request->zip,
                'email'         => $request->email,
                'referred_by'   => $request->referred_by,
                'total'         => $total
            ]);

        } catch (\Exception $ex) {
            return back()->withErrors([
                'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ])->withInput();
        }
    }

}
