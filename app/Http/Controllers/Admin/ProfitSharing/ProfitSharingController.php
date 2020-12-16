<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/22/17
 * Time: 11:13 AM
 */

namespace App\Http\Controllers\Admin\ProfitSharing;

use App\Lib\ProfitSharingProcessor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Model\AppointmentList;
use App\Model\Product;
use App\Model\ProfitShare;
use App\Model\ProfitShareDetail;
use App\Model\ProfitSharingSetup;
use App\Model\ProfitSharingExceptionGroomer;
use App\Model\ProfitSharingExceptionUser;
use App\Model\ProfitSharing;
use App\Model\ProfitSharingDetail;
use App\Model\Groomer;
use App\Model\User;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use Excel;

class ProfitSharingController extends Controller
{

    public function show(Request $request) {

        $share_setups = Product::leftjoin('profit_sharing_setup', 'profit_sharing_setup.package_id', '=', 'product.prod_id')
            ->where('product.prod_type', 'P')
            ->orderBy('profit_sharing_setup.package_id')
            ->get([
                'profit_sharing_setup.groomer_profit',
                'product.prod_id',
                'product.pet_type',
                'product.prod_name'
            ]);

        $query = ProfitSharingExceptionGroomer::whereRaw("1=1");
        if (!empty($request->id)) {
            $query->where('groomer_id', $request->id);
        }

        if (!empty($request->name)) {
            $query->whereRaw("groomer_id in (select groomer_id from groomer where lower(concat(first_name, ' ', last_name)) like '%" . strtolower($request->name) . "%') ");
        }

        if (!empty($request->phone)) {
            $query->whereRaw("groomer_id in (select groomer_id from groomer where phone like '%" . strtolower($request->phone) . "%' or mobile_phone like '%" . strtolower($request->phone) . "%') ");
        }

        if (!empty($request->email)) {
            $query->whereRaw("groomer_id in (select groomer_id from groomer where lower(email) like '%" . strtolower($request->email) . "%') ");
        }

        $groomer_exceptions = $query->get();

        return view('admin.profit_sharing.show', [
            'id'    => $request->id,
            'name'  => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'share_setups' => $share_setups,
            'groomer_exceptions' => $groomer_exceptions
        ]);
    }

    public function updateDefault(Request $request) {
        try {
            $packages = Product::where('prod_type', 'P')->get();
            foreach ($packages as $p) {

                $vp = 'groomer_profit_' . $p->prod_id;
                $rp = $request->$vp;
                if (!empty($rp)) {
                    $setup = ProfitSharingSetup::find($p->prod_id);
                    if (empty($setup)) {
                        $setup = new ProfitSharingSetup();
                        $setup->package_id = $p->prod_id;
                        $setup->cdate = Carbon::now();
                        $setup->created_by = Auth::guard('admin')->user()->admin_id;
                    } else {
                        $setup->mdate = Carbon::now();
                        $setup->modified_by = Auth::guard('admin')->user()->admin_id;
                    }
                    $setup->groomer_profit = $rp;
                    $setup->save();
                }
            }

            return back()->with([
                'success' => 'Your request has been processed successfully!'
            ])->withInput();


        } catch (\Exception $ex) {
            return back()->withErrors([
                'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ])->withInput();
        }
    }

    public function searchGroomer(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_search' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $groomers = Groomer::whereIn('status', ['N', 'A'])
                ->where(function($query) use($request) {
                $query->whereRaw('lower(first_name) like ?', ['%' . strtolower($request->groomer_search) . '%'])
                        ->orWhereRaw('lower(last_name) like ?', ['%' . strtolower($request->groomer_search) . '%'])
                        ->orWhereRaw('phone like ?', ['%' . strtolower($request->groomer_search) . '%'])
                        ->orWhereRaw('mobile_phone like ?', ['%' . strtolower($request->groomer_search) . '%'])
                        ->orWhereRaw('lower(email) like ?', ['%' . strtolower($request->groomer_search) . '%']);
                })
                ->orderBy('first_name', 'asc','last_name','asc')
                ->get();

            if (empty($groomers)) {
                $groomers = [];
            }
            return response()->json([
                'msg' => '',
                'groomers' => $groomers
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function addException(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_id' => 'required',
                'package_id' => 'required',
                'groomer_profit' => 'required|numeric|min:0|max:100'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $groomer_exception = ProfitSharingExceptionGroomer::find($request->groomer_id);
            if (!empty($groomer_exception)) {
                return response()->json([
                    'msg' => 'There is exception record for the groomer already. Please edit instead of add'
                ]);
            }

            $groomer_exception = new ProfitSharingExceptionGroomer;
            $groomer_exception->groomer_id = $request->groomer_id;
            $groomer_exception->package_id = $request->package_id;
            $groomer_exception->groomer_profit = $request->groomer_profit;
            $groomer_exception->cdate = Carbon::now();
            $groomer_exception->created_by = Auth::guard('admin')->user()->admin_id;
            $groomer_exception->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function updateException(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'orig_groomer_id' => 'required',
                //'groomer_id' => 'required',
                'package_id' => 'required',
                'groomer_profit' => 'required|numeric|min:0|max:100'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            if (!empty($request->groomer_id)) {
                $groomer_exception = ProfitSharingExceptionGroomer::where('groomer_id', '!=', $request->orig_groomer_id)
                    ->where('groomer_id', $request->groomer_id)
                    ->first();

                if (!empty($groomer_exception)) {
                    return response()->json([
                        'msg' => 'There is exception record for the groomer already in another record.'
                    ]);
                }
            }

            $groomer_exception = ProfitSharingExceptionGroomer::find($request->orig_groomer_id);
            if (empty($groomer_exception)) {
                return response()->json([
                    'msg' => 'Invalid recrod ID provided'
                ]);
            }

            if (!empty($request->groomer_id)) {
                $groomer_exception->groomer_id = $request->groomer_id;
            }

            $groomer_exception->package_id = $request->package_id;
            $groomer_exception->groomer_profit = $request->groomer_profit;
            $groomer_exception->mdate = Carbon::now();
            $groomer_exception->modified_by = Auth::guard('admin')->user()->admin_id;
            $groomer_exception->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function loadDetail(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            $groomer_exception = ProfitSharingExceptionGroomer::find($request->id);
            if (empty($groomer_exception)) {
                return response()->json([
                    'msg' => 'Invalid exception ID provided'
                ]);
            }

            $groomer_exception->last_updated = $groomer_exception->last_updated;

            return response()->json([
                'msg' => '',
                'groomer_exception' => $groomer_exception
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function loadUserList(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_id' => 'required'
            ]);

            $users = ProfitSharingExceptionUser::join('user', 'user.user_id', '=', 'profit_sharing_exception_user.user_id')
                ->where('profit_sharing_exception_user.groomer_id', $request->groomer_id)
                ->select(['profit_sharing_exception_user.*', 'user.first_name', 'user.last_name', 'user.email', 'user.phone'])
                ->get();
            if (empty($users)) {
                $users = [];
            }

            return response()->json([
                'msg' => '',
                'users' => $users
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function searchUser(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_id' => 'required',
                'user_search' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $users = \App\Model\User::where(function($query) use ($request) {
                    $query->whereRaw('lower(first_name) like ?', ['%' . strtolower($request->user_search) . '%'])
                        ->orWhereRaw('lower(last_name) like ?', ['%' . strtolower($request->user_search) . '%'])
                        ->orWhereRaw('phone like ?', ['%' . strtolower($request->user_search) . '%'])
                        ->orWhereRaw('lower(email) like ?', ['%' . strtolower($request->user_search) . '%']);
                })->whereRaw('user_id not in (select user_id from profit_sharing_exception_user where groomer_id = ?)', [$request->groomer_id])
                //->select(['user_id','email','phone','name'])
                ->get();

            Log::info('### USERS ###' . var_export($users, true));

            if (empty($users)) {
                $users = [];
            }
            return response()->json([
                'msg' => '',
                'users' => $users
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function addUserException(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_id' => 'required',
                'package_id' => 'required',
                'user_id' => 'required',
                'groomer_profit' => 'required|numeric|min:0|max:100'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $user_exception = ProfitSharingExceptionUser::where('groomer_id', $request->groomer_id)
                ->where('user_id', $request->user_id)->first();
            if (!empty($user_exception)) {
                return response()->json([
                    'msg' => 'There is exception record for the user/groomer already. Please edit instead of add'
                ]);
            }

            $user_exception = new ProfitSharingExceptionUser;
            $user_exception->groomer_id = $request->groomer_id;
            $user_exception->package_id = $request->package_id;
            $user_exception->user_id = $request->user_id;
            $user_exception->groomer_profit = $request->groomer_profit;
            $user_exception->cdate = Carbon::now();
            $user_exception->created_by = Auth::guard('admin')->user()->admin_id;
            $user_exception->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function updateUserException(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_id' => 'required',
                'orig_user_id' => 'required',
                'package_id' => 'required',
                'groomer_profit' => 'required|numeric|min:0|max:100'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            if (!empty($request->user_id)) {
                $user_exception = ProfitSharingExceptionUser::where('groomer_id', $request->groomer_id)
                    ->where('user_id', '!=', $request->orig_user_id)
                    ->where('user_id', $request->user_id)
                    ->first();

                if (!empty($user_exception)) {
                    return response()->json([
                        'msg' => 'There is exception record for the user/groomer already in another record.'
                    ]);
                }
            }

            $user_exception = ProfitSharingExceptionUser::where('groomer_id', $request->groomer_id)
                ->where('user_id', $request->orig_user_id)
                ->first();
            if (empty($user_exception)) {
                return response()->json([
                    'msg' => 'Invalid recrod ID provided'
                ]);
            }

            //$user_exception->groomer_id = $request->groomer_id;
            if (!empty($request->user_id)) {
                $user_exception->user_id = $request->user_id;
            }

            $user_exception->package_id = $request->package_id;
            $user_exception->groomer_profit = $request->groomer_profit;
            $user_exception->mdate = Carbon::now();
            $user_exception->modified_by = Auth::guard('admin')->user()->admin_id;
            $user_exception->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function loadUserDetail(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_id' => 'required',
                'user_id' => 'required'
            ]);

            $user_exception = ProfitSharingExceptionUser::where('groomer_id', $request->groomer_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (empty($user_exception)) {
                return response()->json([
                    'msg' => 'Invalid exception ID provided'
                ]);
            }

            $user_exception->last_updated = $user_exception->last_updated;
            $user = User::find($user_exception->user_id);
            $user_exception->user = $user ;

            return response()->json([
                'msg' => '',
                'user_exception' => $user_exception
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function report(Request $request) {

        $sdate = '2018-09-22'; // Carbon::today()->addDays(-14);
        $edate = '2018-09-30'; // Carbon::today()->addDays(1)->addSeconds(-1);

        if (!empty($request->sdate)) {
            if ($request->sdate > '2018-09-30') $request->sdate = '2018-09-30';
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            if ($request->edate > '2018-09-30') $request->edate = '2018-09-30';
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $data = ProfitSharing::where('profit_sharing.cdate', '<', '2018-10-01')
            ->where('profit_sharing.cdate', '>=', $sdate)
            ->where('profit_sharing.cdate', '<=', $edate)
            ->leftJoin('appointment_list as a', function($join) use($request)
            {
                $join->on('a.appointment_id', '=', 'profit_sharing.appointment_id');


            })
            ->leftJoin('address', 'address.address_id', '=', 'a.address_id')
            ->leftJoin('promo_code as p', DB::raw('a.promo_code'), '=', DB::raw('upper(p.code)'));

        if (!empty($request->repeating)) {

            if ($request->repeating == 'N') {
                # 1st time user
                $data = $data->where(DB::raw('f_get_appointment_cnt(a.user_id)'), '=', DB::raw('1'));
            } else {
                # repeating user
                $data = $data->where(DB::raw('f_get_appointment_cnt(a.user_id)'), '>', DB::raw('1'));
            }
        }

        if (!empty($request->appointment_id)) {
            $data = $data->where('profit_sharing.appointment_id', $request->appointment_id);
        }

        if (!empty($request->groomer_id)) {
            $data = $data->where('profit_sharing.groomer_id', $request->groomer_id);
        }

        if (!empty($request->user)) {
            $data = $data->leftJoin('user AS u', 'u.user_id', '=', 'a.user_id')
                    ->whereRaw('(LOWER(u.first_name) like \'%' . strtolower($request->user) . '%\' or LOWER(u.last_name) like \'%' . strtolower($request->user) . '%\')');
        }

        if (!empty($request->promo_code)) {
            $data = $data->whereRaw('a.promo_code like \'%' . strtoupper($request->promo_code) . '%\'');
        }

        if (!empty($request->type)) {
            $data = $data->where('profit_sharing.type', $request->type);
        }

        if (!empty($request->appointment_type)) {
            $data = $data->whereRaw("
                profit_sharing.appointment_id in (
                    select appointment_id
                    from appointment_product 
                    where prod_id = ?
                ) and profit_sharing.type = 'A'
          ", [
              $request->appointment_type
            ]);
        }

        if (!empty($request->repeating)) {
            $data = $data->where('profit_sharing.type', 'A');
        }

        if (!empty($request->promo_type)) {
            $data = $data->where('p.type', $request->promo_type)
                    ->where('profit_sharing.type', 'A');
        }

        if (!empty($request->county)) {
            $data = $data->whereRaw("address.zip in (select zip 
                   from allowed_zip
                   where concat(county_name, '/', state_abbr) = '" . $request->county . "'
                   and lower(available) = 'x')");
        }

        # get total amount #
        $t = $data->selectRaw("
                a.appointment_id,
                profit_sharing.type,
                if(profit_sharing.type = 'D', -1 * profit_sharing.groomer_profit_amt, profit_sharing.groomer_profit_amt) as groomer_profit_amt,
                profit_sharing.remaining_amt,
                IF(profit_sharing.type='A', if(p.type = 'G' and p.total_month > 1, round(p.groupon_amt / p.total_month, 2), p.groupon_amt), 0) AS groupon_amt,
                IF(profit_sharing.type='T', profit_sharing.groomer_profit_amt, 0) AS tip_amt,
                IF(profit_sharing.type='A', f_get_add_on_amt(a.appointment_id), 0) AS add_on_amt,
                IF(profit_sharing.type='A', profit_sharing.sub_total, 0) AS sub_total_amt,
                IF(profit_sharing.type='A', a.promo_amt, 0) AS promo_amt,
                IF(profit_sharing.type='A', a.credit_amt, 0) AS credit_amt,
                IF(profit_sharing.type='A', a.tax, 0) AS tax_amt,
                IF(profit_sharing.type='A', a.safety_insurance, 0) AS safety_insurance_amt,
                IF(profit_sharing.type='T', a.tip, a.total) AS total_amt,
                IF(profit_sharing.type='A', 1, 0) AS cnt,
                CASE 
                    WHEN profit_sharing.type = 'A' THEN  ifnull(a.total, 0) - ifnull(a.tax, 0) - ifnull(a.safety_insurance, 0) - ifnull(profit_sharing.groomer_profit_amt, 0) + ifnull(p.groupon_amt, 0)
                    WHEN profit_sharing.type = 'C' THEN -1 * ifnull(profit_sharing.groomer_profit_amt, 0)
                    WHEN profit_sharing.type = 'D' THEN ifnull(profit_sharing.groomer_profit_amt, 0)
                    ELSE 0
                END as profit_amt                     
            ")->get();

        $sum_pet_qty = 0;
        foreach ($t as $o) {
            $sum_pet_qty += $o->pet_qty;
        }

        $total = collect();
        $total->sum_pet_qty = $sum_pet_qty;
        $total->sum_groomer_profit_amt = $t->sum('groomer_profit_amt');
        $total->sum_remaining_amt = $t->sum('remaining_amt');
        $total->sum_promo_amt = $t->sum('promo_amt');
        $total->sum_credit_amt = $t->sum('credit_amt');
        $total->sum_sub_total_amt = $t->sum('sub_total_amt');
        $total->sum_tip_amt = $t->sum('tip_amt');
        $total->sum_tax_amt = $t->sum('tax_amt');
        $total->sum_safety_insurance_amt = $t->sum('safety_insurance_amt');
        $total->sum_total_amt = $t->sum('total_amt');
        $total->sum_groupon_amt = $t->sum('groupon_amt');
        $total->sum_add_on_amt = $t->sum('add_on_amt');
        $total->sum_profit = $t->sum('profit_amt');
        $total->cnt = $t->sum('cnt');


        if ($request->excel == 'Y') {
            $data = $data->selectRaw('profit_sharing.*,
                    p.groupon_amt,
                    a.appointment_id, 
                    a.promo_code, 
                    a.promo_amt, 
                    a.credit_amt, 
                    a.tip, 
                    a.tax, 
                    a.safety_insurance,
                    a.total,
                    a.user_id,
                    f_get_add_on_amt(a.appointment_id) as add_on_amt')
                ->orderBy('profit_sharing.cdate', 'desc')->get();
            Excel::create('profit_sharing', function($excel) use($data, $total) {

                $excel->sheet('reports', function($sheet) use($data, $total) {

                    $reports = [];

                    foreach ($data as $a) {
                        if ($a->type == 'T') {
                            $appointment_type = '';
                        } else {
                            $appointment_type = $a->appointment_type_name;
                        }

                        $reports[] = [
                            'Appointment.ID' => $a->appointment_id,
                            'Customer.Name' => $a->customer_name. ' (' . $a->user_id . ')',
                            'Type' => $a->type_name,
                            'Pet.#' => $a->pet_qty,
                            'Appointment.Type' => $appointment_type,
                            'AddOn.Total' => $a->type == 'T' ? '0.00' : $a->add_on_amt,
                            'Sub.Total' => $a->type == 'T' ? '0.00' : $a->sub_total,
                            'Tip.Amt' => $a->type == 'T' ? $a->tip : '0.00',
                            'Promo.Amt' => $a->type == 'T' ? '0.00' : $a->promo_amt,
                            'Credit.Amt' => $a->type == 'T' ? '0.00' : $a->credit_amt,
                            'Safety.Ins' => $a->type == 'T' ? '0.00' : $a->safety_insurance,
                            'Tax' => $a->type == 'T' ? '0.00' : $a->tax,
                            'Total' => $a->total_amt,
                            'Groomer.Name' => $a->groomer_name . ' (' . $a->groomer_id . ')',
                            'Groomer.Profit.Ratio' => $a->groomer_profit_ratio,
                            'Groomer.Profit.Amt' => $a->groomer_profit_amt,
                            'Groupon.Payout' => $a->type == 'T' ? '0.00' : $a->groupon_amt,
                            'Profit' => $a->profit_amt,
                            'Exception.Groomer' => $a->type == 'T' ? '' : $a->groomer_exception,
                            'Exception.User' => $a->type == 'T' ? '' : $a->user_exception,
                            'Last.Updated' => $a->cdate
                        ];

                    }

                    $reports[] = [
                        'Appointment.ID' => 'TOTAL Appointment',
                        'Customer.Name' => $total->cnt,
                        'Type' => 'TOTAL',
                        'Pet.#' => $total->sum_pet_qty,
                        'Appointment.Type' => '',
                        'AddOn.Total' => $total->sum_add_on_amt,
                        'Sub.Total' => $total->sum_sub_total_amt,
                        'Tip.Amt' => $total->sum_tip_amt,
                        'Promo.Amt' => $total->sum_promo_amt,
                        'Credit.Amt' => $total->sum_credit_amt,
                        'Safety.Ins' => $total->sum_safety_insurance_amt,
                        'Tax' => $total->sum_tax_amt,
                        'Total' => $total->sum_total_amt,
                        'Groomer.Name' => '',
                        'Groomer.Profit.Ratio' =>'',
                        'Groomer.Profit.Amt' => $total->sum_groomer_profit_amt,
                        'Groupon.Payout' => $total->sum_groupon_amt,
                        'Profit' => $total->sum_profit,
                        'Exception.Groomer' =>'',
                        'Exception.User' => '',
                        'Last.Updated' => ''
                    ];

                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $data = $data->selectRaw("
            profit_sharing.*, 
            if(p.type = 'G' and p.total_month > 1, round(p.groupon_amt / p.total_month, 2), p.groupon_amt) as groupon_amt,
            a.appointment_id, 
            a.promo_code,
            f_get_groupon_seq(a.appointment_id) as groupon_seq, 
            a.promo_amt, 
            a.credit_amt, 
            a.tip, 
            a.tax, 
            a.safety_insurance,
            a.total,
            a.user_id,
            f_get_add_on_amt(a.appointment_id) as add_on_amt
        ")->orderBy('profit_sharing.cdate', 'desc')
          ->paginate();
            //->toSql();dd($data);

        $groomers = Groomer::whereIn('status', ['A','N'])->orderBy('first_name', 'asc','last_name','asc')->get();

        $counties = DB::select("
            select distinct county_name, state_abbr
            from allowed_zip
            where lower(available) = 'x' 
            order by 2, 1
        ");

        return view('admin.profit_sharing.report', [
            'data' => $data,
            'appointment_id' => $request->appointment_id,
            'groomers' => $groomers,
            'groomer_id' => $request->groomer_id,
            'sdate' => $sdate,
            'edate' => $edate,
            'user' => $request->user,
            'promo_code' => $request->promo_code,
            'type' => $request->type,
            'appointment_type' => $request->appointment_type,
            'repeating' => $request->repeating,
            'promo_type' => $request->promo_type,
            'county' => $request->county,
            'total' => $total,
            'counties' => $counties
        ]);
    }

    public function report_new(Request $request)
    {
        $sdate = Carbon::today()->addDays(-14);
        $edate = Carbon::today()->addDays(1)->addSeconds(-1);

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $data = ProfitShare::where('profit_share.cdate', '>=', '2018-10-01')
          ->where('profit_share.cdate', '>=', $sdate)
          ->where('profit_share.cdate', '<=', $edate)
          ->leftJoin('appointment_list','appointment_list.appointment_id', '=', 'profit_share.appointment_id')
          ->leftJoin('address', 'address.address_id', '=', 'appointment_list.address_id')
          ->leftJoin('groomer', 'groomer.groomer_id', '=', 'profit_share.groomer_id')
          ->leftJoin('user', 'user.user_id', '=', 'appointment_list.user_id');

        if (!empty($request->repeating)) {

            $data = $data->where('profit_share.type', 'A');
            if ($request->repeating == 'N') {
                # 1st time user
                $data = $data->where(DB::raw('f_get_appointment_cnt(appointment_list.user_id)'), '=', DB::raw('1'));
            } else {
                # repeating user
                $data = $data->where(DB::raw('f_get_appointment_cnt(appointment_list.user_id)'), '>', DB::raw('1'));
            }
        }

        if (!empty($request->appointment_id)) {
            $data = $data->where('profit_share.appointment_id', $request->appointment_id);
        }

        if (!empty($request->groomer_id)) {
            $data = $data->where('profit_share.groomer_id', $request->groomer_id);
        }

        if (!empty($request->user)) {
            $data = $data->whereRaw('(LOWER(user.first_name) like \'%' . strtolower($request->user) . '%\' or LOWER(user.last_name) like \'%' . strtolower($request->user) . '%\')');
        }

        if (!empty($request->promo_code)) {
            $data = $data->whereRaw('profit_share.app_promo_code like \'%' . strtoupper($request->promo_code) . '%\'');
        }

        if (!empty($request->type)) {
            if ($request->type == 'A') {
                $data = $data->whereIn('profit_share.type', ['A', 'V']);
            } else {
                $data = $data->where('profit_share.type', $request->type);
            }
        }

        if (!empty($request->appointment_type)) {
            if (in_array($request->appointment_type, ['dog', 'cat'])) {
                $data = $data->whereRaw("
                    profit_share.appointment_id in (
                        select appointment_id
                          from appointment_product 
                         where prod_id in (select prod_id from product where pet_type = ?)
                    ) and profit_share.type = 'A'
              ", [$request->appointment_type]);

            } else {
                $data = $data->whereRaw("
                    profit_share.appointment_id in (
                        select appointment_id
                          from appointment_product 
                         where prod_id = ?
                    ) and profit_share.type = 'A'
              ", [$request->appointment_type]);
            }
        }

        if (!empty($request->promo_type)) {
            $data = $data->where('profit_share.app_promo_type', $request->promo_type)
              ->where('profit_share.type', 'A');
        }

        if (!empty($request->state)) {
            $data = $data->whereRaw("address.zip in (select zip 
                   from allowed_zip
                  where state_abbr = '" . $request->state . "'
                    and lower(available) = 'x')");
        }

        if (!empty($request->county)) {
            $data = $data->whereRaw("address.zip in (select zip 
                   from allowed_zip
                  where concat(county_name, '/', state_abbr) = '" . $request->county . "'
                    and lower(available) = 'x')");
        }

        # get total amount #
        $t = $data->selectRaw("
                profit_share.appointment_id,
                profit_share.app_number app_cnt,
                profit_share.app_pet_qty as pet_qty,
                profit_share.type,
                ifnull(profit_share.app_groupon_amt,0) AS groupon_amt,
                IF(profit_share.type = 'T', ifnull(profit_share.sub_total, 0), 0) AS tip_amt,
                ifnull(profit_share.app_addon_amt, 0) AS add_on_amt,
                IF(profit_share.type = 'A' or profit_share.type = 'V' or profit_share.type = 'W', ifnull(profit_share.sub_total,0), 0) AS sub_total_amt,
                ifnull(profit_share.app_promo_amt,0) promo_amt,
                ifnull(profit_share.app_credit_amt,0) AS credit_amt,
                ifnull(profit_share.app_tax,0) as tax_amt,
                ifnull(profit_share.app_safety_insurance,0) AS safety_insurance_amt,
                ifnull(profit_share.app_sameday_booking,0) AS sameday_booking,
                ifnull(profit_share.app_fav_groomer_fee,0) AS fav_groomer_fee,
                ifnull(profit_share.app_total,0) AS total_amt,
                case 
                    when profit_share.type='V' then -1
                    when profit_share.type='A' then 1
                    when profit_share.type='W' then 1
                    else 0
                end AS cnt,
                profit_share.groomer_profit_ratio,
                ifnull(profit_share.groomer_fee,0) as groomer_fee,
                ifnull(profit_share.groomer_sameday_earning,0) as groomer_sameday_earning,
                ifnull(profit_share.groomer_fav_earning,0) as groomer_fav_earning,
                ifnull(profit_share.groomer_profit_amt,0) as groomer_profit_amt,
                ifnull(profit_share.remaining_amt,0) + ifnull(profit_share.app_safety_insurance,0) + ifnull(profit_share.app_sameday_booking,0)+ ifnull(profit_share.app_fav_groomer_fee,0) as profit_amt
            ")->get();


        $sum_market_amt = 0;
        $sum_promo_amt = 0;
        $sum_groomer_credit_amt = 0;
        foreach($t as $b) {
            if ($b->app_cnt == 1) {
                $sum_market_amt += $b->promo_amt - $b->groupon_amt;
            } else {
                $sum_promo_amt += $b->promo_amt;
            }

            if (in_array($b->type, ['C', 'D', 'R','L'])) { //Manual Credit, Debit, Referal of Groomer, Reversal of Referal of Groomer
                $sum_groomer_credit_amt += $b->groomer_profit_amt;
            }
        }

        $total = collect();
        $total->sum_pet_qty         = $t->sum('pet_qty');
        $total->sum_groomer_fee_amt     = $t->sum('groomer_fee');
        $total->sum_groomer_sameday_earning_amt     = $t->sum('groomer_sameday_earning');
        $total->sum_groomer_fav_earning_amt     = $t->sum('groomer_fav_earning');
        $total->sum_groomer_profit_amt = $t->sum('groomer_profit_amt');
        $total->sum_remaining_amt   = $t->sum('remaining_amt');
        $total->sum_promo_amt       = $sum_promo_amt;
        $total->sum_market_amt      = $sum_market_amt;
        $total->sum_credit_amt      = $t->sum('credit_amt');
        $total->sum_sub_total_amt   = $t->sum('sub_total_amt');
        $total->sum_tip_amt         = $t->sum('tip_amt');
        $total->sum_tax_amt         = $t->sum('tax_amt');
        $total->sum_safety_insurance_amt = $t->sum('safety_insurance_amt');
        $total->sum_sameday_booking_amt = $t->sum('sameday_booking');
        $total->sum_fav_groomer_fee_amt = $t->sum('fav_groomer_fee');
        $total->sum_total_amt       = $t->sum('total_amt') + $total->sum_tip_amt;
        $total->sum_groupon_amt     = $t->sum('groupon_amt');
        $total->sum_add_on_amt      = $t->sum('add_on_amt');
        $total->sum_profit          = $t->sum('profit_amt');
        $total->cnt                 = $t->sum('cnt');
        $total->sum_groomer_credit_amt = $sum_groomer_credit_amt;

        if ($request->excel == 'Y') {
            $data = $data->selectRaw("
                appointment_list.user_id,
                profit_share.app_number app_cnt,
                concat(user.first_name, ' ', user.last_name) as customer_name,
                profit_share.groomer_id,
                concat(ifnull(profit_share.app_pet_type,''), '-', profit_share.app_package_type) as app_package_type,
                ifnull(profit_share.sub_total,0) as sub_total,
                ifnull(profit_share.app_groupon_amt,0) as groupon_amt,
                profit_share.appointment_id, 
                profit_share.app_promo_type as promo_type,
                profit_share.app_promo_code as promo_code,
                f_get_groupon_seq(profit_share.appointment_id) as groupon_seq, 
                ifnull(profit_share.app_promo_amt,0) as promo_amt, 
                ifnull(profit_share.app_credit_amt,0) as credit_amt, 
                IF(profit_share.type = 'T', ifnull(profit_share.sub_total,0), 0) AS tip,
                ifnull(profit_share.app_tax,0) as tax, 
                ifnull(profit_share.app_safety_insurance,0) as safety_insurance,
                ifnull(profit_share.app_sameday_booking,0) as sameday_booking,
                 ifnull(profit_share.app_fav_groomer_fee,0) as fav_groomer_fee,
                ifnull(profit_share.app_total,0) as total,
                concat(groomer.first_name, ' ', groomer.last_name) as groomer_name,
                ifnull(profit_share.groomer_fee,0) as groomer_fee,
                ifnull(profit_share.groomer_sameday_earning,0) as groomer_sameday_earning,
                 ifnull(profit_share.groomer_fav_earning,0) as groomer_fav_earning,
                ifnull(profit_share.groomer_profit_amt,0) as groomer_profit_amt,
                ifnull(profit_share.app_addon_amt,0) as add_on_amt,
                profit_share.cdate,
                profit_share.comments,
                ifnull(profit_share.remaining_amt, 0) + ifnull(profit_share.app_safety_insurance,0) + ifnull(profit_share.app_sameday_booking,0) + ifnull(profit_share.app_fav_groomer_fee,0) as profit_amt  
            ")->orderBy('profit_share.cdate', 'desc')
              ->orderBy('profit_share.id', 'desc')
              ->get();

            Excel::create('profit_sharing', function($excel) use($data, $total) {

                $excel->sheet('reports', function($sheet) use($data, $total) {

                    $reports = [];

                    foreach ($data as $a) {
                        $reports[] = [
                          'Appointment.ID'  => $a->appointment_id,
                          'Customer.Name'   => $a->customer_name. ' (' . $a->user_id . ')',
                          'Type'            => $a->type_name,
                          'Pet.#'           => $a->pet_qty,
                          'Appointment.Type' => $a->app_package_type,
                          'AddOn.Total'     => $a->type == 'T' ? '0.00' : $a->add_on_amt,
                          'Sub.Total'       => $a->type == 'T' ? '0.00' : $a->sub_total,
                          'Tip.Amt'         => $a->type == 'T' ? $a->tip : '0.00',
                          'Promo.Code'      => $a->promo_type . ': ' . $a->promo_code,
                          'Promo.Amt'       => $a->type == 'T' ? '0.00' : ($a->app_cnt > 1 ? $a->promo_amt : 0),
                          'Marketing.Cost'  => $a->type == 'T' ? '0.00' : ($a->app_cnt > 1 ? 0 : $a->promo_amt - $a->groupon_amt),
                          'Credit.Amt'      => $a->type == 'T' ? '0.00' : $a->credit_amt,
                          'Safety.Ins'      => $a->type == 'T' ? '0.00' : $a->safety_insurance,
                          'Sameday.Booking' => $a->type == 'T' ? '0.00' : $a->sameday_booking,
                          'Fav. Groomer Fee'=> $a->type == 'T' ? '0.00' : $a->fav_groomer_fee,
                          'Tax'             => $a->type == 'T' ? '0.00' : $a->tax,
                          'Total'           => $a->total,
                          'Groomer.Name'    => $a->groomer_name . ' (' . $a->groomer_id . ')',
                          'Groomer.Profit.Ratio' => $a->groomer_profit_ratio,
                          'Groomer.Fee' => $a->groomer_fee,
                          'Groomer.Sameday.Earning' => $a->groomer_sameday_earning,
                          'Groomer.Fav.Earning' => $a->groomer_fav_earning,
                          'Groomer.Profit.Amt' => $a->groomer_profit_amt,
                          'Groupon.Payout'  => $a->type == 'T' ? '0.00' : $a->groupon_amt,
                          'Profit'          => $a->profit_amt,
                          'Last.Updated'    => $a->cdate
                        ];

                    }

                    $reports[] = [
                      'Appointment.ID'  => 'TOTAL Appointment',
                      'Customer.Name'   => $total->cnt,
                      'Type'            => 'TOTAL',
                      'Pet.#'           => $total->sum_pet_qty,
                      'Appointment.Type' => '',
                      'AddOn.Total'     => $total->sum_add_on_amt,
                      'Sub.Total'       => $total->sum_sub_total_amt,
                      'Tip.Amt'         => $total->sum_tip_amt,
                      'Promo.Code'      => '',
                      'Promo.Amt'       => $total->sum_promo_amt,
                      'Marketing.Cost'  => $total->sum_market_amt,
                      'Credit.Amt'      => $total->sum_credit_amt,
                      'Safety.Ins'      => $total->sum_safety_insurance_amt,
                      'Sameday.Booking' => $total->sum_sameday_booking_amt,
                      'Fav.Groomer Fee' => $total->sum_fav_groomer_fee_amt,
                      'Tax'             => $total->sum_tax_amt,
                      'Total'           => $total->sum_total_amt,
                      'Groomer.Name'    => '',
                      'Groomer.Profit.Ratio' =>'',
                      'Groomer.Fee' => $total->sum_groomer_fee_amt,
                      'Groomer.Sameday.Earning' => $total->sum_groomer_sameday_earning_amt,
                      'Groomer.Fav.Earning'    => $total->sum_groomer_fav_earning_amt,
                      'Groomer.Profit.Amt' => ($total->sum_groomer_profit_amt - $total->sum_groomer_credit_amt) . ' / Credit: ' . $total->sum_groomer_credit_amt,
                      'Groupon.Payout'  => $total->sum_groupon_amt,
                      'Profit'          => $total->sum_profit,
                      'Last.Updated'    => ''
                    ];

                    $sheet->fromArray($reports);

                });

            })->export('xlsx');

        }

        $data = $data->selectRaw("
            appointment_list.user_id,
            profit_share.app_number app_cnt,
            concat(user.first_name, ' ', user.last_name) as customer_name,
            profit_share.groomer_id,
            concat(ifnull(profit_share.app_pet_type,''), '-', profit_share.app_package_type) as app_package_type,
            ifnull(profit_share.sub_total,0) as sub_total,
            ifnull(profit_share.app_groupon_amt,0) as groupon_amt,
            profit_share.appointment_id, 
            profit_share.app_promo_type as promo_type,
            profit_share.app_promo_code as promo_code,
            f_get_groupon_seq(profit_share.appointment_id) as groupon_seq, 
            ifnull(profit_share.app_promo_amt,0) as promo_amt, 
            ifnull(profit_share.app_credit_amt,0) as credit_amt, 
            IF(profit_share.type = 'T', ifnull(profit_share.sub_total,0), 0) AS tip,
            ifnull(profit_share.app_tax,0) as tax, 
            ifnull(profit_share.app_safety_insurance,0) as safety_insurance,
            ifnull(profit_share.app_sameday_booking,0) as sameday_booking,
             ifnull(profit_share.app_fav_groomer_fee,0) as fav_groomer_fee,
            ifnull(profit_share.app_total,0) as total,
            concat(groomer.first_name, ' ', groomer.last_name) as groomer_name,
            ifnull(profit_share.groomer_fee,0) as groomer_fee,
             ifnull(profit_share.groomer_sameday_earning,0) as groomer_sameday_earning,
             ifnull(profit_share.groomer_fav_earning,0) as groomer_fav_earning,
            ifnull(profit_share.groomer_profit_amt,0) as groomer_profit_amt,
            ifnull(profit_share.app_addon_amt,0) as add_on_amt,
            profit_share.cdate,
            profit_share.comments,
            ifnull(profit_share.remaining_amt, 0) + ifnull(profit_share.app_safety_insurance,0) + ifnull(profit_share.app_sameday_booking,0) + ifnull(profit_share.app_fav_groomer_fee,0) as profit_amt
        ")->orderBy('profit_share.cdate', 'desc')
          ->orderBy('profit_share.id', 'desc')
          ->paginate();

        $groomers = //Groomer::whereIn('status', ['A','N'])->get();
                    Groomer::whereNotIn('status', ['N'])
                        ->orderBy('status','asc')
                        ->orderBy('first_name','asc')
                        ->orderBy('last_name','asc')
                        ->get();

        $counties = DB::select("
            select distinct county_name, state_abbr
            from allowed_zip
            where lower(available) = 'x' 
            order by 2, 1
        ");

        $states = DB::select("
            select distinct state_abbr, state_name
            from allowed_zip
            where lower(available) = 'x' 
            order by 1, 2
        ");

        return view('admin.profit_sharing.report-new', [
          'data' => $data,
          'appointment_id' => $request->appointment_id,
          'groomers' => $groomers,
          'groomer_id' => $request->groomer_id,
          'sdate' => $sdate->format('Y-m-d'),
          'edate' => $edate->format('Y-m-d'),
          'user' => $request->user,
          'promo_code' => $request->promo_code,
          'type' => $request->type,
          'appointment_type' => $request->appointment_type,
          'repeating' => $request->repeating,
          'promo_type' => $request->promo_type,
          'county' => $request->county,
          'state'   => $request->state,
          'total' => $total,
          'counties' => $counties,
          'states' => $states
        ]);
    }

    public function loadProfitSharingDetail(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'appointment_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $data = ProfitShareDetail::join('product', 'product.prod_id', '=', 'profit_share_detail.package_id')
                ->where('appointment_id', $request->appointment_id)
                ->get([
                    'profit_share_detail.*',
                    DB::raw('product.prod_name as package')
                ]);

            return response()->json([
                'msg' => '',
                'data' => $data
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function migration() {
        ### Appointment
        $appointments = AppointmentList::where('status', 'P')
            ->whereRaw('appointment_id not in (select appointment_id from profit_share where type = \'A\')')
            ->get();

        foreach ($appointments as $app) {
            ProfitSharingProcessor::share_profit($app, true);
        }
    }
}