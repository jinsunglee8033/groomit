<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $table = 'tax';

    public $timestamps = false;

    protected $dateFormat = 'U';
    //protected $keyType = 'string'; //For Laravel 6.0 migration
    protected $primaryKey = 'state';
}
