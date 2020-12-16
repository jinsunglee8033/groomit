<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/11/18
 * Time: 11:33 AM
 */

namespace App\Http\Controllers\Groomer;


use App\Http\Controllers\Controller;
use App\Lib\EarningProcessor;
use App\Lib\Helper;
use App\Model\Groomer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EarningController extends Controller
{
    public function getHistory(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'type' => 'required|in:W,M,Y',
                'from' => 'required|date',
                'to' => 'required|date'
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $data = EarningProcessor::getHistory($request->type, $groomer->groomer_id, $request->from, $request->to);

            return response()->json([
                'msg' => '',
                'data' => $data
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function getInfo(Request $request) {
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

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [MSGS]'
                ]);
            }

            $current = EarningProcessor::getCurrentEarning($groomer->groomer_id);
            $last_3_week = EarningProcessor::getLast3WeeksEarning($groomer->groomer_id);

            $from = Carbon::today()->startOfWeek()->subDays(14);
            $to = Carbon::today();

            $history = EarningProcessor::getHistory('W', $groomer->groomer_id, $from, $to);

            return response()->json([
                'msg' => '',
                'current' => $current,
                'last_3_week' => $last_3_week,
                'history' => $history
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function get_detail(Request $request) {
        try {

            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token' => 'required',
              'from' => 'required|date',
              'to' => 'required|date'
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                  'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $data = EarningProcessor::get_earning_detail($groomer->groomer_id, $request->from, $request->to);

            return response()->json([
              'msg' => '',
              'data' => $data
            ]);


        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}