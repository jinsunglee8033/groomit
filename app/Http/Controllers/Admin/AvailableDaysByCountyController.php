<?php

namespace App\Http\Controllers\Admin;

use App\Model\PreApply;
use App\Model\ServiceArea;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Excel;


class AvailableDaysByCountyController extends Controller
{

    public function index(Request $request)
    {
        try {

            $res = ServiceArea::orderBy('sort', 'asc')->get();

            return view('admin.available_days_by_county', [
                'msg' => '',
                'results' => $res

            ]);

        }

        catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function day_update(Request $request) {

        $id = $request->id;
        $day = $request->day;
        $val = $request->val;

        $area = ServiceArea::where('area_id', $id)->first();

        if($val == 'Y'){
            if($day == 'mon'){
                $area->mon = 'N';
            }elseif ($day == 'tue'){
                $area->tue = 'N';
            }elseif ($day == 'wed'){
                $area->wed = 'N';
            }elseif ($day == 'thu'){
                $area->thu = 'N';
            }elseif ($day == 'fri'){
                $area->fri = 'N';
            }elseif ($day == 'sat'){
                $area->sat = 'N';
            }elseif ($day == 'sun'){
                $area->sun = 'N';
            }
        }else{
            if($day == 'mon'){
                $area->mon = 'Y';
            }elseif ($day == 'tue'){
                $area->tue = 'Y';
            }elseif ($day == 'wed'){
                $area->wed = 'Y';
            }elseif ($day == 'thu'){
                $area->thu = 'Y';
            }elseif ($day == 'fri'){
                $area->fri = 'Y';
            }elseif ($day == 'sat'){
                $area->sat = 'Y';
            }elseif ($day == 'sun'){
                $area->sun = 'Y';
            }
        }

        $area->save();

        return response()->json([
            'code' => '0',
            'msg'  => 'updated'
        ]);
    }

}
