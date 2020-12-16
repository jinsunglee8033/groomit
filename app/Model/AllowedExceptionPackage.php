<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 2/5/19
 * Time: 5:41 AM
 */


namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AllowedExceptionPackage extends Model
{
    protected $table = 'allowed_exception_package';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
