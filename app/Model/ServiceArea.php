<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    protected $table = 'service_area';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'area_id';
}
