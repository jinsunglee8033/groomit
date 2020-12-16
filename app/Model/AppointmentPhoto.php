<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AppointmentPhoto extends Model
{
    protected $table = 'appointment_photo';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'apo_id';
}
