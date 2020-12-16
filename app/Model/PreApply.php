<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PreApply extends Model
{
    protected $table = 'groomer_pre_apply';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

}
