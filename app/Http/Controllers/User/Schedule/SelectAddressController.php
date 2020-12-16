<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/13/18
 * Time: 5:25 PM
 */

namespace App\Http\Controllers\User\Schedule;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\ScheduleProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SelectAddressController extends Controller
{

    public function show() {

        $user_id = Auth::guard('user')->user()->user_id;

        $addresses = Address::where('user_id', $user_id)
            ->where('status', 'A')
            ->get();

        $force_edit_zip_only_id = '';
        if (count($addresses) == 1 && (
            trim($addresses[0]->address1) == '' ||
            trim($addresses[0]->city) == '' ||
            trim($addresses[0]->state) == '' ||
            trim($addresses[0]->zip) == ''
            )) {
            $force_edit_zip_only_id = $addresses[0]['address_id'];
        }

        $states = Helper::get_states();
        $address1 = ScheduleProcessor::getAddress1();
        $city = ScheduleProcessor::getCity();
        $state = ScheduleProcessor::getState();

        return view('user.schedule.select-address', [
            'addresses' => $addresses,
            'states'    => $states,
            'address1'  => $address1,
            'city'      => $city,
            'state'     => $state,
            'force_edit_zip_only_id' => $force_edit_zip_only_id
        ]);
    }

    public function post(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'selected_address_id' => 'required'
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

            $address = Address::where('user_id', Auth::guard('user')->user()->user_id)
                ->where('address_id', $request->selected_address_id)
                ->where('status', 'A')
                ->first();
            if (empty($address)) {
                return response()->json([
                    'msg' =>  'Invalid address ID provided'
                ]);
            }

            if (trim($address->address1) == '' ||
                trim($address->city) == '' ||
                trim($address->state) == '' ||
                trim($address->zip) == '') {
                return response()->json([
                    'msg' => 'Please complete your address to proceed'
                ]);
            }


            $allowed_zip = AllowedZip::where('zip', $address->zip)->first();
            if (empty($allowed_zip)) {
                return response()->json([
                  'msg' => 'We are sorry but your address does not belongs to our available service areas.'
                ]);
            } else {
                if ($allowed_zip->available != 'x') {
                    return response()->json([
                      'msg' => 'We are sorry but your address does not belongs to our available service areas.' . $allowed_zip->allowed
                    ]);
                }
            }

            if (!ScheduleProcessor::is_allowed_pet($allowed_zip)) {
                return response()->json([
                  'msg' => 'We are sorry but your address does not belongs to our available service areas. ' .
                    ScheduleProcessor::getCurrentPetType() . ' is not available.'
                ]);
            }

            if (!ScheduleProcessor::is_allowed_package($allowed_zip)) {
                return response()->json([
                  'msg' => 'We are sorry but your address does not belongs to our available service areas of Package ' .
                    ScheduleProcessor::getCurrentPackageName() . ' is not available.'
                ]);
            }

            ScheduleProcessor::setAddress($address);

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