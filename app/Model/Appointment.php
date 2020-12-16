<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointment';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'appointment_id';

    public function products() {
        return $this->hasMany('App\Model\AppointmentProduct');
    }

    public function photos() {
        return $this->hasMany('App\Model\AppointmentPhoto');
    }
}
