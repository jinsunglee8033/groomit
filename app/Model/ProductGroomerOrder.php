<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 1/15/19
 * Time: 9:56 AM
 */


namespace App\Model;

use App\Lib\Helper;
use Illuminate\Database\Eloquent\Model;

class ProductGroomerOrder extends Model
{
    protected $table = 'product_groomer_order';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public static function add_by_admin($groomer, $product_groomer, $qty, $admin) {
        $order = new ProductGroomerOrder();
        $order->groomer_id  = $groomer->groomer_id;
        $order->prod_gr_id  = $product_groomer->id;
        $order->prod_type   = $product_groomer->prod_type;
        $order->prod_name   = $product_groomer->prod_name;
        $order->size        = $product_groomer->size;
        $order->price       = $product_groomer->price;
        $order->street      = $groomer->street;
        $order->zip         = $groomer->zip;
        $order->city        = $groomer->city;
        $order->state       = $groomer->state;
        $order->qty         = $qty;
        $order->status      = 'N';
        $order->created_by  = $admin->name;
        $order->cdate       = \Carbon\Carbon::now();
        $order->save();
    }
}
