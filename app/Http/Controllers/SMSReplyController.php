<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/16/17
 * Time: 3:29 PM
 */

namespace App\Http\Controllers;

use App\Model\AppointmentList;
use App\Model\PetPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use Carbon\Carbon;
use Log;
use DB;
use App\Lib\Helper;
use App\Model\Message;
use App\Model\User;
use App\Model\Groomer;

class SMSReplyController extends Controller
{

    public function process(Request $request) {

        $tech_email = 'tech@groomit.me';

        try {

            if (empty($request->From)) {
                throw new \Exception('No from phone # found from request');
            }

            if (empty($request->Body)) {
                throw new \Exception('No messgae body found from request');
            }

            $from = str_replace("+1", "", trim($request->From));
            $to = str_replace("+1", "", trim($request->To));
            $body = trim($request->Body);

            ### find user with phone ###
            $user = User::where('phone', $from)
                ->whereRaw('user_id in (select user_id from appointment_list where status = \'O\')')
                ->first();

            if (empty($user)) {
                $groomer = Groomer::where('mobile_phone', $from)->first();
                if (!empty($groomer)) {

                    $sender_type = 'G';
                    $sender_id = $groomer->groomer_id;

                    $subject = 'Groomer ' . $groomer->first_name . ' sent you a message: ';
                } else {
                    $user = User::where('phone', $from)->first();
                    if (empty($user)) {
                        throw new \Excpetion('Non matching phone # found. Not an user nor a groomer.');
                    }

                    $sender_type = 'U';
                    $sender_id = $user->user_id;

                    $subject = 'Customer ' . $user->first_name . ' sent you a message: ';
                }

            } else {

                $subject = 'Customer ' . $user->first_name . ' sent you a message: ';

                $sender_type = 'U';
                $sender_id = $user->user_id;
            }

            $body .= '(From:' . $to . ')';

            $m = new Message;
            $m->sender_id = $sender_id;
            $m->receiver_id = null;
            $m->appointment_id = null;
            $m->send_method = 'S';
            $m->sender_type = $sender_type;
            $m->receiver_type = 'C';
            $m->message_type = 'R';
            $m->subject = 'SMS Reply From End User';
            $m->message = $body;
            $m->cdate = Carbon::now();
            $m->save();

            $ret = Helper::send_sms_to_cs($subject . $body);
            if (!empty($ret)) {
                $err_msg = '[Groomit][' . getenv('APP_ENV') . '] Error: Send SMS Reply to C/S. Sender ID: ' . $sender_id . ', Message: ' . $subject . $body;
                Helper::send_mail($tech_email, $err_msg, $ret);
            }

            $this->sms_reply();

        } catch (\Exception $ex) {

            $msg = ' - REQUEST : ' . var_export($request->all(), true) . '<br/>';
            $msg .= ' - ERROR : ' . $ex->getMessage() . ' [' . $ex->getCode() . ']';

            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] SMS reply error', $msg);

            $this->sms_reply();

        }

    }

    public function gc_process(Request $request) {

        $tech_email = 'tech@groomit.me';

        try {

            if (empty($request->From)) {
                throw new \Exception('No from phone # found from request');
            }

            if (empty($request->Body)) {
                throw new \Exception('No messgae body found from request');
            }

            $from = str_replace("+1", "", trim($request->From));
            $to = str_replace("+1", "", trim($request->To));
            $body = trim($request->Body);

            ### find user with phone ###
            $user = User::where('phone', $from)
              ->whereRaw('user_id in (select user_id from appointment_list where status = \'O\')')
              ->first();

            if (empty($user)) {
                $groomer = Groomer::where('mobile_phone', $from)->first();

                if (!empty($groomer)) {
                    $sender_type = 'G';
                    $sender_type_name = 'Groomer';
                    $sender_id = $groomer->groomer_id;
                    $sender_name = $groomer->first_name . ' ' . $groomer->last_name;

                    ### find appointment with groomer on the way or work in progress ###
                    $app = AppointmentList::where('groomer_id', $groomer->groomer_id)
                      ->whereIn('status', ['O'])
                      ->whereRaw("accepted_date - interval 2 hour <= ?" , [Carbon::now()])
                      ->whereRaw("accepted_date + interval 60 minute >= ?", [Carbon::now()])
                      ->first();

                    if (!empty($app)) {
                        $user = User::find($app->user_id);
                        if (empty($user)) {
                            throw new \Exception('Invalid user ID found while processing SMS reply');
                        }

                        $subject = 'Your groomer ' . $sender_name . ' sent you a message: ';
                        $ret = Helper::send_sms($user->phone, $subject . $body, 'twilio_gc');
                        if (!empty($ret)) {
                            $err_msg = '[Groomit][' . getenv('APP_ENV') . '] Error: Send SMS Reply From Groomer to End User. Sender ID: ' . $groomer->groomer_id . ', Message: ' . $subject . $body;
                            Helper::send_mail($tech_email, $err_msg, $ret);
                        }

                        $m = new Message;
                        $m->sender_id = $groomer->groomer_id;
                        $m->receiver_id = $user->user_id;
                        $m->appointment_id = $app->appointment_id;
                        $m->send_method = 'S';
                        $m->sender_type = 'G';
                        $m->receiver_type = 'A';
                        $m->message_type = 'R';
                        $m->subject = $subject;
                        $m->message = $body;
                        $m->cdate = Carbon::now();
                        $m->save();

                        $body .= '(From:' . $to . ')';
                        $ret = Helper::send_sms_to_cs($subject . $body);
                        if (!empty($ret)) {
                            $err_msg = '[Groomit][' . getenv('APP_ENV') . '] Error: Send SMS Reply From Groomer to End-User & C/S. Sender ID: ' . $groomer->groomer_id . ', Message: ' . $subject . $body;
                            Helper::send_mail($tech_email, $err_msg, $ret);
                        }

                        exit;
                    }
                } else {

                    $user = User::where('phone', $from)->first();
                    if (empty($user)) {
                        throw new \Excpetion('Non matching phone # found. Not an user nor a groomer.');
                    }

                    $sender_type = 'U';
                    $sender_type_name = 'User';
                    $sender_id = $user->user_id;
                    $sender_name = $user->first_name . ' ' . $user->last_name;
                }
            } else {

                ### find appointment with groomer on the way or work in progress ###
                $app = AppointmentList::where('user_id', $user->user_id)
                  ->whereIn('status', ['O'])
                  ->whereRaw("accepted_date - interval 2 hour <= ?" , [Carbon::now()])
                  ->whereRaw("accepted_date + interval 60 minute >= ?", [Carbon::now()])
                  ->first();

                if (!empty($app)) {

                    $groomer = Groomer::find($app->groomer_id);
                    if (empty($groomer)) {
                        throw new \Exception('Invalid groomer ID found while processing SMS reply');
                    }

                    $subject = 'Your customer ' . $user->first_name . ' ' . $user->last_name . ' sent you a message: ';
                    $ret = Helper::send_sms($groomer->mobile_phone, $subject . $body, 'twilio_gc');
                    if (!empty($ret)) {
                        $err_msg = '[Groomit][' . getenv('APP_ENV') . '] Error: Send SMS Reply From End User to Groomer. Sender ID: ' . $user->user_id . ', Message: ' . $subject . $body;
                        Helper::send_mail($tech_email, $err_msg, $ret);
                    }

                    $m = new Message;
                    $m->sender_id = $user->user_id;
                    $m->receiver_id = $groomer->groomer_id;
                    $m->appointment_id = $app->appointment_id;
                    $m->send_method = 'S';
                    $m->sender_type = 'U';
                    $m->receiver_type = 'B';
                    $m->message_type = 'R';
                    $m->subject = $subject;
                    $m->message = $body;
                    $m->cdate = Carbon::now();
                    $m->save();

                    $body .= '(From:' . $to . ')';
                    $ret = Helper::send_sms_to_cs($subject . $body);
                    if (!empty($ret)) {
                        $err_msg = '[Groomit][' . getenv('APP_ENV') . '] Error: Send SMS Reply From End User to Groomer & C/S. Sender ID: ' . $user->user_id . ', Message: ' . $subject . $body;
                        Helper::send_mail($tech_email, $err_msg, $ret);
                    }

                    exit;
                }

                $sender_type = 'U';
                $sender_type_name = 'User';
                $sender_id = $user->user_id;
                $sender_name = $user->first_name . ' ' . $user->last_name;
            }

            $body .= '(From:' . $to . ')';

            $m = new Message;
            $m->sender_id = $sender_id;
            $m->receiver_id = null;
            $m->appointment_id = null;
            $m->send_method = 'S';
            $m->sender_type = $sender_type;
            $m->receiver_type = 'C';
            $m->message_type = 'R';
            $m->subject = 'SMS Reply From End User';
            $m->message = $body;
            $m->cdate = Carbon::now();
            $m->save();

            $message = 'Reply Message : ' .  $body  . '(' . $sender_type_name . ' - ' . $sender_name . ', ID - ' . $sender_id . ')';

            $err_msg = '[Groomit][' . getenv('APP_ENV') . '] Error: Send SMS Reply From End User to Lars & Sally. Sender ID: ' . $sender_id . ', Message: ' . $message;

            if (getenv('APP_ENV') == 'production') {
                $ret = Helper::send_sms_to_admin($message);
                if (!empty($ret)) {
                    Helper::send_mail($tech_email, $err_msg, $ret);
                }
            }
            ### end send text ###

            $this->sms_reply('Your Contact Customer Window is closed. Please contact CS.');

        } catch (\Exception $ex) {

            $msg = ' - REQUEST : ' . var_export($request->all(), true) . '<br/>';
            $msg .= ' - ERROR : ' . $ex->getMessage() . ' [' . $ex->getCode() . ']';

            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] SMS reply error', $msg);

            $this->sms_reply();

        }

    }

    private function sms_reply($sms = null) {
        $msg = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $msg .= "<Response>" .
            "<Sms>" .
          (( empty($sms) || $sms == '' ) ? "" : $sms).
            "</Sms>" .
            "</Response>";

        header("Content-Type: text/xml; charset=utf-8");
        header("Vary: Accept-Encoding");
        header("X-Shenanigans: none");
        header("Content-Length: " . strlen($msg));
        header("Connection: keep-alive");
        ob_clean();
        echo $msg;
    }

}