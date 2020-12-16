<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/20/19
 * Time: 10:58 AM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroomerAch extends Model
{
    protected $table = 'groomer_ach';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

}
