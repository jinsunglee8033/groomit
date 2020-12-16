<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 5/15/19
 * Time: 8:39 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PromoCodeUsers extends Model
{
    protected $table = 'promo_code_users';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public static function set_users($promo_code, $user_ids) {

        PromoCodeUsers::where('promo_code', $promo_code)->delete();

        if (!empty($user_ids) && $user_ids[0]!='') {
            foreach ($user_ids as $user_id) {
                $pu = new PromoCodeUsers();
                $pu->promo_code = $promo_code;
                $pu->user_id = $user_id;
                $pu->cdate = \Carbon\Carbon::now();
                $pu->save();
            }
        }
    }
}
