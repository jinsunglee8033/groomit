<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 8/16/18
 * Time: 10:04 AM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\AddressProcessor;
use App\Lib\CreditProcessor;
use App\Lib\Helper;
use App\Lib\ImageProcessor;
use App\Lib\PetProcessor;
use App\Lib\ScheduleProcessor;
use App\Lib\UserProcessor;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\AppointmentList;
use App\Model\Breed;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\PromoCode;
use App\Model\RequestReferal;
use App\Model\User;
use App\Model\UserBilling;
use App\Model\UserFavoriteGroomer;
use App\Model\UserLoginHistory;
use App\Model\UserPhoto;
use Carbon\Carbon;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function myaccount() {
        try {

            Session::put('user.menu.show', 'Y');
            Session::put('user.menu.top-title', 'DOG PROFILE');

            $user = Auth::guard('user')->user();
            $user_id = $user->user_id;
            $user = User::findOrFail($user_id);
            if (empty($user)) {
                throw new \Exception('Please login first');
            }

            $user_photo = UserPhoto::where('user_id', $user_id)->first();
            $photo = '';
            if (!empty($user_photo)) {

                try{
                    $photo = base64_encode($user_photo->photo);
                } catch (\Exception $ex) {
                    $photo = $user_photo->photo ;
                }
            }

            $user->photo = $photo;

            if( stripos( $user->email, '@jjonbp.com') ||
                stripos( $user->email, '@groomit.com') ||
                stripos( $user->email, 'tatianacagnolo@gmail.com')
            ){
                $internal='Y';
            }else{
                $internal='N';
            }

            // get Dog Pets
            $user->dog_pets = Pet::leftJoin('breed', 'breed.breed_id', '=', 'pet.breed')
                ->where('pet.user_id', $user_id)
                ->where('pet.type', 'dog')
                ->where('pet.status', 'A')
                ->get();

            if(count($user->dog_pets) > 0){
                foreach ($user->dog_pets as $o) {
                    $photo = PetPhoto::where('pet_id', $o->pet_id)->first();
                    if (!empty($photo)) {
                        try {
                            $o->photo = base64_encode($photo->photo);
                        } catch (\Exception $ex) {
                            $o->photo = $photo->photo;
                        }
                    }
                    $o->age = PetProcessor::get_age($o->pet_id);
                }
            }

            // get Cat Pets
            $user->cat_pets = Pet::leftJoin('breed', 'breed.breed_id', '=', 'pet.breed')
                ->where('pet.user_id', $user_id)
                ->where('pet.type', 'cat')
                ->where('pet.status', 'A')
                ->get();

            if(count($user->cat_pets) > 0){
                foreach ($user->cat_pets as $o) {
                    $photo = PetPhoto::where('pet_id', $o->pet_id)->first();
                    if (!empty($photo)) {
                        try {
                            $o->photo = base64_encode($photo->photo);
                        } catch (\Exception $ex) {
                            $o->photo = $photo->photo;
                        }
                    }
                    $o->age = PetProcessor::get_age($o->pet_id);
                }
            }

            // get Favorite Groomers
            $user->favorite_groomers = UserFavoriteGroomer::select('groomer.*')
                ->join('groomer', 'groomer.groomer_id', '=', 'user_favorite_groomer.groomer_id')
                ->where('user_favorite_groomer.user_id', $user_id)
                ->orderBy('groomer.first_name', 'asc')
                ->get();

            foreach ($user->favorite_groomers as $g){
                $g->total_appts = AppointmentList::where('groomer_id', $g->groomer_id)->where('status', 'P')->count();
            }

            // get Addresses
            $user->addresses = Address::where('user_id', $user_id)
                ->where('status', 'A')
                ->get();

            // get Payments
            $user->payments = UserBilling::where('user_id', $user_id)
                ->where('status', 'A')
                ->orderBy('default_card', 'desc')
                ->orderBy('billing_id', 'desc')
                ->get();

            $user->available_credit = CreditProcessor::getAvailableCredit($user_id);

            $breeds = Breed::orderBy('sorting')->get();
            $states = Helper::get_states();
            $years = Helper::get_expire_years();
            $months = Helper::get_expire_months();

            return view('user.myaccount', [
                'user' => $user,
                'breeds' => $breeds,
                'states' => $states,
                'years' => $years,
                'months' => $months,
                'internal' => $internal
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function myaccount_edit() {
        try {

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Please login first');
            }

            $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
            $photo = '';
            if (!empty($user_photo)) {

                try{
                    $photo = base64_encode($user_photo->photo);
                } catch (\Exception $ex) {
                    $photo = $user_photo->photo ;
                }

            }

            $user->photo = $photo;

            return view('user.myaccount-edit', [
                'user' => $user
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function user_update(Request $request) {


        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required|regex:/^\d{10}$/'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "\n") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Please login first');
            }

            ### check if current password is correct

            if(!empty($request->c_password)) {
                if (!empty($user->passwd)) {
                    $decrypted_passwd = \Crypt::decrypt($user->passwd);
                    if ($decrypted_passwd != $request->c_password) {
                        return response()->json([
                            'msg' => 'Invalid password provided.'
                        ]);
                    }
                }else{
                    return response()->json([
                        'msg' => 'User Not Exist'
                    ]);
                }
            }


            ### check if phone already exists && active ###
            if($user->phone != $request->phone) {
                $temp_user = User::where('phone', $request->phone)
                    ->where('status', 'A')
                    ->first();

                if (!empty($temp_user)) {
                    DB::rollback();

                    return response()->json([
                        'msg' => 'Your Phone Number already exists.'
                    ]);
                }
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->phone = $request->phone;
            $user->dog = $request->dog;
            $user->cat = $request->cat;

            if (!empty($request->password)) {
                $user->passwd = \Crypt::encrypt($request->password);
            }

            //$user->cdate = Carbon::now();

            $user->save();

            ### save photo ###
            if (!empty($request->photo)) {
                $photo = UserPhoto::where('user_id', $user->user_id)->first();
                if (empty($photo)) {
                    $photo = new UserPhoto;
                }

                $photo->user_id = $user->user_id;
                $photo->photo = file_get_contents($request->photo);
                $photo->cdate = Carbon::now();
                $photo->save();
            }

            DB::commit();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {

            DB::rollback();

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function dog_update(Request $request) {

        DB::beginTransaction();

        try{

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Please login first');
            }

            $user_id = $user->user_id;
            $pet_id = $request->pet_id;

            $key = 'pet_photo';
            $pet_image = '';
            if (Input::hasFile($key)) {
                if (!Input::file($key)->isValid()) {
                    DB::rollBack();
                    $this->output_error('Please select valid pet photo');
                }
                $path = Input::file($key)->getRealPath();
                $pet_image = file_get_contents($path);
            }

            $key = 'upload_certificate';
            $pet_certificate = '';
            if (Input::hasFile($key)) {
                if (!Input::file($key)->isValid()) {
                    DB::rollBack();
                    $this->output_error('Please select valid pet certificate');
                }
                $path = Input::file($key)->getRealPath();
                $contents = file_get_contents($path);
                $certificate_name = Input::file($key)->getClientOriginalName();
                $pet_certificate = base64_encode($contents);
            }

            $pet = Pet::where('pet_id', $pet_id)->where('user_id', $user_id)->first();

            $pet->name = $request->name;
            $pet->age = $request->age;
            $pet->gender = $request->gender;
            $pet->breed = $request->breed;
            $pet->size = $request->size;
            $pet->temperament = $request->temperment;
            $pet->vaccinated = $request->vaccinated;
            $pet->vet = $request->vet;
            $pet->vet_phone = $request->vet_phone;

            if(!empty($pet_certificate)) {
                $pet->vaccinated_image = $pet_certificate;
                $pet->vaccinated_image_name = $certificate_name;
            }

            $pet->last_groom = $request->last_groom;
            $pet->coat_type = $request->coat_type;
            $pet->special_note = $request->special_note;
            $pet->mdate = Carbon::now();
            $pet->save();

            if (!empty($pet_image)) {
                DB::statement("
                    delete from pet_photo
                    where pet_id = :pet_id
                ", [
                    'pet_id' => $pet->pet_id
                ]);
                $pet_photo = new PetPhoto;
                $pet_photo->pet_id = $pet->pet_id;
                $pet_photo->photo = $pet_image;
                $pet_photo->cdate = Carbon::now();
                $pet_photo->save();
            }

            DB::commit();

            return redirect('/user/myaccount');

        } catch (\Exception $ex) {

            DB::rollback();

            return redirect('/user/myaccount');
        }
    }

    public function cat_update(Request $request) {

        DB::beginTransaction();

        try{

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Please login first');
            }

            $user_id = $user->user_id;
            $pet_id = $request->cat_pet_id;

            $key = 'cat_pet_photo';
            $pet_image = '';
            if (Input::hasFile($key)) {
                if (!Input::file($key)->isValid()) {
                    DB::rollBack();
                    $this->output_error('Please select valid pet photo');
                }
                $path = Input::file($key)->getRealPath();
                $pet_image = file_get_contents($path);
            }

            $key = 'upload_certificate_cat';
            $pet_certificate = '';
            if (Input::hasFile($key)) {
                if (!Input::file($key)->isValid()) {
                    DB::rollBack();
                    $this->output_error('Please select valid pet certificate');
                }
                $path = Input::file($key)->getRealPath();
                $contents = file_get_contents($path);
                $certificate_name = Input::file($key)->getClientOriginalName();
                $pet_certificate = base64_encode($contents);
            }

            $pet = Pet::where('pet_id', $pet_id)->where('user_id', $user_id)->first();

            $pet->name = $request->cat_name;
            $pet->age = $request->cat_age;
            $pet->gender = $request->cat_gender;
            $pet->temperament = $request->cat_temperment;
            $pet->vaccinated = $request->cat_vaccinated;
            $pet->vet = $request->cat_vet;
            $pet->vet_phone = $request->cat_vet_phone;

            if(!empty($pet_certificate)) {
                $pet->vaccinated_image = $pet_certificate;
                $pet->vaccinated_image_name = $certificate_name;
            }

            $pet->last_groom = $request->cat_last_groom;
            $pet->coat_type = $request->cat_coat_type;
            $pet->special_note = $request->cat_special_note;
            $pet->mdate = Carbon::now();
            $pet->save();

            if (!empty($pet_image)) {
                DB::statement("
                    delete from pet_photo
                    where pet_id = :pet_id
                ", [
                    'pet_id' => $pet->pet_id
                ]);
                $pet_photo = new PetPhoto;
                $pet_photo->pet_id = $pet->pet_id;
                $pet_photo->photo = $pet_image;
                $pet_photo->cdate = Carbon::now();
                $pet_photo->save();
            }

            DB::commit();

            return redirect('/user/myaccount');

        } catch (\Exception $ex) {

            DB::rollback();

            return redirect('/user/myaccount');
        }
    }

    public function address_update(Request $request) {

        try{

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Please login first');
            }

            $user_id = $user->user_id;

            $ret = AddressProcessor::update(
                $user_id,
                $request->address_id,
                $request->address1,
                $request->address2,
                $request->city,
                $request->state,
                $request->zip
            );

            if (!empty($ret['msg'])) {
                return response()->json([
                    'msg' => $ret['msg']
                ]);
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function load() {
        try {

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Please login first');
            }

            $first_name = $user->first_name;
            $last_name = $user->last_name;
            $phone = $user->phone;

            $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
            $photo = '';
            if (!empty($user_photo)) {
                //$photo = base64_encode($user_photo->photo);
                try{
                    $photo = base64_encode($user_photo->photo);
                } catch (\Exception $ex) {
                    $photo = $user_photo->photo ;
                }

            }

            return [
                'msg' => '',
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'photo' => $photo
            ];

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function load_temp() {
        try {

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                throw new \Exception('Please login first');
            }

            if(!empty($user->phone)) {
                return [
                    'msg' => 'phone'
                ];
            }

            $first_name = $user->first_name;
            $last_name = $user->last_name;
            $phone = $user->phone;

            $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
            $photo = '';
            if (!empty($user_photo)) {
                //$photo = base64_encode($user_photo->photo);
                try{
                    $photo = base64_encode($user_photo->photo);
                } catch (\Exception $ex) {
                    $photo = $user_photo->photo ;
                }

            }

            return [
                'msg' => '',
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'photo' => $photo
            ];

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function update(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required',
                'photo' => 'file|mimes:jpg,jpeg,gif,png,bmp'
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
                throw new \Exception('Please login first');
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->phone = $request->phone;
            $user->modified_by = 'User: ' . $user->id;
            $user->save();

            $key = 'photo';
            if (Input::hasFile($key) && Input::file($key)->isValid()) {
                $path = Input::file($key)->getRealPath();

                Helper::log('### FILE ###', [
                    'key' => $key,
                    'path' => $path
                ]);

                $contents = file_get_contents($path);
                $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
                if (empty($user_photo)) {
                    $user_photo = new UserPhoto;
                    $user_photo->user_id = $user->user_id;
                }

                $user_photo->photo = ImageProcessor::optimize($contents);
                $user_photo->save();
            }

            return response()->json([
                'msg' => ''
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

    public function resetPassword(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => ['required', 'confirmed', 'min:6', 'regex:/(\d+\p{L}+|\p{L}+\d+)+/']
            ], [
                'new_password.regex' => 'Password should containt at least one alphabet and one digit'
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
                throw new \Exception('Please login first');
            }

            $user_id = $user->user_id;
            $password = $request->current_password;

            if (!Auth::guard('user')->attempt(compact('user_id', 'password'))) {
                throw new \Exception('Password does not match: ' . $request->current_password);
            }

            $user->passwd = \Crypt::encrypt($request->new_password);
            $user->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage()
            ]);
        }
    }

}