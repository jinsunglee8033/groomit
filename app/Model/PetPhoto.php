<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PetPhoto extends Model
{
    protected $table = 'pet_photo';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'photo_id';
}
