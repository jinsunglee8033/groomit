<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 5/12/19
 * Time: 9:49 PM
 */


namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VWAppointmentAddress extends Model
{
    protected $table = 'vw_appointment_address';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'address';
}
