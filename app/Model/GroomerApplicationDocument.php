<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 1/9/19
 * Time: 2:27 PM
 */

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GroomerApplicationDocument extends Model
{
    protected $table = 'groomer_application_document';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

}
