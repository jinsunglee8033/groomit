<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/8/18
 * Time: 2:53 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\AppointmentProcessor;
use App\Lib\Helper;
use App\Lib\UserProcessor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{

    public function show() {
        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Home');

        $user = Auth::guard('user')->user();
        $user_id = $user->user_id;


        ### get upcoming ###
        $upcoming = AppointmentProcessor::get_upcoming($user_id, 1);

        ### get recent ###
        $recent = AppointmentProcessor::get_recent($user_id, 1);

        Helper::log('### upcoming ###', [
            $upcoming
        ]);

        //$user->referral_code = UserProcessor::get_referral_code($user->user_id);
        $referral_arr = UserProcessor::get_referral_code($user->user_id);
        $user->referral_code = $referral_arr['referral_code'];
        $user->referral_amount = $referral_arr['referral_amount'];

        return view('user.home', [
            'upcoming' => $upcoming,
            'recent' => $recent,
            'user' => $user
        ]);
    }

}