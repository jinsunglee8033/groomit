<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/19/18
 * Time: 4:02 PM
 */

namespace App\Http\Controllers\User\Pets;


use App\Http\Controllers\Controller;
use App\Lib\PetProcessor;
use App\Model\Breed;
use App\Model\Pet;
use App\Model\PetPhoto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DogsController extends Controller
{

    public function show() {
        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'DOG PROFILE');

        $dogs = Pet::where('user_id', Auth::guard('user')->user()->user_id)
            ->where('type', 'dog')
            ->where('status', 'A')
            ->get();

        if (count($dogs) > 0) {
            foreach ($dogs as $o) {
                $o->age = PetProcessor::get_age($o->pet_id);
                $photo = PetPhoto::where('pet_id', $o->pet_id)->first();
                if (!empty($photo)) {
                    try{
                        $o->photo = base64_encode($photo->photo);
                    } catch (\Exception $ex) {
                        $o->photo = $photo->photo ;
                    }
                }
            }
        }

        $breeds = Breed::orderBy('sorting')->get();

        return view('user.pets.dogs', [
            'dogs' => $dogs,
            'breeds' => $breeds
        ]);
    }

}