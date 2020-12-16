<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 12/12/18
 * Time: 6:45 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroomerBlockedBreeds extends Model
{
    protected $table = 'groomer_blocked_breeds';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}