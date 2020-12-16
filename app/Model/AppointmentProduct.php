<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AppointmentProduct extends Model
{
    protected $table = 'appointment_product';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'ap_id';
}
