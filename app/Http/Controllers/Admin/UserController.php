<?php

namespace App\Http\Controllers\Admin;

use App\Lib\AddressProcessor;
use App\Lib\CreditProcessor;
use App\Lib\Helper;
use App\Model\AppointmentList;
use App\Model\Credit;
use App\Model\Groomer;
use App\Model\Pet;
use App\Model\User;
use App\Model\UserBlockedGroomer;
use App\Model\UserLoginHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
#use App\Model\User;
use App\Model\Address;
use App\Model\UserBilling;
use App\Model\UserFavoriteGroomer;
use Carbon\Carbon;
use DB;
use Auth;
use Session;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Redirect;
use App\Lib\Converge;


class UserController extends Controller
{

    public function users(Request $request) {
        try {

            $sdate = Carbon::today()->subMonths(1);
            //$sdate = $sdate = Carbon::createFromFormat('Y-m-d H:i:s', '2017-01-01 00:00:00');
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $query = User::leftJoin('user_stat',function($join){
                $join->on('user.user_id', '=', 'user_stat.user_id')   ;
            })->leftJoin('address', function($join) {
                $join->on('user.user_id', '=', 'address.user_id');
                $join->where('address.status', '=', 'A');
            })->leftJoin('credit', function($join) {
                $join->on('user.user_id', '=', 'credit.user_id');
                $join->where('credit.referral_code', '>', '\'\'');
                $join->where('credit.category', '=', 'S');
            })->select('user.user_id',
                'user.first_name',
                'user.last_name',
                'user.status',
                'user.email',
                'user.phone',
                'user.hear_from',
                'address.address1',
                'address.address2',
                'address.city',
                'address.county',
                'address.state',
                'address.zip',
                'user.cdate',
                'user.referral_url',
                'user.influencer',
                'user_stat.book_cnt',
                'user_stat.last_appt_id',
                'user_stat.last_appt_date',
                'user_stat.last_groomer_id',
                'user_stat.last_groomer_fname',
                'user_stat.last_groomer_lname',
                'user.register_from',
                'user.dog',
                'user.cat',
                DB::raw('credit.referral_code as refer_code'),
                DB::raw('f_get_pet_type_by_user(user.user_id) as type')
            );

            if (!empty($sdate) && empty($request->id)) {
                $query = $query->where('user.cdate', '>=', $sdate);
            }

            if (!empty($edate) && empty($request->id)) {
                $query = $query->where('user.cdate', '<=', $edate);
            }

            if (!empty($request->name)) {
                $query = $query->whereRaw(' ( LOWER(user.first_name) like \'%' . strtolower($request->name) . '%\' or LOWER(user.last_name) like \'%' . strtolower($request->name) . '%\' ) ');
            }

            if (!empty($request->phone)) {
                $query = $query->where('user.phone', 'like', '%' . $request->phone . '%');
            }

            if (!empty($request->email)) {
                $query = $query->whereRaw('LOWER(user.email)  like \'%' . strtolower($request->email) . '%\'');
            }

            if (!empty($request->location)) {
                $query = $query->whereRaw(' ( LOWER(address.address1) like \'%' . strtolower($request->location) . '%\' or LOWER(address.city) like \'%' . strtolower($request->location) . '%\' or LOWER(address.state) like \'%' . strtolower($request->location) . '%\' ) ');

            }

            if (!empty($request->register_from)) {
                $query = $query->where('user.register_from', '=', $request->register_from);
            }

            if (!empty($request->user_id)) {
                $query = $query->where('user.user_id', trim($request->user_id));
            }

            if (!empty($request->status)) {
                if($request->status != 'ALL') {
                    $query = $query->where('user.status', $request->status);
                }
            }

            if (!empty($request->booked)) {
//
//                $query = $query->leftJoin('appointment_list', function($join) {
//                    $join->on('user.user_id', '=', 'appointment_list.user_id');
//                    $join->whereNotIn('appointment_list.status', ['C', 'L']);
//                    //$join->where('appointment_list.status', '!=', 'C');
//                });

                switch ($request->booked) {
                    case 'B':
//                        $query = $query->havingRaw("
//                            count(appointment_list.appointment_id) > 0
//                        ");
                        $query = $query->where('user_stat.book_cnt', '>', 0 );
                        break;
                    case 'O':
//                        $query = $query->havingRaw("
//                            count(appointment_list.appointment_id) = 1
//                        ");
                        $query = $query->where('user_stat.book_cnt', '=', 1 );
                        break;
                    case 'M':
//                        $query = $query->havingRaw("
//                            count(appointment_list.appointment_id) > 1
//                        ");
                        $query = $query->where('user_stat.book_cnt', '>', 1 );
                        break;
                    case 'N':
//                        $query = $query->havingRaw("
//                            count(appointment_list.appointment_id) = 0
//                        ");
                        $query = $query->whereRaw('IfNull(user_stat.book_cnt,0) = 0 ');
                        break;
                }

            }

            if (!empty($request->referred_source) && $request->referred_source != '' ) {
//                if ($request->referred_source == 'Google') {
//                    $query = $query->whereRaw('(user.hear_from = \'Google\' or user.gg_token is not null)');
//                } else if ($request->referred_source == 'Facebook') {
//                    $query = $query->whereRaw('(user.hear_from = \'Facebook\' or user.fb_token is not null)');
//                } else {
                    $query = $query->where('user.hear_from', trim($request->referred_source));
//                }

                if ($request->referred_source == 'none') { //Null data in hear_from
                    $query = $query->whereRaw("IfNull(user.hear_from,'') = '' ");
                }else {
                    $query = $query->where('user.hear_from', trim($request->referred_source));
                }
            }

            if (!empty($request->promo_type)) {

                switch($request->promo_type) {
                    case 'O': //Any of Promo codes
                        $ret = DB::select("
                            SELECT u.user_id
                            FROM user as u 
                            INNER JOIN appointment_list as a ON u.user_id = a.user_id 
                            WHERE a.promo_code > ''
                            AND a.status not in ( 'C', 'L' )
                            GROUP BY u.user_id
                        ");
                        break;
                    case 'X': //Without Promo code
                        $ret = DB::select("
                            SELECT u.user_id
                            FROM user as u 
                            INNER JOIN appointment_list as a ON u.user_id = a.user_id 
                            WHERE (a.promo_code = '' OR a.promo_code is null)
                            AND a.status not in ( 'C', 'L' )
                            GROUP BY u.user_id
                        ");
                        break;
                    default: //A:Affiliate, R:Refer a Friend, N:Normal, G:Groupon, T:Gilt
                        $ret = DB::select("
                            SELECT u.user_id
                            FROM user as u 
                            INNER JOIN appointment_list as a ON u.user_id = a.user_id 
                            INNER JOIN promo_code as p ON p.code = a.promo_code
                            WHERE p.type = :promo_type
                            AND a.status not in ( 'C', 'L' )
                            GROUP BY u.user_id
                        ", ['promo_type' => $request->promo_type]);
                        break;
                }

                if (count($ret) > 0) {
                    $user_ids = [];
                    foreach($ret as $u) {
                        $user_ids[] = $u->user_id;
                    }

                    $query = $query->whereIn('user.user_id', $user_ids);
                } else {
                    $query = $query->whereIn('user.user_id', []);
                }

            }

            if (!empty($request->service_sdate)) {
                $query = $query->whereRaw("
                        user.user_id in (
                            select user_id
                            from appointment_list
                            where cdate >= ?
                            and appointment_list.status not in ( 'C', 'R')
                        )
                    ", [ Carbon::createFromFormat('Y-m-d H:i:s', $request->service_sdate . ' 00:00:00')]);
            }

            if (!empty($request->service_edate)) {
                $query = $query->whereRaw("
                        user.user_id in (
                            select user_id
                            from appointment_list
                            where cdate <= ?
                            and appointment_list.status not in ( 'C', 'R')
                        )
                    ", [ Carbon::createFromFormat('Y-m-d H:i:s', $request->service_edate . ' 23:59:59')]);
            }

            if (!empty($request->pet_type)) {
                $query->whereRaw("
                    user.user_id in (
                        select user_id
                          from pet
                         where type = ?
                         and status ='A'
                    ) 
                ", [$request->pet_type]);
            }

            if (!empty($request->state)) {
                $query = $query->where('state', $request->state);
            }

            if (!empty($request->influencer)) {
                if($request->influencer == 'Y') {
                    $query = $query->where('influencer', $request->influencer);
                }else{
                    $query = $query->where('influencer', '!=', 'Y');
                }
            }

            if (!empty($request->county)) {
                $query = $query->whereRaw("address.zip in (select zip 
                   from allowed_zip
                   where concat(county_name, '/', state_abbr) = '" . $request->county . "'
                   and lower(available) = 'x')");
            }

            if ($request->excel == 'Y') {
                $users = $query->where('user.status', 'A')->groupBy('user.user_id')->orderBy('user.cdate', 'desc')->get();
                Excel::create('users', function($excel) use($users) {

                    $excel->sheet('reports', function($sheet) use($users) {

                        $data = [];
                        foreach ($users as $a) {
                            $address = '';
                            if ($a->address1 != '') {
                                if (!empty( $a->address2) &&  ($a->address2 != '')) {
                                    $address = $a->address1 . ' # ' . $a->address2 . ', ' . $a->city . ', ' . $a->state . ', ' . $a->zip;
                                }else {
                                    $address = $a->address1 .  ', ' . $a->city . ', ' . $a->state . ', ' . $a->zip;
                                }

                            }
                            $row = [
                                'Name' => $a->first_name . ' ' . $a->last_name,
                                'email' => $a->email,
                                'Phone' => $a->phone,
                                'User ID' => $a->user_id,
                                'Type by pet' => $a->type,
                                'Dog at signup' => $a->dog,
                                'Cat at signup'=> $a->cat,
                                'Status' => $a->status,
                                'Zip' => $a->zip,
                                'Address' => $address,
                                'Hear.From' => $a->hear_from,
                                'Referral.From' => strpos($a->referral_url, 'http') !== false ? $a->referral_url : '',
                                'Registered At' => $a->cdate,
                                "Registered From" => ($a->register_from == 'A') ? 'App' : 'Web',
                                'Booked.Count' => $a->book_cnt,
                                'Last.Order' => $a->last_appt_date,
                                'Days' =>  empty($a->last_appt_date) ? '' :  Carbon::parse($a->last_appt_date)->diffInDays( Carbon::now() ) ,
                                'Last.Groomer' => $a->last_groomer_fname . ' ' . $a->last_groomer_lname,
                                'Referral.Code' => $a->refer_code
                            ];

//                            'Booked.Count' => $a->booked(true),
//                                'Last.Order' => $a->last_order(),
//                                'Days' => $a->last_order('days'),
//                                'Last.Groomer' => $a->last_groomer,

                            $data[] = $row;

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $query = $query->groupBy('user.user_id');

            $total = $query->get()->count();

            $users = $query->orderBy('user.cdate', 'desc')->paginate(20);

            //$states = Address::where('status', 'A')->distinct()->get(['state']);
//            $states = DB::select("
//                select distinct state
//                from address a
//                    inner join user b on a.user_id = b.user_id
//                where a.status = 'A'
//                and ifnull(a.state, '') != ''
//
//            ", []);

            $counties = DB::select("
            select distinct county_name, state_abbr
            from allowed_zip
            where lower(available) = 'x' 
            order by 2, 1
            ");

            return view('admin.users', [
                'msg' => '',
                'users' => $users,
                'sdate' => $sdate->format('Y-m-d'),
                'edate' => $edate->format('Y-m-d'),
                'service_sdate' => $request->service_sdate,
                'service_edate' => $request->service_edate,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'location' => $request->location,
                'booked' => $request->booked,
                'promo_type' => $request->promo_type,
                'register_from' => $request->register_from,
                'user_id' => $request->user_id,
                'referred_source' => $request->referred_source,
                'pet_type'  => $request->pet_type,
                'total' => $total,
                //'states' => $states,
                'state' => $request->state,
                'county' => $request->county,
                'counties' => $counties,
                'influencer' => $request->influencer,
                'status' => $request->status
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function user($id) {
        try {
            $user = User::findOrFail($id);

            if(!empty($user->fraud_code)){
                $user->frauds = DB::select("select code_name from codes where code_type='USER_FRAUD_CODE' and code_id =:code_id ",
                    [ 'code_id' => $user->fraud_code ]);
            }

            $user->address = Address::where('user_id',$id)
                ->orderBy('status', 'asc')
                ->orderBy('address_id', 'desc')
                ->get();

            $user->billing = UserBilling::where('user_id',$id)
                ->orderBy('status', 'asc')
                ->orderBy('billing_id', 'desc')
                ->get();

            $user->favorite_groomer = UserFavoriteGroomer::select('groomer.*')
                ->join('groomer', 'groomer.groomer_id', '=', 'user_favorite_groomer.groomer_id')
                ->where('user_favorite_groomer.user_id', $id)
                ->orderBy('groomer.first_name', 'asc')
                ->get();

            $user->blocked_groomer = UserBlockedGroomer::select('groomer.*')
                ->join('groomer', 'groomer.groomer_id', '=', 'user_blocked_groomer.groomer_id')
                ->where('user_blocked_groomer.user_id', $id)
                ->orderBy('groomer.first_name', 'asc')
                ->get();

            $user->pets = Pet::where('user_id', $id)
                ->get();

            foreach ($user->billing as $card) {
                switch ($card->card_type) {
                    case 'V':
                        $card->type = 'Visa';
                        break;
                    case 'A':
                        $card->type = 'Amex';
                        break;
                    case 'M':
                        $card->type = 'Master';
                        break;
                    case 'D':
                        $card->type = 'Discover';
                        break;
                }

                $card->expire_mm = str_pad($card->expire_mm, 2, '0', STR_PAD_LEFT);
                $card->expire_yy = str_pad($card->expire_yy, 2, '0', STR_PAD_LEFT);

                $card->card_number = str_pad(substr($card->card_number, 8), strlen($card->card_number), '*', STR_PAD_LEFT);
            }

            # get referral code
            $user->referral_code = $user->referral_code;

            $user->available_credit = CreditProcessor::getAvailableCredit($user->user_id);

            $groomers = DB::select("
                select * 
                from groomer 
                where status = 'A'
                and groomer_id not in (
                    select groomer_id 
                    from user_favorite_groomer
                    where user_id = :user_id
                )
                and groomer_id not in (
                    select groomer_id 
                    from user_blocked_groomer
                    where user_id = :user_id
                )
                order by first_name asc
            ", [
                'user_id' => $user->user_id
            ]);


            $credit_data = Credit::leftjoin('appointment_list', 'appointment_list.appointment_id', '=', 'credit.appointment_id')
              ->where('credit.user_id', $user->user_id)
              ->orderBy('credit.cdate', 'desc')
              ->paginate(10, [
                  'credit.*',
                  'appointment_list.total',
                  'appointment_list.cdate as order_date'
              ]
              );

            return view('admin.user', [
                'msg' => '',
                'user' => $user,
                'groomers' => $groomers,
                'credit_data' => $credit_data
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function update(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required|regex:/^\d{10}$/',
                'email' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : " | ") . $v[0];
                }

                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            $user = User::findOrFail($request->id);
            if (empty($user)) {
                $msg = 'Invalid user ID provided';
                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            $u = Auth::guard('admin')->user();
            $user->modified_by = $u->name . '(' . $u->admin_id . ')';
            $user->mdate = Carbon::now();

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->influencer = $request->influencer;
            $user->groomer_prefer = $request->groomer_prefer;

            $user->save();

            $msg = "Success";

            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function reset_password(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'password' => 'same:confirm_password|required|min:6'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            ### update user password###
            $u = User::findOrFail($request->id);

            $u->passwd = \Crypt::encrypt($request->password);
            $u->save();

            $msg = "Success";

            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function update_address(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'address_id' => 'required',
                'name' => 'required',
                'address1' => 'required',
                'city' => 'required',
                'state' => ['required','regex:/^(?:A[KLRZ]|C[AOT]|D[CE]|FL|GA|HI|I[ADLN]|K[SY]|LA|M[ADEINOST]|N[CDEHJMVY]|O[HKR]|PA|RI|S[CD]|T[NX]|UT|V[AT]|W[AIVY])*$/'],
                'zip' => 'required|regex:/^\d{5}$/s'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : " | ") . $v[0];
                }

                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            $address = Address::where('address_id', $request->address_id)->first();
            if (empty($address)) {
                $msg = 'Invalid address ID provided';
                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            if ($address->user_id != $request->id) {
                $msg = 'User ID not match';
                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            $ret = AddressProcessor::update(
                $request->id,
                $request->address_id,
                $request->address1,
                $request->address2,
                $request->city,
                $request->state,
                $request->zip,
                $request->default_address
            );

            if (!empty($ret['msg'])) {
                $msg = $ret['msg'];
                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            $msg = "Success";

            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function update_billing(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'billing_id' => 'required',
                'card_type' => 'required|in:V,M,A',
                'card_number' => 'required|regex:/^\d{15,16}$/',
                'card_holder' => 'required',
                'expire_mm' => 'required|regex:/^\d{2}$/',
                'expire_yy' => 'required|regex:/^\d{2}$/',
                'cvv' => 'required|regex:/^\d{3,4}$/',
                'b_address1' => 'required',
                'b_city' => 'required',
                'b_state' => ['required','regex:/^(?:A[KLRZ]|C[AOT]|D[CE]|FL|GA|HI|I[ADLN]|K[SY]|LA|M[ADEINOST]|N[CDEHJMVY]|O[HKR]|PA|RI|S[CD]|T[NX]|UT|V[AT]|W[AIVY])*$/'],
                //'b_state' => 'required',
                'b_zip' => 'required|regex:/^\d{5}$/'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            $user_billing = UserBilling::whereRaw('RIGHT(card_number,4) = \''.substr($request->card_number, -4).'\'')
                ->where('user_id', $request->id)
                ->where('status', 'A')
                ->where('billing_id', '!=', $request->billing_id)
                ->first();

            if (!empty($user_billing)) {
                $msg ='User Billing with same credit card # already exists';
                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            $user_billing = UserBilling::find($request->billing_id);
            if (empty($user_billing)) {
                $msg = 'Invalid billing ID provided';
                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            if ($user_billing->user_id != $request->id) {
                $msg = 'User ID not match';
                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            ### update token information first ###
            if (substr($user_billing->card_number, -4) != substr($request->card_number, -4)) {
                $ret = Converge::get_token(
                    $request->card_number,
                    $request->expire_mm . $request->expire_yy,
                    $request->cvv,
//                $request->address1 . ' ' . $request->address2,
//                $request->city,
//                $request->state,
                    $request->b_zip
                );
            } else {
                $ret = Converge::update_token(
                    $user_billing->card_token,
                    $request->card_number,
                    $request->expire_mm . $request->expire_yy,
                    $request->cvv,
//                $request->address1 . ' ' . $request->address2,
//                $request->city,
//                $request->state,
                    $request->b_zip
                );
            }

            if (!empty($ret['error_msg'])) {
                $msg = 'Credit card verification failed: ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']';

                return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
            }

            $card_token = $ret['token'];

            $user_billing->card_type = $request->card_type;
            if (!$request->card_number.contains('*')) {
                $user_billing->card_number = $request->card_number;
            }
            $user_billing->card_holder = $request->card_holder;
            $user_billing->expire_mm = $request->expire_mm;
            $user_billing->expire_yy = $request->expire_yy;
            $user_billing->cvv = ''; # CVV used only for validation
            $user_billing->address1 = $request->b_address1;
            $user_billing->address2 = $request->b_address2;
            $user_billing->city = $request->b_city;
            $user_billing->state = $request->b_state;
            $user_billing->zip = $request->b_zip;
            $user_billing->mdate = Carbon::now();
            $user_billing->card_token = $card_token;
            $user_billing->status = 'A';

            $u = Auth::guard('admin')->user();
            $user_billing->modified_by = $u->name . '(' . $u->admin_id . ')';

            $user_billing->save();

            ### Update status of rejected appointment, for recharge again.
            AppointmentList::where('user_id', $user_billing->user_id)->where('payment_id', $user_billing->billing_id)
              ->where('status', 'R')
              ->update([
                'status' => 'D'
              ]);

            $msg = "Success";

            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
        }
    }


    public function change_billing_status(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'user_id' => 'required',
                'billing_id' => 'required',
                'status' => 'required',
                'card_number' => 'required'
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

            $user_billing = UserBilling::find($request->billing_id);
            if (empty($user_billing)) {
                $msg = 'Invalid billing ID provided';
                return response()->json([
                    'msg' => $msg
                ]);
            }

            # Check duplicated card number when activate card
            if ($request->status == 'D') {
                $exist_billing = UserBilling::whereRaw('RIGHT(card_number,4) = \''.substr($request->card_number, -4).'\'')
                    ->where('user_id', $request->user_id)
                    ->where('status', 'A')
                    ->where('billing_id', '!=', $request->billing_id)
                    ->first();

                if (!empty($exist_billing)) {
                    $msg ='User Billing with same credit card # already exists';
                    return response()->json([
                        'msg' => $msg
                    ]);
                }
            }

            if ($request->status == 'D') {
                $user_billing->status = 'A';
            } else {
                $user_billing->status = 'D';
            }
            $user_billing->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
            return response()->json([
                'msg' => $msg
            ]);
        }
    }

    public function updateOpNote(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'user_id' => 'required',
                'op_note' => 'required'
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

            $user = User::find($request->user_id);
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Invalid user ID provided'
                ]);
            }

            $user->op_note = $request->op_note;
            $user->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_yelp_review(Request $request) {
        try {

            $v = Validator::make($request->all(), [
              'user_id' => 'required',
              'yelp_review' => 'required'
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

            $user = User::find($request->user_id);
            if (empty($user)) {
                return response()->json([
                  'msg' => 'Invalid user ID provided'
                ]);
            }

            $user->yelp_review = $request->yelp_review;
            $user->update();

            return response()->json([
              'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function removeFavoriteGroomer(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'user_id' => 'required',
                'groomer_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                throw new \Exception($msg);
            }

            $ret = DB::statement("
                delete from user_favorite_groomer
                where user_id = :user_id
                and groomer_id = :groomer_id
            ", [
                'user_id' => $request->user_id,
                'groomer_id' => $request->groomer_id
            ]);

            if ($ret < 1) {
                throw new \Exception('Failed to remove favorite groomer');
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function removeBlockedGroomer(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'user_id' => 'required',
                'groomer_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                throw new \Exception($msg);
            }

            $ret = DB::statement("
                delete from user_blocked_groomer
                where user_id = :user_id
                and groomer_id = :groomer_id
            ", [
                'user_id' => $request->user_id,
                'groomer_id' => $request->groomer_id
            ]);

            if ($ret < 1) {
                throw new \Exception('Failed to remove blocked groomer');
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function addFavoriteGroomer(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'user_id' => 'required',
                'groomer_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                throw new \Exception($msg);
            }
            $cnt = UserFavoriteGroomer::where('user_id', $request->user_id)
                ->where('groomer_id', $request->groomer_id)
                ->count();

            if ($cnt > 0) {
                throw new \Exception('Groomer marked as favorite already');
            }

            $ret = DB::insert("
                insert into user_favorite_groomer (user_id, groomer_id)
                values (:user_id, :groomer_id)
            ", [
                'user_id' => $request->user_id,
                'groomer_id' => $request->groomer_id
            ]);

            if ($ret < 1) {
                throw new \Exception('Failed to add favorite groomer');
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function addBlockedGroomer(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'user_id' => 'required',
                'groomer_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                throw new \Exception($msg);
            }
            $cnt = UserBlockedGroomer::where('user_id', $request->user_id)
                ->where('groomer_id', $request->groomer_id)
                ->count();

            if ($cnt > 0) {
                throw new \Exception('Groomer marked as blocked already');
            }

            $ret = DB::insert("
                insert into user_blocked_groomer (user_id, groomer_id)
                values (:user_id, :groomer_id)
            ", [
                'user_id' => $request->user_id,
                'groomer_id' => $request->groomer_id
            ]);

            if ($ret < 1) {
                throw new \Exception('Failed to add blocked groomer');
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function update_status(Request $request) {

        $user = \App\User::find($request->user_id);
        if (empty($user)) {
            return response()->json([
              'msg' => 'Invalid user ID provided'
            ]);
        }

        //It can activated from Closed.
//        if ($user->status == 'C') {
//            return response()->json([
//              'msg' => 'User is already closed.'
//            ]);
//        }

        $user->status = $request->status;
        if ($user->status == 'C') {
            $user->email .= '.old';
        }else if ($user->status == 'A') {
            $email = $user->email  ;
            $email = str_replace('.old', '', $email );
            $user->email = $email;
            $user->fraud_code = null ;
        }else if ($user->status == 'B') { //Black list by Fraud(Chargeback)
            $user->fraud_code ='ADM'; //Fraud by Admin, codes table,
        }
        $user->update();

        return response()->json([
          'msg' => ''
        ]);
    }

    public function add_credit(Request $request, $user_id) {
        $v = Validator::make($request->all(), [
            'type' => 'required',
            'category' => 'required',
            'amt' => 'required',
            'expire_date' => 'required'
        ]);
        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : " | ") . $v[0];
            }

            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
        }

        $user = User::findOrFail($user_id );
        if (empty($user)) {
            $msg = 'Invalid user ID provided';
            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
        }

        $u = Auth::guard('admin')->user();
        if (empty($u)) {
            $msg = 'Your session is expired. Please login again.';
            return Redirect::route('admin.user', array('id' => $request->id))->with('alert', $msg);
        }


        $credit = new Credit();
        $credit->type       = $request->type;
        $credit->user_id    = $user_id;
        $credit->category   = $request->category;
        $credit->amt        = $request->amt;
        $credit->admin_id   = $u->admin_id;
        $credit->expire_date = $request->expire_date;
        $credit->status     = 'A';
        $credit->notes      = $request->comments;
        $credit->fully_redeemed = 'N';
        $credit->cdate      = Carbon::now();
        $credit->save();

        return back();
    }

    public function loginAs(Request $request) {

        Helper::log('### inside postLoginAs ###', [
            'user_id' => $request->user_id
        ]);

        $user = \App\User::find($request->user_id);
        if (empty($user)) {
            return back()->withErrors([
                'exception' => 'Invalid user ID provided'
            ])->withInput();
        }

        if ( !in_array($user->status , ['A','B']) ) {
            return back()->withErrors([
                'exception' => 'User is not in active status'
            ])->withInput();
        }

        $login_as_user = Auth::guard('admin')->user();

        Auth::guard('admin')->logout();
        Session::flush();
        Session::regenerate();

        Auth::guard('user')->login($user);
        Session::put('login-as-user', $login_as_user);

        # save_login_history($user_id, $email, $login_channel, $ip_addr)
        UserLoginHistory::save_login_history($user->user_id, $login_as_user->email, 'A', $request->ip(), 'I');

        return redirect('/user/home');
    }
}
