<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProfitSharingExceptionGroomer extends Model
{
    protected $table = 'profit_sharing_exception_groomer';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public function getNameAttribute() {
        $groomer = Groomer::find($this->attributes['groomer_id']);
        if (empty($groomer)) {
            return '';
        }

        return $groomer->first_name . ' ' . $groomer->last_name;
    }

    public function getEmailAttribute() {
        $groomer = Groomer::find($this->attributes['groomer_id']);
        if (empty($groomer)) {
            return '';
        }

        return $groomer->email;
    }

    public function getPhoneAttribute() {
        $groomer = Groomer::find($this->attributes['groomer_id']);
        if (empty($groomer)) {
            return '';
        }

        return $groomer->phone;
    }

    public function getLastUpdatedAttribute() {
        if (empty($this->attributes['cdate'])) {
            return $this->attributes['mdate'] . ' (' . $this->attributes['modified_by'] . ')';
        }

        return $this->attributes['cdate'] . ' (' . $this->attributes['created_by'] . ')';
    }

    public function getUserExceptionCountAttribute() {
        $user_exceptions = ProfitSharingExceptionUser::where('groomer_id', $this->attributes['groomer_id'])->get();
        return count($user_exceptions);
    }

    public function getPackageAttribute() {
        $product = Product::find($this->attributes['package_id']);
        if (!empty($product)) {
            return '(' . strtoupper($product->pet_type) . ') ' . $product->prod_name;
        }

        return '-';
    }
}
