<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/15/18
 * Time: 2:59 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Times extends Model
{
    protected $table = 'times';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
