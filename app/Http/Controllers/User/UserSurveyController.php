<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/8/18
 * Time: 2:36 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\CreditProcessor;
use App\Lib\Helper;
use App\Lib\ImageProcessor;
use App\Lib\PetProcessor;
use App\Lib\ScheduleProcessor;
use App\Lib\UserProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\Breed;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\PromoCode;
use App\Model\RequestReferal;
use App\Model\User;
use App\Model\UserLoginHistory;
use App\Model\UserPhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Log;

class UserSurveyController extends Controller
{
    public function show(Request $request)
    {
        try {
            Session::put('schedule.url', $request->path());

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                return view('/user/survey')->withErrors([
                    'exception' => 'Please login first!'
                ]);
            }

            $ret =
                DB::select("select appointment_id, date_format(accepted_date,'%Y-%m-%d at %h:%i %p') service_date
            from appointment_list a
            where appointment_id not in (select appointment_id from survey)
            and user_id = :user_id
            and status = 'P'
            and cdate >= curdate() - interval 30 day
            and  not exists ( select appointment_id from survey where appointment_id = a.appointment_id )
            order by cdate desc
            limit 0,1
            ", [
                    'user_id' => $user->user_id
                ]);
            if (count($ret) > 0) {
                $appointment_id = $ret[0]->appointment_id;
                $service_date = $ret[0]->service_date;
            } else {
                return view('user/survey')->withErrors([
                    'exception' => "We can't find your appointment within a month."
                ]);
            }

            return view('user/survey', [
                'user' => $user,
                'appointment_id' => $appointment_id,
                'service_date' => $service_date
            ]);


        } catch (\Exception $ex) {
            echo $ex->getMessage() . ' [' . $ex->getCode() . ']';
//            return redirect('/user/survey')->withErrors([
//                'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
//            ]);
        }
    }


    public function submit(Request $request)
    {
        try {

            $v = Validator::make($request->all(), [
                'rating_cleanliness' => 'required|in:1,2,3,4,5',
                'rating_scheduling' => 'required|in:1,2,3,4,5',
                'rating_quality' => 'required|in:1,2,3,4,5',
                'rating_value' => 'required|in:1,2,3,4,5',
                'rating_cs' => 'required|in:1,2,3,4,5'
            ]);

            $msg = '';
            if ($v->fails()) {
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }
                Log::info('### In Fail ###', [
                    'msg' =>$msg
                ]);
                return response()->json([
                    'msg' => $msg
                ]);

            }
            Log::info('### Out Fail ###', [
                'msg' =>$msg
            ]);

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Please log in first'

                ]);
            }

            $ret =
                DB::select("select appointment_id
            from appointment_list 
            where appointment_id = :appointment_id 
            and user_id = :user_id
            and status = 'P'
            and cdate >= curdate() - interval 30 day
            ", [
                    'appointment_id' =>$request->appointment_id,
                    'user_id' => $user->user_id
                ]);

            if(!empty($ret)){
                DB::insert("
            insert into survey( appointment_id, ov, sc, gq, cl, va, cs, su, cdate)
            values( :appointment_id, :ov, :sc, :gq, :cl , :va, :cs, :su, :cdate)
                ", [
                    'appointment_id' => $request->appointment_id,
                    'ov' => (($request->rating_scheduling)+($request->rating_quality)+($request->rating_cleanliness)+($request->rating_value)+($request->rating_cs))/5,
                    'sc' => $request->rating_scheduling,
                    'gq' => $request->rating_quality,
                    'cl' => $request->rating_cleanliness,
                    'va' => $request->rating_value,
                    'cs' => $request->rating_cs,
                    'su' => $request->suggestions,
                    'cdate' => Carbon::now()
                ]);
                return response()->json([
                    'msg' => ''

                ]);
            }else{
                return response()->json([
                    'msg' => "We can't find your appointment within a month."

                ]);
            }





        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }

    }

    public function get_survey_appt_id(Request $request) {
        try {
            $v = \Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required'
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [USR_UPD]'
                ]);
            }
            $ret =
                DB::select("select a.appointment_id, a.groomer_id, g.first_name, g.last_name,  date_format(a.accepted_date, '%m/%d/%Y %H:%i')  acpt_date, a.total
            from appointment_list a left join groomer g on a.groomer_id = g.groomer_id 
            where a.user_id = :user_id
            and a.status = 'P'
            and a.cdate >= curdate() - interval 30 day
            and not exists ( select appointment_id from survey where appointment_id = a.appointment_id )
            order by a.cdate desc
            limit 0,1
            ", [
                    'user_id' => $user->user_id
                ]);
            if (count($ret) > 0) {
                $appointment_id = $ret[0]->appointment_id;
            } else {

                return response()->json([
                    'msg' => 'We can not find a valid appointment for you',
                    'appointment_id' => ''
                ]);

            }

            return response()->json([
                'msg' => '',
                'appointment_id' => $appointment_id,
                'groomer_id' => $ret[0]->groomer_id,
                'first_name' => $ret[0]->first_name,
                'last_name' => $ret[0]->last_name,
                'accepted_date' => $ret[0]->acpt_date,
                'total' => $ret[0]->total,
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}




//

//]);


//
//

//
//if($alias_id != '') {
////
//$ret = DB::insert("
//insert into groomer_exclusive_area( alias_id, groomer_id,weekday, cdate)
//values( :alias_id, :groomer_id, :weekday, :cdate)
//", [
//'alias_id' => $alias_id,
//'groomer_id' => $groomer_id,
//'weekday' => $weekday,
//'cdate' => Carbon::now()
//]);
//}
//return redirect('/admin/groomer/' . $groomer_id);
////}