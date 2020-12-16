<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CreditRedemption extends Model
{
    protected $table = 'credit_redemption';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'redemption_id';
}
