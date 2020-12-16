<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/14/18
 * Time: 11:24 AM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\AddressProcessor;
use App\Model\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{

    public function load(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'address_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $address = Address::find($request->address_id);
            if (empty($address)) {
                return response()->json([
                    'msg' => 'Invalid address ID provided'
                ]);
            }

            return response()->json([
                'msg' => '',
                'data' => $address
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function add(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'address1' => 'required',
                'address2' => '',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|regex:/^\d{5}$/'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Please login first!'
                ]);
            }

            $ret = AddressProcessor::add(
                $user->user_id,
                $request->address1,
                $request->address2,
                $request->city,
                $request->state,
                $request->zip,
                $request->default_address
            );

            if (!empty($ret['msg'])) {
                return response()->json([
                    'msg' => $ret['msg']
                ]);
            }

            return response()->json([
                'msg' => ''
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'address_id' => 'required',
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|regex:/^\d{5}$/'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Please login first!'
                ]);
            }

            $ret = AddressProcessor::update(
                $user->user_id,
                $request->address_id,
                $request->address1,
                $request->address2,
                $request->city,
                $request->state,
                $request->zip,
                $request->default_address
            );

            if (!empty($ret['msg'])) {
                return response()->json([
                    'msg' => $ret['msg']
                ]);
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}