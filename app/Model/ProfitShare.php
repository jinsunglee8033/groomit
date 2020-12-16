<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/17/18
 * Time: 5:31 PM
 */

namespace App\Model;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

use App\Lib\ProfitSharingProcessor;

class ProfitShare extends Model
{
    protected $table = 'profit_share';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public function getTypeNameAttribute() {
        switch ($this->attributes['type']) {
            case 'A':
            case 'W':
                return 'Appointment';
            case 'V':
                return 'Reverse Appointment';
            case 'T':
                return 'Tip';
            case 'C':
                return 'Credit';
            case 'D':
                return 'Debit';
            case 'J': //Seems not to be used any longer.
                return 'Adjust';
            case 'R':
                return 'Referal';
            case 'L':
                return 'Reverse Referal';
            default:
                return $this->attributes['type'];

        }
    }

    public function getCategoryNameAttribute() {
        switch ($this->attributes['category']) {
            case 'R':
                return 'Reviews by SNS';
            case 'O':
                return 'Others';
            default:
                return $this->attributes['category'];
        }
    }

    public static function create_credit($type, $groomer_id, $amt, $comments, $created_by) {
        $ps = new ProfitShare();
        //$ps->appointment_id
        //$ps->sub_total
        $ps->type               = $type;
        $ps->groomer_id         = $groomer_id;
        $ps->groomer_profit_amt = $type == 'C' ? $amt : -$amt;
        //$ps->groomer_profit_ratio
        $ps->remaining_amt      = 0;
        $ps->comments           = $comments;
        $ps->created_by         = $created_by;
        $ps->cdate              = Carbon::now();
        $ps->save();
    }

    public static function create_tip($app) {
        $groomer_profit_ratio = 97;
        $groomer_profit_amt = $app->tip * $groomer_profit_ratio / 100;

        $ps = new ProfitShare();
        $ps->appointment_id         = $app->appointment_id;
        $ps->sub_total              = $app->tip;
        $ps->type                   = 'T';
        $ps->groomer_id             = $app->groomer_id;
        $ps->groomer_profit_ratio   = $groomer_profit_ratio;
        $ps->groomer_profit_amt     = $groomer_profit_amt;
        $ps->remaining_amt          = $app->tip - $groomer_profit_amt;
        $ps->created_by             = $app->user_id;
        $ps->cdate = Carbon::now();
        $ps->save();
    }


    public static function create_adjust($app, $type, $pdate, $amt, $comments, $created_by) { //Adjust on Groomit company, reverse of Groomer Credit/Debit.
        $ps = new ProfitShare();
        $ps->appointment_id     = $app->appointment_id;
        //$ps->sub_total
        $ps->type               = 'J';
        $ps->groomer_id         = $app->groomer_id;
        //$ps->groomer_profit_ratio
        $ps->groomer_profit_amt = 0;
        $ps->remaining_amt      = $type == 'C' ? -$amt : $amt;
        $ps->comments           = $comments;
        $ps->created_by         = $created_by;
        $ps->cdate              = $pdate;
        $ps->save();
    }


    public static function create_groomer_referral($app, $referal_code) {
        if (empty($app) || $app->status != 'P') return;

         //if (empty($app) || empty($app->promo_code) || $app->status != 'P') return;
        $promo = PromoCode::whereRaw("code = '" . strtoupper($referal_code) . "'")->first();
        if (empty($promo) || $promo->type != 'R' || empty($promo->groomer_id))
            return;

        $ps = new ProfitShare();
        $ps->appointment_id     = $app->appointment_id;
        //$ps->sub_total
        $ps->type               = 'R'; //Referal of Groomer
        $ps->groomer_id         = $promo->groomer_id;
        //$ps->groomer_profit_ratio   = 100;
        $ps->groomer_profit_amt = $promo->amt;
        $ps->remaining_amt      = 0;
        $ps->comments           = 'Groomer Referral';
        $ps->app_promo_code     = $promo->code;
        $ps->created_by         = $app->user_id;
        $ps->cdate              = Carbon::now();
        $ps->save();
    }

    //Remove it because it's called only when not 'P' status only.
    public static function remove_groomer_referral($app) {
        if (empty($app) || empty($app->promo_code) || $app->status != 'P') return;

        $promo = PromoCode::whereRaw("code = '" . strtoupper($app->promo_code) . "'")->first();

        if (empty($promo) || $promo->type != 'R' || empty($promo->groomer_id)) return;

        ProfitShare::where('appointment_id', $app->appointment_id)->where('type', 'R')->delete();

    }

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
