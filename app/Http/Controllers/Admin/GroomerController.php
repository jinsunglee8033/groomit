<?php

namespace App\Http\Controllers\Admin;

use App\Model\GroomerBlockedBreeds;
use App\Model\GroomerDocument;
use App\Model\GroomerNotificationType;
use App\Model\GroomerServiceArea;
use App\Model\GroomerServicePackage;
use App\Model\Notification;
use App\Model\Product;
use App\Model\ProfitShare;
use App\Model\ProfitSharing;
use App\Model\PromoCode;
use App\Model\ServiceArea;
use App\Model\UserFavoriteGroomer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Groomer;
use App\Model\GroomerAvailability;
use App\Model\GroomerPetPhoto;
use App\Model\GroomerTool;
use App\Model\AppointmentList;
use App\Model\User;
use App\Model\Address;
use App\Model\Constants;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Log;
use Validator;
use Redirect;
use Excel;
use Illuminate\Support\Facades\Input;


class GroomerController extends Controller
{

    public function groomers(Request $request) {
        try {
            $sdate = Carbon::create(2017, 1, 1);
            $edate = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $query = Groomer::select('groomer_id',
                'level',
                'first_name',
                'last_name',
                'dog',
                'cat',
                'email',
                'phone',
                'street',
                'city',
                'state',
                'zip',
                'status',
                'background_check_status',
                'trial_notes',
                'service_area',
                'cdate',
                DB::raw("(select cast(max(accepted_date) as date) from appointment_list where groomer_id = groomer.groomer_id and status = 'P') as last_groom_date")
              );

            if (!empty($sdate) && empty($request->id)) {
                $query = $query->where('cdate', '>=', $sdate);
            }

            if (!empty($edate) && empty($request->id)) {
                $query = $query->where('cdate', '<=', $edate);
            }

            if (!empty($request->level)) {
                $query = $query->where('level', $request->level);
            }

            if (!empty($request->name)) {
                $query = $query->whereRaw('(LOWER(first_name) like \'%' . strtolower($request->name) . '%\' or LOWER(last_name) like \'%' . strtolower($request->name) . '%\')');
            }

            if (!empty($request->phone)) {
                $query = $query->where('phone', 'like', '%' . $request->phone . '%');
            }

            if (!empty($request->email)) {
                $query = $query->where('email', 'like', '%' .$request->email . '%');
            }

            if (!empty($request->state)) {
                $query = $query->where('state', $request->state);
            }

            if (!empty($request->status)) {
                $query = $query->where('status', $request->status);
            }

            if (!empty($request->background_check_status)) {
                $query = $query->where('background_check_status', $request->background_check_status);
            }

            if (!empty($request->location)) {
                $query = $query->whereRaw('(LOWER(city) like \'%' . strtolower($request->name) . '%\' or LOWER(state) like \'%' . strtolower($request->name) . '%\')');
            }

            $query = $query->where('status', '<>', 'D');

            if ($request->excel == 'Y') {
                $groomers = $query->orderBy('cdate', 'desc')->get();

                $yms = [];
                $rdate = Carbon::now()->subMonths(12);
                for ($i=0; $i<12; $i++) {
                    $rdate = $rdate->addMonth();
                    $yms[] = [
                        'year' => $rdate->year,
                        'month' => $rdate->month,
                        'label' => $rdate->format('M y')
                    ];
                }

                Excel::create('groomers', function($excel) use($groomers, $yms) {

                    $excel->sheet('reports', function($sheet) use($groomers, $yms) {

                        $data = [];
                        foreach ($groomers as $a) {
                            $row = [
                                'Groomer ID' => $a->groomer_id,
                                'Name' => $a->first_name . '    ' . $a->last_name,
                                'Dog' => $a->dog,
                                'Cat' => $a->cat,
                                'Level' => $a->level,
                                'email' => $a->email,
                                'Phone' => $a->phone,
                                'Address' => $a->street . ', ' . $a->city . ', ' . $a->state . ', ' . $a->zip,
                                'Service Area' => $a->service_area,
                                'Date' => $a->cdate,
                                'Status' => $a->status_name(),
                                'Last Grooming Date' => Groomer::get_last_groom_date($a->groomer_id),
                                'ACH Authorization form' => Groomer::get_document_status($a->groomer_id, 'A'),
                                'Groomer Agreement' => Groomer::get_document_status($a->groomer_id, 'G'),
                                'W9 Form' => Groomer::get_document_status($a->groomer_id, 'J'),
                                'Driver Liscence' => Groomer::get_document_status($a->groomer_id, 'U'),
                            ];

                            foreach ($yms as $ym) {
                                $row['# of ' . $ym['label']] = Groomer::get_num_of_appointment($a->groomer_id, $ym['year'], $ym['month']);
                            }

                            $data[] = $row;

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $total = $query->count();

            $groomers = $query->orderBy('cdate', 'desc')
                ->paginate(20);

            $states = DB::select("
                select s.code, s.name, sum(1) qty
                  from states s
                  join groomer g on s.code = g.state
                 group by s.code, s.name
                 order by 2 asc
            ");

            return view('admin.groomers', [
                'msg' => '',
                'groomers' => $groomers,
                'sdate' => $sdate->format('Y-m-d'),
                'edate' => $edate->format('Y-m-d'),
                'name'  => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'state' => $request->state,
                'states' => $states,
                'status' => $request->status,
                'background_check_status' => $request->background_check_status,
                'level' => $request->level,
                'location' => $request->location,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return back()->withErrors([
                'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ])->withInput();
        }
    }

    public function groomer($id) {

        try {
            $groomer = Groomer::findOrFail($id);

            $groomer->pet_photo = GroomerPetPhoto::where('groomer_id',$id)->first();

            $groomer->tools = DB::table('tools')
                ->select('tools.name')
                ->join('groomer_tools', 'tools.id', '=', 'groomer_tools.tool_id')
                ->where('groomer_tools.groomer_id',$id)
                ->get();

            $groomer->packages = DB::select("
                select *
                  from vw_groomer_service_package
                 where groomer_id = :groomer_id
            ", [
                'groomer_id' => $groomer->groomer_id
            ]);

            $groomer->breeds = DB::select("
            SELECT
                a.breed_id AS breed_id,
                a.breed_name AS breed_name,
                b.status AS status
            FROM breed a
            LEFT JOIN groomer_blocked_breeds b
                ON b.groomer_id = :groomer_id
                AND a.breed_id = b.breed_id
            ORDER BY a.sorting ASC    
            ", [
               'groomer_id' => $groomer->groomer_id
            ]);

            $groomer->notification = DB::select("
            SELECT
                a.notification_id AS notification_id,
                a.notification_name AS notification_name,
                b.status as status
            FROM
                notification a
            LEFT JOIN groomer_notification_types b 
                ON b.groomer_id = :groomer_id
                AND a.notification_id = b.notification_id
                order by 2 ;
            ", [
                'groomer_id' => $groomer->groomer_id
            ]);

            $date_range = new \stdClass();
            $today = new Carbon('today', 'America/New_York');
            $week_start = $today->startOfWeek();
            $date_range->week_start = $week_start->format('m/d/Y');
            $week_end = $today->copy()->endOfWeek();
            $date_range->week_end = $week_end->format('m/d/Y');
            $date_range->week = 0;

            $groomer->availability = GroomerAvailability::where('groomer_id',$id)
                ->whereRaw("date >= '" . $week_start->format('Y-m-d') . "'")
                ->whereRaw("date <= '" . $week_end->format('Y-m-d') . "'")
                ->get();

            if ($groomer->availability->isEmpty()) {
                $groomer->availability = GroomerAvailability::where('groomer_id',$id)
                    ->where('date','0000-00-00')
                    ->get();
            }

            $aa_all = '';
            foreach ($groomer->availability as $aa) {
                $aa_all .= 'wd' . $aa->weekday . '_h' . str_pad($aa->hour, 2, '0', STR_PAD_LEFT);
            }
            $groomer->availability = $aa_all;

            $groomer->groomer_stat = DB::select("
            SELECT book_cnt, revenue_total, rating_qty, rating_avg
            FROM groomer_stat
            WHERE groomer_id = :groomer_id  
            ", [
                'groomer_id' => $groomer->groomer_id
            ]);

            $groomer->earnings = DB::select("
            SELECT sum(case `type` when 'A' then groomer_profit_amt when 'W' then groomer_profit_amt when 'V' then groomer_profit_amt else 0 end ) earn_appt,
                   sum(case `type` when 'T' then groomer_profit_amt  else 0 end ) earn_tip,
                   sum(case `type` when 'C' then groomer_profit_amt when 'D' then groomer_profit_amt  when 'J' then groomer_profit_amt  else 0 end ) earn_adjust,
			       sum(case `type` when 'R' then groomer_profit_amt when 'L' then groomer_profit_amt else 0 end ) earn_refer
            FROM profit_share
            WHERE groomer_id = :groomer_id  
            ", [
                'groomer_id' => $groomer->groomer_id
            ]);

//            $groomer->average_rating = AppointmentList::where('groomer_id', $groomer->groomer_id)
//                ->where('status', 'P')
//                //->whereNotNull('rating') //This does not affect average of ratings
//                ->avg('rating');
//
//            $groomer->rating_qty = AppointmentList::where('groomer_id', $groomer->groomer_id)
//                ->where('status', 'P')
//                ->whereNotNull('rating')
//                ->count();
//
//            $groomer->total_appts = AppointmentList::where('groomer_id', $groomer->groomer_id)
//                ->where('status', 'P')
//                ->count();

            if($groomer->phone_interview_notes == ''){

                $groomer->phone_question = "
1. Phone First question is ?

2. Phone Second question is ?

3. Phone Third question is ?
";
            }

            if($groomer->trial_interview_notes == ''){

                $groomer->trial_question = "
1. Trial First question is ?

2. Trial Second question is ?

3. Trial Third question is ?
";
            }

//            $ava_counties = GroomerServiceArea::where('groomer_id', $groomer->groomer_id)->where('status', 'A')->get();
//
//            $county_list = '';
//            foreach ($ava_counties as $c){
//                $temp = substr($c->county, 0, -3);
//                $county_list .= "'".$temp."'" . ",";
//            }
//
//            if ($county_list != '') {
//                $county_list = substr($county_list, 0, -1);
//                $counties = DB::select("
//                select distinct county_name, state_abbr
//                from allowed_zip
//                where lower(available) = 'x'
//                and county_name not in ($county_list)
//                order by 2, 1
//                ");
//            }else {
//                $counties = DB::select("
//                select distinct county_name, state_abbr
//                from allowed_zip
//                where lower(available) = 'x'
//                order by 2, 1
//                ");
//            }

            $groomer->area = DB::select("
            SELECT
                a.area_id as area_id,
                a.area_name as area_name,
                b.status as status
            FROM service_area a
                LEFT JOIN groomer_service_area b
                ON b.groomer_id = :groomer_id AND a.area_name = b.county
            ORDER BY a.sort ASC
            ", [
                'groomer_id' => $groomer->groomer_id
            ]);

//            $groomer->exclusive_area = DB::select("
//            SELECT a.alias_id, a.alias_name, a.state, 0 days, b.weekday
//            FROM exclusive_area a LEFT JOIN groomer_exclusive_area b  ON b.groomer_id = :groomer_id0 AND b.alias_id = a.alias_id and weekday = 0
//            UNION ALL
//            SELECT a.alias_id, a.alias_name, a.state, 1 days, b.weekday
//            FROM exclusive_area a LEFT JOIN groomer_exclusive_area b  ON b.groomer_id = :groomer_id1 AND b.alias_id = a.alias_id and weekday = 1
//            UNION ALL
//            SELECT a.alias_id, a.alias_name, a.state, 2 days, b.weekday
//            FROM exclusive_area a LEFT JOIN groomer_exclusive_area b  ON b.groomer_id = :groomer_id2 AND b.alias_id = a.alias_id and weekday = 2
//            UNION ALL
//            SELECT a.alias_id, a.alias_name, a.state, 3 days, b.weekday
//            FROM exclusive_area a LEFT JOIN groomer_exclusive_area b  ON b.groomer_id = :groomer_id3 AND b.alias_id = a.alias_id and weekday = 3
//            UNION ALL
//            SELECT a.alias_id, a.alias_name, a.state, 4 days, b.weekday
//            FROM exclusive_area a LEFT JOIN groomer_exclusive_area b  ON b.groomer_id = :groomer_id4 AND b.alias_id = a.alias_id and weekday = 4
//            UNION ALL
//            SELECT a.alias_id, a.alias_name, a.state, 5 days, b.weekday
//            FROM exclusive_area a LEFT JOIN groomer_exclusive_area b  ON b.groomer_id = :groomer_id5 AND b.alias_id = a.alias_id and weekday = 5
//            UNION ALL
//            SELECT a.alias_id, a.alias_name, a.state, 6 days, b.weekday
//            FROM exclusive_area a LEFT JOIN groomer_exclusive_area b  ON b.groomer_id = :groomer_id6 AND b.alias_id = a.alias_id and weekday = 6
//            ORDER BY 4, 3,2 ASC
//            ", [
//                'groomer_id0' => $groomer->groomer_id,
//                'groomer_id1' => $groomer->groomer_id,
//                'groomer_id2' => $groomer->groomer_id,
//                'groomer_id3' => $groomer->groomer_id,
//                'groomer_id4' => $groomer->groomer_id,
//                'groomer_id5' => $groomer->groomer_id,
//                'groomer_id6' => $groomer->groomer_id
//            ]);

            $documents = DB::select("
                select g.type
                     , g.name as type_name
                     , d.id
                     , d.groomer_id
                     , d.file_name
                     , d.data
                     , d.signed
                     , d.locked
                     , d.e_doc_id
                     , d.esign_url
                     , d.created_by
                     , d.cdate
                     , d.verified
                     , d.verified_date
                  from groomer_document_type g 
                  left join groomer_document d on g.type = d.type and d.groomer_id = :groomer_id and d.status = 'A'
                 order by g.type
            ", [
                'groomer_id' => $groomer->groomer_id
            ]);

            $promocodes = PromoCode::where('groomer_id', $groomer->groomer_id)->get();

//            $admin_id = Auth::guard('admin')->user()->admin_id;
//            if( in_array($admin_id, [12,15,26,29])) { //Lars, Sohel, Zabair only, could update level
//                $enable_upd_level = 'Y';
//            }else {
//                $enable_upd_level = 'N';
//            }
            $enable_upd_level = 'Y';

            $groomer->fav_user_num = UserFavoriteGroomer::where('groomer_id', $groomer->groomer_id)->count();

            return view('admin.groomer', [
                'msg' => '',
                'gr' => $groomer,
                'dr' => $date_range,
                'rating' => $groomer->average_rating,
                'total_appts' => $groomer->total_appts,
//                'counties' => $counties,
//                'ava_counties' => $ava_counties,
                'documents' => $documents,
                'promocodes' => $promocodes,
                'enable_upd_level' => $enable_upd_level
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function document_upload(Request $request, $groomer_id) {
        try {
            $groomer = Groomer::findOrFail($groomer_id);

            if (empty($groomer)) {
                return Redirect::route('admin.groomer', array('id' => $groomer_id))->with('alert', 'The groomer is not available.');
            }

            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                return Redirect::route('admin.groomer', array('id' => $groomer_id))->with('alert', 'Session expired!');
            }

            $modified_by = $admin->name . ' (' . $admin->admin_id . ')';

            $key = 'document_file';
            if (Input::hasFile($key)){
                if (Input::file($key)->isValid()) {
                    $path = Input::file($key)->getRealPath();

                    $contents = file_get_contents($path);
                    $name = Input::file($key)->getClientOriginalName();

                    $document_file = base64_encode($contents);

                    $gd = GroomerDocument::where('groomer_id', $groomer->groomer_id)->where('type', $request->type)->first();
                    if (empty($gd)) {
                        $gd = new GroomerDocument();
                        $gd->groomer_id = $groomer->groomer_id;
                        $gd->type = $request->type;
                    }
                    $gd->file_name = $name;
                    $gd->data = $document_file;
                    $gd->signed = 'N';
                    $gd->locked = 'N';
                    $gd->created_by = $modified_by;
                    $gd->cdate = Carbon::now();
                    $gd->verified = 'N';
                    $gd->verified_date = null;
                    $gd->save();

                } else {
                    DB::rollback();
                    $msg = 'Invalid document file provided';
                    return Redirect::route('admin.groomer', array('id' => $groomer_id))->with('alert', $msg);
                }
            }

            $msg = 'Document upload successfully !!';
            return redirect('/admin/groomer/' . $groomer_id)->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';

            return Redirect::route('admin.groomer', array('id' => $groomer_id))->with('alert', $msg);
        }
    }
    public function document_verified(Request $request, $groomer_id, $document_id){
        $file = \App\Model\GroomerDocument::find($document_id);
        if (!empty($file)) {
            $file->verified = 'Y';
            $file->verified_date = Carbon::now();
            $file->update();
        }

        return back();
    }

    public function get_groomer_schedule(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'week' => 'required',
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
            }

            $week = $request->week;
            //$first_monday = new Carbon('this monday', 'America/New_York');
            $today = new Carbon('today', 'America/New_York');
            $first_monday = $today->startOfWeek();
            $sd = $first_monday->copy()->addDays($week * 7)->format('Y-m-d');
            $ed = $first_monday->copy()->addDays(6 + ($week * 7))->format('Y-m-d');

            $ga = GroomerAvailability::where('groomer_id',$request->id)
                ->whereRaw("date >= '" . $sd . "'")
                ->whereRaw("date <= '" . $ed . "'")
                ->get();

            $aa_all = '';
            foreach ($ga as $aa) {
                $aa_all .= 'wd' . $aa->weekday . '_h' . str_pad($aa->hour, 2, '0', STR_PAD_LEFT);
            }

            return response()->json([
                'msg' => '',
                'ga' => $aa_all
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function groomer_update(Request $request) {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'status' => 'required',
                'level' => 'required',
                'id' => 'required',
                'first_name' => 'required',
//                'phone' => 'required|regex:/^\d{10}$/',
                'mobile_phone' => 'required|regex:/^\d{10}$/',
                'email' => 'required|email',
                'street' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|regex:/^\d{5}$/',
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
            }

            ### update basic groomer information ###
            $gr = Groomer::findOrFail($request->id);

            $old_status = $gr->status;
            $new_status = $request->status;

            // User Groomer Favorite delete.
            if($old_status == 'A' && $new_status == 'I'){
                UserFavoriteGroomer::where('groomer_id', $request->id)->delete();
            }

            $gr->status = $request->status;
            $gr->level = $request->level;
            $gr->dog = isset($request->dog) ? 'Y': 'N';
            $gr->cat = isset($request->cat) ? 'Y': 'N';
            $gr->first_name = $request->first_name;
            $gr->last_name = $request->last_name;
//            $gr->phone = $request->phone;
            $gr->mobile_phone = $request->mobile_phone;
            $gr->email = $request->email;
            $gr->sex = $request->sex;
            $gr->transportation = $request->transportation;
            $gr->relocate = $request->relocate;
            $gr->available_in_my_area = $request->available_in_my_area;
            $gr->street = $request->street;
            $gr->city = $request->city;
            $gr->state = $request->state;
            $gr->zip = $request->zip;
            $gr->groomer_exp_note = $request->groomer_exp_note;
            $gr->bio = $request->bio;
            $gr->weekly_allowance = $request->weekly_allowance;
            $gr->bank_name = $request->bank_name;
            $gr->account_holder = $request->account_holder;
            $gr->account_number = $request->account_number;
            $gr->routing_number = $request->routing_number;
            $gr->service_area = $request->service_area;
            $gr->background_check_status = $request->background_check_status;
            $gr->general_notes = $request->general_notes;
            $gr->text_appt = $request->text_appt;
            $gr->phone_interview_notes = $request->phone_interview_notes;
            $gr->trial_interview_notes = $request->trial_interview_notes;
            $gr->trial_notes = $request->trial_notes;
            $gr->mdate = Carbon::now();


            $key = 'profile_photo';
            if (Input::hasFile($key)){
                if (Input::file($key)->isValid()) {
                    $path = Input::file($key)->getRealPath();

                    Log::info('### FILE ###', [
                        'key' => $key,
                        'path' => $path
                    ]);

                    $contents = file_get_contents($path);
                    $name = Input::file($key)->getClientOriginalName();

                    $gr->profile_photo = base64_encode($contents);

                } else {
                    DB::rollback();
                    $msg = 'Invalid profile photo provided';
                    return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
                }
            }

            $gr->save();

            DB::commit();

            $msg = "Success";

            return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function phone_interview_notes(Request $request) {
        try {

            $gr = Groomer::findOrFail($request->id);

            $gr->phone_interview_notes = $request->phone_interview_notes;
            $gr->mdate = Carbon::now();
            $gr->save();

            DB::commit();

            $msg = "Success";

            return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function trial_interview_notes(Request $request) {
        try {

            $gr = Groomer::findOrFail($request->id);

            $gr->trial_interview_notes = $request->trial_interview_notes;
            $gr->mdate = Carbon::now();
            $gr->save();

            DB::commit();

            $msg = "Success";

            return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function groomer_change_password(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'password' => 'required',
                'confirm_password' => 'required'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
            }

            ### update basic groomer information ###
            $gr = Groomer::findOrFail($request->id);

            $gr->password = \Crypt::encrypt($request->password);
            $gr->mdate = Carbon::now();
            $gr->save();

            $msg = "Success";

            return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function groomer_delete(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
            }

            ### update basic groomer information ###
            $gr = Groomer::findOrFail($request->id);

            $gr->status = 'D'; // instead of delete info, change status and do not show
            $gr->mdate = Carbon::now();
            $gr->save();

            $msg = "Success";

            // User Groomer Favorite delete.
            UserFavoriteGroomer::where('groomer_id', $request->$request->id)->delete();

            return Redirect::route('admin.groomers')->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.groomer', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function groomer_schedule_update(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'id' => 'required',
                'week' => 'required'
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


            $at_least_one_checked = false;
            for ($i=0; $i<=6; $i++) {
                for ($j=8; $j<=24; $j++) {
                    $key = 'wd' . $i . '_h' . str_pad($j, 2, '0', STR_PAD_LEFT);
                    Log::info('### key : ' . $key . ' ###');
                    if ($request->get($key) == 'Y') {
                        $at_least_one_checked = true;
                        break;
                    }
                }

                if ($at_least_one_checked) {
                    break;
                }
            }

            if (!$at_least_one_checked) {
                return response()->json([
                    'msg' => 'Please setup groomer\'s availability'
                ]);
            }

            $week = $request->week;
            //$first_monday = new Carbon('this monday', 'America/New_York');
            $today = new Carbon('today', 'America/New_York');
            $first_monday = $today->startOfWeek();
            $sd = $first_monday->copy()->addDays($week * 7)->format('Y-m-d');
            $ed = $first_monday->copy()->addDays(6 + ($week * 7))->format('Y-m-d');


            DB::beginTransaction();

            # delete only current week range
            GroomerAvailability::where('groomer_id', '=', $request->id)
                ->whereRaw("((date >= '" . $sd . "' and date <= '" . $ed . "') or date = '0000-00-00')")
                ->delete();


            ### Schedule ###
            for ($i=0; $i<=6; $i++) {
                # set the date
                $d = $first_monday->copy()->addDays($i + ($week * 7));

                for ($j=8; $j<=24; $j++) {
                    $key = 'wd' . $i . '_h' . str_pad($j, 2, '0', STR_PAD_LEFT);
                    if ($request->get($key) == 'Y') {

                        $ga = new GroomerAvailability;
                        $ga->groomer_id = $request->id;
                        $ga->weekday = $i;
                        $ga->hour = $j;
                        $ga->date = $d;
                        $ga->save();
                    }
                }
            }

            DB::commit();

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

    public function loadCredit(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_id' => 'required',
                'sdate' => 'required|date',
                'edate' => 'required|date'
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

            $sdate = Carbon::today()->subDays(30);
            $edate = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59);

            if (!empty($request->sdate)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $data = ProfitShare::where('groomer_id', $request->groomer_id)
                ->whereIn('type', ['C', 'D', 'R','L'])  //Credit, Debit, Referal, Reversal of Referal
                ->where('cdate', '>=', $sdate)
                ->where('cdate', '<', $edate)
                ->orderBy('cdate', 'desc')
                ->get();

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

    public function saveCredit(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'groomer_id' => 'required',
                'type' => 'required|in:C,D',
                'amt' => 'required|numeric',
                'comments' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $user = Auth::guard('admin')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Session has been expired. Please login again!'
                ]);
            }

            $ps = new ProfitSharing;
            $ps->type = $request->type;
            $ps->groomer_id = $request->groomer_id;
            $ps->groomer_profit_amt = $request->amt;
            $ps->comments = $request->comments;
//            $ps->cdate = Carbon::now();
            $ps->cdate = !empty($request->cdate) ? $request->cdate : Carbon::now();
            $ps->created_by = $user->admin_id;
            $ps->save();

            $ps = new ProfitShare();
            $ps->type               = $request->type;
            $ps->groomer_id         = $request->groomer_id;
            $ps->groomer_profit_amt = $request->type == 'C' ? $request->amt : -$request->amt;
            $ps->category           = $request->category;
            $ps->remaining_amt      = 0;
            $ps->comments           = $request->comments;
            $ps->cdate              = !empty($request->cdate) ? $request->cdate : Carbon::now();
            $ps->created_by         = $user->admin_id;
            $ps->save();

//            ProfitShare::create_credit($request->type, $request->groomer_id, $request->amt, $request->comments, $user->admin_id, $request->cdate);

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function history(Request $request, $id)
    {
        try {
            $sdate = $request->sdate;
            $edate = $request->edate;

            if (empty($sdate)) {
                $sdate = Carbon::today()->subDays(30);
            }

            if (empty($edate)) {
                $edate = Carbon::today();
            }

            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');

            if (!isset($id)) {
                return response()->json([
                    'msg' => 'Id and type should be provided'
                ]);
            }

            $groomer = Groomer::findOrFail($id);
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Invalid groomer id was provided'
                ]);
            }

            ### list of information to be returned ###
            # - appointment general
            # - groomer information
            # - pet images ( before / after )

            $appointments = AppointmentList::where('groomer_id', $id)
                ->whereIn('status', ['P'])
                ->where('accepted_date', '<>', 'null')
                ->where('accepted_date', '>=', $sdate)
                ->where('accepted_date', '<=', $edate)
                ->select([
                    'appointment_id',
                    'user_id',
                    'address_id',
                    'groomer_id',
                    'reserved_at',
                    'reserved_date',
                    'accepted_date',
                    'special_request',
                    'sub_total',
                    'promo_amt',
                    'tax',
                    'total',
                    'status',
                    'rating',
                    'rating_comments',
                    'cdate'
                ])->orderBy('status', 'desc')
                ->orderBy('reserved_date', 'desc')
                ->get();


            foreach ($appointments as $ap) {

                $ap->status_name = '';
                if (array_key_exists($ap->status, Constants::$appointment_status)) {
                    $ap->status_name = Constants::$appointment_status[$ap->status];
                }

                if ($ap->groomer_id) {
                    $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name')->first();
                    if (!empty($ap->groomer)) {
                        $ap->groomer_name = $ap->groomer->first_name . ' ' . $ap->groomer->last_name;
                    }

                } else {
                    $ap->groomer_name = '';
                }


                $ap->user = User::where('user_id', $ap->user_id)->first();
                $ap->name = $ap->user->first_name . ' ' . $ap->user->last_name;
                $ap->phone = $ap->user->phone;

                $addr = Address::find($ap->address_id);
                if (!empty($addr)) {
                    if( !empty($addr->address2) && ($addr->address2 != '' ) ) {
                        $ap->address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }else {
                        $ap->address = $addr->address1 . ', '  . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }

                }

                if ($accepted_date = Carbon::parse($ap->accepted_date)) {
                    $ap->accepted_date = $accepted_date->format('m/d/Y g:i a');
                } else {
                    $ap->accepted_date = '';
                }
            }


            return view('admin.groomer_history', [
                'msg' => '',
                'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    function add_service_area($groomer_id, $county) {
        if($county == 'ALL') {
            $area = ServiceArea::all();
            foreach ($area as $a) {
                $a_name = $a->area_name;
                $ret = GroomerServiceArea::where('groomer_id', $groomer_id)
                    ->where('county', $a_name)
                    ->first();
                if (empty($ret)) {
                    $ret = new GroomerServiceArea();
                    $ret->groomer_id = $groomer_id;
                    $ret->county = $a_name;
                    $ret->status = 'A';
                    $ret->cdate = Carbon::now();
                    $ret->save();
                } else {
                    $ret->status = 'A';
                    $ret->mdate = Carbon::now();
                    $ret->update();
                }
            }
        } else {
            $area = GroomerServiceArea::where('groomer_id', $groomer_id)->where('county', $county)->first();
            if (empty($area)) {
                $area = new GroomerServiceArea();
                $area->groomer_id = $groomer_id;
                $area->county = $county;
                $area->status = 'A';
                $area->cdate = Carbon::now();
                $area->save();
            } else {
                $area->status = 'A';
                $area->mdate = Carbon::now();
                $area->update();
            }
        }
        return redirect('/admin/groomer/' . $groomer_id);
    }

//    function add_exclusive_area($groomer_id, $weekday, $alias_id ) {
//        if($alias_id != '') {
//
//            $ret = DB::insert("
//                insert into groomer_exclusive_area( alias_id, groomer_id,weekday, cdate)
//                 values( :alias_id, :groomer_id, :weekday, :cdate)
//            ", [
//                'alias_id' => $alias_id,
//                'groomer_id' => $groomer_id,
//                'weekday' => $weekday,
//                'cdate' => Carbon::now()
//            ]);
//        }
//        return redirect('/admin/groomer/' . $groomer_id);
//    }

    function remove_service_area($groomer_id, $id) {
        $area = GroomerServiceArea::where('groomer_id', $groomer_id)->where('county', $id)->first();

        if (!empty($area)) {
            $area->status = 'C';
            $area->mdate = Carbon::now();
            $area->update();
        }
        return redirect('/admin/groomer/' . $groomer_id);
    }

//    function remove_exclusive_area($groomer_id, $weekday, $alias_id) {
//        $ret = DB::DELETE(" delete from groomer_exclusive_area
//                                  where alias_id = :alias_id and groomer_id = :groomer_id and weekday = :weekday ",
//                         [ 'alias_id' =>  $alias_id ,
//                           'groomer_id' => $groomer_id,
//                           'weekday' => $weekday
//                         ] );
//
//        return redirect('/admin/groomer/' . $groomer_id);
//    }

    function add_service_package($groomer_id, $prod_id) {

        if($prod_id == 'ALL') {
            $products = Product::where('prod_type', 'P')->where('status', 'A')->get();
            foreach ($products as $p) {
                $p_id = $p->prod_id;

                $ret = GroomerServicePackage::where('groomer_id', $groomer_id)
                    ->where('prod_id', $p_id)
                    ->first();
                if (empty($ret)) {
                    $ret = new GroomerServicePackage();
                    $ret->groomer_id = $groomer_id;
                    $ret->prod_id = $p_id;
                    $ret->status = 'A';
                    $ret->cdate = Carbon::now();
                    $ret->save();
                } else {
                    $ret->status = 'A';
                    $ret->mdate = Carbon::now();
                    $ret->update();
                }
            }
        } else {

            $package = GroomerServicePackage::where('groomer_id', $groomer_id)->where('prod_id', $prod_id)->first();

            if (empty($package)) {
                $package = new GroomerServicePackage();
                $package->groomer_id = $groomer_id;
                $package->prod_id = $prod_id;
                $package->status = 'A';
                $package->cdate = Carbon::now();
                $package->save();
            } else {
                $package->status = 'A';
                $package->mdate = Carbon::now();
                $package->update();
            }
        }

        return redirect('/admin/groomer/' . $groomer_id);
    }

    function remove_service_package($groomer_id, $prod_id) {

        $package = GroomerServicePackage::where('groomer_id', $groomer_id)->where('prod_id', $prod_id)->first();

        if (!empty($package)) {
            $package->status = 'C';
            $package->mdate = Carbon::now();
            $package->update();
        }
        return redirect('/admin/groomer/' . $groomer_id);
    }

    function add_blocked_breed($groomer_id, $breed_id) {
        $breed = GroomerBlockedBreeds::where('groomer_id', $groomer_id)->where('breed_id', $breed_id)->first();

        if (empty($breed)) {
            $breed = new GroomerBlockedBreeds();
            $breed->groomer_id = $groomer_id;
            $breed->breed_id = $breed_id;
            $breed->status = 'A';
            $breed->cdate = Carbon::now();
            $breed->save();
        } else {
            $breed->status = 'A';
            $breed->mdate = Carbon::now();
            $breed->update();
        }

        return redirect('/admin/groomer/' . $groomer_id);
    }

    function remove_blocked_breed($groomer_id, $breed_id) {
        $breed = GroomerBlockedBreeds::where('groomer_id', $groomer_id)->where('breed_id', $breed_id)->first();

        if (!empty($breed)) {
            $breed->status = 'C';
            $breed->mdate = Carbon::now();
            $breed->update();
        }
        return redirect('/admin/groomer/' . $groomer_id);
    }

    function add_notification_type($groomer_id, $notification_id) {

        if($notification_id == 'ALL'){
            $notifications = Notification::all();
            foreach($notifications as $n){
                $notification_id = $n->notification_id;

                $ret = GroomerNotificationType::where('groomer_id', $groomer_id)
                                            ->where('notification_id', $notification_id)
                                            ->first();
                if(empty($ret)) {
                    $ret = new GroomerNotificationType();
                    $ret->groomer_id = $groomer_id;
                    $ret->notification_id = $notification_id;
                    $ret->status = 'A';
                    $ret->cdate = Carbon::now();
                    $ret->save();
                } else {
                    $ret->status = 'A';
                    $ret->mdate = Carbon::now();
                    $ret->update();
                }
            }

        } else {
            $notification = GroomerNotificationType::where('groomer_id', $groomer_id)->where('notification_id', $notification_id)->first();

            if (empty($notification)) {
                $notification = new GroomerNotificationType();
                $notification->groomer_id = $groomer_id;
                $notification->notification_id = $notification_id;
                $notification->status = 'A';
                $notification->cdate = Carbon::now();
                $notification->save();
            } else {
                $notification->status = 'A';
                $notification->mdate = Carbon::now();
                $notification->update();
            }
        }

        return redirect('/admin/groomer/' . $groomer_id);
    }

    function remove_notification_type($groomer_id, $notification_id) {
        $notification = GroomerNotificationType::where('groomer_id', $groomer_id)->where('notification_id', $notification_id)->first();

        if (!empty($notification)) {
            $notification->status = 'C';
            $notification->mdate = Carbon::now();
            $notification->update();
        }
        return redirect('/admin/groomer/' . $groomer_id);
    }

    function add_promocode($groomer_id, $code) {
        $promocode = PromoCode::whereRaw("code = '" . strtoupper($code) . "'")->first();

        if (empty($promocode)) {
            $user = Auth::guard('admin')->user();

            $promo_code = new PromoCode;
            $promo_code->code       = strtoupper($code);
            $promo_code->type       = 'R';
            $promo_code->status     = 'A';
            $promo_code->amt_type   = 'A';
            $promo_code->amt        =  env('REFERRAL_CODE_AMT'); //Since 07/03/2020    // Since 02/26/2020
            $promo_code->groomer_id = $groomer_id;
            $promo_code->cdate      = Carbon::now();
            $promo_code->first_only = 'Y';
            $promo_code->expire_date = '2099-12-31';
            $promo_code->created_by = $user->admin_id;
            $promo_code->include_tax = 'N';
            $promo_code->no_insurance = 'N';
            $promo_code->save();
        }

        return redirect('/admin/groomer/' . $groomer_id);
    }

    function remove_promocode($groomer_id, $code) {
        PromoCode::where('groomer_id', $groomer_id)->whereRaw("code = '" . strtoupper($code) . "'")->delete();


        return redirect('/admin/groomer/' . $groomer_id);
    }

    public function update_cs_notes(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'groomer_id' => 'required'
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

            DB::beginTransaction();

            $groomer = Groomer::find($request->groomer_id);
            $groomer->cs_notes = $request->cs_notes;
            $groomer->save();

            DB::commit();

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
