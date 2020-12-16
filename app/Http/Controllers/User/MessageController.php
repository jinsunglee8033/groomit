<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 10/26/16
 * Time: 5:40 PM
 */

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Log;
use Validator;
use App\Model\User;
use App\Model\Message;
use App\Model\Groomer;
use App\Lib\Helper;

class MessageController extends Controller
{

    public function messages(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required'
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


            $email = \Crypt::decrypt($request->token);
            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [MSGS]'
                ]);
            }

            $messages = Message::where('src_type', 'G')
                ->where('user_id', $user->user_id)
                ->orderBy('cdate', 'desc')->get();

            foreach ($messages as $m) {
                $m->groomer = Groomer::find($m->groomer_id);
                if (!empty($m->groomer)) {
                    try{
                        $m->groomer->photo = base64_encode($m->groomer->photo);
                    } catch (\Exception $ex) {
                        $m->groomer->photo  = $m->groomer->photo ;
                    }
                }
            }

            return response()->json([
                'msg' => '',
                'messages' => $messages
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}