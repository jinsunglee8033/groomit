<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 7/24/18
 * Time: 11:00 AM
 */

namespace App\Http\Controllers\User\Appointment;


use App\Http\Controllers\Controller;
use App\Lib\AppointmentProcessor;
use App\Model\AppointmentList;
use Auth;
use Illuminate\Http\Request;
use Session;
use Validator;

class ListController extends Controller
{

    public function show() {
        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Appointments');

        $user = Auth::guard('user')->user();
        if (empty($user)) {
            return redirect('/user')->withErrors([
                'Session expired. Please login first!'
            ]);
        }

        $recent = AppointmentProcessor::get_recent($user->user_id);
        $upcoming = AppointmentProcessor::get_upcoming($user->user_id);

        return view('user.appointment.list', [
            'recent' => $recent,
            'upcoming' => $upcoming
        ]);
    }

    public function rate(Request $request ) {
        try {

            $v = Validator::make($request->all(), [
                'appointment_id' => 'required',
                'rating' => 'required|min:0|max:5|numeric'
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

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Session expired. Please login again.');
            }

            $msg = AppointmentProcessor::rate($user->user_id, $request->appointment_id, $request->rating);
            return response()->json([
                'msg' => $msg
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function tip(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'appointment_id' => 'required',
                'tip' => 'required'
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

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Session expired. Please login again.');
            }

            $msg = AppointmentProcessor::tip($user->user_id, $request->appointment_id, $request->tip);
            return response()->json([
                'msg' => $msg
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function markAsFavorite(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'appointment_id' => 'required',
                'add_to_favorite' => 'required'
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

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Session expired. Please login again.');
            }

            $msg = AppointmentProcessor::mark_as_favorite($user->user_id, $request->appointment_id, $request->add_to_favorite);
            return response()->json([
                'msg' => $msg
            ]);

        } catch (\Exception $ex) {
            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }
    }

    public function rebook(Request $request) {
        try {

            $app = AppointmentList::Where('appointment_id', $request->appointment_id)->first();

            $rs = AppointmentProcessor::get_info($app);
            $rs = $rs->pets;

            return view('user.appointment.list', [
                'msg'   => '',
                'rs'    => $rs->pets
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}