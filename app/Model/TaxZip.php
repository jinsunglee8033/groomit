<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TaxZip extends Model
{
    protected $table = 'tax_zip';

    public $timestamps = false;

    protected $dateFormat = 'U';
    //protected $keyType = 'string'; //For Laravel 6.0 migration
    protected $primaryKey = 'zip';
}
