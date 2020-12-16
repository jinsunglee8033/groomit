<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/11/18
 * Time: 2:03 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Model\Subscribe;
use App\Model\ZipQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscribeController extends Controller
{
    public function post(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'phone_number' => 'regex:/^\d{10}$/',
                'subscriber_email' => 'required|email',
                'zip' => 'required|regex:/^\d{5}$/'
            ]);

            if ($v->fails()) {
                return back()->withInput()->withErrors($v);
            }

            $o = Subscribe::where('path', $request->path())
                ->where('zip', $request->zip)
                ->whereRaw("lower(trim(email)) = ?", [strtolower(trim($request->subscriber_email))])
                ->first();

            if (!empty($o)) {
                return back()->withInput()->withErrors([
                    'You have already subscribed.'
                ]);
            }

            $inserted_id = $request->inserted_id;
            $zq = ZipQuery::where('id', $inserted_id)->first();
            $zq->first_name = $request->first_name;
            $zq->last_name  = $request->last_name;
            $zq->phone      = $request->phone_number;
            $zq->email      = strtolower(trim($request->subscriber_email));
            $zq->update();

            $o = new Subscribe;
            $o->path = $request->path();
            $o->first_name = $request->first_name;
            $o->last_name = $request->last_name;
            $o->phone = $request->phone_number;
            $o->email = strtolower(trim($request->subscriber_email));
            $o->zip = $request->zip;
            $o->ip = $request->ip();
            $o->cdate = Carbon::now();
            $o->save();

            return back()->with([
                'success' => 'Thank you, your request has been processed successfully!',
                'zip' => $request->zip
            ]);


        } catch (\Exception $ex) {
            return back()->withInput()->withErrors([
                $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}