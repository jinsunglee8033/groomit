<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 11/19/18
 * Time: 2:24 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VWGroomerAssignLog extends Model
{
    protected $table = 'vw_groomer_assign_log';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'appointment_id';
}
