<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'admin_id';

}
