<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/13/18
 * Time: 1:36 PM
 */

namespace App\Http\Controllers\User\Schedule;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\PetProcessor;
use App\Lib\ScheduleProcessor;
use App\Model\Breed;
use App\Model\Pet;
use App\Model\PetPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SelectPetController extends Controller
{
    public function show(Request $request) {
        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Schedule');
        Session::put('schedule.url', $request->path());

        $pets = [];
        $breeds = Breed::orderBy('sorting')->get();

        if (Auth::guard('user')->check()) {
            $pet_type = ScheduleProcessor::getCurrentPetType();

            $user_id = Auth::guard('user')->user()->user_id;

            $query = Pet::where('user_id', $user_id)
                ->where('type', $pet_type)
                ->where('status', 'A');

            $pets = $query->orderBy('pet_id','asc')->get();


            if (count($pets) > 0) {
                foreach ($pets as $o) {
                    $photo = PetPhoto::where('pet_id', $o->pet_id)->first();
                    if (!empty($photo)) {
                        try{
                            $o->photo = base64_encode($photo->photo);
                        } catch (\Exception $ex) {
                            $o->photo = $photo->photo ;
                        }
                    }

                    $o->age = PetProcessor::get_age($o->pet_id);
                }
            }

            $current_pet = ScheduleProcessor::getCurrentPet();
            if (!empty($current_pet)) {
                $found = false;
                if (count($pets) > 0) {
                    foreach ($pets as $o) {
                        if ($current_pet->pet_id == $o->pet_id) {
                            $found = true;
                        }
                    }
                }

                if (!$found) {
                    ScheduleProcessor::setCurrentPet(null);
                }
            }
        }

        $zip = ScheduleProcessor::getZip();
//        $address1 = ScheduleProcessor::getAddress1(); //Why ?
//        $city = ScheduleProcessor::getCity();
//        $state = ScheduleProcessor::getState();

        return view('user.schedule.select-pet', [
            'pets' => $pets,
            'breeds' => $breeds,
            'zip' => $zip
//            'address1' => $address1,
//            'city' => $city,
//            'state' => $state
        ]);
    }

    public function post(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'pet_id' => 'required'
            ]);

            if ($v->fails()) {
                return back()->withInput()->withErrors($v);
            }

            $pet = Pet::find($request->pet_id);
            if (empty($pet)) {
                return back()->withInput()->withErrors([
                    'exception' => 'Invalid pet ID provided'
                ]);
            }

            ScheduleProcessor::setCurrentPet($pet);
            ScheduleProcessor::addToPets($pet, $request->add_another_pet == 'Y');

            if ($request->add_another_pet == 'Y') {
                ScheduleProcessor::setAddAnotherPet('Y');
                return redirect('/user/schedule/select-' . $pet->type);
            }

            ScheduleProcessor::setAddAnotherPet('N');
            return redirect('/user/schedule/select-date');

        } catch (\Exception $ex) {
            return back()->withInput()->withErrors([
                'exception' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }
}