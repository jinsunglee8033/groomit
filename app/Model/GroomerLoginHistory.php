<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GroomerLoginHistory extends Model
{
    protected $table = 'groomer_login_history';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'login_id';

    public static function save_login_history(  $groomer_id,  $ip_addr, $result='', $msg='',$log_inout='I') {
        $history = new GroomerLoginHistory();
        $history->log_inout = $log_inout;
        $history->groomer_id = $groomer_id;
        $history->ip_addr = $ip_addr;
        $history->result = $result;
        $history->msg = $msg;
        $history->cdate = Carbon::now();
        $history->save();

        if($log_inout == 'O'){ //Reset device_token when logout, in addition to leave log.
            $groomer = Groomer::where('groomer_id', $groomer_id)->first();

            if (!empty($groomer)) {
                $groomer->device_token = '';
                $groomer->mdate = Carbon::now();
                $groomer->modified_by = 1; //modified_by is int. 1:logout
                $groomer->update();
            }
        }
    }
}
