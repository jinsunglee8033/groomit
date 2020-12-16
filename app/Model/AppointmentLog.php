<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AppointmentLog extends Model
{
    protected $table = 'appointment_list_log';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'appointment_id';

    public $incrementing = false;
}
