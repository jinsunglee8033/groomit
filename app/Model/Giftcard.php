<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Giftcard extends Model
{
    protected $table = 'giftcard';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
