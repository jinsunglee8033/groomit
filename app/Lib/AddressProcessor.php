<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/14/18
 * Time: 2:08 PM
 */

namespace App\Lib;


use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\AppointmentList;
use App\Model\User;

class AddressProcessor
{

    public static function get_building_number($address1) {
        $regex = '/^\d+\w*\s*(?:(?:[\-\/]?\s*)?\d*(?:\s*\d+\/\s*)?\d+)?\s+/';
        if (preg_match($regex, $address1, $match)) {
            return $match[0];
        };

        return null;
    }

    public static function add($user_id, $address1, $address2, $city, $state, $zip, $default_address = 'N') {

        $allowed_zip = AllowedZip::where('zip', $zip)->first();
        if (empty($allowed_zip)) {
            return [
                'msg' => 'We are sorry but your address does not belongs to our available service areas.'
            ];
        } else {
            if ($allowed_zip->available != 'x') {
                return [
                    'msg' => 'We are sorry but your address does not belongs to our available service areas. ' . $allowed_zip->allowed
                ];
            }
        }

        if ($allowed_zip->state_abbr != $state) {
            return [
                'msg' => 'Zip code does not match with state'
            ];
        }

        ### duplication check ###
//        $address = Address::whereRaw("
//            concat(lower(trim(substring_index(address1, ' ', 1))), '-', lower(trim(address2)), '-', zip) =
//            concat(lower(trim(substring_index(?, ' ', 1))), '-', lower(trim(?)), '-', ?)
//        ", [$address1,$address2,$zip])->first();
//        if (!empty($address)) {
//            return [
//                'msg' => 'The address is registered already'
//            ];
//        }

        //$allowed_zip = AllowedZip::where('zip', $zip)->first();
        $county = empty($allowed_zip) ? null : $allowed_zip->county_name;


        $address = new Address;
        $address->user_id = $user_id;
        $address->address1 = $address1;
        $address->address2 = ($address2) ? $address2 : '';
        $address->city = $city;
        $address->county = $county;
        $address->state = $state;
        $address->zip = $zip;
        $address->default_address = $default_address;

        $address = self::get_geolocation($address);
        self::check_default_address($address);

        $address->save();

        $u = User::where('user_id', $user_id)->first();
        if(!empty($u)){
            $u->zip = $zip;
            $u->save();
        }

        return [
            'msg' => '',
            'address' => $address
        ];
    }

    public static function update($user_id, $address_id, $address1, $address2, $city, $state, $zip, $default_address = 'N') {
        ### check zip code ###
        $allowed_zip = AllowedZip::where('zip', $zip)->first();

        if (empty($allowed_zip)) {
            return [
                'msg' => 'We are sorry but your address does not belongs to our available service areas. '
            ];
        } else {
            if ($allowed_zip->available != 'x') {
                return [
                    'msg' => 'We are sorry but your address does not belongs to our available service areas.' . $allowed_zip->allowed
                ];
            }
        }

        if ($allowed_zip->state_abbr != $state) {
            return [
                'msg' => 'Zip code does not match with state'
            ];
        }

//        ### duplication check ###
//        $address = Address::whereRaw("
//            concat(lower(trim(substring_index(address1, ' ', 1))), '-', lower(trim(address2)), '-', zip) =
//            concat(lower(trim(substring_index(?, ' ', 1))), '-', lower(trim(?)), '-', ?)
//        ", [$address1, $address2, $zip])->where('address_id', '!=', $address_id)
//            ->first();
//
//        if (!empty($address)) {
//            return [
//                'msg' => 'The address is registered already'
//            ];
//        }

        $address = Address::where('address_id', $address_id)
            ->where('user_id', $user_id)
            ->first();
        if (empty($address)) {
            return [
                'msg' => 'Invalid address ID provided'
            ];
        }
        // Old Address
        $address->default_address = 'N';
        $address->status = 'D';
        $address->save();


        $county = empty($allowed_zip) ? null : $allowed_zip->county_name;

        // New Address
        $new_address = new Address;
        $new_address->user_id = $address->user_id;
        $new_address->address1 = $address1;
        $new_address->address2 = $address2;
        $new_address->city = $city;
        $new_address->county = $county;
        $new_address->state = $state;
        $new_address->zip = $zip;
        $new_address->default_address = 'Y';
        $new_address->status = 'A';

        $new_address = self::get_geolocation($new_address);

        $new_address->save();


        $u = User::where('user_id', $user_id)->first();
        if(!empty($u)){
            $u->zip = $zip;
            $u->save();
        }


        // If the appointment is on processing...
        $apps = AppointmentList::where('user_id', $user_id)
            ->where('address_id', $address->address_id)
            ->whereIn('status', ['N', 'D', 'O', 'W'])
            ->get();
        if (!empty($apps) && count($apps) > 0) {
            AppointmentList::where('user_id', $user_id)->where('address_id', $address->address_id)
                ->whereIn('status', ['N', 'D', 'O', 'W'])
                ->update([
                    'address_id' => $new_address->address_id
            ]);
        }

//        $address = self::get_geolocation($address);
//        self::check_default_address($address);
//        $address->save();

        return [
            'msg' => '',
            'address' => $address
        ];
    }

    private static function get_geolocation($address) {

        $full_address = $address->address1 . ' ' . $address->address2 . ' ' . $address->city . ' ' . $address->state . ' ' . $address->zip;
        $location = Helper::address_to_geolocation($full_address);

        Helper::log('### LOCATION ###', [
            'location' => $location
        ]);

        if ($location['msg'] == '') {
            $address->lat = $location['lat'];
            $address->lng = $location['lng'];
        }

        return $address;
    }

    // Make unique default address
    private static function check_default_address($address)
    {
        if ($address->default_address == 'Y') {

            Address::where('user_id', '=', $address->user_id)
                ->where('address_id', '!=', $address->address_id)
                ->where('default_address', '=', 'Y')
                ->update(array('default_address' => 'N'));
        }
    }
}