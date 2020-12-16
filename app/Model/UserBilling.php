<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserBilling extends Model
{
    protected $table = 'user_billing';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'billing_id';

    public function status_name() {
        switch ($this->status) {
            case 'D':
                return '<span class="text-danger">Deactivated</span>';
            case 'A':
                return '<span class="text-success">Active</span>';
        }
    }


}
