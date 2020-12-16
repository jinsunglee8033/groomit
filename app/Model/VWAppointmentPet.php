<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VWAppointmentPet extends Model
{
    protected $table = 'vw_appointment_pet';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'appointment_id';
}
