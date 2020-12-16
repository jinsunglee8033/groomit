<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/12/18
 * Time: 10:04 AM
 */

namespace App\Http\Controllers\User\Schedule;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\ScheduleProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\Product;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Auth;
use DB;
use Session;
use Validator;

class SelectAddOnController extends Controller
{

    public function show(Request $request) {
        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Schedule');
        Session::put('schedule.url', $request->path());

        $pet_type = ScheduleProcessor::getCurrentPetType();
        if (empty($pet_type)) {
            return redirect('/user/schedule/select-dog')->withErrors([
                'Please select pet type first'
            ]);
        }

        $back_url = '/user/schedule/select-dog';
        switch ($pet_type) {
            case 'cat':
                $back_url = '/user/schedule/select-cat';
                break;
        }

        $zip = ScheduleProcessor::getZip();
        if (empty($zip)) {
            return redirect('/user')->withErrors([
                'exception' => 'Please enter your zip code to continue'
            ]);
        }

        $allowed_zip = AllowedZip::where('zip', $zip)->first();
        if (empty($allowed_zip)) {
            return redirect('/user/zip-not-available')->with([
                'zip' => $zip
            ]);
        }

        $size_id = ScheduleProcessor::getCurrentSize();

        $shampoos = Product::where('prod_type', 'S')
            ->where('pet_type', $pet_type)
            ->where('status', 'A')
            ->get();

        foreach ($shampoos as $o) {
            $o->denom = Helper::get_price($o->prod_id, $size_id, $zip);
        }

        $shampoo = ScheduleProcessor::getCurrentShampoo();
        if (empty($shampoo) && count($shampoos) > 0) {
            $shampoo = $shampoos[0];
            ScheduleProcessor::setCurrentShampoo($shampoo);
        }

        $add_ons = Product::where('prod_type', 'A')
            ->where('pet_type', $pet_type)
            ->where('status', 'A');

        if (ScheduleProcessor::getCurrentPackageId() == 2) {
            //$add_ons = $add_ons->where('prod_id', '!=', 14);
            $add_ons = $add_ons->whereRaw('prod_id not in (14,7)'); //Excluded Demat, Hand Strip for Silver
        }

//        if (ScheduleProcessor::getCurrentPackageId() == 1) {
//            $add_ons = $add_ons->whereRaw('prod_id not in (11,22)');

        // No De-Shedding for CAT Gold (prod_id = 16) 2/26/2020
        if (ScheduleProcessor::getCurrentPackageId() == 16) {
            $add_ons = $add_ons->whereRaw('prod_id not in (20)');
        }

        $add_ons = $add_ons->orderBy('seq','asc')->get();


        foreach ($add_ons as $o) {
            $o->denom = Helper::get_price($o->prod_id, $size_id, $zip);
        }

        $dematting = null;
        if (count($add_ons) > 0) {
            foreach ($add_ons as $o) {
                if (in_array($o->prod_id, [14, 18])) {
                    $dematting = $o;
                }
            }
        }

        return view('user.schedule.select-addon', [
            'back_url' => $back_url,
            'shampoos' => $shampoos,
            'add_ons' => $add_ons,
            'dematting' => $dematting
        ]);
    }

//    public function show_for_test(Request $request) {
//        Session::put('user.menu.show', 'Y');
//        Session::put('user.menu.top-title', 'Schedule');
//        Session::put('schedule.url', $request->path());
//
//        $pet_type = ScheduleProcessor::getCurrentPetType();
//        if (empty($pet_type)) {
//            return redirect('/user/schedule/select-dog')->withErrors([
//              'Please select pet type first'
//            ]);
//        }
//
//        $back_url = '/user/schedule/select-dog';
//        switch ($pet_type) {
//            case 'cat':
//                $back_url = '/user/schedule/select-cat';
//                break;
//        }
//
//        $zip = ScheduleProcessor::getZip();
//        if (empty($zip)) {
//            return redirect('/user')->withErrors([
//              'exception' => 'Please select Zip first!'
//            ]);
//        }
//
//        $allowed_zip = AllowedZip::where('zip', $zip)->first();
//        if (empty($allowed_zip)) {
//            return redirect('/user/zip-not-available')->with([
//              'zip' => $zip
//            ]);
//        }
//
//        $size_id = ScheduleProcessor::getCurrentSize();
//
//        $shampoos = Product::where('prod_type', 'S')
//          ->where('pet_type', $pet_type)
//          ->where('status', 'A')
//          ->get();
//
//        foreach ($shampoos as $o) {
//            $o->denom = Helper::get_price($o->prod_id, $size_id, $zip);
//        }
//
//        $shampoo = ScheduleProcessor::getCurrentShampoo();
//        if (empty($shampoo) && count($shampoos) > 0) {
//            $shampoo = $shampoos[0];
//            ScheduleProcessor::setCurrentShampoo($shampoo);
//        }
//
//        $add_ons = Product::where('prod_type', 'A')
//          ->where('pet_type', $pet_type)
//          ->where('status', 'A');
//
//        if (ScheduleProcessor::getCurrentPackageId() == 2) {
//            $add_ons = $add_ons->where('prod_id', '!=', 14);
//        }
//
//        $add_ons = $add_ons->get();
//        foreach ($add_ons as $o) {
//            $o->denom = Helper::get_price($o->prod_id, $size_id, $zip);
//        }
//
//        $dematting = null;
//        if (count($add_ons) > 0) {
//            foreach ($add_ons as $o) {
//                if (in_array($o->prod_id, [14, 18])) {
//                    $dematting = $o;
//                }
//            }
//        }
//
//        return view('user.schedule.select-addon-new', [
//          'back_url' => $back_url,
//          'shampoos' => $shampoos,
//          'add_ons' => $add_ons,
//          'dematting' => $dematting
//        ]);
//    }

    public function updateShampoo(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'shampoo' => 'required'
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

            $pet_type = ScheduleProcessor::getCurrentPetType();
            $shampoo = Product::where('pet_type', $pet_type)
                ->where('prod_type', 'S')
                ->where('status', 'A')
                ->where('prod_id', $request->shampoo)
                ->first();
            if (empty($shampoo)) {
                return response()->json([
                    'msg' => 'Invalid shampoo selected'
                ]);
            }

            ScheduleProcessor::setCurrentShampoo($shampoo);

            return response()->json([
                'msg' => '',
                'current_shampoo' => ScheduleProcessor::getCurrentShampoo(),
                'current_addons' => ScheduleProcessor::getCurrentAddons(),
                'current_sub_total' => ScheduleProcessor::getCurrentSubTotal()
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function addAddon(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'addon_id' => 'required'
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

            $addon = Product::where('pet_type', ScheduleProcessor::getCurrentPetType())
                ->where('prod_type', 'A')
                ->where('status', 'A')
                ->where('prod_id', $request->addon_id)
                ->first();

            if (empty($addon)) {
                return response()->json([
                    'msg' => 'Invalid addon ID provided'
                ]);
            }

            ScheduleProcessor::addToCurrentAddons($addon);

            return response()->json([
                'msg' => '',
                'current_shampoo' => ScheduleProcessor::getCurrentShampoo(),
                'current_addons' => ScheduleProcessor::getCurrentAddons(),
                'current_sub_total' => ScheduleProcessor::getCurrentSubTotal()
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function removeAddon(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'addon_id' => 'required'
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

            $addon = Product::where('pet_type', ScheduleProcessor::getCurrentPetType())
                ->where('prod_type', 'A')
                ->where('status', 'A')
                ->where('prod_id', $request->addon_id)
                ->first();

            if (empty($addon)) {
                return response()->json([
                    'msg' => 'Invalid addon ID provided'
                ]);
            }

            ScheduleProcessor::removeFromCurrentAddons($addon);

            return response()->json([
                'msg' => '',
                'current_shampoo' => ScheduleProcessor::getCurrentShampoo(),
                'current_addons' => ScheduleProcessor::getCurrentAddons(),
                'current_sub_total' => ScheduleProcessor::getCurrentSubTotal()
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    //NOT USED ANY LONGER, BECASUE IT'S SENT AFTER APPOINTMENTS.
    public function sendTerm(Request $request) {
        try {

            $data = [];
            $data['email'] = $request->email;
            $data['name'] = '';
            $data['subject'] = 'GROOMIT TERMS AND CONDITIONS';
            $ret = Helper::send_html_mail('terms', $data);

            if (!empty($ret)) {
                $msg = 'Failed to send groomer on the way email to groomer';
                throw new \Exception($msg);
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