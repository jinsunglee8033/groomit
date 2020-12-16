<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 11/5/18
 * Time: 5:29 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ApplicationHistory extends Model
{
    protected $table = 'groomer_application_history';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
