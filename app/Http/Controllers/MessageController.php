<?php

namespace App\Http\Controllers;

use App\Model\Groomer;
use Illuminate\Http\Request;
use App\Model\Message;
use App\Model\User;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use App\Lib\Helper;

class MessageController extends Controller
{

    public function messages(Request $request) {
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

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [MSG_LST]'
                ]);
            }

            $messages = Message::where(function($query) use($user) {
                    $query->whereRaw("(receiver_type = 'A' AND receiver_id = " . $user->user_id . ")")
                        ->orWhereRaw("(sender_type = 'U' AND sender_id = " . $user->user_id . ")");
                })->whereNull('parent_id')
                ->orderBy('cdate', 'desc')
                ->get();


            foreach ($messages as $n) {
                $n->cdate = Carbon::parse($n->cdate)->format('m/d/Y h:i A');

            }
            return response()->json([
                'msg' => '',
                'messages' => $messages
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function detail(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'id' => 'required'
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

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [MSG_DTL]'
                ]);
            }

            $messages = Message::where(function($query) use ($request) {
                    $query->where('parent_id', $request->id)
                      ->orWhere('message_id', $request->id);
                })->orderBy('cdate', 'desc')
                ->get();

            foreach ($messages as $n) {

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
                'messages' => $messages
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

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [MSG_SND]'
                ]);
            }

            # decode message
            $m = json_decode($request->message);

            $r = New Message;
            $r->send_method = 'S';
            $r->sender_type = 'U'; // end user
            $r->sender_id = $user->user_id;
            $r->receiver_id = ($m->receiver_id) ? $m->receiver_id : null; // default null
            $r->receiver_type = ($m->receiver_type) ? $m->receiver_type : 'C'; // default admin user
            $r->message_type = 'M';
            $r->subject = '';
            $r->message = $m->message;
            $r->cdate = Carbon::now();
            $r->parent_id = ($m->parent_id) ? $m->parent_id : null;
            $r->save();


            ########

            # Send SMS HERE!!

            ########

            $phone = array();
            $msg_body = ($r->subject) ? $r->subject . '. ' .$r->message : $r->message;

            ## to User
//            $msg_head = 'Your Message: ';
//            if (!empty($user->phone)) {
//                $ret = Helper::send_sms($user->phone, $msg_head . $msg_body);
//                if (!empty($ret)) {
//                    throw new \Exception($ret);
//                }
//            }

            ## to Admin
            $msg_head = "[". $user->first_name . " " . $user->last_name . "/ ID: " . $user->user_id ."] \n";
            if (getenv('APP_ENV') == 'production') {
                // admin receivers : Lars, CS team , Anna? , '9177190390':CS removed
                $phone = ['5515742790', '9145704272',  '6507930213'];
                Helper::send_sms_to_admin($msg_head . $msg_body);
            }

            ## to Groomer : when receiver type is groomer
            if ($r->receiver_type == 'B' || $r->receiver_type == 'G') {

                $msg_head = "[". $user->first_name . " " . $user->last_name . "] \n";
                $groomer = Groomer::find($request->receiver_id);

                if (!empty($groomer)) {
                    $phone = $groomer->mobile_phone;
                    $ret = Helper::send_sms($phone, $msg_head . $msg_body);
                    if (!empty($ret)) {
                        throw new \Exception($ret);
                    }
                }
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
}
