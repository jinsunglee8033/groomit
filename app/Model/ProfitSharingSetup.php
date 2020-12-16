<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProfitSharingSetup extends Model
{
    protected $table = 'profit_sharing_setup';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'package_id';

    public $incrementing = false;
}
