<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Message extends Model
{
    protected $table = 'message';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'message_id';

    public function getReceiverAttribute() {
        $receiver_type = $this->attributes['receiver_type'];
        $receiver_id = $this->attributes['receiver_id'];
        $receiver = '';

        switch($receiver_type) {
            case 'A':
                $user = User::find($receiver_id);
                if (empty($user)) {
                    $receiver = '';
                } else {
                    $receiver = $user->first_name . ' ' . $user->last_name;
                }
                break;
            case 'B':
            case 'G':
                $groomer = Groomer::find($receiver_id);
                if (empty($groomer)) {
                    $receiver = '';
                } else {
                    $receiver = $groomer->first_name . ' ' . $groomer->last_name;
                }
                break;
            case 'C':
                $admin = Admin::find($receiver_id);
                if (empty($admin)) {
                    $receiver = '';
                } else {
                    $receiver = $admin->name;
                }
                break;
        }

        return $receiver;
    }

    public function getSenderAttribute() {
        $sender_type = $this->attributes['sender_type'];
        $sender_id = $this->attributes['sender_id'];
        $sender = '';
        switch($sender_type) {
            case 'U':
                $user = User::find($sender_id);
                if (empty($user)) {
                    $sender = '';
                } else {
                    $sender = $user->first_name . ' ' . $user->last_name;
                }
                break;
            case 'G':
                $groomer = Groomer::find($sender_id);
                if (empty($groomer)) {
                    $sender = '';
                } else {
                    $sender = $groomer->first_name . ' ' . $groomer->last_name;
                }
                break;
            case 'A':
                /*$admin = Admin::find($sender_id);
                if (empty($admin)) {
                    $sender = '';
                } else {
                    $sender = $admin->name;
                }*/
                $sender = 'GroomIt';
                break;
            default:
                $sender = 'GroomIt';
                break;
        }

        return $sender;
    }

    public function getSenderTypeNameAttribute() {
        $sender_type = $this->attributes['sender_type'];

        switch ($sender_type) {
            case 'U':
                return 'End User';
            case 'G':
                return 'Groomer';
            case 'A':
                return 'Admin';
            default:
                return $sender_type;
        }
    }

    public function getReceiverTypeNameAttribute() {
        $receiver_type = $this->attributes['receiver_type'];

        switch ($receiver_type) {
            case 'A':
                return 'End User';
            case 'B':
            case 'G':
                return 'Groomer';
            case 'C':
                return 'An Admin User';
//            case 'D':
//                return 'All End User';
//            case 'E':
//                return 'All Groomers';
            case 'U':
                return 'End User';
            case 'F':
                return 'All Admin Users';
            default:
                return $receiver_type;
        }
    }

    public function getSendMethodNameAttribute() {
        $send_method = $this->attributes['send_method'];
        $send_method_name = $send_method;
        if (array_key_exists($send_method, Constants::$message_send_method)) {
            $send_method_name = Constants::$message_send_method[$send_method];
        }

        return $send_method_name;
    }

    public function getMessageTypeNameAttribute() {
        $message_type = $this->attributes['message_type'];
        $message_type_name = $message_type;
        if (array_key_exists($message_type, Constants::$message_type)) {
            $message_type_name = Constants::$message_type[$message_type];
        }

        return $message_type_name;
    }

    public $appends = ['sender'];

    public static function save_sms_to_user($message, $user, $appointment_id) {
        $r = New Message;
        $r->send_method     = 'S'; // for now SMS
        $r->sender_type     = 'A'; // admin user
        $r->message_type    = 'UC';
        $r->receiver_type   = 'A'; //End User, C is for Admin User
        $r->receiver_id     = $user->user_id;
        $r->appointment_id  = $appointment_id;
        $r->subject = '';
        $r->message = $message;
        $r->cdate = Carbon::now();
        $r->save();
    }
}
