<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 11/8/18
 * Time: 9:38 AM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroomerServiceArea extends Model
{
    protected $table = 'groomer_service_area';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
