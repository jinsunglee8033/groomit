<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 4/29/19
 * Time: 2:10 PM
 */


namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdminPrivilege extends Model
{
    protected $table = 'admin_privilege';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

}
