<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/22/19
 * Time: 1:27 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserCertificate extends Model
{
    protected $table = 'user_certificate';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
