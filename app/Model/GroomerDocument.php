<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 1/8/19
 * Time: 10:18 AM
 */

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GroomerDocument extends Model
{
    protected $table = 'groomer_document';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

}
