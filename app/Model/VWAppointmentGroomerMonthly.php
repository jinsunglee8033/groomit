<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 5/22/19
 * Time: 8:45 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VWAppointmentGroomerMonthly extends Model
{
    protected $table = 'vw_appointment_groomer_monthly';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
