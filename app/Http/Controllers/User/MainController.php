<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/7/18
 * Time: 11:26 AM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class MainController extends Controller
{

    public function show() {
        Session::put('user.menu.show', 'N');
        Session::put('user.menu.top-title', null);
        Session::put('schedule.url', 'user/schedule/select-dog');

        if (Auth::guard('user')->check()) {
            return redirect('/user/schedule/select-dog');
        }

        return view('user.main');
    }

}