<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/11/18
 * Time: 4:08 PM
 */


namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\GiftcardSales;
use Carbon\Carbon;
use Carbon\Laravel\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VoucherController extends Controller
{

    public function show(Request $request) {

        $sdate = Carbon::today()->subDays(6);
        $edate = Carbon::today();

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d', $request->sdate);
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d', $request->edate);
        }

        $query = GiftcardSales::join('user', 'giftcard_sales.created_by', '=', 'user.user_id')
            ->whereIn('giftcard_sales.status', ['S', 'V'])
            ->whereRaw('cast(giftcard_sales.cdate as date) >= \'' . $sdate . '\'')
            ->whereRaw('cast(giftcard_sales.cdate as date) <= \'' . $edate . '\'');

        if ($request->excel == 'Y') {
            $data = $query->orderBy('cdate', 'desc')->get([
              'giftcard_sales.*',
              'user.first_name'
            ]);

            Excel::create('voucher-sales', function($excel) use($data) {

                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                            'ID' => $o->id,
                            'Date.Time' => $o->cdate,
                            'Amt' => $o->amt,
                            'Cost' => $o->cost,
                            'Sendor' => $o->first_name,
                            'Recipient.Name' => $o->recipient_name,
                            'Recipient.Email' => $o->recipient_email,
                            'Status'  => $o->status
                        ];
                    }

                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $total = new \stdClass();
        $total->amt = $query->sum(DB::raw("case when giftcard_sales.status = 'S' then amt else -amt end"));
        $total->cost = $query->sum(DB::raw("case when giftcard_sales.status = 'S' then cost else -cost end"));
        $total->qty = $query->sum(DB::raw("case when giftcard_sales.status = 'S' then 1 else -1 end"));
        $sales = $query->orderBy('cdate', 'desc')->paginate(15, [
              'giftcard_sales.*',
              'user.user_id',
              'user.first_name',
              'user.last_name'
            ]);

        return view('admin.reports.vouchers', [
            'total' => $total,
            'sales' => $sales,
            'sdate' => $sdate,
            'edate' => $edate
        ]);
    }

    public function sales_pop(Request $request, $user_id) {

        $sdate = Carbon::today()->subDays(365);
        $edate = Carbon::today();

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $query = GiftcardSales::where('created_by', $user_id)
          ->where('status', 'S')
          ->where('cdate', '>=', $sdate)
          ->where('cdate', '<=', $edate);

        $sales = $query->orderBy('cdate', 'desc')->paginate(15);

        return view('admin.reports.voucher-sales-pop', [
          'sales' => $sales
        ]);
    }
}