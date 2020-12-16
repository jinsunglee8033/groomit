<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/11/18
 * Time: 3:18 PM
 */

namespace App\Http\Controllers\User\Schedule;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\ScheduleProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SelectCatController extends Controller
{

    public function show(Request $request) {
        
        ### clear session for schedule ###
        // ScheduleProcessor::clearAll();

        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Schedule');
        Session::put('schedule.url', $request->path());

        $first_pet_type = ScheduleProcessor::getFirstPetType();
        if (!empty($first_pet_type) && $first_pet_type != 'cat') {
            return redirect('/user/schedule/select-dog')->withErrors([
                'Pet type does not match with other pets'
            ]);
        }

        $current_pet_type = ScheduleProcessor::getCurrentPetType();
        if ($current_pet_type == 'dog') {
            ### clear add-on selection ###
            ScheduleProcessor::setCurrentAddons(null);
            ### clear shampoo selection ###
            ScheduleProcessor::setCurrentShampoo(null);
            ### clear package selection ###
            ScheduleProcessor::setCurrentPackage(null);
        }

        ScheduleProcessor::setCurrentPetType('cat');
        ScheduleProcessor::setCurrentSize(null);

//        $current_package = ScheduleProcessor::getCurrentPackage();
//
//        if (empty($current_package) || $current_package->pet_type == 'dog') {
//            $packages = Product::where('pet_type', 'cat')->where('status', 'A')->get();
//            foreach ($packages as $p) {
//                if (ScheduleProcessor::is_available_package($p)) {
//                    ScheduleProcessor::setCurrentPackage($p);
//                    break;
//                }
//            }
//        }


        $ret = $this->get_available_packages();
        if (!empty($ret['msg'])) {
            if ($ret['redirect'] == 'back') {
                return back()->withErrors([
                  $ret['msg']
                ]);
            }
            return redirect($ret['redirect'])->withErrors($ret['msg'])->with($ret['with']);
        }

        $gold = $ret['gold'];
        $silver = $ret['silver'];
        $eco = $ret['eco'];

        return view('user.schedule.select-cat')->with([
          'gold'    => $gold,
          'silver'  => $silver,
          'eco'     => $eco
        ]);
    }


    public function show_new(Request $request) {

        ### clear session for schedule ###
        // ScheduleProcessor::clearAll();

        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Schedule');
        Session::put('schedule.url', $request->path());

        $first_pet_type = ScheduleProcessor::getFirstPetType();
        if (!empty($first_pet_type) && $first_pet_type != 'dog') {
            return redirect('/user/schedule/select-cat-new')->withErrors([
              'Pet type does not match with other pets'
            ]);
        }

        $current_pet_type = ScheduleProcessor::getCurrentPetType();
        if ($current_pet_type == 'cat') {
            ### clear add-on selection ###
            ScheduleProcessor::setCurrentAddons(null);
            ### clear shampoo selection ###
            ScheduleProcessor::setCurrentShampoo(null);
            ### clear package selection ###
            ScheduleProcessor::setCurrentPackage(null);
        }

        ScheduleProcessor::setCurrentPetType('dog');

        $current_size = ScheduleProcessor::getCurrentSize();
        if (is_null($current_size)) {
            ScheduleProcessor::setCurrentSize(2);
        }

        $current_package = ScheduleProcessor::getCurrentPackage();
        if (empty($current_package) || $current_package->pet_type == 'cat') {
            $gold = Product::find(1);
            ScheduleProcessor::setCurrentPackage($gold);
        }

        $ret = $this->get_available_packages();
        if (!empty($ret['msg'])) {
            if ($ret['redirect'] == 'back') {
                return back()->withErrors([
                  $ret['msg']
                ]);
            }
            return redirect($ret['redirect'])->withErrors($ret['msg'])->with($ret['with']);
        }

        $gold = $ret['gold'];
        $silver = $ret['silver'];

        return view('user.schedule.select-cat-new', [
          'gold' => $gold,
          'silver' => $silver
        ]);
    }

    private function get_available_packages() {

        $zip = ScheduleProcessor::getZip();

        if (empty($zip)) {
            return [
              'msg' => 'Please enter your zip code to continue',
              'redirect' => '/user',
              'with' => [
                'zip' => ''
              ]
            ];
        }

        $allowed_zip = AllowedZip::where('zip', $zip)->first();
        if (empty($allowed_zip)) {
            return [
              'msg' => 'Zip code not available',
              'redirect' => '/user/zip-not-available',
              'with' => [
                'zip' => $zip
              ]
            ];
        }

        $group_id = $allowed_zip->group_id;

        $ret_gold = DB::select("
                 select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    b.denom,
                    b.min_denom,
                    b.max_denom
                from product a  inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'P'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'cat'
                and a.prod_id = 16
            ", [
          'group_id' => $group_id
        ]);

        if (count($ret_gold) != 1) {
            return [
              'msg' => 'Unable to find gold package price',
              'redirect' => 'back'
            ];
        }

        $gold = $ret_gold[0];

//        select
//                    a.prod_id,
//                    a.prod_type,
//                    a.prod_name,
//                    a.prod_desc,
//                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
//                    b.min_denom,
//                    b.max_denom
//                from product a
//                    inner join product_denom b on a.prod_id = b.prod_id
//                where a.prod_type = 'P'
//        and a.status = 'A'
//        and b.status = 'A'
//        and b.group_id = :group_id
//        and a.pet_type = 'cat'
//        and a.prod_id = 27
        $ret_silver = DB::select("
                 select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    b.denom,
                    b.min_denom,
                    b.max_denom
                from product a  inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'P'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'cat'
                and a.prod_id = 27
            ", [
          'group_id' => $group_id
        ]);

        if (count($ret_gold) != 1) {
            return [
              'msg' => 'Unable to find Silver package price',
              'redirect' => 'back'
            ];
        }

        $silver = $ret_silver[0];

        $ret_eco = DB::select("
                 select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    b.denom,
                    b.min_denom,
                    b.max_denom
                from product a  inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'P'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'cat'
                and a.prod_id = 29
            ", [
          'group_id' => $group_id
        ]);

        if (count($ret_eco) != 1) {
            return [
              'msg' => 'Unable to find ECO package price',
              'redirect' => 'back'
            ];
        }

        $eco = $ret_eco[0];

        return [
            'msg' => '',
            'gold' => $gold,
            'silver' => $silver,
            'eco'   => $eco
        ];
    }

    public function updatePackage(Request $request) {
        try {

            $v = Validator::make($request->all(), [
              'package_id' => 'required|numeric'
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

            $package = Product::where('prod_id', $request->package_id)
              ->where('prod_type', 'P')
              ->first();

            if (empty($package)) {
                return response()->json([
                  'Invalid package ID provided'
                ]);
            }

            $result = ScheduleProcessor::is_available_package($package);
            if (!$result) {
                if ($package->no_mix == 'N') {
                    return response()->json([
                      'msg' => $package->prod_name . ' Package can\'t be combined with other Packages in same appointment.'
                    ]);
                } else {
                    return response()->json([
                      'msg' => ScheduleProcessor::getCurrentPackageName() . ' Package can\'t be combined with other Packages in same appointment.'
                    ]);
                }
            }

            ScheduleProcessor::setCurrentPackage($package);
            ScheduleProcessor::setCurrentShampoo(null);
            ScheduleProcessor::setCurrentAddons(null);

            return response()->json([
              'msg' => '',
              'current_package' => ScheduleProcessor::getCurrentPackage(),
              'current_sub_total' => ScheduleProcessor::getCurrentSubTotal()
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}