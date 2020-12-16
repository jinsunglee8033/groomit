<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/17/18
 * Time: 5:31 PM
 */


namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProfitShareDetail extends Model
{
    protected $table = 'profit_share_detail';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
