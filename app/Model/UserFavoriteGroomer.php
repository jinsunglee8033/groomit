<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteGroomer extends Model
{
    protected $table = 'user_favorite_groomer';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = ['user_id', 'groomer_id'];

    public $incrementing = false;
}
