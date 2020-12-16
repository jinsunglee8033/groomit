<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ApplicationPetPhoto extends Model
{
    protected $table = 'groomer_application_pet_photo';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
