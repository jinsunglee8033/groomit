<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $table = 'size';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'size_id';
}
