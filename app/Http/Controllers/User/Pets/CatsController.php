<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/19/18
 * Time: 4:03 PM
 */

namespace App\Http\Controllers\User\Pets;


use App\Http\Controllers\Controller;
use App\Lib\PetProcessor;
use App\Model\Pet;
use App\Model\PetPhoto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CatsController extends Controller
{

    public function show() {

        Session::put('user.menu.show', 'Y');
        Session::put('user.menu.top-title', 'CAT PROFILE');

        $cats = Pet::where('user_id', Auth::guard('user')->user()->user_id)
            ->where('type', 'cat')
            ->where('status', 'A')
            ->get();

        if (count($cats) > 0) {
            foreach ($cats as $o) {
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

        return view('user.pets.cats', [
            'cats' => $cats
        ]);
    }

}