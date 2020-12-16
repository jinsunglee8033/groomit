<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/15/18
 * Time: 2:34 PM
 */


namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VWAppointmentSchedule extends Model
{
    protected $table = 'vw_appointment_schedule';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'appointment_id';
}
