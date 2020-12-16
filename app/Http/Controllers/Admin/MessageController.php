<?php

namespace App\Http\Controllers\Admin;

use App\Model\Admin;
use App\Model\Constants;
use App\Model\GroomerServiceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\Message;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use Redirect;
use App\Lib\Helper;
use App\Model\User;
use App\Model\Groomer;


class MessageController extends Controller
{

    public function messages(Request $request) {
        try {

            $sdate = Carbon::today()->subMonths(1);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            if (!empty($request->no_date)) { // if request was from user profile page, set start date 01/01/2017
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', '2017-01-01' . ' 00:00:00');
            }

            $query = Message::query();

            if (!empty($sdate)) {
                $query = $query->where('cdate', '>=', $sdate);
            }

            if (!empty($edate)) {
                $query = $query->where('cdate', '<=', $edate);
            }

            if (!empty($request->send_method)) {
                $query = $query->where('send_method', $request->send_method);
            }

            if (!empty($request->message_type)) {
                $query = $query->where('message_type', $request->message_type);
            }

            if (!empty($request->sender_type)) {
                $query = $query->where('sender_type', $request->sender_type);
            }

            if (!empty($request->receiver_type)) {
                if ($request->receiver_type == 'B') { // Groomer
                    $query = $query->whereRaw("(receiver_type = 'B' or receiver_type = 'G')");
                } else if($request->receiver_type == 'C') {
                    $query = $query->whereRaw("(receiver_type = 'C' or receiver_type = 'F')");
                } else if($request->receiver_type == 'A'){
                    $query = $query->whereRaw("(receiver_type = 'A' or receiver_type = 'U')");
                }
            }

            if (!empty($request->appointment_id)) {
                $query = $query->where('appointment_id', $request->appointment_id);
            }

            if (!empty($request->name)) {

                $query = $query->whereRaw("
                    (
                        sender_type = 'U' and sender_id in ( select user_id from user where lower(concat(first_name, ' ', last_name)) like ? ) or 
                        sender_type = 'G' and sender_id in ( select groomer_id from groomer where lower(concat(first_name, ' ', last_name)) like ? ) or
                        sender_type not in ('U', 'G') and sender_id in (select admin_id from admin where lower(name) like ? ) or
                        receiver_type = 'A' and receiver_id in ( select user_id from user where lower(concat(first_name, ' ', last_name)) like ? ) or 
                        receiver_type = 'B' and receiver_id in ( select groomer_id from groomer where lower(concat(first_name, ' ', last_name)) like ? ) or
                        receiver_type not in ('A', 'B') and receiver_id in (select admin_id from admin where lower(name) like ? )
                    )
                ", [
                    '%' . strtolower($request->name). '%',
                    '%' . strtolower($request->name). '%',
                    '%' . strtolower($request->name). '%',
                    '%' . strtolower($request->name). '%',
                    '%' . strtolower($request->name). '%',
                    '%' . strtolower($request->name). '%'
                ]);

            }

            if (!empty($request->subject)) {
                $query = $query->whereRaw("LOWER(subject) like ?", ['%' . strtolower($request->subject) . '%']);
            }

            if (!empty($request->user_id)) {
                $query = $query->whereRaw("((sender_type = 'U' and sender_id = ".$request->user_id.") or ( (receiver_type = 'A' or receiver_type = 'C') and receiver_id = " .$request->user_id. " ))");
            }

            if (!empty($request->groomer_id)) {
                $query = $query->whereRaw("((sender_type = 'G' and sender_id = ".$request->groomer_id.") or (receiver_type not in ('A','C') and receiver_id = " .$request->groomer_id. " ) or (receiver_type = 'B' and receiver_id = " .$request->groomer_id. " ) )");
            }

            if ($request->excel == 'Y') {
                $messages = $query->orderBy('cdate', 'desc')->get();
                Excel::create('messages', function($excel) use($messages) {

                    $excel->sheet('reports', function($sheet) use($messages) {

                        $data = [];
                        foreach ($messages as $a) {
                            $row = [

                                'Message ID' => $a->message_id,
                                'Appointment ID' => $a->appointment_id,
                                'Send Method' => $a->send_method,
                                'Message Type' => $a->message_type,
                                'Sender' => $a->sender,
                                'Receiver' => $a->receiver,
                                'Subject' => $a->subject,
                                'Message' => $a->message,
                                'Date' => $a->cdate
                            ];

                            $data[] = $row;

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $query->whereNull('parent_id');

            $total = $query->count();

            $messages = $query->orderBy('cdate', 'desc')
                ->paginate(20);

            return view('admin.messages', [
                'msg' => '',
                'messages' => $messages,
                'sdate' => !empty($sdate) ? $sdate->format('Y-m-d') : '',
                'edate' => !empty($edate) ? $edate->format('Y-m-d') : '',
                'name' => $request->name,
                'send_method' => $request->send_method,
                'appointment_id' => $request->appointment_id,
                'message_type' => $request->message_type,
                'receiver_type' => $request->receiver_type,
                'sender_type' => $request->sender_type,
                'subject' => $request->subject,
                'user_id' => $request->user_id,
                'groomer_id' => $request->groomer_id,
                'message' => $request->message,
                'message_types' => Constants::$message_type,
                'receiver_types' => Constants::$message_receiver_type,
                'receiver_modal_types' => Constants::$message_receiver_modal_type,
                'sender_types' => Constants::$message_sender_type,
                'send_methods' => Constants::$message_send_method,
                'total' => $total
            ]);



        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function message($id) {
        try {

            $sdate = Carbon::today()->subMonths(1);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

//            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $sdate . ' 00:00:00');
//            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $edate . ' 23:59:59');

            $query = Message::query();

            if (!empty($sdate)) {
                $query = $query->where('cdate', '>=', $sdate);
            }

            if (!empty($edate)) {
                $query = $query->where('cdate', '<=', $edate);
            }

            if (!empty($id)) {
                $query = $query->whereRaw("((sender_type = 'G' and sender_id = ".$id.") or (receiver_type = 'G' and receiver_id = " .$id. " ) or (receiver_type = 'B' and receiver_id = " .$id. " ) )");
            }

            $query->whereNull('parent_id');

            $total = $query->count();

            $messages = $query->orderBy('cdate', 'desc')
                ->paginate(20);

            $groomer = Groomer::find($id);

            return view('admin.messages', [
                'msg' => '',
                'messages' => $messages,
                'sdate' => !empty($sdate) ? $sdate->format('Y-m-d') : '',
                'edate' => !empty($edate) ? $edate->format('Y-m-d') : '',
                'name' => '',
                'send_method' => '',
                'appointment_id' => '',
                'message_type' => '',
                'receiver_type' => '',
                'sender_type' => '',
                'subject' => '',
                'user_id' => '',
                'groomer_id' => $id,
                'g_id'      => $id,
                'g_name' => $groomer->first_name,
                'g_email' => $groomer->email,
                'g_phone' => $groomer->phone,
                'message' => '',
                'message_types' => Constants::$message_type,
                'receiver_types' => Constants::$message_receiver_type,
                'receiver_modal_types' => Constants::$message_receiver_modal_type,
                'sender_types' => Constants::$message_sender_type,
                'send_methods' => Constants::$message_send_method,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function message_user($id) {
        try {

            $sdate = Carbon::today()->subMonths(1);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            $query = Message::query();

            if (!empty($sdate)) {
                $query = $query->where('cdate', '>=', $sdate);
            }

            if (!empty($edate)) {
                $query = $query->where('cdate', '<=', $edate);
            }

            if (!empty($id)) {
                $query = $query->whereRaw("((sender_type = 'A' and sender_id = ".$id.") or (receiver_type = 'A' and receiver_id = " .$id. " ) or (receiver_type = 'U' and receiver_id = " .$id. " ) )");
            }

            $query->whereNull('parent_id');

            $total = $query->count();

            $messages = $query->orderBy('cdate', 'desc')
                ->paginate(20);

            $user = User::find($id);

            return view('admin.messages', [
                'msg' => '',
                'messages' => $messages,
                'sdate' => !empty($sdate) ? $sdate->format('Y-m-d') : '',
                'edate' => !empty($edate) ? $edate->format('Y-m-d') : '',
                'name' => '',
                'send_method' => '',
                'appointment_id' => '',
                'message_type' => '',
                'receiver_type' => '',
                'sender_type' => '',
                'subject' => '',
                'user_id' => '',
                'user_id' =>$id,
                'u_id'  =>$id,
                'u_name' => $user->first_name,
                'u_email' => $user->email,
                'u_phone' => $user->phone,
                'message' => '',
                'message_types' => Constants::$message_type,
                'receiver_types' => Constants::$message_receiver_type,
                'receiver_modal_types' => Constants::$message_receiver_modal_type,
                'sender_types' => Constants::$message_sender_type,
                'send_methods' => Constants::$message_send_method,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function send(Request $request) {

        try {
            $msg = '';
            $v = Validator::make($request->all(), [
                'send_method' => 'required',
                //'receiver_type' => 'required',
                //'message_type' => 'required',
                'message' => 'required'
            ]);

            if ($v->fails()) {
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }


            # check required field by receiver type
            $type = $request->receiver_type;
            if (($type == 'A' || $type == 'B' || $type == 'C') && empty($request->receiver_id)) {
                $msg = 'Please select a Receiver';
                //return Redirect::route('admin.messages')->with('alert', $msg);
                return response()->json([
                    'msg' => $msg
                ]);
            }


            if ($auth = Auth::guard('admin')->user()) {
                $admin_id = $auth->admin_id;
            } else {
                $msg = 'Admin auth required';
                return response()->json([
                    'msg' => $msg
                ]);
            }

            //DB::beginTransaction();

            if($type == 'D' || $type == 'R'){

                // Messages to all groomers - only able to CS1, CS2, MG1 - Added CS2 on 1/31/2020
                $admin = Admin::where('admin_id', $admin_id)->whereIn('group', ['CS1', 'CS2', 'MG1'])->first();
                if (empty($admin)) {
                    return response()->json([
                        'msg' => 'Available only for CS & MG1 groups !'
                    ]);
                }

                if($type == 'D'){ // All Groomers
                    $groomers = Groomer::where('status', 'A')->select('groomer_id')->get();
                }else if($type == 'R'){ // Specific Groomer Group (Level x Area)
                    $areas = $request->area;
                    $temp = " ";
                    foreach ($areas as $a){
                        $temp .= " county like '%".$a."' or";
                    }
                    $condition = substr($temp, 0, -2);

                    $groomers = Groomer::where('status', 'A')
                        ->whereIn('level', $request->level)
                        ->whereRaw(" groomer_id in ( select groomer_id from groomer_service_area where status = 'A' and ( $condition ) ) ")
                        ->select('groomer_id')->get();
                }

                if (!empty($groomers)) {
                    $cnt_sms = 0;
                    $cnt_push = 0;
                    foreach ($groomers as $g) {
                        $r = new Message();
                        $r->send_method = $request->send_method;  //S/P/B
                        $r->sender_type = 'A'; // admin user
                        $r->sender_id = $admin_id;
                        $r->receiver_type = 'B';
                        $r->receiver_id = $g->groomer_id;
                        $r->message_type = $request->message_type; //Advertise, Notification,...
                        $r->subject = $request->subject;
                        $r->message = $request->message;
                        $r->cdate = Carbon::now();
                        $r->parent_id = ($request->parent_id) ? $request->parent_id : null;
                        $r->save();

                        if (in_array($r->send_method, ['P', 'B'])) {
                            $groomer = Groomer::find($g->groomer_id);
                            if (empty($groomer)) {
                                throw new \Exception('No user found');
                            }

                            if (!empty($groomer->device_token)) {

                                $payload = [
                                    'type' => 'M',
                                    'id' => $r->message_id
                                ];

                                $error = Helper::send_notification('groomer', $r->message, $groomer->device_token, 'Groomer', $payload);
                                if (!empty($error)) {
                                    throw new \Exception($error);
                                }
                                $cnt_push++;
                            }
                        }

                        if (in_array($r->send_method, ['S', 'B'])) {
                            $phone = '';
                            $groomer = Groomer::find($g->groomer_id);
                            $phone = empty($groomer) ? '' : $groomer->mobile_phone;
                            if (!empty($phone)) {
                                $msg_body = ($r->subject) ? $r->subject . '. ' . $r->message : $r->message;
                                $ret = Helper::send_sms($phone, '[Groomit] ' . $msg_body);
                                if (!empty($ret)) {
                                    $msg .=  "[Fail to TEXT:" . $phone . "]|" ;
                                    //throw new \Exception($ret);
                                }
                                $cnt_sms++;
                            } else {
                                    $msg .=  "[No Phone groomer:" . $g->groomer_id . "]|" ;
                                    //throw new \Exception('No phone # found: ' . $phone);
                            }
                        }
                    }
                }

                Helper::send_mail('it@jjonbp.com', '[GroomIt][' . getenv('APP_ENV') .'] Group Push/SMS to Groomers Summary', ' - Push: ' . $cnt_push . ' - SMS : ' . $cnt_sms);

            } else {

                $r = New Message;

                $r->send_method = $request->send_method;
                $r->sender_type = 'A'; // admin user
                $r->sender_id = $admin_id;
                $r->receiver_type = $request->receiver_type;
                $r->receiver_id = $request->receiver_id;
                $r->message_type = $request->message_type;
                $r->subject = $request->subject;
                $r->message = $request->message;
                $r->cdate = Carbon::now();
                $r->parent_id = ($request->parent_id) ? $request->parent_id : null;
                $r->save();

                ########

                # Send Push Notification / SMS HERE!!

                ########

                if (in_array($r->send_method, ['P', 'B'])) {
                    if ($r->receiver_type == 'B') {
                        $groomer = Groomer::find($request->receiver_id);
                        if (empty($groomer)) {
                            throw new \Exception('No user found');
                        }

                        if (!empty($groomer->device_token)) {

                            $payload = [
                                'type' => 'M',
                                'id' => $r->message_id
                            ];

                            $error = Helper::send_notification('groomer', $r->message, $groomer->device_token, 'Groomer', $payload);

                            if (!empty($error)) {
                                throw new \Exception($error);
                            }
                        }
                    } else {
                        $user = User::find($request->receiver_id);
                        if (empty($user)) {
                            throw new \Exception('No user found');
                        }

                        if (!empty($user->device_token)) {

                            $payload = [
                                'type' => 'M',
                                'id' => $r->message_id
                            ];

                            $error = Helper::send_notification('groomit', $r->message, $user->device_token, 'Groomit', $payload);

                            if (!empty($error)) {
                                throw new \Exception($error);
                            }
                        }
                    }
                }

                if (in_array($r->send_method, ['S', 'B'])) {
                    $phone = '';
                    if ($r->receiver_type == 'A' || $r->receiver_type == 'U') {
                        $user = User::find($request->receiver_id);
                        $phone = empty($user) ? '' : $user->phone;
                    } else if ($r->receiver_type == 'B' || $r->receiver_type == 'G') {
                        $groomer = Groomer::find($request->receiver_id);
                        $phone = empty($groomer) ? '' : $groomer->mobile_phone;
                    }

                    if (!empty($phone)) {

                        $msg_body = ($r->subject) ? $r->subject . '. ' . $r->message : $r->message;

                        $ret = Helper::send_sms($phone, '[Groomit] ' . $msg_body);
                        if (!empty($ret)) {
                            throw new \Exception($ret);
                        }

                    } else {
                        throw new \Exception('No phone # found: ' . $phone);
                    }
                }
            }


            //DB::commit();

            //$msg = "Success";

            //return Redirect::route('admin.messages')->with('alert', $msg);
            return response()->json([
                'msg' => $msg,
                'parent_id' => $r->parent_id
            ]);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' - ' . $ex->getCode();

            Helper::send_mail('it@jjonbp.com', '[GroomIt][' . getenv('APP_ENV') .'] groomit.me/admin/messages/send', ' - msg: ' . $msg . '<br/> - error_msg : ' . $ex->getMessage() . '<br/> - error_code: ' . $ex->getTraceAsString());
            return response()->json([
                'msg' => $msg
            ]);
        }
    }

    public function detail(Request $request) {
        try {

            $v = Validator::make($request->all(), [
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


            if ($auth = Auth::guard('admin')->user()) {
                $admin_id = $auth->admin_id;
            } else {
                $msg = 'Admin auth required';
                return response()->json([
                    'msg' => $msg
                ]);
            }

            $messages = Message::where('parent_id', $request->id)
                ->orWhere('message_id', $request->id)
                ->orderBy('cdate', 'desc')
                ->get();

            foreach ($messages as $n) {

                switch($n->sender_type) {
                    case 'U':
                        $sender = DB::table('user')
                            ->selectRaw('CONCAT(first_name,\' \', last_name) AS name')
                            ->where('user_id', '=', $n->sender_id)
                            ->first();
                        break;
                    case 'G':
                        $sender = DB::table('groomer')
                            ->selectRaw('CONCAT(first_name,\' \', last_name) AS name')
                            ->where('groomer_id', '=', $n->sender_id)
                            ->first();
                        break;
                    case 'A':
                        $sender = DB::table('admin')
                            ->select('name')
                            ->where('admin_id', '=', $n->sender_id)
                            ->first();
                        break;
                    default:
                        $n->sender = "GroomIt";
                        break;
                }

                if (!empty($sender)) {
                    $n->sender = $sender->name;
                } else {
                    $n->sender = "Groomit";
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

    public function get_receivers($user_type, $q) {
        try {

            $users = null;

            if (!isset($user_type) || !isset($q)) {
                return response()->json([
                    'name' => 'Please try again'
                ]);
            }
            switch ($user_type) {
                case 'groomer':
                    $users = DB::table('groomer')
                        ->selectRaw("groomer_id AS id, CONCAT(first_name,' ', last_name) AS name, phone, email")
                        ->where('status', 'A')
                        ->where(function($query) use ($q) {
                            $query->whereRaw("LOWER(CONCAT(first_name,' ', last_name)) like '%" . strtolower($q) . "%'")
                                ->orWhereRaw("phone like '%" . strtolower($q) . "%'")
                                ->orWhereRaw("email like '%" . strtolower($q) . "%'");
                        })->get();
                    break;
                case 'user':
                    $users = DB::table('user')
                        ->selectRaw("user_id AS id, CONCAT(first_name,' ', last_name) AS name, phone, email")
                        ->whereRaw("LOWER(CONCAT(first_name,' ', last_name)) like '%" . strtolower($q) . "%'")
                        ->orWhereRaw("phone like '%" . strtolower($q) . "%'")
                        ->orWhereRaw("email like '%" . strtolower($q) . "%'")
                        ->get();
                    break;
                case 'admin':
                    $users = DB::table('admin')
                        ->selectRaw("admin_id AS id, name, email")
                        ->whereRaw("LOWER(name) like '%" . strtolower($q) . "%'")
                        ->orWhereRaw("email like '%" . strtolower($q) . "%'")
                        ->get();
                    break;
            }

            return response()->json($users);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }
}
