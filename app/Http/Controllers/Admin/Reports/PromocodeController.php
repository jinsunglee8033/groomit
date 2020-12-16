<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 4/22/19
 * Time: 9:31 AM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\Groomer;
use App\Model\ProfitShare;
use App\Model\VWGroomerEvaluation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

//use DB;

class PromocodeController extends Controller
{

    public function performance(Request $request) {

        $sdate = Carbon::today()->addDays(-30);
        $edate = Carbon::today()->addDays(1)->addSeconds(-1);

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $package_query = "";
        if (!empty($request->package)) {
            $package_query = " and a.appointment_id in (select appointment_id from appointment_product where prod_id in (" . $request->package . "))";
        }

        $data = DB::select("
             select a.cdate, a.user_id, p.appointment_id, p.app_number, p.app_promo_type promo_type, p.app_promo_code promo_code
               from appointment_list a
               join profit_share p on a.appointment_id = p.appointment_id and p.type = 'A'
              where a.promo_code is not null
                and a.promo_amt > 0
                and p.app_number = 1
                and a.cdate >= :sdate
                and a.cdate <= :edate
                and p.id not in (select original_id from profit_share where type = 'V')
                " . $package_query. "
        ", [
          'sdate' => $sdate,
          'edate' => $edate
        ]);

        $summary_by_type = array(
            'G' => array(
                'first_customer_qty' => 0,
                'again_customer_qty' => 0,
                'fullpaid_qty' => 0,
            ),
            'T' =>  array(
              'first_customer_qty' => 0,
              'again_customer_qty' => 0,
              'fullpaid_qty' => 0,
            ),
            'R' => array(
              'first_customer_qty' => 0,
              'again_customer_qty' => 0,
              'fullpaid_qty' => 0,
            ),
            'A' => array(
              'first_customer_qty' => 0,
              'again_customer_qty' => 0,
              'fullpaid_qty' => 0,
            ),
            'N' => array(
              'first_customer_qty' => 0,
              'again_customer_qty' => 0,
              'fullpaid_qty' => 0,
            ),
        );

        $summary_by_code = [
            'SILVER50' => [
                'first_customer_qty' => 0,
                'again_customer_qty' => 0,
                'fullpaid_qty' => 0,
            ],
            'PAW20' => [
                'first_customer_qty' => 0,
                'again_customer_qty' => 0,
                'fullpaid_qty' => 0,
            ],
            'PAW10' => [
                'first_customer_qty' => 0,
                'again_customer_qty' => 0,
                'fullpaid_qty' => 0,
            ],
            'NEW10' => [
                'first_customer_qty' => 0,
                'again_customer_qty' => 0,
                'fullpaid_qty' => 0,
            ],
            'NEW25' => [
                'first_customer_qty' => 0,
                'again_customer_qty' => 0,
                'fullpaid_qty' => 0,
            ],
        ];

        $total = new \stdClass();
        $total->first_customer_qty = 0;
        $total->again_customer_qty = 0;
        $total->fullpaid_qty = 0;

        $pkeys = array_keys($summary_by_code);

        foreach ($data as $d) {
            if (empty($d->promo_type) || empty($d->promo_code)) continue;

            $promo_type = $d->promo_type == 'B' ? 'A' : $d->promo_type;

            $repeat_qty = ProfitShare::where('type', 'A')
              ->whereRaw('id not in (select original_id from profit_share where type = \'V\')')
              ->where('appointment_id', '<>', $d->appointment_id)
              ->whereRaw('appointment_id in (select appointment_id from appointment_list where user_id = ' . $d->user_id . ')')
              ->count();

            $fullpaid_qty = 0;

            if ($repeat_qty > 0) {
                $repeat_qty = 1;

                $fullpaid_qty = ProfitShare::where('type', 'A')
                  ->where('appointment_id', '<>', $d->appointment_id)
                  ->where('app_promo_amt', 0)
                  ->whereRaw('id not in (select original_id from profit_share where type = \'V\')')
                  ->whereRaw('appointment_id in (select appointment_id from appointment_list where user_id = ' . $d->user_id . ')')
                  ->count();

                if ($fullpaid_qty > 0) {
                    $fullpaid_qty = 1;
                }
            }

            $summary_by_type[$promo_type]['first_customer_qty'] = $summary_by_type[$promo_type]['first_customer_qty'] + 1;
            $summary_by_type[$promo_type]['again_customer_qty'] = $summary_by_type[$promo_type]['again_customer_qty'] + $repeat_qty;
            $summary_by_type[$promo_type]['fullpaid_qty'] = $summary_by_type[$promo_type]['fullpaid_qty'] + $fullpaid_qty;

            if (in_array($d->promo_code, $pkeys)) {
                $summary_by_code[$d->promo_code]['first_customer_qty'] = $summary_by_code[$d->promo_code]['first_customer_qty'] + 1;
                $summary_by_code[$d->promo_code]['again_customer_qty'] = $summary_by_code[$d->promo_code]['again_customer_qty'] + $repeat_qty;
                $summary_by_code[$d->promo_code]['fullpaid_qty'] = $summary_by_code[$d->promo_code]['fullpaid_qty'] + $fullpaid_qty;
            }

//            if ($fullpaid_qty > 0) {
//                print_r($d);
//                exit;
//            }

            $total->first_customer_qty += 1;
            $total->again_customer_qty += $repeat_qty;
            $total->fullpaid_qty += $fullpaid_qty;
        }

        if ($request->excel == 'Y') {
            Helper::log('########### EXCEL #############');

            Excel::create('groomer-evaluation', function($excel) use($data) {

                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                          'Groomer.ID' => $o->groomer_id,
                          'Groomer.Name' => $o->groomer_name,
                          'Appointment.Qty' => $o->appointment_qty,
                          'Hours.Available' => $o->hours_total,
                          'Sub.Total' => $o->sub_total,
                          'Promo.Paid' => $o->promo_amt,
                          'Tip' => $o->tip,
                          'Profit.Adjustment' => $o->adjust,
                          'Groomer.Fee' => $o->groomer_fee,
                          'Payout' => $o->payout,
                          'Weekly.Allowance' => $o->weekly_allowance,
                          'P/L' => $o->weekly_allowance > 0 || $o->payout > 0 ? number_format($o->payout - $o->weekly_allowance, 2) : '-'
                        ];
                    }

                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $data = Helper::arrayPaginator($data, $request);

        return view('admin.reports.promocode_performance', [
            'sdate' => $sdate,
            'edate' => $edate,
            'package' => $request->package,
            'data'  => $data,
            'summary_by_type' => $summary_by_type,
            'summary_by_code' => $summary_by_code,
            'total' => $total

        ]);

    }

}