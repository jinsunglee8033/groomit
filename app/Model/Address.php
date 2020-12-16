<?php

namespace App\Model;

use App\Lib\Helper;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'address_id';

    public function status_name() {
        switch ($this->status) {
            case 'D':
                return '<span class="text-danger">Deactivated</span>';
            case 'A':
                return '<span class="text-success">Active</span>';
        }
    }

    public function getGeolocation($address) {

        $full_address = $address->address1 . ' ' . $address->address2 . ' ' . $address->city . ' ' . $address->state . ' ' . $address->zip;
        $location = Helper::address_to_geolocation($full_address);

        if ($location['msg'] == '') {
            $address->lat = $location['lat'];
            $address->lng = $location['lng'];
        }

        return $address;
    }
}
