<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ChargeBack extends Model
{
    protected $table = 'charge_back';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
