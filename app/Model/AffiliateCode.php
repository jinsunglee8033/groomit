<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class AffiliateCode extends Model
{
    protected $table = 'affiliate_code';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'aff_code';

    public $incrementing = false;

    public static function newAffiliateCode($aff_id, $aff_amt = 15, $promo_amt = 15) {

        try {

            DB::beginTransaction();

            # 6 digit, alphabet only
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $length = 6;
            $stop = false;
            $new_code = '';

            while (!$stop) {
                for ($i = 0; $i < $length; $i++) {
                    $new_code .= $chars{mt_rand(0, strlen($chars)-1)};
                }

                $rc = PromoCode::whereRaw("code = '$new_code'")->first();
                if (empty($rc)) {
                    $stop = true;
                }
            }

            # new affiliate code
            $code =  new AffiliateCode;
            $code->aff_id = $aff_id;
            $code->aff_code = strtoupper($new_code);
            $code->earning = $aff_amt; // default $15 since 07/07/2020
            $code->status = 'A'; // 'A' : active, 'I' : inactive
            $code->assigned_date = date("m/d/Y");
            $code->save();

            # add to promo code
            $promo_code = new PromoCode;
            $promo_code->type = 'B'; // Affiliate Code created by affiliate user
            $promo_code->code = strtoupper($new_code);
            $promo_code->amt_type = 'A'; // Amount
            $promo_code->first_only = 'Y';
            $promo_code->no_insurance = 'N';
            $promo_code->include_tax = 'N';
            $promo_code->amt = $promo_amt;
            $promo_code->cdate = Carbon::now();;
            $promo_code->save();

            DB::commit();

            $result = $new_code;

            return $result;

        } catch (\Exception $ex) {
            DB::RollBack();
            //dd($ex);
            //return $ex->getMessage() . ' [' . $ex->getCode() . ']';
            return null;
        }

    }

    public static function newCustomAffiliateCode($aff_id, $custom_code, $aff_amt = 15, $promo_amt = 15) {

        try {

            DB::beginTransaction();

            # 6 digit, alphabet only
            $new_code = strtoupper($custom_code);

            # new affiliate code
            $code =  new AffiliateCode;
            $code->aff_id = $aff_id;
            $code->aff_code = $new_code;
            $code->earning = $aff_amt; // default  $15 since 07/07/2020
            $code->status = 'A'; // 'A' : active, 'I' : inactive
            $code->assigned_date = date("m/d/Y");
            $code->save();

            # add to promo code
            $promo_code = new PromoCode;
            $promo_code->type = 'B'; // Affiliate Code created by affiliate user
            $promo_code->code = $new_code;
            $promo_code->amt_type = 'A'; // Amount
            $promo_code->amt = $promo_amt;
            $promo_code->first_only = 'Y';
            $promo_code->no_insurance = 'N';
            $promo_code->include_tax = 'N';
            $promo_code->cdate = Carbon::now();;
            $promo_code->save();

            DB::commit();

            $result = $new_code;

            return $result;

        } catch (\Exception $ex) {
            DB::RollBack();
            //dd($ex);
            //return $ex->getMessage() . ' [' . $ex->getCode() . ']';
            return null;
        }

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
