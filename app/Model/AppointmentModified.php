<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 4/10/19
 * Time: 4:54 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AppointmentModified extends Model
{
    //use Traits\HasCompositePrimaryKey;

    protected $table = 'appointment_modified';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public static function add_record($appointment_id, $seq, $orig_date) {
        $m = new AppointmentModified();
        $m->appointment_id = $appointment_id;
        $m->seq = $seq;
        $m->orig_date = $orig_date;
        $m->cdate = \Carbon\Carbon::now();
        $m->save();
    }
}
