<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class Affiliate extends Model
{
    protected $table = 'affiliate';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'aff_id';

    public function full_name() {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function full_address() {
        $addr = '';
        if (!empty($this->address)) {
            $addr = $this->address;
            if (!empty($this->address2)) {
                $addr .= ' # ' . $this->address2;
            }
            $addr .= ', ';
        }

        if (!empty($this->city)) {
            $addr .= $this->city . ', ';
        }

        $addr .= $this->state . ' ' . $this->zip;

        return $addr;
    }

    public function status_name() {
        switch ($this->status) {
            case 'I':
                return 'Inactive';
            case 'A':
                return 'Active';
        }
    }
}
