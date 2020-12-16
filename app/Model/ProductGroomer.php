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

class ProductGroomer extends Model
{
    protected $table = 'product_groomer';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
