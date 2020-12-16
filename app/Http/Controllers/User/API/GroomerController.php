<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 12/5/18
 * Time: 6:27 PM
 */


namespace App\Http\Controllers\User\API;

use App\Http\Controllers\Controller;
use App\Model\AppointmentList;
use App\Model\UserBlockedGroomer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

use App\Lib\Helper;

use App\Model\User;
use App\Model\Groomer;
use App\Model\UserFavoriteGroomer;

use Carbon\Carbon;
use Log;
use DB;

class GroomerController extends Controller
{
    public function detail(Request $request) {
        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key'     => 'required',
              'token'       => 'required',
              'groomer_id'  => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $groomer = Groomer::where('groomer_id', $request->groomer_id)->first([
                'groomer_id',
                DB::raw("concat(first_name, ' ', last_name) as name"),
                'profile_photo',
                'bio'
            ]);

            if (!empty($groomer)) {
                $ret = DB::select("
                        select avg(rating) avg_rating, count(*) total_appts
                        from appointment_list
                        where groomer_id = :groomer_id
                          and status = 'P'
                    ", [
                  'groomer_id' => $groomer->groomer_id
                ]);

                $groomer->overall_rating = 0;
                $groomer->total_appts = 0;
                if (count($ret) > 0) {
                    $groomer->overall_rating = round($ret[0]->avg_rating);
                    $groomer->total_appts = $ret[0]->total_appts;
                }


                $fav = DB::select("
                        select groomer_id
                        from user_favorite_groomer
                        where user_id = :user_id
                        and groomer_id = :groomer_id
                    ", [
                  'user_id' => $user->user_id,
                  'groomer_id' => $groomer->groomer_id
                ]);

                if (count($fav) > 0) {
                    $groomer->favorite = 'Y';
                } else {
                    $groomer->favorite = 'N';
                }
            }

//            $ssdate = Carbon::today()->addDays(-365);
//            $groomer->number_of_appts = AppointmentList::where('status', 'P')->where('groomer_id', $groomer->groomer_id)
//                ->where('cdate', '>', $ssdate)
//                ->count();
            //WE can remove it after CA version of 2.0.66
            $groomer->number_of_appts = AppointmentList::where('status', 'P')->where('groomer_id', $groomer->groomer_id)
                ->count();

            if (!empty($request->without_photo) && $request->without_photo == 'Y') {
                $groomer->profile_photo = '';
            }

            return response()->json([
                'code' => '0',
                'groomer' => $groomer
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_list(Request $request) {
        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key'     => 'required',
              'token'       => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $groomers = Groomer::whereRaw('groomer_id in (select groomer_id from appointment_list where status=\'P\' and user_id=' . $user->user_id . ')')
                ->get([
                'groomer_id',
                'first_name',
                'last_name',
                'profile_photo'
            ]);

            foreach ($groomers as $g) {
                $fav = UserFavoriteGroomer::where('user_id', $user->user_id)->where('groomer_id', $g->groomer_id)->first();
                if (!empty($fav)) {
                    $g->is_favorite = 'Y';
                } else {
                    $g->is_favorite = 'N';
                }
            }

            foreach ($groomers as $groomer) {
                $block = UserBlockedGroomer::where('user_id', $user->user_id)->where('groomer_id', $groomer->groomer_id)->first();
                if (!empty($block)) {
                    $groomer->is_blocked = 'Y';
                } else {
                    $groomer->is_blocked = 'N';
                }
            }

            return response()->json([
              'code' => '0',
              'user_id'  => $user->user_id,
              'groomers' => $groomers
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'code' => '-9',
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function make_favorite(Request $request) {
        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key'     => 'required',
              'token'       => 'required',
              'groomer_id'  => 'required',
              'is_favorite' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $favorite_groomer = UserFavoriteGroomer::where('user_id', $user->user_id)
              ->where('groomer_id', $request->groomer_id)
              ->first();

            if ($request->is_favorite == 'N') {
                # unset favorite groomer
                if (!empty($favorite_groomer)) {
                    UserFavoriteGroomer::where('user_id', $user->user_id)
                      ->where('groomer_id', $request->groomer_id)
                      ->delete();
                }

            } else {
                if ($request->is_favorite == 'Y') {

                    # check whether groomer belong to blocked list
                    $ret = UserBlockedGroomer::where('user_id', $user->user_id)
                            ->where('groomer_id', $request->groomer_id)
                            ->first();

                    if(!empty($ret)){
                        return response()->json([
                            'code' => '-3',
                            'msg' => 'This Groomer is belong to Blocked Groomer list. Please select again.'
                        ]);
                    }

                    # set favorite groomer
                    if (empty($favorite_groomer)) {
                        $favorite_groomer = new UserFavoriteGroomer;
                        $favorite_groomer->groomer_id = $request->groomer_id;
                        $favorite_groomer->user_id = $user->user_id;
                        $favorite_groomer->save();

                        UserBlockedGroomer::where('user_id', $user->user_id)
                            ->where('groomer_id', $request->groomer_id)
                            ->delete();
                    }
                }
            }

            return response()->json([
                'code' => '0',
                'msg'  => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function make_blocked(Request $request) {
        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
                'api_key'     => 'required',
                'token'       => 'required',
                'groomer_id'  => 'required',
                'is_blocked' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'code' => '-1',
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $blocked_groomer = UserBlockedGroomer::where('user_id', $user->user_id)
                ->where('groomer_id', $request->groomer_id)
                ->first();

            if ($request->is_blocked == 'N') {
                # unset favorite groomer
                if (!empty($blocked_groomer)) {
                    UserBlockedGroomer::where('user_id', $user->user_id)
                        ->where('groomer_id', $request->groomer_id)
                        ->delete();
                }

            } else {
                if ($request->is_blocked == 'Y') {

                    # check whether groomer belong to favorite list
                    $ret = UserFavoriteGroomer::where('user_id', $user->user_id)
                        ->where('groomer_id', $request->groomer_id)
                        ->first();

                    if(!empty($ret)){
                        return response()->json([
                            'code' => '-3',
                            'msg' => 'This Groomer is belong to Favorite Groomer list. Please select again.'
                        ]);
                    }

                    # set favorite groomer
                    if (empty($blocked_groomer)) {
                        $blocked_groomer = new UserBlockedGroomer();
                        $blocked_groomer->groomer_id = $request->groomer_id;
                        $blocked_groomer->user_id = $user->user_id;
                        $blocked_groomer->save();

                        // remove from favorite table
                        UserFavoriteGroomer::where('user_id', $user->user_id)
                            ->where('groomer_id', $request->groomer_id)
                            ->delete();
                    }
                }
            }

            return response()->json([
                'code' => '0',
                'msg'  => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_favorite_list(Request $request) {
        try {
            ############### START VALIDATION ###########
            ############################################

            $v = Validator::make($request->all(), [
                'api_key'     => 'required',
                'token'       => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'code' => '-1',
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);

            $user = User::where('email', strtolower($email))->first();

            if (empty($user)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $fav_groomers = DB::select(" SELECT a.user_id, a.groomer_id, b.first_name, b.last_name
                                        from user_favorite_groomer a, groomer b
                                        where a.groomer_id = b.groomer_id
                                        and b.status ='A'
                                        and a.user_id = :user_id ",
                           [
                             'user_id' => $user->user_id
                           ]);

//            $fav_groomers = UserFavoriteGroomer::join('groomer', 'groomer.groomer_id' , '=', 'user_favorite_groomer.groomer_id')
//                ->where('user_id', $user->user_id)
//                ->whereRaw("groomer.status ='A' ")
//                ->get();

//            foreach ($fav_groomers as $fg) {
//                $groomer = Groomer::where('groomer_id', $fg->groomer_id)->first();
//                $fg->first_name = $groomer->first_name;
//                $fg->last_name  = $groomer->last_name;
//                //$fg->profile_photo = $groomer->profile_photo; //Do not send photo because of N/W issue.
//                //As of now, CA uses #of fav groomers only, not details.
//            }

            return response()->json([
                'code' => '0',
                'user_id'  => $user->user_id,
                'favorite_groomers' => $fav_groomers
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_blocked_list(Request $request) {
        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
                'api_key'     => 'required',
                'token'       => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'code' => '-1',
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $blocked_groomers = UserBlockedGroomer::where('user_id', $user->user_id)->get();

            foreach ($blocked_groomers as $bg) {
                $groomer = Groomer::where('groomer_id', $bg->groomer_id)->first();
                $bg->first_name = $groomer->first_name;
                $bg->last_name  = $groomer->last_name;
                $bg->profile_photo = $groomer->profile_photo;
            }

            return response()->json([
                'code' => '0',
                'user_id'  => $user->user_id,
                'groomers' => $blocked_groomers
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    //Without Flexible Time
    public function groomer_calendar(Request $request) {
        $v = Validator::make($request->all(), [
            'api_key'     => 'required',
            'token'       => 'required',
            'date'    => 'required|date',
            'groomer_id'    => 'required|numeric'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
                'code' => '-1',
                'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $calendar = Helper::get_groomer_calendar( $request->groomer_id, $request->date, $user->user_id ); //No ZIP is delivered in case of API.

        return response()->json([
            'code' => '0',
            'calendar' => $calendar
        ]);

    }

    //Includes Flexible Time
    public function groomer_calendar2(Request $request) {
        $v = Validator::make($request->all(), [
            'api_key'     => 'required',
            'token'       => 'required',
            'date'    => 'required|date',
            'groomer_id'    => 'required|numeric'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
                'code' => '-1',
                'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $calendar = Helper::get_groomer_calendar2( $request->groomer_id, $request->date, $user->user_id ); //No ZIP is delivered in case of API.

        return response()->json([
            'code' => '0',
            'calendar' => $calendar
        ]);

    }

    public function groomer_calendar_availability(Request $request) {
        $v = Validator::make($request->all(), [
            'api_key'     => 'required',
            'token'       => 'required',
            'groomer_id'    => 'required|numeric',
            'include_eco' => 'in:Y,N'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
                'code' => '-1',
                'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $data = Helper::get_groomer_calendar_availability( $request->groomer_id, $user->user_id, '' ,$request->include_eco);
        $min_date = '';
        $max_date = '';
        if(is_array($data)){
            $cnt = count($data);
            $min_date = $data[0]['full_date'];
            $max_date = $data[$cnt-1]['full_date'];
        }
        return response()->json([
            'code' => '0',
            'min_date' => $min_date,
            'max_date' => $max_date,
            'data' => $data
        ]);

    }
}
