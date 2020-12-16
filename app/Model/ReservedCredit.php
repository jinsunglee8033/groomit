<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReservedCredit extends Model
{
    protected $table = 'reserved_credit';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'reserved_credit_id';
}
