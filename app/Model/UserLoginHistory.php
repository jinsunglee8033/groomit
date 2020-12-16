<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserLoginHistory extends Model
{
    protected $table = 'user_login_histsory';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    ## login_channel
    ## E: End User
    ## A: Login as by Admin
    public static function save_login_history($user_id, $email, $login_channel, $ip_addr, $log_inout='', $result='') {
        $history = new UserLoginHistory();
        $history->user_id = $user_id;
        $history->log_inout = $log_inout;
        $history->result = $result;
        $history->email = $email;
        $history->login_channel = $login_channel;
        $history->ip_addr = $ip_addr;
        $history->cdate = Carbon::now();
        $history->save();

//        if($log_inout == 'O'){ //Reset device_token when logout, in addition to leave log.
//            $user = User::where('user_id', $user_id)->first();
//
//            if (!empty($user)) {
//                $user->device_token = '';
//                $user->mdate = Carbon::now();
//                //$groomer->modified_by = 'logout'; //modified_by is int.
//                $user->update();
//            }
//        }



//        if( $result == 'S'){
//            $segment = new \Segment();
//            $segment->init("5Ve8XWVizx6obmb2aunTqyQ89tta5a0c");
//
//            $segment->identify( [
//                    "userId" => empty($user_id)? 'SessionTimeOut':$user_id,
//                    //"name" => $ip_addr,
//                    "email" => $email,
//                    //"plan" => $login_channel,
//                    "logins" => $log_inout
//                ]
//            );
//
//            if($login_channel == 'I') {
//                $segment->track([
//                        "userId" => empty($user_id)? 'SessionTimeOut': $user_id,
//                        "event" => 'sign in',
//                        //"name" => $ip_addr,
//                        "email" => $email,
//                        "device_type" => $login_channel,
//                    ]
//                );
//            }else if($login_channel == 'O'){
//                $segment->track( [
//                        "userId" => empty($user_id)? 'SessionTimeOut': $user_id,
//                        "event" => 'sign out',
//                        //"name" => $ip_addr,
//                        "email" => $email,
//                        "device_type" => $login_channel,
//                    ]
//                );
//            }
//        }
    }
}
