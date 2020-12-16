<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 1/16/19
 * Time: 10:57 AM
 */

namespace App\Model;

use App\Lib\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductGroomerCart extends Model
{
    protected $table = 'product_groomer_cart';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public static function add_to_cart($groomer_id, $pr_id, $qty) {
        try {
            $pgc = new ProductGroomerCart();
            $pgc->groomer_id = $groomer_id;
            $pgc->prod_gr_id = $pr_id;
            $pgc->qty = $qty;
            $pgc->cdate = \Carbon\Carbon::now();
            $pgc->save();
        } catch (\Exception $e){
            return false;
        }
        return true;
    }

    public static function delete_from_cart($groomer_id, $pr_id) {
        try {
            ProductGroomerCart::where('groomer_id', $groomer_id)
            ->where('prod_gr_id', $pr_id)
            ->delete();
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

}
