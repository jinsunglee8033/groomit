<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/30/17
 * Time: 4:09 PM
 */

namespace App\Lib;

use App\Model\Address;
use App\Model\AppointmentList;
use App\Model\AppointmentPet;
use App\Model\AppointmentPhoto;
use App\Model\AppointmentProduct;
use App\Model\CCTrans;
use App\Model\Credit;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\Product;
use App\Model\ProductDenom;
use App\Model\TaxZip;
use App\Model\UserBilling;
use App\Model\User;
use App\Model\Message;
use App\Model\Constants;
use App\Model\Groomer;
use App\Model\PromoCode;
use Auth;
use Carbon\Carbon;
use DB;

class UserAppointmentProcessor
{
   public static function getPackageAddOns($size_id, $zip) {

       $group_id = 1;
       if (!empty($zip)) {
           $allowed_zip = AllowedZip::where('zip', $zip)->first();
           if (!empty($allowed_zip)) {
               $group_id = $allowed_zip->group_id;
           }
       }

       $packages = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where b.size_id = :size_id
                and a.prod_type = 'P'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
            ", [
           'size_id' => $size_id,
           'zip' => $zip,
           'group_id' => $group_id
       ]);

       $add_ons = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where b.size_id = :size_id
                and a.prod_type = 'A'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                order by a.seq
            ", [
           'size_id' => $size_id,
           'zip' => $zip,
           'group_id' => $group_id
       ]);

       $shampoos = DB::select("
                select
                    a.prod_id,
                    a.prod_name,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'S'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
            ", [
           'zip' => $zip,
           'group_id' => $group_id
       ]);

       return array($packages, $add_ons, $shampoos);
   }
}