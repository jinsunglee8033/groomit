<?php

namespace App\Lib;

/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/3/16
 * Time: 6:09 PM
 */

use App\Model\AdminPrivilege;
use App\Model\AdminPrivilegeAction;
use App\Model\CCTrans;
use App\Model\Product;
use App\Model\SMS;
use App\Model\UserFavoriteGroomer;
use Illuminate\Support\Facades\Auth;
use Twilio;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;
use Request;
use Log;
use App\Lib\UserProcessor;

use App\Model\AppointmentList;
use App\Model\User;
use App\Model\Groomer;
use App\Model\Address;
use App\Model\Constants;
use App\Model\Message;
use Carbon\Carbon;
use DateTime;
use DB;

class Helper
{
    public static function check_app_key($app_key) {
        try {
            $decryped_api_key = \Crypt::decrypt($app_key);
        } catch (\Exception $ex) {
            return false;
        }

        $android_key = getenv('ANDROID_KEY_PLAIN');
        $ios_key = getenv('IOS_KEY_PLAIN');

        if ($ios_key != $decryped_api_key && $android_key != $decryped_api_key) {
            return false;
        }

        return true;
    }

    public static function log($ident, $msg = '') {
        Log::info("### INFO ###", [
            'PATH' => Request::path(),
            'IDENT' => $ident,
            'MSG' => $msg,
            'UNIQUE_ID' => isset($_SERVER['UNIQUE_ID']) ? $_SERVER['UNIQUE_ID'] : ''
        ]);
    }

    public static function geolocation_to_address($latitude, $longitude) {

        try {

            //Focus postal_code only.
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&result_type=postal_code&key=AIzaSyCOjd_MpYd0KaeqgMjjyYKMdxTC1SEPHDk";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            curl_close($ch);
            $response_a = json_decode($response);

//            Log::info('### reverse geocoding ###', [
//                'latitude' => $latitude,
//                'longitude' => $longitude,
////                'response' => $response,
////                'response_decoded' => $response_a,
////                'response_decoded results' => $response_a->results[0],
//                'address_components' => $response_a->results[0]->address_components[0],
//                'postal_code' => $response_a->results[0]->address_components[0]->short_name,
//            ]);

            if ($response_a->status != 'OK') {
                Helper::send_mail('jun@jjonbp.com', '[GroomIt][' . getenv('APP_ENV') .'] non OK response on reverse geolocation',
                    ' - request: ' . $url . '<br/> - error_msg : ' . $response . '<br/>' );

                return [
                    'msg' => $response_a->status,

                ];
            }

            return [
                'msg' => '',
                'zip' =>$response_a->results[0]->address_components[0]->short_name
            ];
        } catch (\Exception $ex) {
            Helper::send_mail('jun@jjonbp.com', '[GroomIt][' . getenv('APP_ENV') .'] Exception on reverse geolocation',
                ' - request: ' . $url . '<br/> - error_msg : ' . $ex->getMessage() . ': ' . $ex->getCode() . '<br/>' );
            return [
                'msg' => $ex->getMessage() . ': ' . $ex->getCode()
            ];
        }
    }

    public static function address_to_geolocation($address) {

        try {

            $address = str_replace('#', '', $address);
            $address = str_replace(' ', '+', $address);

            //$url = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false";
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=AIzaSyCOjd_MpYd0KaeqgMjjyYKMdxTC1SEPHDk";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            curl_close($ch);
            $response_a = json_decode($response);

            Log::info('### ADDRESS ###', [
                'address' => $address,
                'response' => $response
            ]);

            if ($response_a->status != 'OK') {
                return [
                    'msg' => $response_a->status,
                    'lat' => null,
                    'lng' => null
                ];
            }

            return [
                'msg' => '',
                'lat' => $response_a->results[0]->geometry->location->lat,
                'lng' => $response_a->results[0]->geometry->location->lng
            ];
        } catch (\Exception $ex) {
            return [
                'msg' => $ex->getMessage() . ': ' . $ex->getCode()
            ];
        }
    }

    public static function get_distance($lat1, $lng1, $lat2, $lng2){
        if (($lat1 == $lat2) && ($lng1 == $lng2)) {
            return 0;
        }
        else {
            $theta = $lng1 - $lng2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;

            return $miles;

        }
    }
    public static function get_distance_to_groomer( $groomer_id, $appointment_id, $distance_type='', $x='' , $y='' ){
       $distance = 'N/A';

       if( !isset($groomer_id)){
           return $distance;
       }

        $ap = AppointmentList::find($appointment_id);
        if (empty($ap)) {
            return $distance;
        }

        $address = Address::where('address_id', $ap->address_id)->first();
        if (empty($address) ) {
            return $distance;
        }else if(  empty($address->lat) || empty($address->lng) ) {
            if( !empty($address->address1) &&  !empty($address->city) && !empty($address->state) && !empty($address->zip)) {
                $full_address = $address->address1 . ' ' . $address->city . ' ' . $address->state . ' ' . $address->zip;
                $ret = Helper::address_to_geolocation($full_address);

                if($ret['msg'] == '') {
                    $address->lat = $ret['lat'];
                    $address->lng = $ret['lng'];
                    $address->save();
                }else {
                    return $distance;
                }
            }

        }

       if( $distance_type == 'C'){ //Current Location of a groomr. $x & $y is mandatory.

           $distance = Helper::get_distance($x,$y, $address->lat, $address->lng) ;
           $distance = round($distance ,2) ;

       }else if( $distance_type == 'H' ) { //From Home

           $groomer = Groomer::where('groomer_id', $groomer_id)->first();
           if (empty($groomer) ) {
               return $distance;
           }else if(  empty($groomer->lat) || empty($groomer->lng) ) {
               if( !empty($groomer->street) && !empty($groomer->city)  && !empty($groomer->state)  && !empty($groomer->zip)  ) {
                   $full_address = $groomer->street . ' ' . $groomer->city . ' ' . $groomer->state . ' ' . $groomer->zip;
                   $ret = Helper::address_to_geolocation($full_address);

                   if($ret['msg'] == '') {
                       $groomer->lat = $ret['lat'];
                       $groomer->lng = $ret['lng'];
                       $groomer->save();
                   }else {
                       $groomer->lat = 41.00000001; //In order to prevent repeate look up for groomer home, setup w/ wrong values.
                       $groomer->lng = -74.00000001;
                       $groomer->save();
                   }
               }else {
                   return $distance;
               }

           }

           $distance = Helper::get_distance( $groomer->lat, $groomer->lng, $address->lat , $address->lng) ;
           $distance = round($distance ,2) ;

       }else if( $distance_type == 'P' ) { //From Previous appointment

           $dt1 = new DateTime($ap->accepted_date ); //use accepted_date if exist.
           if( empty($ap->accepted_date)){                          //use reserved_date, if accepted_date not exist.
               $dt1 = new DateTime($ap->reserved_date );
           }
           $dt2 = $dt1->format('Y-m-d');

           $data = DB::select("select address_id 
                                     from appointment_list a  
                                     where accepted_date >= :dt1                                         
                                     and accepted_date < :dt2 + interval 1 day
                                     and accepted_date < :std_date
                                     and  groomer_id = :groomer_id
                                     order by accepted_date desc 
                                     limit 0,1",
               [
                   'dt1' => $dt2,
                   'dt2' => $dt2,
                   'std_date' => $dt1,
                   'groomer_id' => $groomer_id
               ]);

           if (!empty($data) && (count($data) == 1) ) {
                   $prev_addr_id = $data[0]->address_id ; //the address of the latest appointment.
                   $prev_address_id = Address::where('address_id', $prev_addr_id)->first();

                   if(  empty($prev_address_id->lat) || empty($prev_address_id->lng) ) {
                       Helper::log('In empty of lat/lng of prev_address_id:', '*********');
                       if(  !empty($prev_address_id->address1) && !empty($prev_address_id->city) && !empty($prev_address_id->state) && !empty($prev_address_id->zip) ) {
                           $full_address = $prev_address_id->address1 . ' ' . $prev_address_id->city . ' ' . $prev_address_id->state . ' ' . $prev_address_id->zip;
                           $ret = Helper::address_to_geolocation($full_address);

                           if ($ret['msg'] == '') {
                               $prev_address_id->lat = $ret['lat'];
                               $prev_address_id->lng = $ret['lng'];
                               $prev_address_id->save();
                           } else {
                               return $distance;
                           }
                       }else {
                           return $distance;
                       }
                   }

                   $distance = Helper::get_distance($prev_address_id->lat,$prev_address_id->lng, $address->lat, $address->lng) ;
                   $distance = round($distance ,2) ;

           }else { //If no previous appointment, use distance from 'Home' of the groomer.
                   $distance = Helper::get_distance_to_groomer( $groomer_id, $appointment_id, 'H' );
           }
       }else if( $distance_type == 'N' ) { //to dist from Next appointment
           Helper::log('Next appt option sent:', "$appointment_id*********");
           $dt1 = new DateTime($ap->accepted_date ); //use accepted_date if exist.
           //Helper::log('dt1:', "$dt1*********");
           if( empty($ap->accepted_date)){   //use reserved_date, if accepted_date not exist. Do not check empty($dt1)
               $dt1 = new DateTime($ap->reserved_date );
               Helper::log('replace w/ reserved_date because no accepted_date:', "$appointment_id*********");
           }
           $dt2 = $dt1->format('Y-m-d');

           $data = DB::select("select address_id 
                                     from appointment_list a  
                                     where accepted_date >= :dt1                                         
                                     and accepted_date < :dt2 + interval 1 day
                                     and  groomer_id = :groomer_id
                                      and accepted_date > :std_date
                                     order by accepted_date asc 
                                     limit 0,1",
               [
                   'dt1' => $dt2,
                   'dt2' => $dt2,
                   'std_date' => $dt1,
                   'groomer_id' => $groomer_id
               ]);

           if (!empty($data) && (count($data) == 1) ) {
               $next_addr_id = $data[0]->address_id ; //the address of the latest appointment.
               $next_address_id = Address::where('address_id', $next_addr_id)->first();

               if(  empty($next_address_id->lat) || empty($next_address_id->lng) ) {
                   //Helper::log('In empty of lat/lng of next_address_id:', '*********');
                   if(  !empty($next_address_id->address1) && !empty($next_address_id->city) && !empty($next_address_id->state) && !empty($next_address_id->zip) ) {
                       $full_address = $next_address_id->address1 . ' ' . $next_address_id->city . ' ' . $next_address_id->state . ' ' . $next_address_id->zip;
                       $ret = Helper::address_to_geolocation($full_address);

                       if ($ret['msg'] == '') {
                           $next_address_id->lat = $ret['lat'];
                           $next_address_id->lng = $ret['lng'];
                           $next_address_id->save();
                       } else {
                           return $distance;
                       }
                   }else {
                       return $distance;
                   }
               }

               $distance= Helper::get_distance($next_address_id->lat,$next_address_id->lng, $address->lat, $address->lng) ;
               $distance = round($distance ,2) ;

           }else { //If no next appointment, use distance from 'Home' of the groomer.
               $distance = 'N/A';
           }
       }

       return $distance;
    }

    public static function send_notification($channel = "", $msg, $device_token, $title = "", $payload = "") {
        $curl = curl_init();

        //$api_token = $channel == 'groomit' ? "AIzaSyASFgy7oW-oXwNfVxH8vZibBKfaRTRqq64" : "AIzaSyCUWaXq-ZpEfI1H08EX6K9yRyxd84yF5Ks";
        //=> This returns Invalid Key Type.
        //response":"<HTML>\n<HEAD>\n<TITLE>INVALID_KEY_TYPE</TITLE>\n</HEAD>\n<BODY BGCOLOR=\"#FFFFFF\" TEXT=\"#000000\">\n<H1>INVALID_KEY_TYPE</H1>\n<H2>Error 401</H2>\n</BODY>\n</HTML>\n
        $api_token = $channel == 'groomit' ? "AIzaSyBNnpF8qXABllsb6VAaIfmyLTDf90nZkVE" : "AIzaSyAQth5V1dqnT19yfLFCIEUz871FjVsEoNQ";
        //$api_token = "AAAAzgJFzZw:APA91bEPWeLCxkrsU9EnuzEy4OTz4Tjy8yoOJSIlxaLEZCw0C34gAPKuUsxx9mt0g8WbiDn7pEpKEzhwcZUDNCOXwCERXMR9-IPpNoYHvsLtE1xOTsTQOK8bRvKYVqB2_RQjlmKjhTBy";

        $data = json_encode([
            "notification" => [
                "title" => $title,
                "body" => $msg,
                "sound" => "default",
                //"click_action" => "FCM_PLUGIN_ACTIVITY",
                "icon" => "fcm_push_icon"
            ],
            "data" => [
                "payload" => $payload
            ],
            "to" => $device_token,
            "priority" => 1100,
            "restricted_package_name" => ""
        ]);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Authorization: key=$api_token",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        Log::info('### PUSH RESPONSE ###', [
            'request' => $data,
            'response' => $response
        ]);

        if ($err) {
            return  "cURL Error #:" . $err;
        } else {

            try{
                $res = json_decode($response);
                if ($res->success != 1) {
                    if (isset($res->results) && count($res->results) > 0) {
                        return isset($res->results[0]->error) ? $res->results[0]->error : 'Unrecognized error : ' . var_export($res->results[0], true);
                    } else {
                        return 'No Result Found: ' . var_export($res);
                    }

                }
            }catch (\Exception $ex) {

                Helper::send_mail('it@jjonbp.com', '[GroomIt][' . getenv('APP_ENV') .'] Error on Sending Notification', ' - msg: ' . $msg . '<br/> - error_msg : ' . $ex->getMessage() . '<br/> - error_code: ' . $ex->getCode());

                return $ex->getMessage() . ': ' . $ex->getCode();
            }

            return "";
        }
    }

    /*
    public static function send_notification($channel = 'groomit', $msg, $device_token, $title = "", $payload = "") {
        $curl = curl_init();

        # api token on ionic.io
        # - for groomit
        //$api_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJiYjI5YzIyNS0xYzA1LTRiNmItYjZjMy0wZmU2MWY4NjM4NTcifQ.PKvjUbw2Y8HIkxiV1-knoba8VoK6Z3YrZuoaj8Mp-Oo";
        $api_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJiYjI5YzIyNS0xYzA1LTRiNmItYjZjMy0wZmU2MWY4NjM4NTcifQ.nYVcEPFgTcnPkGYSOVtBrmEVAHJqZ3Had2KWrh1T9Ho";
        $profile_tag = "groomit";
        ### demo : groomitdemo ###

        if ($channel == 'groomer') {
            # - for groomer
            $api_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI0MmI1Y2E4OS1iZjJkLTQwMGItYWZlNS0zZGMyNDgzNzQwYzMifQ.IGLr_A86hCs5l5b9E4GF7DCTFrF9TSVg2p-vLkMJvog";
            $profile_tag = "groomerpush";
        }

        $data = json_encode([
            "tokens" => [$device_token],
            "profile" => $profile_tag,
            "notification" => [
                "message" => $msg,
                "title" => $title,
                "payload" => $payload
            ]
        ]);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ionic.io/push/notifications",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $api_token",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        Log::info('### PUSH RESPONSE ###', [
            'request' => $data,
            'response' => $response
        ]);

        if ($err) {
            return  "cURL Error #:" . $err;
        } else {

            $res = json_decode($response);
            if ($res->meta->status < 200 || $res->meta->status >= 300) {
                return $res->error->message . ' [' . $res->error->type . ']';
            }

            return "";
        }
    }
    */

    public static function send_mail($to, $subject, $body) {

        try {
            $ret = \Mail::raw($body, function($message) use ($to, $subject, $body) {
                $message->to($to);
                $message->subject($subject);
            });

            if (!$ret) {
                return 'Failed to send message';
            }
        } catch (\Exception $ex) {
            return $ex->getMessage() . ': ' . $ex->getCode();
        }

        return '';

    }

    public static function send_html_mail($template, $data) {

        try {
            $ret = \Mail::send(['html' => 'emails.' . $template ], ['data' => $data], function ($m) use ($data) {
                $m->to($data['email'], $data['name'])->subject($data['subject']);

                if (!empty($data['bcc'])) {
                    $m->bcc($data['bcc']);
                }
            });

            if (!$ret) {
                return 'Failed to send message';
            }

        } catch (\Exception $ex) {
            return $ex->getMessage() . ': ' . $ex->getCode();

            Log::info('### SEND HTML MAIL ###', [
                'EXCEPTION' => $ex->getTrace()
            ]);
        }

        return '';

    }

    public static function get_price($prod_id, $size_id, $zip) {
        $ret = DB::select("
            select f_get_product_price(:prod_id, :size_id, :zip) as price
        ", [
            'prod_id' => $prod_id,
            'size_id' => $size_id,
            'zip' => $zip
        ]);

        $price = 0;
        if (count($ret) > 0) {
            $price = $ret[0]->price;
        }

        return $price;
    }

    //Called by groomer API & WA(Customer Fav.Groomer)
    //and date_format(a.stime, '%H') >= date_format(current_timestamp + interval 1 hour ,'%H')
//    public static function get_groomer_calendar($groomer_id, $target_date) {
//        $ret = DB::select("
//                  select a.id ,
//                         concat( '$target_date' , 'T', a.stime , '.000Z') as 'start',
//                         concat( '$target_date' , 'T', etime + interval 1 minute , '.000Z') as 'end' , a.desc ,
//                         If( b.hour is null , 'NA' , IF( c.appointment_id is not null, 'NA', 'AV') ) availability,
//                         f_get_short_name(c.address_id) area_name
//                  from times a left join groomer_availability b on a.hour = b.hour
//                                        and b.date = :date1 and b.groomer_id = :groomer_id1
//			                   left join appointment_list c on c.groomer_id = :groomer_id2
//                                        and  date_format(c.accepted_date,'%Y-%m-%d') = :date2
//                                        and c.status not in ('C','L')
//                                        and date_format(stime,'%H%i') >= date_format(c.accepted_date,'%H%i')
//                                        and date_format(stime,'%H%i') < date_format(c.accepted_date + interval (select sum(hour)*60 from appointment_pet where appointment_id = c.appointment_id) minute ,'%H%i')
//                   where a.id <= 25
//                   order by a.id ",
//            [
//                'groomer_id1' => $groomer_id,
//                'groomer_id2' => $groomer_id,
//                'date1' => $target_date,
//                'date2' => $target_date
//            ]);
//
//        return $ret ;
//    }

    //without flexible time. need to be removed, after CA go live.
    public static function get_groomer_calendar($groomer_id, $target_date, $user_id = 0, $zip = '') {
        $today = Carbon::now()->format('Y-m-d');
        //Helper::log('get_groomer_calendar:today:', $today );
        //Helper::log('get_groomer_calendar:target_date:', $target_date );
        $today_nexthours = '';

        if( $target_date == $today ) {
            $today_nexthours = " and date_format(a.stime, '%H%i') >= date_format(  '" . Carbon::now()->addHour(1) . "' ,'%H%i') ";
        }
        //Helper::log('get_groomer_calendar:sql:', $today_nexthours );
        // {"id": "0", "title": "Specific Time", "start": "08:00:00", "end": "09:00:00", "time": "08:00am - 09:00am"},
        if($groomer_id > 0 ){
            $ret = DB::select("
                  select a.id, a.stime, etime + interval 1 minute etime, a.desc,
                  If( b.hour is null , 'NA' , IF( c.appointment_id is not null, 'NA', 'AV') ) availability,
                  f_get_short_name(c.address_id) area_name
                  from times a left join groomer_availability b on a.hour = b.hour
                                        and b.date = :date1 and b.groomer_id = :groomer_id1
			                   left join appointment_list c on c.groomer_id = :groomer_id2
                                        and  date_format(c.accepted_date,'%Y-%m-%d') = :date2
                                        and c.status not in ('C','L')
                                        and date_format(stime,'%H%i') >= date_format(c.accepted_date,'%H%i')
                                        and date_format(stime,'%H%i') < date_format(c.accepted_date + interval (select sum(hour)*60 from appointment_pet where appointment_id = c.appointment_id) minute ,'%H%i')
                   where a.id <= 25 "
                   . $today_nexthours .
                   " order by a.id ",
                [
                    'groomer_id1' => $groomer_id,
                    'groomer_id2' => $groomer_id,
                    'date1' => $target_date,
                    'date2' => $target_date
                ]);
        }else { //In case of groomer_id of 0, which means 'All day available'.
            $query = "  select a.id, a.stime, etime + interval 1 minute etime, a.desc, 'AV' availability, '' area_name
                  from times a
                  where a.id <= 25 " .
                  $today_nexthours .
                " order by a.id " ;
            $ret = DB::select($query,
                [ ]);
        }

        $address = null;
        $customer_zip = '';
        $exclusive_area = 0 ;//By default, no Exclusive area.

        if($user_id > 0 ){
            $address = Address::where('user_id', $user_id)  //ignore users who has no 'Default_address' setup, becasue there existed 73 users only, as of 0721/2020.
            ->where('status', 'A')
            ->where('default_address', 'Y')
            ->orderBy('address_id', 'desc')
            ->first();
            if(empty($address)) {
                $customer_zip = $zip; //If input ZIP exist.
            }else {
                $customer_zip =$address->zip;
            }
        }else if ( $zip != '' ){
            $customer_zip = $zip;
        }


        return $ret ;
    }

    //With flexible time. with groomer exclusive area logics => not used from the beginning.
//    public static function get_groomer_calendar2($groomer_id, $target_date, $user_id = 0, $zip = '') { //Includes Flexible Time
//        $today = Carbon::now()->format('Y-m-d');
//        $today_nexthours = '';
//
//        if( $target_date == $today ) {
//            $today_nexthours = " and date_format(a.stime, '%H%i') >= date_format(  '" . Carbon::now()->addHour(1) . "' ,'%H%i') ";
//        }
//
////        Log::info('### In get_groomer_calendar2() ###', [
////            'groomer_id' => $groomer_id,
////            'target_date' => $target_date,
////            'user_id' => $user_id,
////            'zip' => $zip
////        ]);
//
//        if($groomer_id > 0 ){
//            $ret = DB::select("
//                  select a.id, a.stime, etime + interval 1 minute etime, a.desc,
//                  If( b.hour is null , 'NA' , IF( c.appointment_id is not null, 'NA', 'AV') ) availability,
//                  f_get_short_name(c.address_id) area_name, 'Specific Time' title, 'specific' type_time, a.hour
//                  from times a left join groomer_availability b on a.hour = b.hour
//                                        and b.date = :date1 and b.groomer_id = :groomer_id1
//			                   left join appointment_list c on c.groomer_id = :groomer_id2
//                                        and  date_format(c.accepted_date,'%Y-%m-%d') = :date2
//                                        and c.status not in ('C','L')
//                                        and date_format(stime,'%H%i') >= date_format(c.accepted_date,'%H%i')
//                                        and date_format(stime,'%H%i') < date_format(c.accepted_date + interval (select sum(hour)*60 from appointment_pet where appointment_id = c.appointment_id) minute ,'%H%i')
//                   where a.id <= 25 "
//                . $today_nexthours .
//                " order by a.id ",
//                [
//                    'groomer_id1' => $groomer_id,
//                    'groomer_id2' => $groomer_id,
//                    'date1' => $target_date,
//                    'date2' => $target_date
//                ]);
//        }else { //In case of groomer_id of 0, which means 'All day available'.
//            $query = "  select a.id, a.stime, etime + interval 1 minute etime, a.desc, 'AV' availability, '' area_name, 'Specific Time' title, 'specific' type_time, a.hour
//                  from times a
//                  where a.id <= 25 " .
//                $today_nexthours .
//                " order by a.id " ;
//            $ret = DB::select($query,
//                [ ]);
//        }
//
//
//        $obj_morning = new \stdClass();
//        $obj_morning->id = 100;
//        $obj_morning->stime = '08:00:00';
//        $obj_morning->etime = '12:00:00';
//        $obj_morning->desc = '08:00am - 12:00pm';
//        $obj_morning->availability = 'AV';
//        $obj_morning->area_name = '';
//        $obj_morning->title = 'Morning';
//        $obj_morning->type_time = 'flexible';
//        $obj_morning->hour = 8;
//
//        $obj_after = new \stdClass();
//        $obj_after->id = 101;
//        $obj_after->stime = '12:00:00';
//        $obj_after->etime = '16:00:00';
//        $obj_after->desc = '12:00pm - 04:00pm';
//        $obj_after->availability = 'AV';
//        $obj_after->area_name = '';
//        $obj_after->title = 'Afternoon';
//        $obj_after->type_time = 'flexible';
//        $obj_after->hour = 12;
//
//        $obj_lateafter = new \stdClass();
//        $obj_lateafter->id = 102;
//        $obj_lateafter->stime = '16:00:00';
//        $obj_lateafter->etime = '20:00:00';
//        $obj_lateafter->desc = '04:00am - 08:00pm';
//        $obj_lateafter->availability = 'AV';
//        $obj_lateafter->area_name = '';
//        $obj_lateafter->title = 'Late Afternoon';
//        $obj_lateafter->type_time = 'flexible';
//        $obj_lateafter->hour = 16;
//
//        if( $target_date == $today ) {
//            if( Carbon::now()->format('H') >= "11" ) {
//                $obj_morning->availability = 'NA';
//            }
//            if( Carbon::now()->format('H') >= "15" ) {
//                $obj_after->availability = 'NA';
//            }
//            if( Carbon::now()->format('H') >= "19" ) {
//                $obj_lateafter->availability = 'NA';
//            }
//        }
//
//        $address = null;
//        $customer_zip = '';
//        $exclusive_area = 0 ;//By default, no Exclusive area.
//
//        if($user_id > 0 ){
//            $address = Address::where('user_id', $user_id)  //ignore users who has no 'Default_address' setup, becasue there existed 73 users only, as of 0721/2020.
//            ->where('status', 'A')
//                ->where('default_address', 'Y')
//                ->orderBy('address_id', 'desc')
//                ->first();
//            if(empty($address)) {
//                $customer_zip = $zip; //If input ZIP exist.
//            }else {
//                $customer_zip =$address->zip;
//            }
//        }else if ( $zip != '' ){
//            $customer_zip = $zip;
//        }
//
//        if($customer_zip != '' ){
//            $exclusive_area_ret = DB::select( "select distinct c.weekday
//                                             from exclusive_area a  inner join exclusive_area_detail b on a.alias_id = b.alias_id and b.zip = :customer_zip
//                                             inner join groomer_exclusive_area c on a.alias_id = c.alias_id  ",
//                ['customer_zip' => $customer_zip] );
//
//            $d = Carbon::parse( $target_date );
//            $weekday = Carbon::parse($d)->dayOfWeek; //1:Monday...7:Sunday, but 0:Monday... 6:Sunday in DB.
//            $weekday = $weekday - 1;
//
//            $belongToLimitedAreaAndWeekday = false;
//            if(is_array($exclusive_area_ret) && count($exclusive_area_ret) > 0 ){               //If limited area, always show Flexible times only, regardless of 'Next..' or 'Fav.Groomer', as of now.
//                foreach( $exclusive_area_ret as $aline) {
//                    if( $aline->weekday == $weekday ) {
//                        $belongToLimitedAreaAndWeekday = true ;
//                        break;
//                    }
//                }
//
//                $ret = [] ; //remove existing data in the array.
//                if( $belongToLimitedAreaAndWeekday ) { //Flexible Time only, if days are allowed
//                    $ret[] = $obj_morning;
//                    $ret[] = $obj_after;
//                    $ret[] = $obj_lateafter;
//
//                }else {                                 //exclusive area, but not available days.
//                    $obj_morning->availability = 'NA';
//                    $ret[] = $obj_morning;
//
//                    $obj_after->availability = 'NA';
//                    $ret[] = $obj_after;
//
//                    $obj_lateafter->availability = 'NA';
//                    $ret[] = $obj_lateafter;
//                }
//
//                Log::info('### result of ret in exclusive area ###', [
//                    'ret' => $ret
//                ]);
//
//                return $ret ;
//            }
//        }
//
////        Log::info('### isLimitedWeekday ###', [
////            'isLimitedWeekday' => $belongToLimitedAreaAndWeekday
////        ]);
////        Log::info('### result of hours ###', [
////            'ret' => $ret
////        ]);
//
//         if($groomer_id > 0) {
//             $morning_av_cnt = 0;
//             $after_av_cnt = 0;
//             $lateafter_av_cnt = 0;
//
//             foreach($ret as $aday ) {  //Check how many hours are available for each flexible Times.
//                 if($aday->hour < 12) {
//                     if($aday->availability == 'AV' ) {
//                         $morning_av_cnt += 1;
//                     }
//                 }else if( $aday->hour < 16 ) {
//                     if($aday->availability == 'AV' ) {
//                         $after_av_cnt += 1;
//                     }
//                 }else if( $aday->hour < 20 ) {
//                     if($aday->availability == 'AV' ) {
//                         $lateafter_av_cnt += 1;
//                     }
//                 }
//             }
//
//
//             if( $target_date == $today ) {
//                 $halfhour_to_pass = 1; //Once exist, In case of Today, it already get after 1hr as of now.
//             }else {
//                 $halfhour_to_pass = 2; //1hr
//             }
//
//             if( $morning_av_cnt >= $halfhour_to_pass) { //if more than 1 hr is available, go 'AV'. If not, 'NA'.
//                 $ret[] = $obj_morning;
//             }else {
//                 $obj_morning->availability = 'NA';
//                 $ret[] = $obj_morning;
//             }
//
//             if( $after_av_cnt >= $halfhour_to_pass) { //if more than 1 hr is available, go 'AV'. If not, 'NA'.
//                 $ret[] = $obj_after;
//             }else {
//                 $obj_after->availability = 'NA';
//                 $ret[] = $obj_after;
//             }
//
//             if( $lateafter_av_cnt >= $halfhour_to_pass) { //if more than 1 hr is available, go 'AV'. If not, 'NA'.
//                 $ret[] = $obj_lateafter;
//             }else {
//                 $obj_lateafter->availability = 'NA';
//                 $ret[] = $obj_lateafter;
//             }
//
//
//
//         }else { //Next available groomer. Add Flexible Times w/ 'AV'.
//             $ret[] = $obj_morning;
//             $ret[] = $obj_after;
//             $ret[] = $obj_lateafter;
//         }
//
////        Log::info('### result of ret in non exclusive area ###', [
////            'ret' => $ret
////        ]);
//        return $ret ;
//    }


    public static function get_groomer_calendar2($groomer_id, $target_date, $user_id = 0, $zip = '') { //Includes Flexible Time
        $today = Carbon::now()->format('Y-m-d');
        $today_nexthours = '';

        if( $target_date == $today ) {
            $today_nexthours = " and date_format(a.stime, '%H%i') >= date_format(  '" . Carbon::now()->addHour(1) . "' ,'%H%i') ";
        }

        if($groomer_id > 0 ){
            $ret = DB::select("
                  select a.id, a.stime, etime + interval 1 minute etime, a.desc,
                  If( b.hour is null , 'NA' , IF( c.appointment_id is not null, 'NA', 'AV') ) availability,
                  f_get_short_name(c.address_id) area_name, 'Specific Time' title, 'specific' type_time, a.hour
                  from times a left join groomer_availability b on a.hour = b.hour
                                        and b.date = :date1 and b.groomer_id = :groomer_id1
			                   left join appointment_list c on c.groomer_id = :groomer_id2
                                        and  date_format(c.accepted_date,'%Y-%m-%d') = :date2
                                        and c.status not in ('C','L')
                                        and date_format(stime,'%H%i') >= date_format(c.accepted_date,'%H%i')
                                        and date_format(stime,'%H%i') < date_format(c.accepted_date + interval (select sum(hour)*60 from appointment_pet where appointment_id = c.appointment_id) minute ,'%H%i')
                   where a.id <= 25 "
                . $today_nexthours .
                " order by a.id ",
                [
                    'groomer_id1' => $groomer_id,
                    'groomer_id2' => $groomer_id,
                    'date1' => $target_date,
                    'date2' => $target_date
                ]);
        }else { //In case of groomer_id of 0, which means 'All day available'.
            $query = "  select a.id, a.stime, etime + interval 1 minute etime, a.desc, 'AV' availability, '' area_name, 'Specific Time' title, 'specific' type_time, a.hour
                  from times a 
                  where a.id <= 25 " .
                $today_nexthours .
                " order by a.id " ;
            $ret = DB::select($query,
                [ ]);
        }


        $obj_morning = new \stdClass();
        $obj_morning->id = 100;
        $obj_morning->stime = '08:00:00';
        $obj_morning->etime = '12:00:00';
        $obj_morning->desc = '08:00am - 12:00pm';
        $obj_morning->availability = 'AV';
        $obj_morning->area_name = '';
        $obj_morning->title = 'Morning';
        $obj_morning->type_time = 'flexible';
        $obj_morning->hour = 8;

        $obj_after = new \stdClass();
        $obj_after->id = 101;
        $obj_after->stime = '12:00:00';
        $obj_after->etime = '16:00:00';
        $obj_after->desc = '12:00pm - 04:00pm';
        $obj_after->availability = 'AV';
        $obj_after->area_name = '';
        $obj_after->title = 'Afternoon';
        $obj_after->type_time = 'flexible';
        $obj_after->hour = 12;

        $obj_lateafter = new \stdClass();
        $obj_lateafter->id = 102;
        $obj_lateafter->stime = '16:00:00';
        $obj_lateafter->etime = '20:00:00';
        $obj_lateafter->desc = '04:00pm - 08:00pm';
        $obj_lateafter->availability = 'AV';
        $obj_lateafter->area_name = '';
        $obj_lateafter->title = 'Late Afternoon';
        $obj_lateafter->type_time = 'flexible';
        $obj_lateafter->hour = 16;

        if( $target_date == $today ) {
            if( Carbon::now()->format('H') >= "11" ) {
                $obj_morning->availability = 'NA';
            }
            if( Carbon::now()->format('H') >= "15" ) {
                $obj_after->availability = 'NA';
            }
            if( Carbon::now()->format('H') >= "19" ) {
                $obj_lateafter->availability = 'NA';
            }
        }

        $address = null;
        $customer_zip = '';

        if($user_id > 0 ){
            $address = Address::where('user_id', $user_id)  //ignore users who has no 'Default_address' setup, becasue there existed 73 users only, as of 0721/2020.
            ->where('status', 'A')
                ->where('default_address', 'Y')
                ->orderBy('address_id', 'desc')
                ->first();
            if(empty($address)) {
                $customer_zip = $zip; //If input ZIP exist.
            }else {
                $customer_zip =$address->zip;
            }
        }else if ( $zip != '' ){
            $customer_zip = $zip;
        }

        $AvailableDays = true; //By default, allow days.

        if($customer_zip != '' ){
            $exclusive_area_ret = DB::select( "select distinct concat(   case IfNull(b.mon,'') when 'Y' then ' 0 ' else ' ' end , " .
                " case IfNull(b.tue,'') when 'Y' then ' 1 ' else ' ' end , " .
                " case IfNull(b.wed,'') when 'Y' then ' 2 ' else ' ' end , " .
                " case IfNull(b.thu,'') when 'Y' then ' 3 ' else ' ' end , " .
                " case IfNull(b.fri,'') when 'Y' then ' 4 ' else ' ' end , " .
                " case IfNull(b.sat,'') when 'Y' then ' 5 ' else ' ' end , " .
                " case IfNull(b.sun,'') when 'Y' then ' 6 ' else ' ' end )  av_days " .
                " from allowed_zip a inner join service_area b on concat( a.county_name, '.', a.state_abbr) = b.area_name " .
                " WHERE a.zip = :customer_zip ",
                [ 'customer_zip' => $customer_zip ] );

            $d = Carbon::parse( $target_date );
            $weekday = Carbon::parse($d)->dayOfWeek ; //1:Monday...0:Sunday, but in DB, 0:Monday, 0 : Tue... 6 Sunday.
            if( $weekday == 0) { //Sunday
                $weekday = 6;
            }else {
                $weekday = $weekday - 1;
            }

            if( !empty($exclusive_area_ret) ) {        ;  //convert weekday into string from int. if not , not work correctly.
                if (strpos($exclusive_area_ret[0]->av_days, $weekday . "" ) === false) { //If not found, not available days.
                    $AvailableDays = false;
                }
            }

//            Log::info('### result of ret in non exclusive area ###', [
//                'exclusive_area_ret[0]->av_days' => $exclusive_area_ret[0]->av_days,
//                'weekday' =>$weekday,
//                'strpos:' => strpos($exclusive_area_ret[0]->av_days, $weekday . ""  )
//            ]);

            if( !$AvailableDays ) { //Not Available for all hours
                $obj_morning->availability = 'NA';
                $obj_after->availability = 'NA';
                $obj_lateafter->availability = 'NA';

                foreach($ret as $aday ) {
                    $aday->availability = 'NA' ;
                }
            }
        }



        $morning_av_cnt = 0;
        $after_av_cnt = 0;
        $lateafter_av_cnt = 0;

        foreach ($ret as $aday) {  //Check how many hours are available for each flexible Times.
            if ($aday->hour < 12) {
                if ($aday->availability == 'AV') {
                    $morning_av_cnt += 1;
                }
            } else if ($aday->hour < 16) {
                if ($aday->availability == 'AV') {
                    $after_av_cnt += 1;
                }
            } else if ($aday->hour < 20) {
                if ($aday->availability == 'AV') {
                    $lateafter_av_cnt += 1;
                }
            }
        }

        if ($target_date == $today) {
            $halfhour_to_pass = 1; //Once exist, In case of Today, it already get after 1hr as of now.
        } else {
            $halfhour_to_pass = 2; //1hr
        }

        if ($morning_av_cnt >= $halfhour_to_pass) { //if more than 1 hr is available, go 'AV'. If not, 'NA'.
            $ret[] = $obj_morning;
        } else {
            $obj_morning->availability = 'NA';
            $ret[] = $obj_morning;
        }
        if ($after_av_cnt >= $halfhour_to_pass) { //if more than 1 hr is available, go 'AV'. If not, 'NA'.
            $ret[] = $obj_after;
        } else {
            $obj_after->availability = 'NA';
            $ret[] = $obj_after;
        }
        if ($lateafter_av_cnt >= $halfhour_to_pass) { //if more than 1 hr is available, go 'AV'. If not, 'NA'.
            $ret[] = $obj_lateafter;
        } else {
            $obj_lateafter->availability = 'NA';
            $ret[] = $obj_lateafter;
        }
        return $ret ;
    }

    //zip/include_eco will not be delivered if it's called from appt edit UI, but it's ok.
    public static function get_groomer_calendar_availability($groomer_id, $user_id = 0, $zip = '', $include_eco='N' ) {

        $address = null;
        $customer_zip = '';
        //$exclusive_area = 0 ;//By default, no Exclusive area.
        $customer_state = ''; //use only when new marketing area in the future, for example Miami from 11/01/2020.

        if($user_id > 0 ){
            $address = Address::where('user_id', $user_id)  //ignore users who has no 'Default_address' setup, becasue there existed 73 users only, as of 0721/2020.
                ->where('status', 'A')
                ->where('default_address', 'Y')
                ->orderBy('address_id', 'desc')
                ->first();
            if(empty($address)) {
                $customer_zip = $zip; //If input ZIP exist.
            }else {
                $customer_zip =$address->zip;
                $customer_state = $address->state;
            }
        }else if ( $zip != '' ){
            $customer_zip = $zip;
        }

        if( isset($groomer_id) && $groomer_id>0){
            if( $include_eco == 'Y'){
                $ret = DB::select(
                    "
 select date_format(a.dt,'%c/%e') dt ,date_format(a.dt,'%Y-%m-%d') full_dt , weekday(dt) weekdy, count(distinct b.hour) hr_cnt , count(distinct c.appointment_id) appt_cnt
 from yyyymmdd a left join groomer_availability b on a.dt = b.date and b.groomer_id = :groomer_id3 and b.hour>= 8 and b.hour < 21
			    left join appointment_list c on a.dt = date_format(c.accepted_date,'%Y-%m-%d') and c.groomer_id = :groomer_id4  and c.status not in ('C','L')
 where a.dt >= curdate() + interval 7 day
 and a.dt < curdate() + interval 22 day
 group by 1, 2, 3
 order by 2",
                    [
                        'groomer_id3' => $groomer_id,
                        'groomer_id4' => $groomer_id
                    ]);
            }else {
                $ret = DB::select(
                    "select date_format(a.dt,'%c/%e') dt ,date_format(a.dt,'%Y-%m-%d') full_dt , weekday(dt) weekdy, count( distinct b.hour) hr_cnt , count(distinct c.appointment_id) appt_cnt
 from yyyymmdd a left join groomer_availability b on a.dt = b.date and b.groomer_id = :groomer_id1 and b.hour>= 8 and b.hour < 21 and b.hour > date_format(  '" . Carbon::now()->addHour(1) . "' ,'%H')
			    left join appointment_list c on a.dt = date_format(c.accepted_date,'%Y-%m-%d') and c.groomer_id = :groomer_id2  and c.status not in ('C','L') and date_format(c.accepted_date , '%H%i') >= date_format(  '" . Carbon::now()->addHour(1) . "' ,'%H%i') 
 where a.dt >= curdate()
 and a.dt < curdate() + interval 1 day
 group by 1, 2, 3
 UNION ALL
 select date_format(a.dt,'%c/%e') dt ,date_format(a.dt,'%Y-%m-%d') full_dt , weekday(dt) weekdy, count(distinct b.hour) hr_cnt , count(distinct c.appointment_id) appt_cnt
 from yyyymmdd a left join groomer_availability b on a.dt = b.date and b.groomer_id = :groomer_id3 and b.hour>= 8 and b.hour < 21
			    left join appointment_list c on a.dt = date_format(c.accepted_date,'%Y-%m-%d') and c.groomer_id = :groomer_id4  and c.status not in ('C','L')
 where a.dt >= curdate() + interval 1 day
 and a.dt < curdate() + interval 15 day
 group by 1, 2, 3
 order by 2",
                    ['groomer_id1' => $groomer_id,
                        'groomer_id2' => $groomer_id,
                        'groomer_id3' => $groomer_id,
                        'groomer_id4' => $groomer_id
                    ]);
            }


            if($customer_zip != '' ){

                $exclusive_area_ret = DB::select( "select distinct concat(   case IfNull(b.mon,'') when 'Y' then ' 0 ' else ' ' end , " .
                                                        " case IfNull(b.tue,'') when 'Y' then ' 1 ' else ' ' end , " .
                                                        " case IfNull(b.wed,'') when 'Y' then ' 2 ' else ' ' end , " .
                                                        " case IfNull(b.thu,'') when 'Y' then ' 3 ' else ' ' end , " .
                                                        " case IfNull(b.fri,'') when 'Y' then ' 4 ' else ' ' end , " .
                                                        " case IfNull(b.sat,'') when 'Y' then ' 5 ' else ' ' end , " .
                                                        " case IfNull(b.sun,'') when 'Y' then ' 6 ' else ' ' end )  av_days " .
                                                        " from allowed_zip a inner join service_area b on concat( a.county_name, '.', a.state_abbr) = b.area_name " .
                                                        " WHERE a.zip = :customer_zip ",
                                                       [ 'customer_zip' => $customer_zip ] );
            }

        }else {
            //Always available if groomer_id does not exist.
            $today = Carbon::now()->format('Y-m-d');

            if( $customer_state == "FL" ){
                if( $include_eco == 'Y') {
                    if ($today < '2020-10-25'){
                        $ret = DB::select(
                            "select a.dt,weekday(a.dt) weekdy,date_format(a.dt,'%Y-%m-%d') full_dt ,  100 hr_cnt , 0 appt_cnt
                      from yyyymmdd a	     
                      where a.dt >= '2020-11-01'
                      and a.dt < '2020-11-15'
                      order by 3",
                            []);
                    }else {
                        $ret = DB::select(
                            "select a.dt,weekday(a.dt) weekdy,date_format(a.dt,'%Y-%m-%d') full_dt ,  100 hr_cnt , 0 appt_cnt
                      from yyyymmdd a	     
                      where a.dt >= curdate() + interval 7 day
                      and a.dt < curdate() + interval 22 day
                      order by 3",
                            []);
                    }

                }else {
                    if ($today < '2020-11-01'){
                        $ret = DB::select(
                            "select a.dt,weekday(a.dt) weekdy,date_format(a.dt,'%Y-%m-%d') full_dt ,  100 hr_cnt , 0 appt_cnt
                      from yyyymmdd a	     
                      where a.dt >= '2020-11-01'
                      and a.dt < '2020-11-16'
                      order by 3",
                            []);
                    }else {
                        $ret = DB::select(
                            "select a.dt,weekday(a.dt) weekdy,date_format(a.dt,'%Y-%m-%d') full_dt ,  100 hr_cnt , 0 appt_cnt
                      from yyyymmdd a	     
                      where a.dt >= curdate() 
                      and a.dt < curdate() + interval 16 day
                      order by 3",
                            []);
                    }
                }
            }else {

                if( $include_eco == 'Y') {
                    $ret = DB::select(
                        "select a.dt,weekday(a.dt) weekdy,date_format(a.dt,'%Y-%m-%d') full_dt ,  100 hr_cnt , 0 appt_cnt
                      from yyyymmdd a	     
                      where a.dt >= curdate() + interval 7 day
                      and a.dt < curdate() + interval 22 day
                      order by 3",
                        []);
                }else {
                    $ret = DB::select(
                        "select a.dt,weekday(a.dt) weekdy,date_format(a.dt,'%Y-%m-%d') full_dt ,  100 hr_cnt , 0 appt_cnt
                      from yyyymmdd a	     
                      where a.dt >= curdate()
                      and a.dt < curdate() + interval 15 day
                      order by 3",
                        []);
                }
            }


            if($customer_zip != '' ){
//                $exclusive_area_ret = DB::select( "select distinct c.weekday
//                                             from exclusive_area a  inner join exclusive_area_detail b on a.alias_id = b.alias_id and b.zip = :customer_zip
//                                             inner join groomer_exclusive_area c on a.alias_id = c.alias_id  ",
//                                          ['customer_zip' => $customer_zip] );
                $exclusive_area_ret = DB::select( "select distinct concat(   case IfNull(b.mon,'') when 'Y' then ' 0 ' else ' ' end , " .
                    " case IfNull(b.tue,'') when 'Y' then ' 1 ' else ' ' end , " .
                    " case IfNull(b.wed,'') when 'Y' then ' 2 ' else ' ' end , " .
                    " case IfNull(b.thu,'') when 'Y' then ' 3 ' else ' ' end , " .
                    " case IfNull(b.fri,'') when 'Y' then ' 4 ' else ' ' end , " .
                    " case IfNull(b.sat,'') when 'Y' then ' 5 ' else ' ' end , " .
                    " case IfNull(b.sun,'') when 'Y' then ' 6 ' else ' ' end ) av_days " .
                    " from allowed_zip a inner join service_area b on concat( a.county_name, '.', a.state_abbr) = b.area_name " .
                    " WHERE a.zip = :customer_zip ",
                    [ 'customer_zip' => $customer_zip ] );
            }
        }

//        $exclusive_area_array = [];
//        if (!empty($exclusive_area_ret)) {
//            foreach ($exclusive_area_ret as $app) {
//                $exclusive_area_array[] = $app->weekday ;
//            }
//        }

        $data = [];
        foreach($ret as $aday ) {
            $avail = '';
            if( $aday->hr_cnt >0) {
                    if( $aday->hr_cnt - ( $aday->appt_cnt * 2 ) >= 2 ){
                        if( !empty($exclusive_area_ret) ) {
                            if( strpos( $exclusive_area_ret[0]->av_days, $aday->weekdy . "" ) != false ) {
                                $avail = 'AV';
                            }else {
                                $avail = 'NA';
                            }
                        }else {
                            $avail = 'AV';
                        }
                    }else {
                        $avail = 'NA';
                    }
            }else {
                $avail = 'NA';
            }

            $data[] = [
                        'date' => $aday->dt,
                        'full_date' => $aday->full_dt,
                        'available' => $avail
                      ];
        }




        return $data ;
    }

    public static function get_dog_pricing( $zone=1 ) {

        $ret = DB::select("
           select
                a.prod_id,
                a.prod_type,
                a.prod_name,
                a.prod_desc,
                a.no_mix,
                b.denom,
                b.min_denom,
                b.max_denom
            from product a  inner join product_denom b on a.prod_id = b.prod_id
            where a.prod_type = 'P'
            and a.status = 'A'
            and ( (a.size_required = 'Y' and b.size_id = 2) or a.size_required = 'N')
            and b.status = 'A'
            and b.group_id = :zone
            and a.pet_type = 'dog'
            and a.prod_id in (1,2,28)
            order by 1
        ",
            ['zone' => $zone]
        );

        $gold   = $ret[0];
        $sliver = $ret[1];
        $eco    = $ret[2];

        return [
            'gold'      => $gold,
            'silver'    => $sliver,
            'eco'       => $eco
        ];
    }

    public static function get_cat_pricing( $zone=1) {

        $ret = DB::select("
           select
                a.prod_id,
                a.prod_type,
                a.prod_name,
                a.prod_desc,
                a.no_mix,
                b.denom,
                b.min_denom,
                b.max_denom
            from product a  inner join product_denom b on a.prod_id = b.prod_id
            where a.prod_type = 'P'
            and a.status = 'A'
            and ( (a.size_required = 'Y' and b.size_id = 2) or a.size_required = 'N')
            and b.status = 'A'
            and b.group_id = :zone
            and a.pet_type = 'cat'
            and a.prod_id in (16,27,29)
            order by 1
        ",
            ['zone' => $zone]
        );

        $gold   = $ret[0];
        $sliver = $ret[1];
        $eco    = $ret[2];

        return [
            'gold'      => $gold,
            'silver'    => $sliver,
            'eco'       => $eco
        ];
    }

    public static function get_dog_addon() {

        $dog_addons = Product::where('prod_type', 'A')
            ->where('status', 'A')
            ->where('pet_type', 'dog')
            ->orderBy('seq', 'asc')
            ->get();

        return [
            'dog_addons' => $dog_addons
        ];
    }

    public static function get_cat_addon() {

        $cat_addons = Product::where('prod_type', 'A')
            ->where('status', 'A')
            ->where('pet_type', 'cat')
            ->orderBy('seq', 'asc')
            ->get();

        return [
            'cat_addons' => $cat_addons
        ];
    }

    public static function send_sms_to_admin($msg, $include_sohel = false) {
        $error = '';

        ### Lars' phone #
        $ret = Helper::send_sms('5515742790', $msg);
        if (!empty($ret)) {
            $error .= $ret . "\n";
        }

        //Mohamed Kapadia
        $ret = Helper::send_sms('9144411184', $msg);
        if (!empty($ret)) {
            $error .= $ret . "\n";
        }

        //Sohel
        if ($include_sohel) {
            $ret = Helper::send_sms('9176738128', $msg);
            if (!empty($ret)) {
                $error .= $ret . "\n";
            }
        }



        ### CS ###
        $ret = Helper::send_sms_to_cs($msg);
        if (!empty($ret)) {
            $error .= $ret .  "\n";
        }

        return $error;
    }

    public static function send_sms_to_cs($message) {
        try {

            $error = '';
            $ret = self::send_sms('9145704272', $message);
            if (!empty($ret)) {
                $error = $ret . "\n";
            }

//            $ret = self::send_sms('9177190390', $message);
//            if (!empty($ret)) {
//                $error .= $ret . "\n";
//            }

            $ret = self::send_sms('6507930213', $message);
            if (!empty($ret)) {
                $error .= $ret . "\n";
            }

            //Zabair
//            $ret = Helper::send_sms('6464417678', $message);
//            if (!empty($ret)) {
//                $error .= $ret . "\n";
//            }

            return $error;
        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function send_sms($to, $message, $from = null) {
        try {
            $sms = new SMS();
            $sms->phone = $to;
            $sms->message = $message;
            $sms->status = 'S';
            $sms->cdate = Carbon::now();
            $sms->save();

            if (getenv('APP_ENV') == 'production') {
                if ( strlen($to) == 10 ) {
                    if (empty($from)) {
                        $ret = Twilio::message('1' . $to, $message);
                    } else {
                        $ret = Twilio::from($from)->message('1' . $to, $message);
                    }

                    if ($ret->status == 'failed') {
                        $sms->status = 'F';
                        $sms->update();
                        return "Failed to send SMS message";
                    }
                }else {
                    return "Not correct Phone Format[$to][$message]" ;
                }
            }

            return "";

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }


    public static function save_message($type, $sender_id, $receiver_id, $appointment_id, $subject, $message) {

        try {

            $m = New Message;

            switch ($type) {
                case 'reminder_groomer':
                    $m->send_method = 'S'; // sms
                    $m->sender_type = 'A'; // admin
                    $m->receiver_type = 'B'; // groomer
                    $m->message_type = 'RM'; // Reminder
                    $sender_id = 19; // system admin
                    break;
                case 'reminder_user':
                    $m->send_method = 'B'; // both
                    $m->sender_type = 'A'; // admin
                    $m->receiver_type = 'A'; //user
                    $m->message_type = 'RM'; // Reminder
                    $sender_id = 19; // system admin
                    break;
                case 'groomer_not_on_the_way':
                    $m->send_method = 'S'; // sms
                    $m->sender_type = 'A'; // admin
                    $m->receiver_type = 'C'; // admin user
                    $m->message_type = 'NO'; // groomer_not_on_the_way
                    $sender_id = 19; // system admin
                    $receiver_id = 19; // system admin
                    break;
                default : //Not used
                    $m->send_method = 'B'; // sms
                    $m->sender_type = 'A'; // admin
                    $m->receiver_type = 'B'; // groomer //U does not exist.
                    $m->message_type = 'N'; // Notification
                    $sender_id = 19; // system admin
                    break;
            }

            $m->sender_id = $sender_id;
            $m->receiver_id = $receiver_id;
            $m->appointment_id = $appointment_id;
            $m->subject = $subject;
            $m->message = $message;
            $m->cdate = Carbon::now();
            $m->save();

            return '';

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function send_appointment_msg(AppointmentList $app){
        try {

            if (empty($app)) {
                throw new \Exception('Invalid appointment ID provided', -1);
            }

            # send notification if groomer has been assigned.
            $groomer = Groomer::where('groomer_id', $app->groomer_id)->first();

            if ($app->status == 'C') {

                if ($groomer) {

                    ### send SMS ###
                    $message = Constants::$message_groomer['Cancelled'];
                    $addr = Address::find($app->address_id);
                    $address = '';
                    if (!empty($addr)) {
                        if(!empty( $addr->address2 ) && ( $addr->address2  != '')) {
                            $address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ' ' . $addr->state . ' ' . $addr->zip;
                        }else {
                            $address = $addr->address1  . ', ' . $addr->city . ' ' . $addr->state . ' ' . $addr->zip;
                        }

                    }
                    $message = str_replace('ADDRESS', $address, $message);
                    $message = str_replace('DATETIME', $app->accepted_date, $message);

                    ## to groomer ##
                    Helper::send_sms($groomer->mobile_phone, $message);

                    ## save message ##
                    $r = New Message;
                    $r->send_method = 'S'; // SMS
                    $r->sender_type = 'U'; // end user
                    $r->sender_id = $app->user_id;
                    $r->receiver_type = 'B'; // groomer
                    $r->receiver_id = $groomer->groomer_id;
                    $r->message_type = 'D';
                    $r->appointment_id = $app->appointment_id;
                    $r->subject = '';
                    $r->message = $message;
                    $r->cdate = Carbon::now();
                    $r->save();


                    ### send email ###

                    ## send email to user ##
                    $user = User::findOrFail($app->user_id);

                    $addr = Address::find($app->address_id);
                    if (!empty($addr)) {
                        if(!empty( $addr->address2 ) && ( $addr->address2  != '')) {
                            $address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state;
                        }else {
                            $address = $addr->address1 . ', ' . $addr->city . ', ' . $addr->state;
                        }

                    }

                    $pets = DB::select("
                        select
                            a.pet_id,
                            c.name as pet_name,
                            c.dob as pet_dob,
                            b.prod_name as package_name,
                            a.amt as price
                        from appointment_pet p
                            inner join appointment_product a on p.appointment_id = a.appointment_id
                            inner join product b on a.prod_id = b.prod_id
                            inner join pet c on p.pet_id = c.pet_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = p.pet_id
                        and b.prod_type = 'P'
                        and b.pet_type = c.type
                    ", [
                        'appointment_id' => $app->appointment_id
                    ]);

                    $subject = 'Your scheduled appointment has been cancelled';

                    $data = [];
                    $data['subject'] = $subject;
                    $data['address'] = $address;
//                    $data['referral_code'] = $user->referral_code;
                    $data['groomer'] = $groomer->first_name . ' ' . $groomer->last_name; // temp.

                    foreach ($pets as $k=>$v) {
                        $data['pet'][$k]['pet_name'] = $v->pet_name;
                        $data['pet'][$k]['package_name'] = $v->package_name;
                    }

                    if ($app->accepted_date instanceof DateTime) {
                        $data['accepted_date'] = $app->accepted_date->format('l, F j Y, h:i A');
                    } else {
                        $data['accepted_date'] = $app->accepted_date;
                    }

                    # send email to Groomer #
                    $data['name'] = $groomer->first_name;
                    $data['email'] = $groomer->email;
                    $data['user'] = $user->first_name . ' ' . $user->last_name;

                    $ret_groomer = Helper::send_html_mail('appointment_cancelled_for_groomer', $data);
                    if (!empty($ret_groomer)) {
                        $msg = 'Failed to send appointment cancellation email for groomer: ' . $data['name']. ' / ' .$data['email'];
                        return $msg;
                    }

                    # send email to User #
                    $data['name'] = $user->first_name;
                    $data['email'] = $user->email;

                    $referral_arr = UserProcessor::get_referral_code($user->user_id);
                    $data['referral_code'] = $referral_arr['referral_code'];
                    $data['referral_amount'] = $referral_arr['referral_amount'];

                    $ret = Helper::send_html_mail('appointment_cancelled', $data);
                    if (!empty($ret)) {
                        $msg = 'Failed to send appointment cancellation email: ' . $data['name']. ' / ' . $data['email'];
                        return $msg;
                    }

                    ## send SMS ##
                    if (!empty($user->phone)) {
                        $user_message = 'Your scheduled Groomit appointment has been successfully canceled.';
                        $ret = Helper::send_sms($user->phone, $user_message);
                        if (!empty($ret)) {
                            //throw new \Exception('End-User SMS Error: ' . $ret);
                            Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $user_message . '/ Appointment ID:' . $app->appointment_id);
                        }
                        Message::save_sms_to_user($user_message, $user, $app->appointment_id);
                    }
                    ### end send SMS to end user ###
                }

                ### send text ###

                $reason = (!empty($app->note)) ? ' (Reason: ' . $app->note . ')' : '';
                $msg = 'Appointment ID : ' . $app->appointment_id . ' cancelled' . $reason;
                $err_msg = '[GROOMIT][' . getenv('APP_ENV') . '] Cancellation SMS Error - Appointment ID : ' . $app->appointment_id;

                if (getenv('APP_ENV') == 'production') {
                    $ret = Helper::send_sms_to_admin($msg, true);
                    if (!empty($ret)) {
                        Helper::send_mail('tech@groomit.me', $err_msg, $ret);
                    }
                }

                ### .send text ###
                return '';
            }

        } catch (\Exception $ex) {

            return  $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public static function get_meta_title() {

        if (request()->is('about-us')) {
            return 'About us - Grooming your dog at home';
        } else if(request()->is('investors')) {
            return 'Investors - pet grooming at home New Jersey';
        } else if(request()->is('affiliate')) {
            return 'Affiliate – Dog Grooming to your house and Hair Cut in NYC';
        } else if(request()->is('affiliate/apply')) {
            return 'Affiliate Program – Apply';
        } else if(request()->is('affiliate/login')) {
            return 'Login Detail – Groomit';
        } else if(request()->is('affiliate/forgot-password/verify-email')) {
            return 'Forgot Password – Verify Email';
        } else if(request()->is('faqs')) {
            return 'Frequently Asked Question - Best dog groomers – House Call Dog Grooming';
        } else if(request()->is('application')) {
            return 'Application – Apply Now';
        } else if(request()->is('user')) {
            return 'Schedule a cat or dog appointment with Groomit';
        } else if(request()->is('user/gift-cards')) {
            return 'The grooming present for any dog and cat owner';
        } else if(request()->is('user/memberships')) {
            return 'Groomit Memberships';
        };

        return 'Groomit App Offers Pet Grooming On Demand In NYC - NJ - Miami';
    }

    public static function get_meta_description() {

        if (request()->is('about-us')) {
            return 'Groomit is the first mobile platform app connecting pet owners and groomers. Grooming your dog at home with Groomit.';
        } else if(request()->is('investors')) {
            return 'Groomit is the first on demand platform for connecting pet owners for their pet grooming at home New York. Our Business model also provides opportunities for investors.';
        } else if(request()->is('affiliate')) {
            return 'Sign up for the Groomit Affiliate Program. We also provide dog grooming at your house and Hair Cut in NYC.';
        } else if(request()->is('affiliate/apply')) {
            return 'Apply for Affiliate program submit your details including your first and last name business name and address.';
        } else if(request()->is('affiliate/login')) {
            return 'Groomit Affiliate Program Login details. Include username and pass.';
		} else if(request()->is('affiliate/forgot-password/verify-email')) {
            return 'Groomit Affiliate Program Verify email to recover your password.';
        } else if(request()->is('faqs')) {
            return 'Frequently Asked Question about in home dog grooming app  for best dog groomers in house call for grooming.';
        } else if(request()->is('application')) {
            return 'Welcome to the nations first In-Home Pet Grooming Service App on Demand. Make money on your own schedule - Be your own boss. Insurance provided, Earn more money with less hassle. Keep 100% of all your tips.';
        } else if(request()->is('user')) {
            return 'Groomit offers top quality services at affordable prices, all performed in the convenience and comfortability of your home.';
        } else if(request()->is('user/gift-cards')) {
            return 'Groomit Gift Card is the perfect present for any dog or cat owner. Get It For Your Pet Or Send It As Gift.';
        } else if(request()->is('user/memberships')) {
            return 'Save on your yearly pet grooming needs with Groomit Memberships. Get It For Your Pet Or Send It As Gift.';
        };

        return 'Groomit Offers In Home Best Dog & Cat Grooming in New Jersey, New York NYC and Miami. Pet Grooming App On Demand At Your Time and Place.';
    }

    public static function get_meta_keyword() {

        if (request()->is('about-us')) {
            return 'grooming your dog at home';
        } else if(request()->is('investors')) {
            return 'pet grooming at home New Jersey and New York';
        } else if(request()->is('affiliate')) {
            return 'dog hair cut nyc, dog grooming to your house';
        } else if(request()->is('faqs')) {
            return 'Best dog groomers, House Call Dog Grooming';
        } else if(request()->is('application')) {
            return 'Application';
        };

        return 'in home dog grooming New Jersey, in home dog grooming nyc, best dog grooming in New York city, in home pet grooming, dog grooming app';
    }

    public static function get_meta_canonical() {

        if (request()->is('about-us')) {
            return 'https://www.groomit.me/about-us';
        } else if(request()->is('affiliate')) {
            return 'https://www.groomit.me/affiliate';
        } else if(request()->is('application')) {
            return 'https://www.groomit.me/application';
        } else if(request()->is('faqs')) {
            return 'https://www.groomit.me/faqs';
        } else if(request()->is('investors')) {
            return 'https://www.groomit.me/investors';
        } else if(request()->is('affiliate/apply')) {
            return 'https://www.groomit.me/affiliate/apply';
        } else if(request()->is('affiliate/login')) {
            return 'https://www.groomit.me/affiliate/login';
        } else if(request()->is('affiliate/forgot-password/verify-email')) {
            return 'https://www.groomit.me/affiliate/forgot-password/verify-email';
        } else if(request()->is('user')) {
            return 'https://www.groomit.me/user';
		} else if(request()->is('user/gift-cards')) {
            return 'https://www.groomit.me/user/gift-cards';
        } else if(request()->is('user/memberships')) {
            return 'https://www.groomit.me/user/memberships';
        };

        return 'https://www.groomit.me/';
    }

    public static function app_buttons(AppointmentList $app, $allowed_admin) {
        $html = '';
        switch ($app->status) {
            case 'N':   ### Groomer Not Assigned Yet ###
                ### Cancel ###
                $html .= '<div class="btn-right btn btn-warning status-button" onclick="change_status(' . $app->appointment_id . ',\'C\')">Cancel</div>';

                ### Send New Notifications ###
                $html .= '<div class="btn-right btn btn-success status-button" id="reminder" onclick="send_renotification()">Re-Notification to Groomers</div>';

                ### Assign Groomer ###
                $html .= '<div class="btn-right btn btn-info status-button" data-toggle="modal" data-target="#assign_groomer">Assign Groomer</div>';

                ### Change Requested Time ###
                if (\App\Lib\Helper::get_action_privilege('appointment_change_requested_time', 'Appointment Detail Change Requested Time')){
                    $html .= '<div class="btn-right btn btn-success status-button" data-toggle="modal" data-target="#change_requested_date" >Change Requested Time</div>';
                }
                break;
            case 'D':   ### Groomer Assigned###
                ### Cancel With Fee ###
                $html .= '<div class="btn-right btn btn-danger status-button" onclick="cancel_with_fee()">Cancel With Fee</div>';

                ### Cancel ###
                $html .= '<div class="btn-right btn btn-warning status-button" onclick="change_status(' . $app->appointment_id . ',\'C\')">Cancel</div>';

                ### Send New Notifications ###
                $html .= '<div class="btn-right btn btn-success status-button" id="reminder" onclick="send_renotification()">Re-Notification to Groomers</div>';

                ### Assign Groomer ###
                $html .= '<div class="btn-right btn btn-info status-button" data-toggle="modal" data-target="#assign_groomer">Change Groomer/Date</div>';

                ### Groomer on the way ###
                $html .= '<div class="btn-right btn btn-warning status-button" id="groomer_on_the_way" onclick="groomer_on_the_way(' . $app->appointment_id . ')">Groomer On The Way</div>';

                ### Work In Progress ###
                $html .= '<div class="btn-right btn btn-warning status-button" onclick="change_status(' . $app->appointment_id . ',\'W\')">Work In Progress</div>';

                ### Send Reminder ###
                //$html .= '<div class="btn-right btn btn-success status-button" id="reminder" onclick="send_reminder()">Send Reminder</div>';


                break;
            case 'O':   ### Groomer On The Way ###
                ### Assign Groomer ###
                $html .= '<div class="btn-right btn btn-info status-button" data-toggle="modal" data-target="#assign_groomer">Assign New Groomer</div>';

                ### Cancel With Fee ###
                $html .= '<div class="btn-right btn btn-danger status-button" onclick="cancel_with_fee()">Cancel With Fee</div>';

                ### Cancel ###
                $html .= '<div class="btn-right btn btn-warning status-button" onclick="change_status(' . $app->appointment_id . ',\'C\')">Cancel</div>';

                ### Work Completed ###
                $html .= '<div class="btn-right btn btn-warning status-button" onclick="change_status(' . $app->appointment_id . ',\'S\')">Work Completed</div>';

                ### Work In Progress ###
                $html .= '<div class="btn-right btn btn-warning status-button" onclick="change_status(' . $app->appointment_id . ',\'W\')">Work In Progress</div>';

                ### Send Reminder ###
                //$html .= '<div class="btn-right btn btn-success status-button" id="reminder" onclick="send_reminder()">Send Reminder</div>';
                break;
            case 'W':   ### Work In Progress ###

                ### Cancel With Fee ###
                $html .= '<div class="btn-right btn btn-danger status-button" onclick="cancel_with_fee()">Cancel With Fee</div>';

                ### Cancel ###
                $html .= '<div class="btn-right btn btn-warning status-button" onclick="change_status(' . $app->appointment_id . ',\'C\')">Cancel</div>';
                
                ### Work Completed ###
                $html .= '<div class="btn-right btn btn-warning status-button" onclick="change_status(' . $app->appointment_id . ',\'S\')">Work Completed</div>';
                break;
            case 'R':   ### Failed to Hold amount. Please retry after updating user credit card. ###
                ### Assign Groomer ###
                $html .= '<div class="btn-right btn btn-info status-button" data-toggle="modal" data-target="#assign_groomer">Assign Groomer</div>';

                ### Groomer on the way ###
                $html .= '<div class="btn-right btn btn-warning status-button" id="groomer_on_the_way" onclick="groomer_on_the_way(' . $app->appointment_id . ')">Groomer On The Way</div>';
                break;
            case 'C':   ### Cancelled ###
//                ### Reschedule ###
//                $html .= '<div class="btn-right btn btn-info status-button" onclick="change_status(' . $app->appointment_id . ',\'L\')">Mark as Rescheduled</div>';
                break;
            case 'P':  ### payment completed ###
                if ($allowed_admin) {
                    $html .= '<div class="btn-right btn btn-info status-button" id="send_service_completion_email" onclick="send_service_completion_email()">Send Service Completion Email</div>';
                }
                break;
            case 'F':
                ### Work Completed ###
                $html .= '<div class="btn-right btn btn-warning status-button" onclick="change_status(' . $app->appointment_id . ',\'S\')">Work Completed</div>';
                break;
        }

        return $html;
    }

    public static function arrayPaginator($array, $request)
    {
        $page = Input::get('page', 1);
        $perPage = 50;
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]);
    }


    
    public static function get_states() {
        $states_string = '[
            { "code": "AL", 	"name": "ALABAMA" },
            { "code": "AK", 	"name": "ALASKA" },
            { "code": "AZ", 	"name": "ARIZONA" },
            { "code": "AR", 	"name": "ARKANSAS" },
            { "code": "CA", 	"name": "CALIFORNIA" },
            { "code": "CO", 	"name": "COLORADO" },
            { "code": "CT", 	"name": "CONNECTICUT" },
            { "code": "DE", 	"name": "DELAWARE" },
            { "code": "DC", 	"name": "DISTRICT OF COLUMBIA" },
            { "code": "FL", 	"name": "FLORIDA" },
            { "code": "GA", 	"name": "GEORGIA" },
            { "code": "HI", 	"name": "HAWAII" },
            { "code": "ID", 	"name": "IDAHO" },
            { "code": "IL", 	"name": "ILLINOIS" },
            { "code": "IN", 	"name": "INDIANA" },
            { "code": "IA", 	"name": "IOWA" },
            { "code": "KS", 	"name": "KANSAS" },
            { "code": "KY", 	"name": "KENTUCKY" },
            { "code": "LA", 	"name": "LOUISIANA" },
            { "code": "ME", 	"name": "MAINE" },
            { "code": "MD", 	"name": "MARYLAND" },
            { "code": "MA", 	"name": "MASSACHUSETTS" },
            { "code": "MI", 	"name": "MICHIGAN" },
            { "code": "MN", 	"name": "MINNESOTA" },
            { "code": "MS", 	"name": "MISSISSIPPI" },
            { "code": "MO", 	"name": "MISSOURI" },
            { "code": "MT", 	"name": "MONTANA" },
            { "code": "NE", 	"name": "NEBRASKA" },
            { "code": "NV", 	"name": "NEVADA" },
            { "code": "NH", 	"name": "NEW HAMPSHIRE" },
            { "code": "NJ", 	"name": "NEW JERSEY" },
            { "code": "NM", 	"name": "NEW MEXICO" },
            { "code": "NY", 	"name": "NEW YORK" },
            { "code": "NC", 	"name": "NORTH CAROLINA" },
            { "code": "ND", 	"name": "NORTH DAKOTA" },
            { "code": "OH", 	"name": "OHIO" },
            { "code": "OK", 	"name": "OKLAHOMA" },
            { "code": "OR", 	"name": "OREGON" },
            { "code": "PA", 	"name": "PENNSYLVANIA" },
            { "code": "RI", 	"name": "RHODE ISLAND" },
            { "code": "SC", 	"name": "SOUTH CAROLINA" },
            { "code": "SD", 	"name": "SOUTH DAKOTA" },
            { "code": "TN", 	"name": "TENNESSEE" },
            { "code": "TX", 	"name": "TEXAS" },
            { "code": "UT", 	"name": "UTAH" },
            { "code": "VT", 	"name": "VERMONT" },
            { "code": "VI", 	"name": "VIRGIN ISLANDS" },
            { "code": "VA", 	"name": "VIRGINIA" },
            { "code": "WA", 	"name": "WASHINGTON" },
            { "code": "WV", 	"name": "WEST VIRGINIA" },
            { "code": "WI", 	"name": "WISCONSIN" },
            { "code": "WY", 	"name": "WYOMING" }
        ]';

        return json_decode($states_string);
    }

    public static function get_time_windows() {
//        $time_window_string = '[
//            {"id": "0", "start": "08:00:00", "time": "08:00am - 09:00am"},
//            {"id": "1", "start": "08:30:00", "time": "08:30am - 09:30am"},
//            {"id": "2", "start": "09:00:00", "time": "09:00am - 10:00am"},
//            {"id": "3", "start": "09:30:00", "time": "09:30am - 10:30am"},
//            {"id": "4", "start": "10:00:00", "time": "10:00am - 11:00am"},
//            {"id": "5", "start": "10:30:00", "time": "10:30am - 11:30am"},
//            {"id": "6", "start": "11:00:00", "time": "11:00am - 12:00pm"},
//            {"id": "7", "start": "11:30:00", "time": "11:30am - 12:30pm"},
//            {"id": "8", "start": "12:00:00", "time": "12:00pm - 01:00pm"},
//            {"id": "9", "start": "12:30:00", "time": "12:30pm - 01:30pm"},
//            {"id": "10", "start": "13:00:00", "time": "01:00pm - 02:00pm"},
//            {"id": "11", "start": "13:30:00", "time": "01:30pm - 02:30pm"},
//            {"id": "12", "start": "14:00:00", "time": "02:00pm - 03:00pm"},
//            {"id": "13", "start": "14:30:00", "time": "02:30pm - 03:30pm"},
//            {"id": "14", "start": "15:00:00", "time": "03:00pm - 04:00pm"},
//            {"id": "15", "start": "15:30:00", "time": "03:30pm - 04:30pm"},
//            {"id": "16", "start": "16:00:00", "time": "04:00pm - 05:00pm"},
//            {"id": "17", "start": "16:30:00", "time": "04:30pm - 05:30pm"},
//            {"id": "18", "start": "17:00:00", "time": "05:00pm - 06:00pm"},
//            {"id": "19", "start": "17:30:00", "time": "05:30pm - 06:30pm"},
//            {"id": "20", "start": "18:00:00", "time": "06:00pm - 07:00pm"},
//            {"id": "21", "start": "18:30:00", "time": "06:30pm - 07:30pm"},
//            {"id": "22", "start": "19:00:00", "time": "07:00pm - 08:00pm"},
//            {"id": "23", "start": "19:30:00", "time": "07:30pm - 08:30pm"},
//            {"id": "24", "start": "20:00:00", "time": "08:00pm - 09:00pm"}
//        ]';
        $time_window_string = '[
            {"id": "0", "title": "Specific Time", "start": "08:00:00", "end": "09:00:00", "time": "08:00am - 09:00am"},
            {"id": "1", "title": "Specific Time", "start": "08:30:00", "end": "09:30:00", "time": "08:30am - 09:30am"},
            {"id": "2", "title": "Specific Time", "start": "09:00:00", "end": "10:00:00", "time": "09:00am - 10:00am"},
            {"id": "3", "title": "Specific Time", "start": "09:30:00", "end": "09:30:00", "time": "09:30am - 10:30am"},
            {"id": "4", "title": "Specific Time", "start": "10:00:00", "end": "11:00:00", "time": "10:00am - 11:00am"},
            {"id": "5", "title": "Specific Time", "start": "10:30:00", "end": "11:30:00", "time": "10:30am - 11:30am"},
            {"id": "6", "title": "Specific Time", "start": "11:00:00", "end": "12:00:00", "time": "11:00am - 12:00pm"},
            {"id": "7", "title": "Specific Time", "start": "11:30:00", "end": "12:30:00", "time": "11:30am - 12:30pm"},
            {"id": "8", "title": "Specific Time", "start": "12:00:00", "end": "13:00:00", "time": "12:00pm - 01:00pm"},
            {"id": "9", "title": "Specific Time", "start": "12:30:00", "end": "13:30:00", "time": "12:30pm - 01:30pm"},
            {"id": "10", "title": "Specific Time", "start": "13:00:00", "end": "14:00:00", "time": "01:00pm - 02:00pm"},
            {"id": "11", "title": "Specific Time", "start": "13:30:00", "end": "14:30:00", "time": "01:30pm - 02:30pm"},
            {"id": "12", "title": "Specific Time", "start": "14:00:00", "end": "15:00:00", "time": "02:00pm - 03:00pm"},
            {"id": "13", "title": "Specific Time", "start": "14:30:00", "end": "15:30:00", "time": "02:30pm - 03:30pm"},
            {"id": "14", "title": "Specific Time", "start": "15:00:00", "end": "16:00:00", "time": "03:00pm - 04:00pm"},
            {"id": "15", "title": "Specific Time", "start": "15:30:00", "end": "16:30:00", "time": "03:30pm - 04:30pm"},
            {"id": "16", "title": "Specific Time", "start": "16:00:00", "end": "17:00:00", "time": "04:00pm - 05:00pm"},
            {"id": "17", "title": "Specific Time", "start": "16:30:00", "end": "17:30:00", "time": "04:30pm - 05:30pm"},
            {"id": "18", "title": "Specific Time", "start": "17:00:00", "end": "18:00:00", "time": "05:00pm - 06:00pm"},
            {"id": "19", "title": "Specific Time", "start": "17:30:00", "end": "18:30:00", "time": "05:30pm - 06:30pm"},
            {"id": "20", "title": "Specific Time", "start": "18:00:00", "end": "19:00:00", "time": "06:00pm - 07:00pm"},
            {"id": "21", "title": "Specific Time", "start": "18:30:00", "end": "19:30:00", "time": "06:30pm - 07:30pm"},
            {"id": "22", "title": "Specific Time", "start": "19:00:00", "end": "20:00:00", "time": "07:00pm - 08:00pm"},
            {"id": "23", "title": "Specific Time", "start": "19:30:00", "end": "20:30:00", "time": "07:30pm - 08:30pm"},
            {"id": "24", "title": "Specific Time", "start": "20:00:00", "end": "21:00:00", "time": "08:00pm - 09:00pm"},
            {"id": "100", "title": "Morning",        "start": "08:00:00", "end": "12:00:00", "time": "08:00am - 12:00pm"},
            {"id": "101", "title": "Afternoon",      "start": "12:00:00", "end": "16:00:00", "time": "12:00pm - 04:00pm"},
            {"id": "102", "title": "Late Afternoon", "start": "16:00:00", "end": "18:00:00", "time": "04:00pm - 08:00pm"}
        ]'; 
        return json_decode($time_window_string);
    }

    //This will be used in edit.blade.php only to pre-select date/time.
    //So it will be pre-selected to Specific hours only, not morning/afternoon/Late afternoon.
    public static function get_time_by_value($time_string, $reserved_at='') { //When reserved_at has value, find it first, because there's no 'accepted_date yet.
        $time_windows = self::get_time_windows();
        $time = Carbon::parse($time_string);

        if($reserved_at != '') {
            foreach ($time_windows as $o) {
                if (strpos($reserved_at, $o->time)) { //Try to find it from reserved_at first.
                    return $o;
                }
            }
        }else {
            foreach ($time_windows as $o) {

                //If not found, try to find through $time_string, but no possibility with it.
                $start = Carbon::parse($o->start);
                $end_o = self::get_time_by_id($o->id + 1);
                if (empty($end_o)) {
                    if ($start->lte($time)) {
                        return $o;
                    }
                } else {
                    $end = Carbon::parse($end_o->start);
                    if ($start->lte($time) && $end->gt($time)) {
                        return $o;
                    }
                }
            }
        }


        return null;
    }

    public static function get_time_by_id($id) {
        $time_windows = self::get_time_windows();
        foreach ($time_windows as $o) {
            if ($o->id == $id) {
                return $o;
            }
        }

        return null;
    }

    public static function get_time_windows_by_date($date) {
        $time_windows = self::get_time_windows();

        $date = Carbon::parse($date);
        if ($date->isToday()) {
            //$min_time = Carbon::now();
            $min_time = Carbon::now()->addMinutes(30);
            $new_windows = [];
            foreach ($time_windows as $o) {
                $window = Carbon::createFromFormat('Y-m-d H:i:s', $min_time->format('Y-m-d') . ' ' . $o->start);
                if ($window >= $min_time) {
                    $new_windows[] = $o;
                }
            }

            return $new_windows;
        }

        return $time_windows;
    }

    public static function get_expire_months() {
        return [
            ['code' => '01', 'name' => '01'],
            ['code' => '02', 'name' => '02'],
            ['code' => '03', 'name' => '03'],
            ['code' => '04', 'name' => '04'],
            ['code' => '05', 'name' => '05'],
            ['code' => '06', 'name' => '06'],
            ['code' => '07', 'name' => '07'],
            ['code' => '08', 'name' => '08'],
            ['code' => '09', 'name' => '09'],
            ['code' => '10', 'name' => '10'],
            ['code' => '11', 'name' => '11'],
            ['code' => '12', 'name' => '12']
        ];
    }

    public static function get_expire_years() {
        $current_year = date('Y');
        $years = [];
        for ($i=0; $i<10; $i++) {
            $year = $current_year + $i;
            $years[] = [
                'code' => $year - 2000,
                'name' => $year
            ];
        }

        return $years;
    }

    public static function generate_code($length) {

        //$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@$%&";
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $referral_code = '';
        for ($i = 0; $i < $length; $i++) {
            $referral_code .= $chars{mt_rand(0, strlen($chars)-1)};
        }

        return $referral_code;
    }

    public static function appointment_paid($appointment_id) {

        //Consider S/V only, not A.
        $cctrans = CCTrans::where('appointment_id', $appointment_id)
          ->whereIn('type', [ 'S', 'V'])
          ->where('category', 'S')
          ->where('result', 0)
          //->whereNull('void_date')
          ->where('amt', '!=', 0.01)
          ->sum( \Illuminate\Support\Facades\DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );

        if ( empty($cctrans) || ($cctrans == 0) ) {
            return false;
        }

        return true;
    }

    public static function get_privileges() {
        $u = \Auth::guard('admin')->user();

        $privileges = AdminPrivilege::where('group', $u->group)->get();
        if (empty($privileges)) return [];

        $result = [];
        foreach ($privileges as $privilege) {
            $result[$privilege->url] = $privilege->type;
        }

        return $result;
    }

    public static function get_privilege($group, $url) {
//        $privilege = AdminPrivilege::where('group', $group)
//            ->whereRaw("'" . $url . "' like CONCAT('%', url ,'%')")
//            ->first();
//
//        if (empty($privilege)) return 'W';
//
//        return $privilege->type;

        return 'W';
    }


    public static function get_action_privilege($code, $label = null) {
        $u = \Auth::guard('admin')->user();

        $privilege = AdminPrivilege::where('group', $u->group)
            ->whereRaw("'" . $code . "' like CONCAT('%', url ,'%')")
            ->first();

        if (empty($privilege)) {
            if (!empty($label)) {
                $privilege = AdminPrivilegeAction::where('url', $code)->first();
                if (empty(!empty($privilege))) {
                    $privilege = new AdminPrivilegeAction();
                    $privilege->type ='B';
                    $privilege->label = $label;
                    $privilege->url = $code;
                    $privilege->cdate = Carbon::now();
                    $privilege->status = 'A';
                    $privilege->save();
                }
            }

            return true;
        }

        return $privilege->type != 'D' ? true : false;
    }

    public static function is_favorite_groomer($user_id, $groomer_id) {
        $fav = UserFavoriteGroomer::where('user_id', $user_id)->where('groomer_id', $groomer_id)->first();

        if (empty($fav)) return false;

        return true;
    }

    public static function get_appointment_by_date_time($rdate, $time_id, $status = 'N') {
        return DB::select("
            select ap.*, a.zip
              from vw_appointment_daytimely ap 
              join address a on ap.address_id = a.address_id
             where ap.rdate = :rdate
               and ap.time_id = :time_id 
               and ap.status = :status
        ", [
            'rdate' => $rdate,
            'time_id'  => $time_id,
            'status' => $status
        ]);
    }

    public static function get_avg_rating($groomer_id) {

        $rating = AppointmentList::where('groomer_id', $groomer_id)
            ->where('status', 'P')
            ->whereNotNull('rating')
            ->avg('rating');

        return round($rating);
    }

    public static function get_num_groom($groomer_id) {

        $user_id = Auth::guard('user')->user()->user_id;

        $num = AppointmentList::where('groomer_id', $groomer_id)
            ->where('user_id', $user_id)
            ->where('status', 'P')
            ->count();

        return $num;
    }

//Not working.
//    public static function twilio_log()
//    {
//
//        /* Your Twilio account sid and auth token */
//
//        $account_sid = "AC8f1af8519c6374fe3ad941bbf5fe54b5";
//        $auth_token = "4478fb6776fd6042e06b7029d4b75d3c";
//
//        /* Download data from Twilio API */
//        $client = new Client($account_sid, $auth_token);
//        $messages = $client->messages->stream(
//            array(
//                'dateSentAfter' => '2020-01-13',
//                'dateSentBefore' => '2015-01-14'
//            )
//        );
//
//        /* Browser magic */
//        $filename = $account_sid . "_sms.csv";
//        header("Content-Type: application/csv");
//        header("Content-Disposition: attachment; filename={$filename}");
//
//        /* Write headers */
//        $fields = array('SMS Message SID', 'From', 'To', 'Date Sent', 'Status', 'Direction', 'Price', 'Body');
//        echo '"' . implode('","', $fields) . '"' . "\n";
//
//        /* Write rows */
//        foreach ($messages as $sms) {
//            $row = array(
//                $sms->sid,
//                $sms->from,
//                $sms->to,
//                $sms->dateSent->format('Y-m-d H:i:s'),
//                $sms->status,
//                $sms->direction,
//                $sms->price,
//                $sms->body
//            );
//
//            echo '"' . implode('","', $row) . '"' . "\n";
//        }
//    }
}
