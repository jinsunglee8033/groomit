<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AppointmentPet extends Model
{
    protected $table = 'appointment_pet';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = ['appointment_id', 'pet_id'];

    public $incrementing = false;
}
