<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroomerTool extends Model
{
    protected $table = 'groomer_tools';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
