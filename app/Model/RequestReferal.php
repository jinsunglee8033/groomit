<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/4/19
 * Time: 3:13 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RequestReferal extends Model
{
    protected $table = 'request_referal';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';


    public static function log($ip_addr) {

        $referal = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
        if (!empty($referal)) {
            $referal = (strlen($referal) > 127) ? substr($referal, 0, 127) : $referal;

            if (strpos($referal, 'groomit.me') == false && strpos($referal, 'localhost') == false) {
                $rqlog = RequestReferal::where('ip_addr', $ip_addr)->where('url', $referal)->first();
                if (empty($rqlog)) {
                    $rqlog = new RequestReferal();
                    $rqlog->ip_addr = $ip_addr;
                    $rqlog->url = $referal;
                    $rqlog->cdate = \Carbon\Carbon::now();
                    $rqlog->save();
                }
            }
        }
    }
}
