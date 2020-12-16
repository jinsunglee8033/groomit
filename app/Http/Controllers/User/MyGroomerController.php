<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/14/18
 * Time: 3:54 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\PaymentProcessor;
use App\Model\AppointmentList;
use App\Model\UserBilling;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class MyGroomerController extends Controller
{

    public function show() {
        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'MY GROOMERS');

        $user_id = Auth::guard('user')->user()->user_id;

        $my_groomers = DB::select("
            select distinct a.groomer_id, b.first_name, b.last_name, b.bio, b.profile_photo, c.groomer_id fav_groomer_id
            from appointment_list a inner join groomer b on a.groomer_id = b.groomer_id and b.status='A'
                                    left join user_favorite_groomer c on a. groomer_id = c.groomer_id and c.user_id = :user_id 
            where a.user_id = :user_id 
            and a.status = 'P'
            ",
            [
                'user_id' => $user_id
            ]);

        foreach ($my_groomers as $g){
            $g->total_appts = AppointmentList::where('groomer_id', $g->groomer_id)->where('status', 'P')->count();
        }

        $fav_groomers = DB::select(" 
            SELECT a.user_id, a.groomer_id, b.first_name, b.last_name, b.bio, b.profile_photo
            from user_favorite_groomer a, groomer b
            where a.groomer_id = b.groomer_id
            and b.status ='A'
            and a.user_id = :user_id ",
            [
                'user_id' => $user_id
            ]);

        foreach ($fav_groomers as $g){
            $g->total_appts = AppointmentList::where('groomer_id', $g->groomer_id)->where('status', 'P')->count();
        }

        return view('user.my-groomer', [
            'fav_groomers' => $fav_groomers,
            'my_groomers' => $my_groomers
        ]);
    }

    public function makeFavorite(Request $request) {

        try{

            $groomer_id = $request->groomer_id;

            $user = \Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Session expired. Please login again.');
            }
            $user_id    = $user->user_id;

            $ret = DB::insert("
                insert into user_favorite_groomer (user_id, groomer_id)
                values (:user_id, :groomer_id)
            ", [
                'user_id' => $user_id,
                'groomer_id' => $groomer_id
            ]);
            if ($ret < 1) {
                return 'Failed to add user favorite groomer';
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

    public function removeFavorite(Request $request) {

        try{

            $groomer_id = $request->groomer_id;

            $user = \Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Session expired. Please login again.');
            }
            $user_id    = $user->user_id;

            DB::statement("
                delete from user_favorite_groomer
                where user_id = :user_id
                and groomer_id = :groomer_id
            ", [
                'user_id' => $user_id,
                'groomer_id' => $groomer_id
            ]);

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }
}