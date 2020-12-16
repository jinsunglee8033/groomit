<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 4/10/19
 * Time: 10:14 AM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VWPetGroomerNote extends Model
{
    protected $table = 'vw_pet_groomer_note';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'appointment_id';
}
