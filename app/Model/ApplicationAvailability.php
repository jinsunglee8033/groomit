<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ApplicationAvailability extends Model
{
    protected $table = 'groomer_application_availability';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
