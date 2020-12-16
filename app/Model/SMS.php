<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 12/7/18
 * Time: 4:26 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SMS extends Model
{
    protected $table = 'sms';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
