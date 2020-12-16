<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    protected $table = 'reply';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'reply_id';
}
