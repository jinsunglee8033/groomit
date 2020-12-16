<?php

namespace App\Model;

use App\Lib\AppointmentProcessor;
use Illuminate\Database\Eloquent\Model;

class AffiliateRedeemHistory extends Model
{
    protected $table = 'affiliate_redeem_history';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'aff_redeemed_id';


    public function status_name() {
        switch ($this->status) {
            case 'N':
                return 'New Request';
            case 'S':
                return 'Processing';
            case 'P':
                return 'Paid';
            case 'C':
                return 'Canceled';
        }
    }

    public function type_name() {
        switch ($this->type) {
            case 'B':
                return 'Bank Transfer';
            case 'C':
                return 'Check';
        }
    }


    public static function earnings($aff_id) {

        $earned_amt = 0;
        $redeemed_amt = 0;
        $data = AffiliateRedeemHistory::where('aff_id', $aff_id)->where('status', '<>', 'C')->get();
        if (!empty($data)) {
            foreach($data as $d) {
                $earned_amt += $d->amount;

                if ($d->status == 'P') {
                    $redeemed_amt += $d->amount;
                }
            }
        }

        $earnings = $earned_amt - $redeemed_amt;

        return $earnings;
    }


    public static function redeemed_amt($aff_id) {

        $redeemed_amt = AffiliateRedeemHistory::where('aff_id', $aff_id)->where('status', 'P')->sum('amount');
        if (empty($redeemed_amt)) {
            $redeemed_amt = 0;
        }

        return $redeemed_amt;
    }


    public static function earned_amt($code) {

        if (empty($code)) {
            return 0;
        }

        $earned_amt = AffiliateRedeemHistory::where('status', '<>', 'C')
            ->whereRaw("appointment_id in (select appointment_id from appointment_list where promo_code = '" . strtoupper($code) . "')")
            ->sum('amount');

        if (empty($earned_amt)) $earned_amt = 0;

        return $earned_amt;
    }

    public static function add($app) {

        if (!empty($app->promo_code)) {
            $promo = PromoCode::find($app->promo_code);
            if (!empty($promo) && $promo->type == 'B') {
                $aff = AffiliateRedeemHistory::where('appointment_id', $app->appointment_id)->where('status', '<>', 'C')->first();
                if (empty($aff)) {
                    $afcode = AffiliateCode::where('aff_code', $app->promo_code)->where('status', 'A')->first();

                    if (!empty($afcode)) {
                        $is_first = AppointmentProcessor::is_first_appointment($app);

                        if ($is_first) {
                            $aff = new AffiliateRedeemHistory();
                            $aff->aff_id = $afcode->aff_id;
                            $aff->amount = $afcode->earning;
                            $aff->type = '';
                            $aff->appointment_id = $app->appointment_id;
                            $aff->status = 'N';
                            $aff->redeemed_by = $app->user_id;
                            $aff->cdate = \Carbon\Carbon::now();
                            $aff->save();
                        }
                    }
                }
            }
        }
    }


    public static function remove($app) {
        if (!empty($app->promo_code)) {
            $promo = PromoCode::find($app->promo_code);
            if (!empty($promo) && $promo->type == 'B') {
                $aff = AffiliateRedeemHistory::where('appointment_id', $app->appointment_id)->where('status', '<>', 'C')->first();
                if (!empty($aff)) {
                    $aff->status = 'C';
                    $aff->mdate = \Carbon\Carbon::now();
                    $aff->update();
                }
            }
        }
    }
}
