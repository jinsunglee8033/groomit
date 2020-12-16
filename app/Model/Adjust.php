<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/29/19
 * Time: 10:26 AM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Adjust extends Model
{
    protected $table = 'adjust';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

//    public static function create($appointment_id, $type, $pdate, $amt, $comments) {
//
//        $adjust = new Adjust();
//        $adjust->appointment_id = $appointment_id;
//        $adjust->type       = $type;
//        $adjust->pdate      = $pdate;
//        $adjust->amt        = $amt;
//        $adjust->comments   = $comments;
//        $adjust->cdate      = Carbon::now();
//        $adjust->save();
//    }

}
