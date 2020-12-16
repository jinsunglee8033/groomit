<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $table = 'credit';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'credit_id';

    public function getAppointmentTypeNameAttribute() {
        $aps = AppointmentProduct::join('product', 'product.prod_id', '=', 'appointment_product.prod_id')
            ->where('product.prod_type', 'P')
            ->where('appointment_product.appointment_id', $this->attributes['appointment_id'])
            ->select('product.prod_name')
            ->get();

        $packages = '';
        foreach ($aps as $o) {
            $packages .=  (empty($packages) ? '' : ', ') . $o->prod_name;
        }

        return $packages;
    }

    public static function get_category_name($category) {
        switch ($category) {
            case 'S':
                return "Signup Credit with Referral Code";
            case 'N':
                return "Normal";
            case "R":
                return "Referral Credit (To Owner)";
            case "T":
                return "Store Credit";
            case "G":
                return "$50 gold package credit";
            default:
                return $category;
        }
    }
}
