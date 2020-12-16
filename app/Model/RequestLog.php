<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 12/2/18
 * Time: 10:01 PM
 */


namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $table = 'request_log';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';


    public static function log($url, $user_id, $appointment_id, $groomer_id, $req, $res, $ip_addr) {

        $rqlog = new RequestLog();
        $rqlog->url = $url;
        $rqlog->user_id = $user_id;
        $rqlog->appointment_id = $appointment_id;
        $rqlog->groomer_id = $groomer_id;
        $rqlog->request = $req;
        $rqlog->response = $res;
        $rqlog->ip_addr = $ip_addr;
        $rqlog->cdate = \Carbon\Carbon::now();
        $rqlog->save();
    }
}
