<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ApplicationTool extends Model
{
    protected $table = 'groomer_application_tools';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
