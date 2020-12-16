<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/29/19
 * Time: 10:26 AM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $table = 'survey';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'appointment_id';

}
