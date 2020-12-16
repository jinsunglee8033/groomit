<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/12/18
 * Time: 4:54 PM
 */

namespace App\Lib;

use App\Model\Address;
use App\Model\GroomerAvailability;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use stdClass;

class GroomerScheduleProcessor
{
    public static function get_availability_by_date($groomer_id, $sdate, $edate) {
        $base_availables = GroomerAvailability::where('groomer_id', $groomer_id)
          ->where('date', '>=', $sdate)
          ->where('date', '<=', $edate)
          ->orderBy('date', 'asc')
          ->orderBy('hour', 'asc')
          ->get();

        Helper::log('########## GROOMER AVAILABILITIES ##########', $base_availables);

        $availabilities = [];

        if (!empty($base_availables) && count($base_availables) > 0) {
            $day = null;
            $hours = [];
            $hour = new stdClass();
            foreach ($base_availables as $ba) {
                if (empty($day)) {
                    $day = $ba->date;
                    $hour->from = $ba->hour;
                    $hour->to   = $ba->hour;
                } else if ($day == $ba->date) {
                    if ($hour->to + 1 == $ba->hour) {
                        $hour->to = $ba->hour;
                    } else {
                        $hours[] = [
                          'from'  => $hour->from,
                          'to'    => $hour->to + 1
                        ];

                        $hour->from = $ba->hour;
                        $hour->to = $ba->hour;
                    }
                } else {
                    $hours[] = [
                      'from'  => ($hour->from < 10) ? '0' . $hour->from : (string)$hour->from,
                      'to'    => ($hour->to < 9) ? '0' . ($hour->to + 1) : (string)($hour->to + 1)
                    ];

                    $total_hours = GroomerAvailability::where('groomer_id', $groomer_id)
                      ->where('date', $day)
                      ->count();

                    $availabilities[] = [
                        'day'     => $day,
                        'hours'   => $hours,
                        'total_hours' => $total_hours
                    ];

                    $hours = [];
                    $day = $ba->date;
                    $hour->from = $ba->hour;
                    $hour->to = $ba->hour;
                }
            }

            $hours[] = [
              'from'  => ($hour->from < 10) ? '0' . $hour->from : (string)$hour->from,
              'to'    => ($hour->to < 9) ? '0' . ($hour->to + 1) : (string)($hour->to + 1)
            ];

            $total_hours = GroomerAvailability::where('groomer_id', $groomer_id)
              ->where('date', $day)
              ->count();

            $availabilities[] = [
              'day'     => $day,
              'hours'   => $hours,
              'total_hours' => $total_hours
            ];

            foreach ($availabilities as $ava) {
                $total_hours = GroomerAvailability::where('groomer_id', $groomer_id)
                  ->where('date', $ava['day'])
                  ->count();

                $ava['total_hours'] = $total_hours;
            }
        }

        return $availabilities;
    }


    public static function set_availability_by_date($groomer_id, $availabilities) {

        Helper::log('########## GROOMER SAVE TEST ########## CT: ' . count($availabilities) . ' ###', $availabilities);

        $today = Carbon::today()->format('Y-m-d');
        foreach ($availabilities as $abt) {
            if ($abt->day < $today) continue;

            $day        = $abt->day;
            $weekday    = date('w', strtotime($day));
            $weekday    = $weekday - 1;
            if ($weekday < 0) {
                $weekday = 6;
            }

            GroomerAvailability::where('groomer_id', $groomer_id)->where('date', $day)->delete();

            $hours      = $abt->hours;

            if (!empty($hours)) {
                foreach ($hours as $hour) {
                    $pt = (int)$hour->from;
                    $to = (int)$hour->to;

                    while ($pt < $to) {
                        $availability = new GroomerAvailability();
                        $availability->groomer_id = $groomer_id;
                        $availability->weekday = $weekday;
                        $availability->hour = $pt;
                        $availability->date = $day;
                        $availability->cdate = Carbon::now();
                        $availability->save();
                        $pt = $pt + 1;
                    }
                }
            }
        }
    }

    public static function get_appointments_by_date($groomer_id, $sdate, $edate , $distance_type='', $x=0, $y=0) {

        $appointment_data = array();

        ### GET APPOINTMENTS ###
        $appointments = DB::select("
                select *
                  from vw_appointment_schedule_base
                 where groomer_id = :groomer_id
                   and adate >= :sdate
                   and adate <= :edate
                 order by adate, stime
            ", [
          'groomer_id' => $groomer_id,
          'sdate' => $sdate,
          'edate' => $edate
        ]);

        if (count($appointments) > 0) {
            $cru_date = null;
            $cru_qty = 0;
            $cru_appointments = array();

            foreach ($appointments as $app) {
                $cru_qty ++;
                if ($cru_date != $app->adate) {
                    if (!empty($cru_date)) {
                        $appointment_data[] = [
                            'date'  => $cru_date,
                            'appointment_qty' => $cru_qty - 1,
                            'appointments' => $cru_appointments
                        ];
                    }
                    $cru_qty = 1;
                    $cru_date = $app->adate;
                    $cru_appointments = array();
                }

                $address = Address::find($app->address_id);
                if (empty($address)) {
                    $address = new \stdClass();
                    $address->address1 = 'N/A';
                    $address->address2 = 'N/A';
                    $address->city = 'N/A';
                    $address->state = 'N/A';
                    $address->zip = 'N/A';
                }
                $products = DB::select("
                    select p.*
                      from appointment_product ap
                      join product p on ap.prod_id = p.prod_id and p.prod_type = 'P'
                     where appointment_id = :appointment_id
                    ", [
                    'appointment_id' => $app->appointment_id
                ]);

                $packages = array();
                $package_name = '';
                foreach ($products as $p) {
                    $packages[] = [
                      'name'  => $p->prod_name
                    ];
                    $package_name = $p->prod_name;
                }
                if (count($products) > 1) {
                    $package_name = 'Multi Package';
                }

                $pet_list = \DB::select("
                    select type, breed_name, package_name , size
                      from vw_appointment_pet 
                     where appointment_id = :appointment_id
                     ", [
                    'appointment_id' => $app->appointment_id
                ]);

                $distance = 'N/A';
                $distance_next = 'N/A';
                if( in_array($distance_type, ['C','H','P'] ) ){
                    //Get Distance in miles
                    $dist = Helper::get_distance_to_groomer( $groomer_id, $app->appointment_id, $distance_type, $x, $y );
                    $dist_next = Helper::get_distance_to_groomer( $groomer_id, $app->appointment_id, 'N', $x, $y );

                    $distance = ($dist == 'N/A') ? $dist : $dist . ' miles';
                    $distance_next = ($dist_next == 'N/A') ? $dist_next : $dist_next . ' miles';
                }

                $cru_appointments[] = [
                    'appointment_id' => $app->appointment_id,
                    'package_name'  => $package_name,
                    'packages'      => $packages,
                    'address'       => [
                    'address1'  =>  empty($address->address1) ? '' : $address->address1,
                    'address2'  =>  empty($address->address2) ? '' : ' # ' . $address->address2,
                    'city'      =>  empty($address->city) ? '' : $address->city,
                    'state'     =>  empty($address->state) ? '' : $address->state,
                    'zip'       =>  empty($address->zip) ? '' : $address->zip,
                    ],
                    'stime' => substr($app->stime, 0, 5),
                    'etime' => substr($app->etime, 0, 5),
                    'pet_list'  => $pet_list,
                    'distance' => $distance,
                    'distance_next' => $distance_next
                ];

            }

            $appointment_data[] = [
              'date'    => $cru_date,
              'appointment_qty' => $cru_qty,
              'appointments' => $cru_appointments
            ];
        }

        Helper::log('########## GROOMER APPOINTMENTS ##########', $appointment_data);

        return $appointment_data;
    }
}