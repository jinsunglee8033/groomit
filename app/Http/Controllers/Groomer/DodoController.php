<?php
/**
 * Created by PhpStorm.
 * User: Jin
 * Date: 9/10/19
 * Time: 2:08 PM
 */

namespace App\Http\Controllers\Groomer;

use App\Lib\Helper;
use App\Model\Application;
use App\Model\ApplicationAvailability;
use App\Model\ApplicationPetPhoto;
use App\Model\ApplicationTool;
use App\Model\GroomerDodo;
use App\Model\PreApply;
use App\Model\Tool;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Log;
use Validator;
use Illuminate\Support\Facades\Input;

class DodoController extends Controller
{

    public function post(Request $request) {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'phone' => 'required|regex:/^\d{10}$/'
            ]);

            if ($v->fails()) {
                DB::rollback();
                return back()->withErrors($v)->withInput();
            }

            $gd = new GroomerDodo();
            $gd->phone          = $request->phone;
            $gd->full_name      = $request->full_name;
            $gd->email          = strtolower($request->email);
            $gd->zip            = $request->zip;
            $gd->cdate          = Carbon::now();
            $gd->save();

            DB::commit();

            ### SEND SMS
            $sms_message = "Welcome to Groomit! Download the app ( http://bit.ly/2uP3BUS ) and use the code ‘DODO’ to get $25 off your first booking!";
            $ret = Helper::send_sms($request->phone, $sms_message);

            if (!empty($ret)) {
                Helper::send_mail('it@jjonbp.com', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $sms_message);
            }

            return back()->with([
                'success' => 'Y'
            ]);

        } catch (\Exception $ex) {
            DB::rollback();
            return back()->withErrors([
                'exception' => $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString()
            ])->withInput();
        }
    }

}