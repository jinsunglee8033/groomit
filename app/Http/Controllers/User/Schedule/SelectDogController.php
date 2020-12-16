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
use App\Model\ProductDenom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SelectDogController extends Controller
{

    public function show(Request $request) {
        
        ### clear session for schedule ###
        // ScheduleProcessor::clearAll();

        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Schedule');
        Session::put('schedule.url', $request->path());

        $first_pet_type = ScheduleProcessor::getFirstPetType();
        if (!empty($first_pet_type) && $first_pet_type != 'dog') {
            return redirect('/user/schedule/select-cat')->withErrors([
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

//        $current_package = ScheduleProcessor::getCurrentPackage();
//        if (empty($current_package) || $current_package->pet_type == 'cat') {
//            $packages = Product::where('pet_type', 'dog')->where('status', 'A')->get();
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

        return view('user.schedule.select-dog', [
            'gold' => $gold,
            'silver' => $silver,
            'eco' => $eco
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
        $size_id = ScheduleProcessor::getCurrentSize();

//        select
//                    a.prod_id,
//                    a.prod_type,
//                    a.prod_name,
//                    a.prod_desc,
//                    a.no_mix,
//                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
//                    b.min_denom,
//                    b.max_denom
//                from product a
//                    inner join product_denom b on a.prod_id = b.prod_id
//                where b.size_id = :size_id
//        and a.prod_type = 'P'
//        and a.status = 'A'
//        and b.status = 'A'
//        and b.group_id = :group_id
//        and a.pet_type = 'dog'
//        and a.prod_id = 1
        $ret_gold = DB::select("
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
                and ( (a.size_required = 'Y' and b.size_id = :size_id) or a.size_required = 'N')
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'dog'
                and a.prod_id = 1
            ", [
            'size_id' => $size_id,
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
//                    a.no_mix,
//                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
//                    b.min_denom,
//                    b.max_denom
//                from product a
//                    inner join product_denom b on a.prod_id = b.prod_id
//                where b.size_id = :size_id
//        and a.prod_type = 'P'
//        and a.status = 'A'
//        and b.status = 'A'
//        and b.group_id = :group_id
//        and a.pet_type = 'dog'
//        and a.prod_id = 2
        $ret_silver = DB::select("
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
                and ( (a.size_required = 'Y' and b.size_id = :size_id) or a.size_required = 'N')
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'dog'
                and a.prod_id = 2
            ", [
            'size_id' => $size_id,
            'group_id' => $group_id
        ]);

        if (count($ret_silver) != 1) {
            return [
                'msg' => 'Unable to find gold package price',
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
                    a.no_mix,
                    b.denom,
                    b.min_denom,
                    b.max_denom
                from product a  inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'P'
                and a.status = 'A'
                and ( (a.size_required = 'Y' and b.size_id = :size_id) or a.size_required = 'N')
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'dog'
                and a.prod_id = 28
            ", [
          'size_id' => $size_id,
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
            'eco' => $eco
        ];
    }

    public function updateSize(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'size' => 'required|numeric'
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

            ScheduleProcessor::setCurrentSize($request->size, true);

            $ret = $this->get_available_packages();
            if (!empty($ret['msg'])) {
                return response()->json([
                    'msg' => $ret['msg']
                ]);
            }

            $gold   = $ret['gold'];
            $silver = $ret['silver'];
            $eco    = $ret['eco'];

            return response()->json([
                'msg'       => '',
                'gold'      => $gold,
                'silver'    => $silver,
                'eco'       => $eco,
                'current_sub_total' => ScheduleProcessor::getCurrentSubTotal()
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
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
                    'msg' => 'Invalid package ID provided'
                ]);
            }

            if (!ScheduleProcessor::is_available_package($package)) {
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