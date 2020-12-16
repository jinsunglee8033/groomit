<?php

namespace App\Lib;


use App\Lib\Helper;

class Applozic {
// Documents :
//https://docs.applozic.com/reference#update-user
    public static function get_user_detail($user_id )
    {
        try {

            $data = [
                "userIdList" => [ $user_id ]
            ];

            $data_string = json_encode($data);

            $curl = curl_init();
            curl_setopt( $curl, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/user/v2/detail' );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, [
                'Application-Key: 6390f1d46b341e65215b139cb2b15fd7',
                "Authorization: Basic " . base64_encode('TechBot:Yonkers1091@' ),
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            ] );

            curl_setopt( $curl, CURLINFO_HEADER_OUT, true);
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_PORT , 443);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt($curl, CURLOPT_TIMEOUT, 600);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $result = curl_exec($curl);
            $info = curl_getinfo($curl);

            $error = curl_errno($curl);
            curl_close($curl);

            Helper::log('### get_user_detail response ###', [
                'request'   => $data_string,
                'result'    => $result
            ]);

            if ($error) {
                switch ($http_code = $info['http_code']) {
                    case 200:  # OK
                    case 201:
                        break;
                    default:
                        return [
                            'msg' => 'Error : ' . $http_code
                        ];
                }
            }

            $res = json_decode($result);
            Helper::log('### get_user_detail response-decoded ###', [
                'decoded result'    => $res
            ]);
            var_dump( $res );

            //"decoded result":"[object] (stdClass: {\"status\":\"success\",\"generatedAt\":1579137680401,\"response\":[{\"userId\":\"60\",\"userName\":\"Charles Walker        \",\"connected\":false,\"status\":0,\"lastSeenAtTime\":1579113019956,\"createdAtTime\":1554596577143,\"unreadCount\":2,\"displayName\":\"Charles Walker        \",\"deactivated\":true,\"connectedClientCount\":0,\"active\":true,\"lastLoggedInAtTime\":1556403495860,\"roleKey\":\"09a9238c-1fe9-40d9-b90c-4efd090ba4a3\",\"metadata\":{},\"roleType\":3}]})"

//            $code       = $res->Code;
//            $message    = $res->Message;
//            $esn        = $res->esn;
//
//            if($code != 1){
//                return [
//                    'code'      => $code,
//                    'message'   => $message,
//                    'esn'       => $esn,
//                    'result'    => $result
//                ];
//            } else {
//                return [
//                    'code'      => $code,
//                    'message'   => $message,
//                    'esn'       => $esn,
//                    'result'    => $result
//                ];
//            }

        } catch (\Exception $ex) {
            return [
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ];
        }
    }

    //Should be GET Method, not POST
    //Of-user-id is required in header.
    public static function block_user( $user_id, $block_type='B' )
    {
        try {

//            $data = [
//                "userId" => [ $user_id ]
//            ];
//
//            $data_string = json_encode($data);

            $curl = curl_init();
            if( $block_type =='B') {
                curl_setopt( $curl, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/user/block?userId=' . $user_id );
            }else { //In case of 'U' : Unblock
                curl_setopt( $curl, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/user/unblock?userId=' . $user_id );
            }

            curl_setopt( $curl, CURLOPT_HTTPHEADER, [
                'Application-Key: 6390f1d46b341e65215b139cb2b15fd7',
                "Authorization: Basic " . base64_encode('TechBot:Yonkers1091@' ),
                //'Content-Type: application/json',
                "Of-User-Id : 49"                            //Lars, who ordered block a user.
                //'Content-Length: ' . strlen($data_string)
            ] );

            curl_setopt( $curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_PORT , 443);
            curl_setopt($curl, CURLOPT_POST, 0);
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt($curl, CURLOPT_TIMEOUT, 600);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $result = curl_exec($curl);
            $info = curl_getinfo($curl);

            $error = curl_errno($curl);
            curl_close($curl);

            Helper::log('### block_user response ###', [
                //'request'   => $data_string,
                'result'    => $result
            ]);

            if ($error) {
                switch ($http_code = $info['http_code']) {
                    case 200:  # OK
                    case 201:
                        break;
                    default:
                        return [
                            'msg' => 'Error : ' . $http_code
                        ];
                }
            }

            $res = json_decode($result);
            Helper::log('### block_user response-decoded ###', [
                'decoded result'    => $res
            ]);
            var_dump( $res );
            //{\"status\":\"success\",\"generatedAt\":1579192322991,\"response\":\"success\"})"}
            //{\"status\":\"error\",\"errorResponse\":[{\"errorCode\":\"AL-MA-01\",\"description\":\"method not allowed\",\"displayMessage\":\"Request method \\u0027POST\\u0027 not supported\"}],\"generatedAt\":1579138408927}"


//            $code       = $res->Code;
//            $message    = $res->Message;
//            $esn        = $res->esn;
//
//            if($code != 1){
//                return [
//                    'code'      => $code,
//                    'message'   => $message,
//                    'esn'       => $esn,
//                    'result'    => $result
//                ];
//            } else {
//                return [
//                    'code'      => $code,
//                    'message'   => $message,
//                    'esn'       => $esn,
//                    'result'    => $result
//                ];
//            }

        } catch (\Exception $ex) {
            return [
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ];
        }
    }

    //Should be GET Method, not POST
    //Of-user-id is required in header.
    public static function get_group_info( $group_id=0 )
    {
        try {

            $curl = curl_init();

            //curl_setopt( $curl, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/group/v2/list?updatedAt=1514470790462' );
            //curl_setopt( $curl, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/group/v2/list' );
            curl_setopt( $curl, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/group/v2/info?groupId=' . $group_id );

            curl_setopt( $curl, CURLOPT_HTTPHEADER, [
                'Application-Key: 6390f1d46b341e65215b139cb2b15fd7',
                "Authorization: Basic " . base64_encode('TechBot:Yonkers1091@' ),
                //'Content-Type: application/json',
                "Of-User-Id : 49"                            //Lars, who ordered block a user.
                //'Content-Length: ' . strlen($data_string)
            ] );

            curl_setopt( $curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_PORT , 443);
            curl_setopt($curl, CURLOPT_POST, 0);
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt($curl, CURLOPT_TIMEOUT, 600);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $result = curl_exec($curl);
            $info = curl_getinfo($curl);

            $error = curl_errno($curl);
            curl_close($curl);

            Helper::log('### get_group_info response ###', [
                //'request'   => $data_string,
                'result'    => $result
            ]);

            if ($error) {
                switch ($http_code = $info['http_code']) {
                    case 200:  # OK
                    case 201:
                        break;
                    default:
                        return [
                            'msg' => 'Error : ' . $http_code
                        ];
                }
            }

            $res = json_decode($result);
            Helper::log('### get_group_info response-decoded ###', [
                'decoded result'    => $res
            ]);
            var_dump( $res );


        } catch (\Exception $ex) {
            return [
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ];
        }
    }

    //remove a user from all groups
    public static function is_user_present( $user_id, $group_id )
    {
        try {

            $curl = curl_init();

            curl_setopt( $curl, CURLOPT_URL, "https://apps.applozic.com/rest/ws/group/check/user?clientGroupId=$group_id&userId=$user_id" );

            curl_setopt( $curl, CURLOPT_HTTPHEADER, [
                'Application-Key: 6390f1d46b341e65215b139cb2b15fd7',
                "Authorization: Basic " . base64_encode('TechBot:Yonkers1091@' ),
                //'Content-Type: application/json',
                "Of-User-Id : 49"                            //Lars, who ordered block a user.
                //'Content-Length: ' . strlen($data_string)
            ] );

            curl_setopt( $curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_PORT , 443);
            curl_setopt($curl, CURLOPT_POST, 0);
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt($curl, CURLOPT_TIMEOUT, 600);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $result = curl_exec($curl);
            $info = curl_getinfo($curl);

            $error = curl_errno($curl);
            curl_close($curl);

            Helper::log('### is_user_present response ###', [
                //'request'   => $data_string,
                'result'    => $result
            ]);

            if ($error) {
                switch ($http_code = $info['http_code']) {
                    case 200:  # OK
                    case 201:
                        break;
                    default:
                        return [
                            'msg' => 'Error : ' . $http_code
                        ];
                }
            }

            $res = json_decode($result);
            Helper::log('### is_user_present response-decoded ###', [
                'decoded result'    => $res
            ]);
            var_dump( $res );


        } catch (\Exception $ex) {
            return [
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ];
        }
    }
   //json
   public static function add_user_to_group($user_id , $group_id,  $add_type='A', $role=3 )
    {
        try {

            $curl = curl_init();
            if( $add_type == 'A') { //Add a user to the group
                $data = [
                    "userId" => $user_id,
                    "clientGroupId" => $group_id,
                    "role" => $role
                ];
                //role 1 : Admin, 2 : moderator, 3 : member

                curl_setopt( $curl, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/group/add/member' );
            }else {            //D : Delete a user from the group.
                $data = [
                    "userId" => $user_id,
                    "clientGroupId" => $group_id
                ];


                curl_setopt( $curl, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/group/remove/member' );
            }

            $data_string = json_encode($data);


            curl_setopt( $curl, CURLOPT_HTTPHEADER, [
                'Application-Key: 6390f1d46b341e65215b139cb2b15fd7',
                "Authorization: Basic " . base64_encode('TechBot:Yonkers1091@' ),
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            ] );

            curl_setopt( $curl, CURLINFO_HEADER_OUT, true);
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_PORT , 443);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt($curl, CURLOPT_TIMEOUT, 600);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $result = curl_exec($curl);
            $info = curl_getinfo($curl);

            $error = curl_errno($curl);
            curl_close($curl);

            Helper::log('### add_user_to_group response ###', [
                'request'   => $data_string,
                'result'    => $result
            ]);
           // {\"status\":\"success\",\"generatedAt\":1579209254876,\"response\":\"success\"}
            if ($error) {
                switch ($http_code = $info['http_code']) {
                    case 200:  # OK
                    case 201:
                        break;
                    default:
                        return [
                            'msg' => 'Error : ' . $http_code
                        ];
                }
            }

            $res = json_decode($result);
            Helper::log('### add_user_to_group response-decoded ###', [
                'decoded result'    => $res
            ]);
            var_dump( $res );

            //"decoded result":"[object] (stdClass: {\"status\":\"success\",\"generatedAt\":1579137680401,\"response\":[{\"userId\":\"60\",\"userName\":\"Charles Walker        \",\"connected\":false,\"status\":0,\"lastSeenAtTime\":1579113019956,\"createdAtTime\":1554596577143,\"unreadCount\":2,\"displayName\":\"Charles Walker        \",\"deactivated\":true,\"connectedClientCount\":0,\"active\":true,\"lastLoggedInAtTime\":1556403495860,\"roleKey\":\"09a9238c-1fe9-40d9-b90c-4efd090ba4a3\",\"metadata\":{},\"roleType\":3}]})"

//            $code       = $res->Code;
//            $message    = $res->Message;
//            $esn        = $res->esn;
//
//            if($code != 1){
//                return [
//                    'code'      => $code,
//                    'message'   => $message,
//                    'esn'       => $esn,
//                    'result'    => $result
//                ];
//            } else {
//                return [
//                    'code'      => $code,
//                    'message'   => $message,
//                    'esn'       => $esn,
//                    'result'    => $result
//                ];
//            }

        } catch (\Exception $ex) {
            return [
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ];
        }
    }

    //Delete a group
    //Get
    public static function delete_group( $group_id )
    {
        try {

            $curl = curl_init();


            curl_setopt( $curl, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/group/delete?clientGroupId=' . $group_id );

            curl_setopt( $curl, CURLOPT_HTTPHEADER, [
                'Application-Key: 6390f1d46b341e65215b139cb2b15fd7',
                "Authorization: Basic " . base64_encode('TechBot:Yonkers1091@' ),
                //'Content-Type: application/json',
                "Of-User-Id : tech@groomit.me"                            //
                //'Content-Length: ' . strlen($data_string)
            ] );

            curl_setopt( $curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_PORT , 443);
            curl_setopt($curl, CURLOPT_POST, 0);
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt($curl, CURLOPT_TIMEOUT, 600);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $result = curl_exec($curl);
            $info = curl_getinfo($curl);

            $error = curl_errno($curl);
            curl_close($curl);

            Helper::log('### delete_group response ###', [
                //'request'   => $data_string,
                'result'    => $result
            ]);

            if ($error) {
                switch ($http_code = $info['http_code']) {
                    case 200:  # OK
                    case 201:
                        break;
                    default:
                        return [
                            'msg' => 'Error : ' . $http_code
                        ];
                }
            }

            $res = json_decode($result);
            Helper::log('### delete_group response-decoded ###', [
                'decoded result'    => $res
            ]);
            var_dump( $res );


        } catch (\Exception $ex) {
            return [
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ];
        }
    }
}