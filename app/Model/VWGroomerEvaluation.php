<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 4/16/19
 * Time: 3:59 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VWGroomerEvaluation extends Model
{
    protected $table = 'vw_groomer_evaluation';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
