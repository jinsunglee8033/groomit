<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 2/5/19
 * Time: 5:40 AM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AllowedExceptionPet extends Model
{
    protected $table = 'allowed_exception_pet';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
