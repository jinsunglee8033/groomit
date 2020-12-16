<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AppointmentList extends Model
{
    protected $table = 'appointment_list';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'appointment_id';

    public function products() {
        return $this->hasMany('App\Model\AppointmentProduct');
    }

    public function photos() {
        return $this->hasMany('App\Model\AppointmentPhoto');
    }

    public function getStatusNameAttribute() {
        if (array_key_exists($this->attributes['status'], Constants::$appointment_status)) {
            return Constants::$appointment_status[$this->attributes['status']];
        }
        return '';
    }

    public function getPromoTypeAttribute() {
        $code = PromoCode::whereRaw("code = ?", [$this->attributes['promo_code']])->first();
        if (empty($code)) {
            return '';
        }

        return $code->type_name();
    }

    public function getAddressAttribute() {
        $addr = Address::find($this->attributes['address_id']);
        if (!empty($addr)) {
            if( !empty($addr->address2) && ($addr->address2 != '' ) ) {
                return $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->county . ', ' . $addr->state . ' ' . $addr->zip;
            }else {
                return $addr->address1 . ', ' . $addr->city . ', ' . $addr->county . ', ' . $addr->state . ' ' . $addr->zip;
            }

        }

        return '';
    }

    public function getGroomerNameAttribute() {
        $groomer = Groomer::find($this->attributes['groomer_id']);
        if (empty($groomer)) {
            return '';
        }

        return $groomer->first_name . ' ' . $groomer->last_name;
    }

    public function getAddressMatchAttribute() {
        $addr = Address::find($this->attributes['address_id']);
        $user_billing = UserBilling::find($this->attributes['payment_id']);

        $address_num = '';
        $address_zip = '';
        if (!empty($addr)) {
            $address_num_arr = explode(" ", trim($addr->address1));
            $address_num = count($address_num_arr) > 0 ? $address_num_arr[0] : '';
            $address_zip = trim($addr->zip);
        }

        $billing_num = '';
        $billing_zip = '';
        if (!empty($user_billing)) {
            $billing_num_arr = explode(" ", trim($user_billing->address1));
            $billing_num = count($billing_num_arr) > 0 ? $billing_num_arr[0] : '';
            $billing_zip = trim($user_billing->zip);
        }

        return $address_num == $billing_num && $address_zip == $billing_zip;
    }

    public function getAssignedByAttribute() {

        $groomer_id = $this->attributes['groomer_id'];
        if (empty($groomer_id)) {
            return '';
        }

        switch ($this->attributes['groomer_assigned_by']) {
            case 'A':
//                $admin_id = $this->attributes['groomer_assigned_by_id'];
//                $admin = Admin::find($admin_id);
                $name = 'CS';
//                if (!empty($admin)) {
//                    $name = $admin->name . ' (' . $admin->admin_id . ')';
//                }
                return $name;
            case 'G':
                return 'GA';
            default:
                return $this->attributes['groomer_assigned_by'];
        }
    }
}
