<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 5/12/19
 * Time: 9:50 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VWAppointmentPhone extends Model
{
    protected $table = 'vw_appointment_phone';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'phone';
}
