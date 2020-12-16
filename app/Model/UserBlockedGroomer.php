<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserBlockedGroomer extends Model
{
    protected $table = 'user_blocked_groomer';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = ['user_id', 'groomer_id'];

    public $incrementing = false;
}
