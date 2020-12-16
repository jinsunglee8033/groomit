<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 6/28/19
 * Time: 10:04 AM
 */

namespace App\Http\Controllers\User\Schedule;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\ScheduleProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\AppointmentList;
use App\Model\AppointmentPet;
use App\Model\AppointmentProduct;
use App\Model\Groomer;
use App\Model\Pet;
use App\Model\Product;
use App\Model\UserFavoriteGroomer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Auth;
use DB;
use Session;
use Validator;

class SelectRebookController extends Controller
{
    public function show(Request $request, $appointment_id) {

        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'Schedule');
        Session::put('schedule.url', $request->path());
        Session::put('schedule.rebook', 'Y');

        $app = AppointmentList::find($appointment_id);
        if (empty($app)) {
            return back()->withErrors([
                'Invalid appointment ID provided'
            ]);
        }

        $ap_pets = AppointmentPet::where('appointment_id', $appointment_id)->get();

        ScheduleProcessor::setPets(null);

        if( count($ap_pets) > 0 ) {
            foreach ($ap_pets as $ap_pet) {
                $pet = Pet::where('pet_id', $ap_pet->pet_id)->first();

                ScheduleProcessor::setCurrentPet($pet);
                ScheduleProcessor::setCurrentPetType($pet->type);
                ScheduleProcessor::setCurrentSize($pet->size);

                $ap_prods = AppointmentProduct::where('appointment_id', $appointment_id)
                    ->where('pet_id', $ap_pet->pet_id)
                    ->get();

                if( count($ap_prods) > 0 ) {
                    foreach ($ap_prods as $ap_prod) {
                        $product = Product::where('prod_id', $ap_prod->prod_id)->first();
                        if($product->prod_type =='P'){
                            ScheduleProcessor::setCurrentPackage($product);
                        }elseif ($product->prod_type =='S'){
                            ScheduleProcessor::setCurrentShampoo($product);
                        }elseif ($product->prod_type =='A') {

                            $current_addons = ScheduleProcessor::getCurrentAddons();
                            $found = false;
                            if (count($current_addons)) {
                                foreach ($current_addons as $o) {
                                    if ($o->prod_id == $ap_prod->prod_id) {
                                        $found = true;
                                        break;
                                    }
                                }
                            }

                            if (!$found) {
                                $zip = ScheduleProcessor::getZip();
                                $size = ScheduleProcessor::getCurrentSize();
                                $product->denom = Helper::get_price($product->prod_id, $size, $zip);
                                Session::push('schedule.current-add-ons', $product);
                            }

                        }
                    }
                }
                ScheduleProcessor::addToPets($pet,'Y');
            }
            ScheduleProcessor::setAddAnotherPet('N');
        }

        // FAV GROOMER
        $favs = UserFavoriteGroomer::where('user_id', $app->user_id)->get();
        $num_favs = count($favs);

        if(count(array($favs))>0){
            foreach ($favs as $fav){
                $id = $fav->groomer_id;
                $groomer_obj = Groomer::where('groomer_id', $id)->first();
                $fav->name  = $groomer_obj->first_name;
                $fav->pic   = $groomer_obj->profile_photo;
            }
        }
        return view('user.schedule.select-date', [
            //'time_windows' => Helper::get_time_windows(),
            'favs'          => $favs,
            'num_favs'      => $num_favs
        ]);

    }

}