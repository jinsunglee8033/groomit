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
use App\Model\PreApply;
use App\Model\Tool;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Log;
use Validator;
use Illuminate\Support\Facades\Input;

class PreApplyController extends Controller
{

    public function post(Request $request) {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'full_name' => 'required',
                'email' => 'required|email',
                'phone' => 'required|regex:/^\d{10}$/',
                'zip'   => 'required|regex:/^\d{5}$/'
            ]);

            if ($v->fails()) {
                DB::rollback();
                return back()->withErrors($v)->withInput();
            }

            $pa = new PreApply();
            $pa->full_name      = $request->full_name;
            $pa->email          = strtolower($request->email);
            $pa->phone          = $request->phone;
            $pa->zip            = $request->zip;
            $pa->referred_by    = $request->referred_by;
            $pa->cdate = Carbon::now();
            $pa->save();

            DB::commit();


            ### SEND EMAIL
            $data = [];
            $data['name']       = $pa->full_name;
            $data['message']    = 'Thank you for your interest to work with Groomit. We will be in touch with you very soon.';

            $data['email']      = $pa->email;
            $data['subject']    = 'Thank you for your interest to work with Groomit. We will be in touch with you very soon.';

            Helper::log('##### EMAIL DATA #####', [
              'data' => $data
            ]);

            $ret = Helper::send_html_mail('groomer/new-groomer', $data);

            if (!empty($ret)) {
                $msg = 'Failed to send email';
                Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . ']' . $msg, ' - ' . $pa->email . '<br> - error : ' . $ret);
            }

            $data['email']      = 'lars@groomit.me';
            $data['subject']    = '[GROOMER PRE-APPLY] NEW ARRIVED !!';
            Helper::send_html_mail('groomer/new-groomer', $data);
         
            $data['email']      = 'help@groomit.me';
            Helper::send_html_mail('groomer/new-groomer', $data);

            $data['email']      = 'faez@groomit.me';
            Helper::send_html_mail('groomer/new-groomer', $data);

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