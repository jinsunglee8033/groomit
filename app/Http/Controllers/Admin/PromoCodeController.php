<?php

namespace App\Http\Controllers\Admin;

use App\Lib\PromoCodeProcessor;
use App\Model\Product;
use App\Model\PromoCodeUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\PromoCode;
use App\Lib\Helper;
use App\Model\User;
use App\Model\AppointmentList;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use Redirect;
use Excel;

class PromoCodeController extends Controller
{

    public function promo_codes(Request $request) {
        try {

            $query = PromoCode::query();

            if (!empty($request->code)) {
                $query = $query->whereRaw('code like \'%' . strtoupper($request->code) . '%\'');
            }else {
                if (!empty($request->promo_code)) {
                    $query = $query->whereRaw('code like \'%' . strtoupper($request->promo_code) . '%\'');
                }
            }

            if (!empty($request->type)) {
                $query = $query->where('type', $request->type);
            }

            if (!empty($request->first_only)) {
                $query = $query->where('first_only', $request->first_only);
            }

            if (!empty($request->redeemed)) {

                if ($request->redeemed == 'Y') {
                    $query = $query->whereRaw("
                        (
                            (
                                select count(*)
                                from appointment_list 
                                where status != 'C'
                                and promo_code = promo_code.code
                            ) > 0 
                            or
                            (
                                select count(*)
                                from credit
                                where type = 'C'
                                and category = 'S'
                                and status = 'A'
                                and referral_code = promo_code.code
                            ) > 0
                        )
                    ");
                } else {
                    $query = $query->whereRaw("
                        (
                            (
                                select count(*)
                                from appointment_list 
                                where status != 'C'
                                and promo_code = promo_code.code
                            ) = 0 
                            and
                            (
                                select count(*)
                                from credit
                                where type = 'C'
                                and category = 'S'
                                and status = 'A'
                                and referral_code = promo_code.code
                            ) = 0
                        )
                    ");
                }
            }

            if ($request->excel == 'Y') {
                $promo_codes = $query->orderBy('code', 'asc')->get();
                Excel::create('promo_codes', function($excel) use($promo_codes) {

                    $excel->sheet('reports', function($sheet) use($promo_codes) {

                        $data = [];
                        foreach ($promo_codes as $a) {
                            $row = [

                                'Promo ' => $a->code,
                                'Type' => $a->type_name(),
                                'Amount.Type' => $a->amt_type_name(),
                                'Amount' => '$' . $a->amt,
                                'Redeemed.Appointment' => $a->redeemed_appointment(''),
                                'Redeemed.Amt' => $a->redeemed_appointment('amount'),
                                'Redeemed.date' => $a->redeemed_appointment('date'),
                                'Status' => $a->status_name()
                            ];

                            $data[] = $row;

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $total = $query->count();

            $promo_codes = $query->orderBy('type', 'asc')
                ->paginate(20);

            $packages = Product::where('prod_type', 'P')->orderBy('pet_type', 'desc')->get();

            return view('admin.promo_codes', [
                'msg' => '',
                'promo_codes' => $promo_codes,
                'packages' => $packages,
                'redeemed' => $request->redeemed,
                'type' => $request->type,
                'promo_code' => $request->promo_code,
                'first_only' => $request->first_only,
                'total' => $total
            ])->withModel($promo_codes);



        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function redeemed_groupon(Request $request) {
        try {

            $query = PromoCode::query();

            $query->where('type','G');

            $sdate = Carbon::today()->subDays(30);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);
            $date_query = "";

            if (!empty($request->sdate)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            if (!empty($request->promo_code)) {
                $query = $query->whereRaw('code like \'%' . strtoupper($request->promo_code) . '%\'');
            }

            if (!empty($sdate)) {
                $date_query = " and cdate >= '" . $sdate . "'";
            }

            if (!empty($edate)) {
                $date_query .= " and cdate <= '" . $edate . "'";
            }


            $query = $query->whereRaw("
                (
                    (
                        select count(*)
                        from appointment_list 
                        where status != 'C'
                        and promo_code = promo_code.code
                        $date_query
                    ) > 0 
                    or
                    (
                        select count(*)
                        from credit
                        where type = 'C'
                        and category = 'S'
                        and status = 'A'
                        and referral_code = promo_code.code
                        $date_query
                    ) > 0
                )
            ");


            if ($request->excel == 'Y') {
                $promo_codes = $query->orderBy('code', 'asc')->get();
                Excel::create('redeemed_groupon', function($excel) use($promo_codes) {

                    $excel->sheet('reports', function($sheet) use($promo_codes) {

                        $data = [];
                        foreach ($promo_codes as $a) {
                            $row = [

                                'PromoCode' => $a->code,
                                'Amount' => '$' . $a->amt,
                                'Redeemed.Appointment' => $a->redeemed_appointment(''),
                                'Redeemed.Amt' => $a->redeemed_appointment('amount'),
                                'Redeemed.date' => $a->redeemed_appointment('date'),
                                'Status' => $a->status_name()
                            ];

                            $data[] = $row;

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $total = $query->count();

            $promo_codes = $query->orderBy('type', 'asc')
                ->paginate(20);

            return view('admin.redeemed_groupon', [
                'msg' => '',
                'promo_codes' => $promo_codes,
                'redeemed' => $request->redeemed,
                'type' => $request->type,
                'promo_code' => $request->promo_code,
                'total' => $total,
                'sdate' => $sdate->format('Y-m-d'),
                'edate' => $edate->format('Y-m-d')
            ])->withModel($promo_codes);



        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }


    public function promo_redeemed_history(Request $request) {
        try {
            $sdate = Carbon::today()->subDays(30);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            if (!empty($request->sdate)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }
            if (!empty($request->edate)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            if (!empty($request->type)) {
                if($request->type == 'B'){
                    // A or B for Affiliate
                    $condition = " and p.type in ('A', 'B') ";
                }else {
                    $condition = " and p.type in ('$request->type') ";
                }
            }else{
                $condition = " ";
            }

            if (!empty($request->owner_name)) {
                $query1 = " and upper(c.name) like '%".strtoupper($request->owner_name)."%' ";
                $query2 = " and upper(d.business_name) like '%".strtoupper($request->owner_name)."%' ";
                $query3 = " and upper(u.first_name) like '%".strtoupper($request->owner_name)."%' ";
                $query4 = " and upper(g.first_name) like '%".strtoupper($request->owner_name)."%' ";
            }else {
                $query1 = " ";
                $query2 = " ";
                $query3 = " ";
                $query4 = " ";
            }

            $query = "
            select p.code, p.type, p.amt_type, p.amt, c.name, p.cdate, a.appointment_id, a.promo_amt, a.accepted_date, '' owner_name
              from appointment_list a
             inner join promo_code p on a.promo_code = p.code 
                    $condition
              left join admin c on p.created_by = c.admin_id
             where a.status = 'P'
               and p.created_by > 0
               and p.type not in ('B')
               and a.accepted_date >= '$sdate'
               and a.accepted_date <= '$edate 23:59:59'
               and p.code like '%".strtoupper($request->promo_code)."%'
                $query1
             union all
            select p.code, p.type, p.amt_type, p.amt, d.business_name, c.cdate, a.appointment_id, a.promo_amt, a.accepted_date, d.business_name owner_name
              from appointment_list a
             inner join promo_code p on a.promo_code = p.code
                    $condition
              left join affiliate_code c on a.promo_code = c.aff_code
              left join affiliate d on c.aff_id = d.aff_id
             where a.status = 'P'
               and p.created_by = 0
               and p.type in ('B','A')
               and a.accepted_date >= '$sdate'
               and a.accepted_date <= '$edate 23:59:59'
               and p.code like '%".strtoupper($request->promo_code)."%'
                $query2
             union all
            select p.code, p.type, p.amt_type, p.amt, u.first_name, p.cdate, a.appointment_id, a.promo_amt, a.accepted_date, u.first_name owner_name
              from appointment_list a
             inner join promo_code p on a.promo_code = p.code
                    $condition
             inner join user u on p.user_id = u.user_id
             where a.status = 'P'
               and p.created_by =  0
               and p.type in ('R')
               and a.accepted_date >= '$sdate'
               and a.accepted_date <= '$edate 23:59:59'
               and p.code like '%".strtoupper($request->promo_code)."%'
                $query3
             union all
            select p.code, p.type, p.amt_type, p.amt, 'System', p.cdate, a.appointment_id, a.promo_amt, a.accepted_date, g.first_name owner_name
              from appointment_list a
             inner join promo_code p on a.promo_code = p.code
                    $condition
             inner join groomer g on p.groomer_id = g.groomer_id
             where a.status = 'P'
               and p.created_by =  0
               and p.type in ('R')
               and a.accepted_date >= '$sdate'
               and a.accepted_date <= '$edate 23:59:59'
               and p.code like '%".strtoupper($request->promo_code)."%'
                $query4
             order by accepted_date desc  
            ";

            if ($request->excel == 'Y') {
                $promo_history = DB::select($query);
                Excel::create('promo_redeemed_history', function($excel) use($promo_history) {
                    $excel->sheet('reports', function($sheet) use($promo_history) {
                        $data = [];
                        foreach ($promo_history as $a) {
                            $row = [
                                'Promo.Code' => $a->code,
                                'Code.Type' => $a->type,
                                'Amount.Type' => $a->amt_type,
                                'Amount.Ratio' => $a->amt,
                                'Created.By.Who' => $a->name,
                                'Code.Created.Date' => $a->cdate,
                                'Appointment.ID' => $a->appointment_id,
                                'Redeemed.Amount' => $a->promo_amt,
                                'Code.Redeemed.Date' => $a->accepted_date
                            ];
                            $data[] = $row;
                        }
                        $sheet->fromArray($data);
                    });
                })->export('xlsx');
            }

            $data = DB::select($query);

            return view('admin.promo_redeemed_history', [
                'msg' => '',
                'promo_codes' => $data,
                'promo_code' => $request->promo_code,
                'sdate' => $sdate->format('Y-m-d'),
                'edate' => $edate->format('Y-m-d'),
                'type' => $request->type,
                'owner_name' => $request->owner_name
            ]);



        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function change_status(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'code' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $promo_code = PromoCode::whereRaw('code = ?', [strtoupper($request->code)])->first();
            if (empty($promo_code)) {
                return response()->json([
                    'msg' => 'Invalid promo code information'
                ]);
            }

            if ($promo_code->status == 'A') {
                $promo_code->status = 'I';
            } else {
                $promo_code->status = 'A';
            }

            $promo_code->mdate = Carbon::now();
            $promo_code->modified_by = Auth::guard('admin')->user()->admin_id;
            $promo_code->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return response()->json([
                'msg' => $msg
            ]);
        }
    }

    public function load(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'code' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return response()->json([
                    'msg' =>  $msg
                ]);
            }

            $promo_code = PromoCode::find(strtoupper($request->code));
            if (empty($promo_code)) {
                throw new \Exception('Unabled to find promo code in our system.', -1);
            }

            $promo_code->valid_user_ids = PromoCodeUsers::where('promo_code', $promo_code->code)->get();

            return response()->json([
                'msg' => '',
                'promo_code' => $promo_code
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ' ]'
            ]);
        }
    }

    public function add(Request $request) {
        try {

            if (!empty($request->codes)) {
                return $this->upload($request);
            }

            $v = Validator::make($request->all(), [
                'code' => 'required',
                'amt_type' => 'required',
                'amt' => 'required',
                'status' => 'required',
                'expire_date' => 'required|date'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return response()->json([
                    'msg' =>  $msg
                ]);
            }

            $new_promo_code = $request->code;

            $exist_promo = PromoCode::whereRaw("code = '". strtoupper($new_promo_code) . "'")->first();
            if (!empty($exist_promo)) {
                return response()->json([
                    'msg' =>  'You entered existing Promo Code. Please enter another one.'
                ]);
            }

            $promo_code = new PromoCode;
            $promo_code->type = 'N';
            $promo_code->status = $request->status;
            $promo_code->amt_type = $request->amt_type;
            $promo_code->amt = $request->amt;
            $promo_code->code = strtoupper($new_promo_code);
            $promo_code->note = $request->note;
            $promo_code->cdate = Carbon::now();
            $promo_code->influencer = empty($request->influencer) ? 'N' : $request->influencer;
            $promo_code->first_only = empty($request->first_only) ? 'N' : $request->first_only;
            $promo_code->no_insurance = empty($request->no_insurance) ? 'N' : $request->no_insurance;
            $promo_code->include_tax = empty($request->include_tax) ? 'N' : $request->include_tax;
            $promo_code->expire_date = $request->expire_date;
            $promo_code->states = $request->states;
            $promo_code->package_ids = $request->package_ids;
            $promo_code->valid_user_ids = $request->valid_user_ids;
            $promo_code->created_by = Auth::guard('admin')->user()->admin_id;

            $promo_code->save();

            if (!empty($request->valid_user_ids)) {
                $user_ids = preg_split('/[\ \r\n\,]+/', trim($request->valid_user_ids));
                PromoCodeUsers::set_users($promo_code->code, $user_ids);
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return response()->json([
                'msg' => $msg
            ]);
        }
    }

    public function upload(Request $request) { //Might not used any longer
        try {

            $v = Validator::make($request->all(), [
              'codes'       => 'required',
//              'type'        => 'required',
              'amt_type'    => 'required',
              'amt'         => 'required',
//              'groupon_amt' => 'required',
              'status'      => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return response()->json([
                  'msg' =>  $msg
                ]);
            }

            $codes = explode(PHP_EOL, $request->codes);

            foreach ($codes as $new_promo_code) {

                $exist_promo = PromoCode::whereRaw("code = '". strtoupper($new_promo_code) . "'")->first();
                if (!empty($exist_promo)) {
                    return response()->json([
                      'msg' =>  'You entered existing Promo Code [' . $new_promo_code . '] . Please enter another one.'
                    ]);
                }

                $promo_code = new PromoCode;
                $promo_code->type           = 'N';
                $promo_code->status         = $request->status;
                $promo_code->amt_type       = $request->amt_type;
                $promo_code->amt            = $request->amt;
                $promo_code->groupon_amt    = $request->groupon_amt;
                $promo_code->code           = strtoupper($new_promo_code);
                $promo_code->note           = $request->note;
                $promo_code->cdate          = Carbon::now();
                $promo_code->influencer     = empty($request->influencer) ? 'N' : $request->influencer;
                $promo_code->first_only     = empty($request->first_only) ? 'N' : $request->first_only;
                $promo_code->no_insurance   = empty($request->no_insurance) ? 'N' : $request->no_insurance;
                $promo_code->include_tax    = empty($request->include_tax) ? 'N' : $request->include_tax;
                $promo_code->expire_date    = empty($request->expire_date) ? '2999-12-31' : $request->expire_date;
                $promo_code->states   = empty($request->states) ? '' : $request->states;
                $promo_code->created_by     = Auth::guard('admin')->user()->admin_id;

                $promo_code->save();

                if (!empty($request->valid_user_ids)) {
//                    $user_ids = explode(PHP_EOL, $request->valid_user_ids);
                    $user_ids = preg_split('/[\ \r\n\,]+/', $request->valid_user_ids);
                    PromoCodeUsers::set_users($promo_code->code, $user_ids);
                }
            }

            return response()->json([
              'msg' => ''
            ]);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return response()->json([
              'msg' => $msg
            ]);
        }
    }

    public function update(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'code' => 'required',
                'amt_type' => 'required',
                'amt' => 'required',
                'status' => 'required',
                'expire_date' => 'required|date'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return response()->json([
                    'msg' =>  $msg
                ]);
            }

            $new_promo_code = $request->code;

            $promo_code = PromoCode::whereRaw("code = '". strtoupper($new_promo_code) . "'")->first();
            if (empty($promo_code)) {
                return response()->json([
                    'msg' =>  'Promo code does not exist'
                ]);
            }

            $promo_code->status = $request->status;
            $promo_code->amt_type = $request->amt_type;
            $promo_code->amt = $request->amt;
            $promo_code->note = $request->note;
            $promo_code->influencer = empty($request->influencer) ? 'N' : $request->influencer;
            $promo_code->first_only = empty($request->first_only) ? 'N' : $request->first_only;
            $promo_code->no_insurance = empty($request->no_insurance) ? 'N' : $request->no_insurance;
            $promo_code->include_tax = empty($request->include_tax) ? 'N' : $request->include_tax;
            $promo_code->expire_date = $request->expire_date;
            $promo_code->states = $request->states;
            $promo_code->package_ids = $request->package_ids;
            $promo_code->valid_user_ids = $request->valid_user_ids;
            $promo_code->mdate = Carbon::now();
            $promo_code->modified_by = Auth::guard('admin')->user()->admin_id;

            $promo_code->save();

            if (!empty($request->valid_user_ids)) {
                $user_ids = preg_split('/[\ \r\n\,]+/', trim($request->valid_user_ids));
                PromoCodeUsers::set_users($promo_code->code, $user_ids);
            }else{
                PromoCodeUsers::where('promo_code', $promo_code->code)->delete();
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return response()->json([
                'msg' => $msg
            ]);
        }
    }
}
