<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroomerDodo extends Model
{
    protected $table = 'groomer_dodo';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

}
