<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contact';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'contact_id';

    function getAppointmentNumbers() {
        $apps = AppointmentList::where('user_id', $this->user_id)->whereNotIn('status',['C'])->get();
        return $apps->count();
    }
}
