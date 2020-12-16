<?php

namespace App\Http\Controllers;

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
    public function get_by_id(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [GRM_GID]'
                ]);
            }


            $groomer = Groomer::where('groomer_id', $request->groomer_id)->first();

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
                    $groomer->total_appts = round($ret[0]->total_appts);
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
                    $groomer->favorite = true;
                } else {
                    $groomer->favorite = false;
                }
            }

            return response()->json([
                'msg' => '',
                'groomer' => $groomer
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function make_favorite(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'groomer_id' => 'required',
                'favorite' => 'required'
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
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [GRM_FAV]'
                ]);
            }

            $favorite_groomer = UserFavoriteGroomer::where('user_id', $user->user_id)
                ->where('groomer_id', $request->groomer_id)
                ->first();

            if ($request->favorite == 'false') {
                # unset favorite groomer
                if (!empty($favorite_groomer)) {
                    UserFavoriteGroomer::where('user_id', $user->user_id)
                        ->where('groomer_id', $request->groomer_id)
                        ->delete();
                }

            } else {

                # set favorite groomer
                if (empty($favorite_groomer)) {
                    $favorite_groomer = new UserFavoriteGroomer;
                    $favorite_groomer->groomer_id = $request->groomer_id;
                    $favorite_groomer->user_id = $user->user_id;
                    $favorite_groomer->save();
                }
            }


            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required|digits:10',
            'email' => 'required|email',
            'name' => 'required',
            'zip' => 'required|digits:5'
        ]);

        $results = \DB::select('select * from groomer where lower(email) = :email', ['email' => strtolower($request->email)]);
        if (count($results) > 0) {
            $errors = ['Error', 'Your email address has been used already!'];
            return redirect('/')->withErrors($errors)->withInput();
        }

        $ret = \DB::insert('
            insert into groomer (name, email, phone, zip, applied_at)
            values (:name, :email, :phone, :zip, :applied_at)
        ', [
            'name' => $request->name,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'zip' => $request->zip,
            'applied_at' => Carbon::now()
        ]);

        if ($ret < 1) {
            $errors = ['Error', 'Failed to insert data!'];
            return redirect('/')->withErrors($errors)->withInput();
        }

        return view('welcome');
    }
}
