<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroomerAvailability extends Model
{
    protected $table = 'groomer_availability';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
