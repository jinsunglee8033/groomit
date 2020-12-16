<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/11/18
 * Time: 1:48 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\ScheduleProcessor;
use App\Model\AllowedZip;
use App\Model\ZipQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CheckZipController extends Controller
{

    public function post(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'zip' => 'required|regex:/^\d{5}$/'
            ]);

            if ($v->fails()) {
                return back()->withErrors($v);
            }

            if( !isset($request->zip) || strlen($request->zip) > 10){
                return back()->withErrors([
                    'exception' => 'We are sorry but we cannot recognize your location. Please try again.'
                ]);
                exit;
            }

            ### save zip code query log for later use ###
            $q = new ZipQuery();
            $q->path = isset($request->path) ? $request->path : '-';
            $q->zip = $request->zip;
            $q->address1 = isset($request->address1)? $request->address1 : '' ;
            $q->city = isset($request->city)? $request->city : '' ;
            $q->state =isset($request->state)? $request->state : '' ;
            $q->full_address =isset($request->address)? $request->address : '' ;
            $q->cdate = Carbon::now();
            $q->save();

            $inserted_id = $q->id;

            $zip = AllowedZip::where('zip', $request->zip)
                ->whereRaw("lower(trim(available)) = 'x'")
                ->first();

            if (empty($zip)) {

                return redirect('/user/zip-not-available')->with([
                    'zip'           => $request->zip,
                    'inserted_id'   => $inserted_id
                ]);
            }else {
                if($q->state == ''){ //if no input on state
                    $q->city = $zip->city_name;
                    $q->state = $zip->state_abbr;
                    $q->save();
                }
            }

            ScheduleProcessor::setZip($request->zip);
            ScheduleProcessor::setAddress1($request->address1);
            ScheduleProcessor::setCity($request->city);
            ScheduleProcessor::setState($request->state);

            //return redirect('/user/zip-available');
            return redirect('/user/schedule/select-dog');

        } catch (\Exception $ex) {
            return back()->withErrors([
                'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function showAvailable(Request $request) {
        Session::put('user.menu.show', 'N');
        return view('user.zip-available');
    }

    public function showNotAvailable(Request $request) {
        Session::put('user.menu.show', 'N');
        return view('user.zip-not-available');
    }

}