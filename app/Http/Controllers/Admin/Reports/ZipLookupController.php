<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 1/22/19
 * Time: 11:35 AM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\Admin;
use App\Model\AllowedZip;
use App\Model\GiftcardSales;
use App\Model\Groomer;
use App\Model\Message;
use App\Model\User;
use App\Model\ZipQuery;
use Carbon\Carbon;
use Carbon\Laravel\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class ZipLookupController extends Controller
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

        if(!empty($request->state)) {
            $state = $request->state;
        }

//        $query = ZipQuery::leftJoin('allowed_zip', function($join) {
//                $join->on('zip_query.zip', '=', 'allowed_zip.zip')
//                 ->where('allowed_zip.available', '<>', 'x');}) ;

        $query = ZipQuery::whereRaw('cast(zip_query.cdate as date) >= \'' . $sdate . '\'')
                          ->whereRaw('cast(zip_query.cdate as date) <= \'' . $edate . '\'')
                          ->whereRaw( 'zip not in ( select zip from allowed_zip where available = \'x\' )' ) ;

        if(!empty($request->state)){
            $query = $query->whereRaw("allowed_zip.state_abbr ='$state'");
        }

        if ($request->excel == 'Y') {
            $data = $query->orderBy('zip_query.cdate', 'desc')->get([
                'zip_query.cdate',
                'zip_query.first_name',
                'zip_query.last_name',
                'zip_query.phone',
                'zip_query.email',
                'zip_query.zip',
                'zip_query.city',
                'zip_query.state'
            ]);

            Excel::create('ZipLookup', function($excel) use($data) {

                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                          'Date.Time' => $o->cdate,
                          'Zip' => $o->zip,
                          'City Name' => $o->city,
                          //'County Name' => $o->county_name,
                          'State' => $o->state,
                          //'ID' => $o->id,
                          'First Name' => $o->first_name,
                          'Last Name' => $o->last_name,
                          'Phone' => $o->phone,
                          'Email' => $o->email,
                        ];
                    }

                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $data = $query->orderBy('zip_query.cdate', 'desc')->paginate(50, [
          'zip_query.cdate',
          'zip_query.first_name',
          'zip_query.last_name',
          'zip_query.phone',
          'zip_query.email',
          'zip_query.zip',
          'zip_query.city',
          //'allowed_zip.county_name',
          'zip_query.state',
          'zip_query.id'
        ]);





        return view('admin.reports.ziplookup', [
          'data'  => $data,
          'sdate' => $sdate,
          'edate' => $edate,
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

    public function get_zip($zip) {
        try {
            $allowed_zip = null;

            if (!isset($zip)) {
                return response()->json([
                    'zip' => 'Please try again'
                ]);
            }

            $allowed_zip = \DB::table('allowed_zip')
                ->where('zip', $zip)
                ->get();

            return response()->json($allowed_zip);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_lookup(Request $request) {

        try {
            $msg = '';
            $v = Validator::make($request->all(), [
                'zip_id' => 'required'
            ]);

            if ($v->fails()) {
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $allowed_zip = AllowedZip::where('id', $request->zip_id)->first();

            if($request->available != ''){
                if($request->available == 'E'){
                    $allowed_zip->available = 'x';
                }else{
                    $allowed_zip->available = '';
                }

            }
            if($request->short_name != ''){
                $allowed_zip->short_name = $request->short_name;
            }
            if($request->group_id != ''){
                $allowed_zip->group_id = $request->group_id;
            }

            $allowed_zip->save();

            //return Redirect::route('admin.messages')->with('alert', $msg);
            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            \DB::rollback();

            $msg = $ex->getMessage() . ' - ' . $ex->getCode();

            Helper::send_mail('it@jjonbp.com', '[GroomIt][' . getenv('APP_ENV') .'] groomit.me/admin/messages/send', ' - msg: ' . $msg . '<br/> - error_msg : ' . $ex->getMessage() . '<br/> - error_code: ' . $ex->getTraceAsString());
            return response()->json([
                'msg' => $msg
            ]);
        }
    }

}