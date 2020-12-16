<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AppointmentReject extends Model
{
    //use Traits\HasCompositePrimaryKey;

    protected $table = 'appointment_rejected';

    public $timestamps = false;

    protected $dateFormat = 'U';

    //protected $primaryKey = ['appointment_id', 'groomer_id'];
}
