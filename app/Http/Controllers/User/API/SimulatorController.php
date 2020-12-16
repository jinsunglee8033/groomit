<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 11/2/18
 * Time: 3:06 PM
 */

namespace App\Http\Controllers\User\API;


use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;



class SimulatorController extends Controller
{
    public function index(Request $request) {
        return view('user.api.simulator')->with([
            'api' => $request->api,
        ]);
    }
}