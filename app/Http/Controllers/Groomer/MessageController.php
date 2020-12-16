<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/15/18
 * Time: 1:24 PM
 */

namespace App\Http\Controllers\Groomer;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\AppointmentList;
use App\Model\Groomer;
use App\Model\Message;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{

    public function getList(Request $request) {
        try {

            $v = Validator::make($request->all(), [
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $data = Message::where(function($query) use($groomer) {
                $query->whereRaw("(receiver_type = 'B' AND receiver_id = " . $groomer->groomer_id . ")")
                    ->orWhereRaw("(sender_type = 'G' AND sender_id = " . $groomer->groomer_id . ")");
            })->whereNull('parent_id')
                ->orderBy('cdate', 'desc')
                ->get();

            foreach ($data as $n) {
                $n->cdate = Carbon::parse($n->cdate)->format('m/d/Y h:i A');

            }

            return response()->json([
                'msg' => '',
                'data' => $data
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
    }

    public function getDetail(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'message_id' => 'required'
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $data = Message::where(function($query) use ($request) {
                $query->where('parent_id', $request->message_id)
                    ->orWhere('message_id', $request->message_id);
            })->orderBy('cdate', 'desc')
                ->get();

            foreach ($data as $n) {

                switch($n->sender_type) {
                    case 'U':
                        $sender = DB::table('user')
                            ->selectRaw('CONCAT(first_name,\' \', last_name) AS name')
                            ->where('user_id', '=', $n->sender_id)
                            ->first();
                        if (!empty($sender)) {
                            $n->sender = $sender->name;
                        } else {
                            $n->sender = "Groomit";
                        }
                        break;
                    case 'G':
                        $sender = DB::table('groomer')
                            ->selectRaw('CONCAT(first_name,\' \', last_name) AS name')
                            ->where('groomer_id', '=', $n->sender_id)
                            ->first();
                        if (!empty($sender)) {
                            $n->sender = $sender->name;
                        } else {
                            $n->sender = "Groomit";
                        }
                        break;
                    ### hide admin user name : requested by Lars ###
                    case 'A':
                        /*$sender = DB::table('admin')
                            ->select('name')
                            ->where('admin_id', '=', $n->sender_id)
                            ->first();
                        if (!empty($sender)) {
                            $n->sender = $sender->name;
                        } else {
                            $n->sender = "Groomit";
                        }*/

                        $n->sender = "Groomit";
                        break;
                    default:
                        $n->sender = "GroomIt";
                        break;
                }



                $n->cdate = Carbon::parse($n->cdate)->format('m/d/Y h:i A');

            }

            return response()->json([
                'msg' => '',
                'info' => $data
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
    }

    public function send(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'message' => 'required',
                'parent_id' => ''
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

            $email = \Crypt::decrypt($request->token);
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            # decode message
            $m = json_decode($request->message);

            $r = New Message;
            $r->send_method = 'S';
            $r->sender_type = 'G'; // end user
            $r->sender_id = $groomer->groomer_id;
            $r->receiver_id = ($m->receiver_id) ? $m->receiver_id : null; // default null
            $r->receiver_type = ($m->receiver_type) ? $m->receiver_type : 'C'; // default admin user
            $r->message_type = 'M';
            $r->subject = '';
            $r->message = $m->message;
            $r->cdate = Carbon::now();
            $r->parent_id = ($m->parent_id) ? $m->parent_id : null;
            $r->save();


            ### Send SMS HERE!! ###
            $msg_body = ($r->subject) ? $r->subject . '. ' .$r->message : $r->message;

            ### to Admin ###
            $msg_head = "[". $groomer->first_name . " " . $groomer->last_name . "/ ID: " . $groomer->groomer_id ."] \n";
            if (getenv('APP_ENV') == 'production') {
                Helper::send_sms_to_admin($msg_head . $msg_body);
            }

            return response()->json([
                'msg' => '',
                'parent_id' => $r->parent_id,
                'message_id' => $r->message_id
            ]);

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ')';

            return response()->json([
                'msg' => $msg
            ]);
        }
    }

    public function sendToUser(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required',
                'message' => 'required'
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

            $email = \Crypt::decrypt($request->token);
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            ### find appointment ###
            $app = AppointmentList::where('appointment_id', $request->appointment_id)
                ->where('groomer_id', $groomer->groomer_id)
                ->whereIn('status', ['O'])
                ->where('accepted_date', '>=', Carbon::now()->subHours(2))
                ->first();

            if (empty($app)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            $user = User::find($app->user_id);
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Invalid user ID set for the appointment'
                ]);
            }

            # decode message
            $r = New Message;
            $r->send_method = 'S';
            $r->sender_type = 'G';      // groomer
            $r->sender_id = $groomer->groomer_id;
            $r->receiver_id = $user->user_id;
            $r->receiver_type = 'A';    // end-user
            $r->message_type = 'M';
            $r->subject = '';
            $r->message = $request->message;
            $r->cdate = Carbon::now();
            $r->save();

            ########
            # Send SMS HERE!!
            ########

            $msg_body = $request->message;

            ## to User
            $msg_head = 'Your groomer ' . $groomer->first_name . ' has sent you a message: ';
            if (!empty($user->phone)) {
                $ret = Helper::send_sms($user->phone, $msg_head . $msg_body, 'twilio_gc');
                if (!empty($ret)) {
                    throw new \Exception($ret);
                }
            } else {
                return response()->json([
                    'msg' => 'No user phone # found'
                ]);
            }

            $ret = Helper::send_sms_to_cs($msg_head . $msg_body);
            if (!empty($ret)) {
                $err_msg = '[Groomit][' . getenv('APP_ENV') . '] Error: Send SMS Reply From End User to Groomer & C/S. Sender ID: ' . $user->user_id . ', Message: ' . $msg_head . $msg_body;
                Helper::send_mail('tech@groomit.me', $err_msg, $ret);
            }

            return response()->json([
                'msg' => '',
                'message_id' => $r->message_id
            ]);

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ')';

            return response()->json([
                'msg' => $msg
            ]);
        }
    }

}