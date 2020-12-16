<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ZipQuery extends Model
{
    protected $table = 'zip_query';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
