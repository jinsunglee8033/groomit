<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/22/18
 * Time: 2:56 PM
 */

namespace App\Http\Controllers;


use App\Lib\Helper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TokenExController extends Controller
{

    public function getAuthKey(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $client_secret_key = 'E3Bhx2dHNFWUvxin5HpNF7AWycMcXy4A1yPOlrum';
            $token_ex_id = '9118090813754290';
            $origin = 'http://localhost:8100';
            $timestamp = Carbon::now()->setTimezone('UTC')->format('YmdHis');
            $token_scheme = 'sixTOKENfour';

            $string = $token_ex_id . '|' . $origin . '|' . $timestamp . '|' . $token_scheme;

            $auth_key = hash_hmac('sha256', $string, $client_secret_key);
            $auth_key = base64_encode($auth_key);

            return response()->json([
                'msg' => '',
                'token_ex_id' => $token_ex_id,
                'origin' => $origin,
                'timestamp' => $timestamp,
                'token_scheme' => $token_scheme,
                'auth_key' => $auth_key
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}