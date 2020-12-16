<?php

namespace App\Model;

use App\Lib\Helper;
use Illuminate\Database\Eloquent\Model;

class GroomerPoint extends Model
{
    protected $table = 'groomer_point';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

}
