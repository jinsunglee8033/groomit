<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\Affiliate;
use App\Model\AffiliateCode;
use App\Model\AffiliateRedeemHistory;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use Redirect;
use Excel;
use Illuminate\Support\Facades\Input;


class AffiliateController extends Controller
{
    public function index() {

        if (Auth::guard('admin')->check()) {
            return Redirect::route('admin.appointments');
        } else {
            return Redirect::route('admin.login');
        }

    }

    public function affiliates(Request $request) {
        try {

            $sdate = Carbon::create(2017, 1, 1); //Carbon::today()->subMonths(1);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $query = Affiliate::select('*');

            if (!empty($sdate) && empty($request->id)) {
                $query = $query->where('cdate', '>=', $sdate);
            }

            if (!empty($edate) && empty($request->id)) {
                $query = $query->where('cdate', '<=', $edate);
            }

            if (!empty($request->name)) {
                $query = $query->whereRaw('LOWER(first_name) like \'%' . strtolower($request->name) . '%\' or LOWER(last_name) like \'%' . strtolower($request->name) . '%\'');
            }

            if (!empty($request->email)) {
                $query = $query->whereRaw('LOWER(email) like \'%' . strtolower($request->email) . '%\'');
            }

            if (!empty($request->business_name)) {
                $query = $query->whereRaw('LOWER(business_name) like \'%' . strtolower($request->business_name) . '%\'');
            }

            if ($request->excel == 'Y') {
                $aa = $query->orderBy('cdate', 'desc')->get();
                Excel::create('affiliates', function($excel) use($aa) {

                    $excel->sheet('reports', function($sheet) use($aa) {

                        $data = [];
                        foreach ($aa as $a) {

                            $row = [
                                'Affiliate ID' => $a->aff_id,
                                'Business Name' => $a->business_name,
                                'Name' => $a->full_name(),
                                'Email' => $a->email,
                                'Bank Name.' => $a->bank_name,
                                'Bank Account #' => $a->bank_account_number,
                                'Routing Number' => $a->routing_number,
                                'Date' => $a->cdate
                            ];

                            $data[] = $row;

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $affiliates = $query->orderBy('cdate', 'desc')->get();

            $total = $affiliates->count();

            foreach ($affiliates as $a) {
                $a->earnings = AffiliateRedeemHistory::earnings($a->aff_id);
                $a->redeemed_amt = AffiliateRedeemHistory::redeemed_amt($a->aff_id);
            }

            return view('admin.affiliates', [
                'msg' => '',
                'affiliates' => $affiliates,
                'sdate' => $sdate->format('Y-m-d'),
                'edate' => $edate->format('Y-m-d'),
                'name' => $request->name,
                'business_name' => $request->business_name,
                'email' => $request->email,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function withdraw_requests(Request $request) {
        try {

            $sdate = Carbon::today()->subMonths(1);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }


            $query = DB::table('affiliate_redeem_history AS a')
                ->leftjoin('affiliate as b', 'a.aff_id', '=', 'b.aff_id')
                ->select([
                    'a.*',
                    'b.first_name',
                    'b.last_name',
                    'b.email',
                    'b.phone',
                    'b.business_name',
                    'b.bank_name',
                    'b.bank_account_number',
                    'b.routing_number',
                    'b.address',
                    'b.address2',
                    'b.city',
                    'b.state',
                    'b.zip'
                ]);

            if (!empty($sdate) && empty($request->id)) {
                $query = $query->where('a.cdate', '>=', $sdate);
            }

            if (!empty($edate) && empty($request->id)) {
                $query = $query->where('a.cdate', '<=', $edate);
            }

            if (!empty($request->name)) {
                $query = $query->whereRaw('LOWER(b.first_name) like \'%' . strtolower($request->name) . '%\' or LOWER(b.last_name) like \'%' . strtolower($request->name) . '%\'');
            }

            if (!empty($request->email)) {
                $query = $query->whereRaw('LOWER(b.email) like \'%' . strtolower($request->email) . '%\'');
            }

            if (!empty($request->business_name)) {
                $query = $query->whereRaw('LOWER(b.business_name) like \'%' . strtolower($request->business_name) . '%\'');
            }

            if ($request->excel == 'Y') {
                $aa = $query->orderBy('a.cdate', 'desc')->get();
                Excel::create('affiliates', function($excel) use($aa) {

                    $excel->sheet('reports', function($sheet) use($aa) {

                        $data = [];
                        foreach ($aa as $a) {

                            $row = [
                                'Affiliate ID' => $a->aff_id,
                                'Business Name' => $a->business_name,
                                'Name' => $a->first_name . ' ' . $a->last_name,
                                'Email' => $a->email,
                                'Phone' => $a->phone,
                                'Bank Name.' => $a->bank_name,
                                'Bank Account #' => $a->bank_account_number,
                                'Routing Number' => $a->routing_number,
                                'Address' => $a->address . ' ' . $a->address2 . ', ' . $a->city . ', ' . $a->state . ' ' . $a->zip ,
                                'Date' => $a->cdate
                            ];

                            $data[] = $row;

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $withdraw_req = $query->orderBy('cdate', 'desc')
                ->paginate(50);

            $total = $withdraw_req->count();

            foreach ($withdraw_req as $a) {
                $a->earnings = AffiliateRedeemHistory::earnings($a->aff_id);
                $a->redeemed_amt = AffiliateRedeemHistory::redeemed_amt($a->aff_id);
            }

            return view('admin.affiliate_withdraw_requests', [
                'msg' => '',
                'withdraw_req' => $withdraw_req,
                'sdate' => $sdate->format('Y-m-d'),
                'edate' => $edate->format('Y-m-d'),
                'name' => $request->name,
                'business_name' => $request->business_name,
                'email' => $request->email,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function affiliate($id) {
        try {

            $affiliate = Affiliate::findOrFail($id);
            $codes = AffiliateCode::where('aff_id', $id)->get();

            foreach($codes as $c) {
                $c->earned_amt = AffiliateRedeemHistory::earned_amt($c->aff_code);
            }

            $redeems = AffiliateRedeemHistory::where('aff_id', $id)->get();
            $earnings = AffiliateRedeemHistory::earnings($id);
            $redeemed_amt = AffiliateRedeemHistory::redeemed_amt($id);

            return view('admin.affiliate', [
                'msg' => '',
                'affiliate' => $affiliate,
                'codes' => $codes,
                'redeems' => $redeems,
                'earnings' => $earnings,
                'redeemed_amt' => $redeemed_amt
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }


    public function change_redeem_status (Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'id' => 'required',
                'status' => 'required'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return response()->json([
                    'msg' => $msg
                ]);
            }

            if ($auth = Auth::guard('admin')->user()) {
                $admin_id = $auth->admin_id;
            } else {
                $msg = 'Admin auth required';
                return response()->json([
                    'msg' => $msg
                ]);
            }

            $aff_redeem = AffiliateRedeemHistory::findOrFail($request->id);
            $aff_redeem->status = $request->status;
            $aff_redeem->mdate = Carbon::now();
            $aff_redeem->m_id = $admin_id;
            $aff_redeem->save();

            return response()->json([
                'msg' => ''
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function update(Request $request) {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'aff_id' => 'required',
                'business_name' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required',
                'password' => 'same:confirm_password|min:6'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.affiliate', array('id' => $request->aff_id))->with('alert', $msg);
            }

            $affiliate = Affiliate::findOrFail($request->aff_id);
            if (empty($affiliate)) {
                $msg = 'Invalid affiliate ID provided';
                return Redirect::route('admin.affiliate', array('id' => $request->aff_id))->with('alert', $msg);
            }

            $affiliate->business_name = $request->business_name;
            $affiliate->first_name = $request->first_name;
            $affiliate->last_name = $request->last_name;
            $affiliate->phone = $request->phone;
            $affiliate->address = $request->address;
            $affiliate->address2 = $request->address2;
            $affiliate->city = $request->city;
            $affiliate->state = $request->state;
            $affiliate->zip = $request->zip;
            $affiliate->bank_name = $request->bank_name;
            $affiliate->bank_account_number = $request->bank_account_number;
            $affiliate->routing_number = $request->routing_number;

            if (!empty($request->password)) {
                $affiliate->password = bcrypt($request->password);
            }

            $key = 'affiliate_photo';
            if (Input::hasFile($key)){
                if (Input::file($key)->isValid()) {
                    $path = Input::file($key)->getRealPath();
                    Log::info('### FILE ###', [
                        'key' => $key,
                        'path' => $path
                    ]);
                    $contents = file_get_contents($path);
                    $name = Input::file($key)->getClientOriginalName();
                    $affiliate->affiliate_photo = base64_encode($contents);
                } else {
                    DB::rollback();
                    $msg = 'Invalid profile photo provided';
                    return Redirect::route('admin.affiliate', array('id' => $request->aff_id))->with('alert', $msg);
                }
            }

            $affiliate->save();

            DB::commit();

            $msg = "Success";

            return Redirect::route('admin.affiliate', array('id' => $request->aff_id))->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            return Redirect::route('admin.affiliate', array('id' => $request->aff_id))->with('alert', $msg);
        }
    }

}
