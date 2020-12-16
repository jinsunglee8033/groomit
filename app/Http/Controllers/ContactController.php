<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/3/16
 * Time: 5:29 PM
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Validator;
use App\Lib\Helper;
use App\Model\User;
use App\Model\Contact;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Log;
use DB;

class ContactController extends Controller
{
    public function add(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                'subject' => 'required'
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


            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Your session has expired. Please login again. [CNT_ADD]'
                ]);
            }

            $contact = new Contact;
            $contact->user_id = $user->user_id;
            $contact->first_name = $request->first_name;
            $contact->last_name = $request->last_name;
            $contact->email = $request->email;
            $contact->phone = $request->phone;
            $contact->subject = $request->subject;
            $contact->message = $request->message;
            $contact->type = 'App';
            $contact->cdate = Carbon::now();

            $contact->save();

            ### send text ###

            $msg = '['.$contact->type.']There is new contact us request for ID: ' . $contact->contact_id . ' (# of Appointments: ' . $contact->getAppointmentNumbers() . ') [User: ' . $contact->email . ' / ' . $contact->user_id . ']';

            if (getenv('APP_ENV') == 'production') {
                Helper::send_sms_to_admin($msg, true);
            }

            ### .send text ###


            return response()->json([
                'code' => '0',
                'msg' => '',
                'contact_id' => $contact->contact_id
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code'    => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function web_add(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                //'subject' => 'required',
                'verification_code' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                //return Redirect::to(URL::previous() . "#contactus")->withErrors([$msg])->withInput();
                return Redirect::to(URL::previous())->withErrors([$msg])->withInput();
            }

            $scode = Session::get('verification-code');
            if ($request->verification_code != $scode) {
                return Redirect::to(URL::previous())->withErrors(['Invalid Verification Code Provided !!'])->withInput();
            }

            $contact = new Contact;
            $contact->first_name = $request->first_name;
            $contact->last_name = $request->last_name;
            $contact->email = $request->email;
            $contact->subject = '' ;//$request->subject;
            $contact->message = $request->message;
            $contact->type = 'Web';
            $contact->cdate = Carbon::now();

            $contact->save();

            ### send text ###

            $msg = '['.$contact->type.']There is new contact us request for ID: ' . $contact->contact_id . ' [' . $contact->email . ']';

            if (getenv('APP_ENV') == 'production') {
                Helper::send_sms_to_admin($msg);
            }

            ### .send text ###

            return Redirect::to(URL::previous() )->withErrors(['Your message was sent. Thank you.']);


        } catch (\Exception $ex) {

            return Redirect::to(URL::previous() )->withErrors([$ex->getMessage() . ' [' . $ex->getCode() . ']'])->withInput();
        }
    }

}