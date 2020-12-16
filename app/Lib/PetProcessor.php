<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Lib;

use App\Model\Pet;
use Carbon\Carbon;

class PetProcessor
{

    public static function get_age($pet_id) {
        $pet = Pet::find($pet_id);
        if (empty($pet)) {
            return '';
        }

        $dob = Carbon::parse($pet->dob);
        $age = $dob->diffInMonths(Carbon::now());

        $year = intval($age / 12);
        $month = intval($age % 12);
        $new_age = ($year > 0 ? $year . ' year' . (($year > 1) ? 's' : ' ') : ' ') . ' ' . $month . ' month' . ($month > 1 ? 's' : '');

        if (!empty($pet->age)) {
            $new_age = $pet->age . ' year' . ($pet->age > 1 ? 's' : '');
        }

        return $new_age;
    }

}