<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'notification_id';
}
