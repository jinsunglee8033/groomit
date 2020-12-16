<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/12/18
 * Time: 3:47 PM
 */

namespace App\Http\Controllers\User\Schedule;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\ScheduleProcessor;
use App\Model\Address;
use App\Model\AppointmentList;
use App\Model\Groomer;
use App\Model\GroomerAvailability;
use App\Model\UserFavoriteGroomer;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SelectDateController extends Controller
{

    public function show(Request $request) {
        Session::put('schedule.url', $request->path());

        $user = \Auth::guard('user')->user();

        // FAV GROOMER
        $favs = UserFavoriteGroomer::where('user_id', $user->user_id)->get();
        $num_favs = count($favs);

        if(count(array($favs))>0){
            foreach ($favs as $fav){
                $id = $fav->groomer_id;
                $groomer_obj = Groomer::where('groomer_id', $id)->first();
                $fav->name  = $groomer_obj->first_name;
                $fav->pic   = $groomer_obj->profile_photo;
            }
        }
        // FAV GROOMER

//        $state = ''; //To allow calendar after 11/01/2010 for FL. Not controlled in blade, but servierside
//        $address = Address::where('user_id', $user->user_id)
//            ->where('status', '!=', 'D')
//            ->where(DB::raw("ifnull(zip, '')"), '!=', '')
//            ->first();
//        if(!empty($address)){
//            $state = $address->state;
//        }


        //return view('user.schedule.select-date-at', [
        return view('user.schedule.select-date', [
            //'time_windows'  => Helper::get_time_windows(), => Not used any longer, 07282020
            // FAV GROOMER
            'favs'          => $favs,
            'num_favs'      => $num_favs
        ]);
    }

//  Not used any longer, 07/28/2020.
//    public function loadTimes(Request $request) {
//        try {
//
//            $v = Validator::make($request->all(), [
//                'date' => 'required|date'
//            ]);
//
//            if ($v->fails()) {
//                $msg = '';
//                foreach ($v->messages()->toArray() as $k => $v) {
//                    $msg .= (empty($msg) ? '' : "|") . $v[0];
//                }
//
//                return response()->json([
//                    'msg' => $msg
//                ]);
//            }
//
//            $time_windows = Helper::get_time_windows_by_date($request->date);
//
//            return response()->json([
//                'msg' => '',
//                'times' => $time_windows
//            ]);
//
//        } catch (\Exception $ex) {
//            return response()->json([
//                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
//            ]);
//        }
//    }

//    public function loadGroomerTimes(Request $request) {
//        try {
//
//            $time_windows = Helper::get_time_windows_by_date_with_groomer($request->date, $request->groomer_id);
//
//            return response()->json([
//                'msg' => '',
//                'groomer'   => $request->groomer_id,
//                'times' => $time_windows
//            ]);
//
//
//        } catch (\Exception $ex) {
//            return response()->json([
//                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
//            ]);
//        }
//    }

    //json only for calendar plugin.
    public function loadGroomerCalendar( $groomer_id , $target_date) {
        try {

//            if(empty($groomer_id)){
//                return response()->json([
//                    'msg' => ''
//                ]);
//            }
//            //No message when no date is sent.
//            if(empty($target_date)){
//                return response()->json([
//                    'msg' => ''
//                ]);
//            }
            if (Auth::guard('user')->check()) {
                $user_id = Auth::guard('user')->user()->user_id;
            }else {
                $user_id = ''; //No login yet.
            }

            $zip = ScheduleProcessor::getZip();

            $groomer_calendar = Helper::get_groomer_calendar( $groomer_id, $target_date, $user_id, $zip ) ;
            return $groomer_calendar;


//            return response()->json([
//                'msg' => '',
//                'calendar' => $groomer_calendar
//            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
    public function loadGroomerCalendar2( $groomer_id , $target_date) {
        try {

//            if(empty($groomer_id)){ //In case of Next available option, it become true, so should not comment out.
//                return response()->json([
//                    'msg' => ''
//                ]);
//            }
//            //No message when no date is sent.
//            if(empty($target_date)){
//                return response()->json([
//                    'msg' => ''
//                ]);
//            }

            if (Auth::guard('user')->check()) {
                $user_id = Auth::guard('user')->user()->user_id;
            }else {
                $user_id = ''; //No login yet.
            }

            $zip = ScheduleProcessor::getZip();

            $groomer_calendar = Helper::get_groomer_calendar2( $groomer_id, $target_date, $user_id, $zip ) ;
            return $groomer_calendar;


//            return response()->json([
//                'msg' => '',
//                'calendar' => $groomer_calendar
//            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
    public function loadGroomerCalendarAvailability( Request $request ) {
        try {


            $groomer_id = $request->groomer_id;

            if (Auth::guard('user')->check()) {
                $user_id = Auth::guard('user')->user()->user_id;
            }else {
                $user_id = ''; //No login yet.
            }

            //zip/include_eco will not work if it's called from 'appt edit' UI, but it's ok.
            $zip = ScheduleProcessor::getZip();
            $pkg_id = ScheduleProcessor::getCurrentPackageId();
            //Helper::log('### loadGroomerCalendarAvailability:getCurrentPackageId ###', $pkg_id);

            if ( isset($pkg_id) && in_array($pkg_id, [28, 29])) {
                $include_eco = 'Y';
            }else {
                $include_eco = 'N';
            }
            //Helper::log('### loadGroomerCalendarAvailability:include_eco ###', $include_eco);
            $availability = Helper::get_groomer_calendar_availability( $groomer_id, $user_id, $zip, $include_eco ) ;
            $min_date = '';
            $max_date = '';
            if(is_array($availability)){
                $cnt = count($availability);
                $min_date = $availability[0]['full_date'];
                $max_date = $availability[$cnt-1]['full_date'];
            }

            return response()->json([
                'msg' => '',
                'availability' => $availability,
                'min_date' => $min_date,
                'max_date' => $max_date
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
    public function post(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'date' => 'required|date',
                'time' => 'required'
            ]);

            if ($v->fails()) {
                return back()->withInput()->withErrors($v);
            }

            if (!Auth::guard('user')->check()) {
                return back()->withInput()->withErrors([
                    'exception' => 'Please login first!'
                ]);
            }

            $windows = Helper::get_time_windows();
            $time = null;
            if (is_array($windows)) {
                foreach ($windows as $o) {
                    //if ($o->id == $request->time) {
                    if ($o->id == $request->time_id) {
                        $time = $o;
                        break;
                    }
                }
            }

            if (empty($time)) {
                return back()->withInput()->withErrors([
                    'exception' => 'Unable to find selected time window: ' . $request->time
                ]);
            }

            if (in_array(ScheduleProcessor::getCurrentPackageId(), [28, 29])) {
                $min_date = Carbon::today()->addDays(7)->format('Y-m-d');
                $r_date = Carbon::parse($request->date)->format('Y-m-d');

                if ($r_date < $min_date) {
                    return back()->withInput()->withErrors([
                      'exception' => 'Only could book 7 days in advance.'
                    ]);
                }
            }

            ScheduleProcessor::setDate(Carbon::parse($request->date));
            ScheduleProcessor::setTime($time);

            if ($request->grooming_place) {
                ScheduleProcessor::setPlace($request->grooming_place);

                if($request->grooming_place == 'O'){
                    ScheduleProcessor::setPlaceOther($request->other_grooming_place);
                }

            }

            ### Fav Groomer ###
            if ($request->select_groomer == 'select_fav_groomer') {
                ScheduleProcessor::setFavGroomer('F');
                ScheduleProcessor::setFavGroomer_id($request->groomer_id );
            } else {
                ScheduleProcessor::setFavGroomer('N');
                ScheduleProcessor::setFavGroomer_id(null );
            }
            ###

            return redirect('/user/schedule/select-address');

        } catch (\Exception $ex) {
            return back()->withInput()->withErrors([
                'exception' => $ex->getMessage() . ' [' . $ex->getCode() . '['
            ]);
        }
    }

}