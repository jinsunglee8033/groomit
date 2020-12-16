<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AllowedZip extends Model
{
    protected $table = 'allowed_zip';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
