<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProfitSharing extends Model
{
    protected $table = 'profit_sharing';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public function getLastUpdatedAttribute() {
        $admin = Admin::find($this->attributes['created_by']);
        if (!empty($admin)) {
            return $this->attributes['cdate'] . ' (' . $admin->email . ')';
        }

        return '';
    }

    public function getGroomerExceptionAttribute() {
        $ex = ProfitSharingExceptionGroomer::find($this->attributes['exception_groomer_id']);
        if (!empty($ex)) {
            return $ex->groomer_profit;
        }

        return '';
    }

    public function getUserExceptionAttribute() {
        $ex = ProfitSharingExceptionUser::find($this->attributes['exception_user_id']);
        if (!empty($ex)) {
            return $ex->groomer_profit;
        }

        return '';
    }

    public function getGroomerNameAttribute() {
        $groomer = Groomer::find($this->attributes['groomer_id']);
        if (empty($groomer)) {
            return '';
        }

        return $groomer->first_name. ' ' . $groomer->last_name;
    }

    public function getCustomerNameAttribute() {
        $user = User::find($this->attributes['user_id']);
        if (empty($user)) {
            return '';
        }

        return $user->first_name. ' ' . $user->last_name;
    }

    public function getTypeNameAttribute() {
        switch ($this->attributes['type']) {
            case 'A':
                return 'Appointment';
            case 'T':
                return 'Tip';
            case 'C':
                return 'Credit';
            case 'D':
                return 'Debit';
            default:
                return $this->attributes['type'];

        }
    }

    public function getAppointmentTypeNameAttribute() {
        $aps = AppointmentProduct::join('product', 'product.prod_id', '=', 'appointment_product.prod_id')
            ->where('product.prod_type', 'P')
            ->where('appointment_product.appointment_id', $this->attributes['appointment_id'])
            ->select('product.prod_name')
            ->get();

        $appointment_type = '';
        foreach ($aps as $o) {
                $appointment_type .=  (empty($appointment_type) ? '' : ', ') . $o->prod_name;
        }

        return $appointment_type;
    }

    public function getPetQtyAttribute() {
        //dd($this->attributes);

        if ($this->attributes['type'] != 'A') {
            return 0;
        }

        return AppointmentPet::where('appointment_id', $this->attributes['appointment_id'])
            ->count();
    }

    public function getCreatedByNameAttribute() {
        $admin = Admin::find($this->attributes['created_by']);
        if (empty($admin)) {
            return '';
        }

        return $admin->name;
    }

    public $appends = ['groomer_name', 'type_name', 'created_by_name'];
}
