<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 4/30/19
 * Time: 3:08 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdminPrivilegeAction extends Model
{
    protected $table = 'admin_privilege_action';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

}
