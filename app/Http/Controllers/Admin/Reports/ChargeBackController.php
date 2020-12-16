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
use App\Lib\SimValueBinder;
use App\Model\Admin;
use App\Model\AllowedZip;
use App\Model\ChargeBack;
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
use Illuminate\Support\Facades\Input;

class ChargeBackController extends Controller
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

        $query = ChargeBack::whereNotNull('case_id');

        if ($request->excel == 'Y') {
            $data = $query->orderBy('id', 'desc')->get();

            Excel::create('ChargeBack', function($excel) use($data) {

                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                            'ID' => $o->id,
                            'Case.ID' => $o->case_id,
                            'MID' => $o->m_id,
                            'Case.Stage' => $o->case_stage,
                            'Transaction.Date' => $o->transaction_date,
                            'Response.Expiration' => $o->response_expiration,
                            'Financial.Action.Amount' => $o->financial_action_amount,
                            'Currency' => $o->currency,
                            'Financial.Action.Date' => $o->financial_action_date,
                            'Card.Brand/Reason.Code' => $o->card_brand_reason_code,
                            'Reason.Code.Description' => $o->reason_code_description,
                            'APP.ID' => $o->app_id,
                            'Bakkar.Comments' => $o->bakkar_comments,
                            'Customer.Service.Comments' => $o->customer_service_comments,
                            'Groomer.Name' => $o->groomer_name,
                            'Service.Date' => $o->service_date,
                            'Credit.Back' => $o->credit_back,
                            'Upload.Date' => $o->upload_date,
                        ];
                    }

                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $data = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.reports.chargeback', [
          'data'  => $data,
          'sdate' => $sdate,
          'edate' => $edate
        ]);
    }

    public function upload(Request $request)
    {

        Helper::log('### Charge Back Upload Exception ###', [
            'res' => $request->all()
        ]);

        ini_set('max_execution_time', 600);

        DB::beginTransaction();

        $line = '';

        try {

            $key = 'charge_back_csv_file';

            if (Input::hasFile($key) && Input::file($key)->isValid()) {

                $path = Input::file($key)->getRealPath();

                $binder = new SimValueBinder();
                $results = Excel::setValueBinder($binder)->load($path)->setSeparator('_')->get();

                $line_no = 0;

                foreach ($results as $row) {

                    $line_no++;

                    $case_id = $row->case_id;
                    $mid = $row->mid;
                    $case_stage = $row->case_stage;
                    $transaction_date = $row->transaction_date;
                    $response_expiration = $row->response_expiration;
                    $financial_action_amount = $row->financial_action_amount;
                    $currency = $row->currency;
                    $financial_action_date = $row->financial_action_date;
                    $card_brand_reason_code = $row->card_brand_reason_code;
                    $reason_code_description = $row->reason_codedescription;
                    $app_id = $row->appid;
                    $bakkar_comments = $row->bakkar_comments;
                    $customer_service_comments = $row->customer_service_comments;
                    $groomer_name = $row->groomer_name;
                    $service_date = $row->service_date;
                    $credit_back = $row->credit_back;

                    if (trim($case_id) == '') {
                        continue;
                    }

                    $cb_obj = ChargeBack::where('case_id', $case_id)->first();

                    if (!empty($cb_obj)) {
                        return response()->json([
                            'code' => '-1',
                            'msg' => 'Groomer Not Available',
                        ]);
//                        return response()->json([
//                            'msg' => 'Duplicated data found: '. $line_no
//                        ]);
                    } else {
                        $cb_obj = new ChargeBack();
                    }

                    $cb_obj->case_id = $case_id;
                    $cb_obj->m_id = $mid;
                    $cb_obj->case_stage = $case_stage;
                    $cb_obj->transaction_date = $transaction_date;
                    $cb_obj->response_expiration = $response_expiration;
                    $cb_obj->financial_action_amount = $financial_action_amount;
                    $cb_obj->currency = $currency;
                    $cb_obj->financial_action_date = $financial_action_date;
                    $cb_obj->card_brand_reason_code = $card_brand_reason_code;
                    $cb_obj->reason_code_description = $reason_code_description;
                    $cb_obj->app_id = $app_id;
                    $cb_obj->bakkar_comments = $bakkar_comments;
                    $cb_obj->customer_service_comments = $customer_service_comments;
                    $cb_obj->groomer_name = $groomer_name;
                    $cb_obj->service_date = $service_date;
                    $cb_obj->credit_back = $credit_back;
                    $cb_obj->upload_date = Carbon::now();

                    $cb_obj->save();
                }

            }else{
                DB::rollback();

                return response()->json([
                    'msg' => 'Please select valid file'
                ]);
            }

            DB::commit();

            return redirect('/admin/reports/chargeback');


        } catch (\Exception $ex) {
            DB::rollback();

            Helper::log('### PIN Upload Exception ###', [
                'line' => $line,
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString()
            ]);

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
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

    public function update(Request $request) {

        try {

            $charge_back = ChargeBack::where('id', $request->chargeback_id)->first();

            $charge_back->case_stage = $request->case_stage;
            $charge_back->transaction_date = $request->transaction_date;
            $charge_back->response_expiration = $request->response_expiration;
            $charge_back->financial_action_amount = $request->financial_action_amount;
            $charge_back->currency = $request->currency;
            $charge_back->financial_action_date = $request->financial_action_date;
            $charge_back->card_brand_reason_code = $request->card_brand_reason_code;
            $charge_back->reason_code_description = $request->reason_code_description;
            $charge_back->app_id = $request->app_id;
            $charge_back->bakkar_comments = $request->bakkar_comments;
            $charge_back->customer_service_comments = $request->customer_service_comments;
            $charge_back->groomer_name = $request->groomer_name;
            $charge_back->service_date = $request->service_date;
            $charge_back->credit_back = $request->credit_back;
            $charge_back->update_date = Carbon::now();

            $charge_back->save();

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