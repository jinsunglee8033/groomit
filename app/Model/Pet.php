<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pet extends Model
{
    protected $table = 'pet';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'pet_id';

    public function photos() {
        return $this->hasMany('App\Model\PetPhoto');
    }

    public function getDobAttribute($value) {
        if ($value) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $value . ' 00:00:00')->format('m/d/Y');
        } else {
            return '';
        }
    }
}
