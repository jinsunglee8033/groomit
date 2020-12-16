<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Breed extends Model
{
    protected $table = 'breed';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'breed_id';
}
