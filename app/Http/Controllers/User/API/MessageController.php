<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 12/3/18
 * Time: 4:33 PM
 */

namespace App\Http\Controllers\User\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\Groomer;
use App\Model\Message;
use App\Model\User;

use Carbon\Carbon;
use DB;
use Log;
use Validator;
use App\Lib\Helper;

class MessageController extends Controller
{

    public function show(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required'
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
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $messages = Message::where(function($query) use($user) {
                $query->whereRaw("(receiver_type = 'A' AND receiver_id = " . $user->user_id . ")")
                  ->orWhereRaw("(sender_type = 'U' AND sender_id = " . $user->user_id . ")");
            })->whereNull('parent_id')
              ->orderBy('cdate', 'desc')
              ->get();

            return response()->json([
              'code' => '0',
              'messages' => $messages
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function detail(Request $request) {
        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'id'      => 'required'
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
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

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
                        $n->sender = "Groomit";
                        break;
                    default:
                        $n->sender = "GroomIt";
                        break;
                }
            }

            return response()->json([
                'code' => '0',
                'messages' => $messages
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'code' => '-9',
                'msg'  => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function send(Request $request) {

        try {
            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token'   => 'required',
              'message' => 'required'
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
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'code' => '-2',
                    'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;

            $r = New Message;
            $r->send_method     = 'S';
            $r->sender_type     = 'U'; // end user
            $r->sender_id       = $user->user_id;
            $r->receiver_id     = !empty($request->receiver_id) ? $request->receiver_id : null; // default null
            $r->receiver_type   = !empty($request->receiver_type) ? $request->receiver_type : 'C'; // default admin user
            $r->message_type    = 'M';
            $r->subject = '';
            $r->message         = $request->message;
            $r->cdate           = Carbon::now();
            $r->parent_id       = !empty($request->parent_id) ? $request->parent_id : null;
            $r->save();


            ########

            # Send SMS HERE!!

            ########

            $phone = array();
            $msg_body = ($r->subject) ? $r->subject . '. ' .$r->message : $r->message;

            ## to Admin
            $msg_head = "[". $user->first_name . " " . $user->last_name . "/ ID: " . $user->user_id ."] \n";
            if (getenv('APP_ENV') == 'production') {
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
                'code' => '0',
                'parent_id'  => $r->parent_id,
                'message_id' => $r->message_id
            ]);

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ')';

            return response()->json([
                'code' => '-9',
                'msg'  => $msg
            ]);
        }
    }
}
