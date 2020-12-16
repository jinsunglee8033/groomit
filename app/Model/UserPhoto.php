<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserPhoto extends Model
{
    protected $table = 'user_photo';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'photo_id';
}
