<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class User extends Model 
{
    protected $table = 'user';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'user_id';

    public function pets() { // modeal add
        return $this->hasMany('App\Model\Pet');
    }

    protected $hidden = ['passwd', 'fb_token', 'gg_token'];
    //protected $visible = ['user_id'];

    public function getReferralCodeAttribute() {
        $promo_code = PromoCode::where('type', 'R')->where('user_id', $this->attributes['user_id'])->first();
        if (empty($promo_code)) {
            return '';
        }

        return $promo_code->code;
    }

    public function getRegisterFromNameAttribute() {
        switch ($this->attributes['register_from']) {
            case 'D':
                return 'Web';
            case 'A':
                return 'App';
            default:
                return $this->attributes['register_from'];
        }

    }

//    public function booked($export = false) {
//        $appointment = AppointmentList::where('user_id', $this->user_id)
//            ->whereNotIn('status', ['C', 'L'])
//            ->get();
//
//        if ($appointment->count() == 0) {
//            return "";
//        } else {
//            if ($export) {
//                return "Booked (" . $appointment->count() . ")";
//            } else {
//                return "Booked ( <b>" . $appointment->count() . "</b> )";
//            }
//        }
//    }

//    public function last_order($type = null) {
//        $appointment = AppointmentList::where('user_id', $this->user_id)
//            ->whereNotIn('status', ['C', 'L'])
//            ->orderBy('accepted_date', 'desc')
//            ->first();
//
//        if (empty($appointment)) {
//            return "";
//        } else {
//            $order_date = Carbon::parse($appointment->cdate);
//
//            if ($type == 'days') {
//                $today = Carbon::now();
//
//                return $order_date->diffInDays($today);
//            } else {
//                return $order_date->format('m/d/Y');
//            }
//        }
//    }

//    public function getLastGroomerAttribute() {
//        $app = AppointmentList::where('user_id', $this->attributes['user_id'])
//            ->where('status', 'P')
//            ->orderBy('accepted_date', 'desc')
//            ->first();
//
//        if (!empty($app)) {
//            $groomer = Groomer::find($app->groomer_id);
//            if (!empty($groomer)) {
//                return $groomer->first_name . ' ' . $groomer->last_name . ' (' . $groomer->groomer_id . ')';
//            }
//        }
//
//        return '';
//    }

}
