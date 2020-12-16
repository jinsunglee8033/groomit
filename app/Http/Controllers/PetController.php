<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/3/16
 * Time: 5:29 PM
 */

namespace App\Http\Controllers;

use App\Lib\ImageProcessor;
use App\Model\PetPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Lib\Helper;
use App\Model\User;
use App\Model\Pet;
use Carbon\Carbon;
use App\Model\Breed;
use App\Model\Size;
use Log;
use DB;
use URL;

class PetController extends Controller
{

    public function get_by_id(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'pet_id' => 'required'
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [PET_GID]'
                ]);
            }


            $pet = DB::select("
                        select 
                            p.*,
                            b.breed_name,
                            s.size_name,
                            s.size as size_short_name,
                            s.size_desc
                        from pet p
                            inner join breed b on p.breed = b.breed_id
                            inner join size s on p.size = s.size_id
                        where p.pet_id = :pet_id
                        and p.status = 'A'
                    ", ['pet_id' => $request->pet_id]);

            if (empty($pet)) {
                $pet = Pet::where('pet_id', $request->pet_id)->get();
            }

            if (!empty($pet)) {
                $pet[0]->gender_desc = $pet[0]->gender == "F" ? "Female" : "Male";
                $pet[0]->vaccinated_desc = $pet[0]->vaccinated == "Y" ? "Vaccinated" : "Not Vaccinated";
            }

            return response()->json([
                'msg' => '',
                'pet' => $pet
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function show_pet_image($photo_id) {

        $pet_photo = PetPhoto::where('photo_id', $photo_id)->first();
        if (empty($pet_photo)) {
            echo 'No-Pet-Image Found';
            return null;
        }

        return response($pet_photo->photo, 200)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Transfer-Encoding', 'binary')
            ->header('Content-Length', strlen($pet_photo->photo));
        //Test This one too.
//        return response()
//            ->view('hello', $data)
//            ->header('Content-Type', $type);
    }

    public function add_pet(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'name' => 'required',
                //'dob' => 'required|date_format:m/d/Y',
                'gender' => 'required|in:M,F',
                //'breed' => 'required|regex:/^\d+$/', // there is no breed for cat
                //'size' => 'required|regex:/^\d+$/', // there is no size for cat
                'special_note' => 'max:2000',
                'vaccinated' => 'required|in:Y,N',
                'vet_phone' => 'regex:/^\d{10}$/',
                'image' => ''
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [PET_ADD]'
                ]);
            }

            $pet_name = $request->name;
            $pet_type = empty($request->type) ? 'dog' : $request->type;

            $pet_dob = null;
            if (isset($request->dob)) {
                $pet_dob = Carbon::createFromFormat('m/d/Y H:i:s', $request->dob . ' 00:00:00');
            }

            $pet = Pet::whereRaw('lower(name) = ?', [strtolower($pet_name)])
                ->where('dob', $pet_dob)
                ->where('age', $request->age)
                ->where('user_id', $user->user_id)
                ->where('status', 'A')
                ->first();
            if (!empty($pet)) {
                return response()->json([
                    'msg' => 'Pet with same name and age exists already'
                ]);
            }

            DB::beginTransaction();

            $pet = new Pet;
            $pet->user_id = $user->user_id;
            $pet->name = $pet_name;
            if ($pet_dob) {
                $pet->dob = $pet_dob;
            }
            $pet->type = $pet_type;
            $pet->age = $request->age;
            $pet->gender = $request->gender;
            $pet->breed = $request->breed;
            $pet->size = $request->size;
            $pet->special_note = $request->special_note;
            $pet->vaccinated = $request->vaccinated;
            $pet->vet = $request->vet;
            $pet->vet_phone = $request->vet_phone;
            $pet->coat_type = $request->coat_type;
            $pet->last_groom = $request->last_groom;
            $pet->temperament = $request->temperament;
            $pet->cdate = Carbon::now();

            $pet->save();

            if (!empty($request->photo)) {
                $photo = json_decode($request->photo);
                if (!empty($photo)) {
                    DB::statement("
                        delete from pet_photo
                        where pet_id = :pet_id
                    ", [
                        'pet_id' => $pet->pet_id
                    ]);

                    if (!is_array($photo)) {

                        $pet_photo = new PetPhoto;
                        $pet_photo->pet_id = $pet->pet_id;
                        $pet_photo->photo = ImageProcessor::optimize(base64_decode($photo));
                        $pet_photo->cdate = Carbon::now();
                        $pet_photo->save();

                    } else {
                        foreach ($photo as $p) {
                            $pet_photo = new PetPhoto;
                            $pet_photo->pet_id = $pet->pet_id;
                            $pet_photo->photo = ImageProcessor::optimize(base64_decode($p->photo));
                            $pet_photo->cdate = Carbon::now();
                            $pet_photo->save();
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'msg' => '',
                'pet_id' => $pet->pet_id
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_pet(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'pet_id' => 'required',
                'name' => 'required',
                //'dob' => 'required|date_format:m/d/Y',
                //'age' => 'regex:/^\d+$/',
                'gender' => 'required|in:M,F',
                //'breed' => 'required|regex:/^\d+$/', // there is no breed for cat
                //'size' => 'required|regex:/^\d+$/', // there is no size for cat
                'special_note' => 'max:2000',
                'vaccinated' => 'required|in:Y,N',
                'vet_phone' => 'regex:/^\d{10}$/',
//                'coat_type' => 'required',
//                'last_groom' => 'required',
//                'temperament' => 'required',
                'image' => ''
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', $email)->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [PET_UPD]'
                ]);
            }

            $pet_name = $request->name;

            $pet_dob = null;
            if (isset($request->dob)) {
                $pet_dob = Carbon::createFromFormat('m/d/Y H:i:s', $request->dob . ' 00:00:00');
            }

            $dup_pet = Pet::whereRaw('lower(name) = ?', [strtolower($pet_name)])
                ->where('dob', $pet_dob)
                ->where('age', $request->age)
                ->where('pet_id', '!=', $request->pet_id)
                ->where('user_id', $user->user_id)
                ->where('status', 'A')
                ->first();
            if (!empty($dup_pet)) {
                return response()->json([
                    'msg' => 'Pet with same name and age exists already'
                ]);
            }

            $pet = Pet::where('pet_id', $request->pet_id)->first();
            if (empty($pet)) {
                return response()->json([
                    'msg' => 'Invalid pet ID provided'
                ]);
            }

            DB::beginTransaction();

            $pet->user_id = $user->user_id;
            $pet->name = $pet_name;
            if ($pet_dob) {
                $pet->dob = $pet_dob;
            }
            $pet->type = empty($request->type) ? 'dog' : $request->type;
            $pet->age = $request->age;
            $pet->gender = $request->gender;
            $pet->breed = $request->breed;
            $pet->size = $request->size;
            $pet->special_note = $request->special_note;
            $pet->vaccinated = $request->vaccinated;
            $pet->vet = $request->vet;
            $pet->vet_phone = $request->vet_phone;
            $pet->coat_type = $request->coat_type;
            $pet->last_groom = $request->last_groom;
            $pet->temperament = $request->temperament;
            $pet->mdate = Carbon::now();

            $pet->save();


            if (!empty($request->photo)) {
                $photo = json_decode($request->photo);
                if (!empty($photo)) {
                    DB::statement("
                        delete from pet_photo
                        where pet_id = :pet_id
                    ", [
                        'pet_id' => $pet->pet_id
                    ]);

                    if (!is_array($photo)) {
                        $pet_photo = new PetPhoto;
                        $pet_photo->pet_id = $pet->pet_id;
                        $pet_photo->photo = ImageProcessor::optimize(base64_decode($photo));
                        $pet_photo->cdate = Carbon::now();
                        $pet_photo->save();
                    } else {
                        foreach ($photo as $p) {
                            $pet_photo = new PetPhoto;
                            $pet_photo->pet_id = $pet->pet_id;
                            $pet_photo->photo = ImageProcessor::optimize(base64_decode($p->photo));
                            $pet_photo->cdate = Carbon::now();
                            $pet_photo->save();
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'msg' => '',
                'pet_id' => $pet->pet_id
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function remove_pet(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'pet_id' => 'required'
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $pet = Pet::where('pet_id', $request->pet_id)->first();
            if (empty($pet)) {
                return response()->json([
                    'msg' => 'Invalid pet ID provided'
                ]);
            }

            $pet->status = 'D';
            $pet->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_user_pets(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required'
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [PET_USRPET]'
                ]);
            }


            $pets = Pet::where('user_id', $user->user_id)->where('status', 'A')->get();

            # Pet photo
            foreach($pets as $p) {

                $p->photo = '';

                $pet_photo = PetPhoto::where('pet_id', $p->pet_id)->orderBy('cdate', 'desc')->first();

                if ($pet_photo) {
                    //$p->photo = base64_encode($pet_photo->photo);
                    try{
                        $p->photo = base64_encode($pet_photo->photo);
                    } catch (\Exception $ex) {
                        $p->photo = $pet_photo->photo;
                    }
                }
            }


            return response()->json([
                'msg' => '',
                'pets' => $pets
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'. $ex->getTraceAsString()
            ]);
        }
    }

    public function get_user_dogs(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required'
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [PET_USRDG]'
                ]);
            }


            $pets = Pet::where('user_id', $user->user_id)->where('status', 'A')
                ->where(function($q) {
                    $q->where('type', 'dog')
                        ->orWhereNull('type');
                })
                ->get();


            # Pet photo
            foreach($pets as $p) {

                $p->photo = '';

                $pet_photo = PetPhoto::where('pet_id', $p->pet_id)->orderBy('cdate', 'desc')->first();

                if ($pet_photo) {
                    //$p->photo = base64_encode($pet_photo->photo);
                    try{
                        $p->photo = base64_encode($pet_photo->photo);
                    } catch (\Exception $ex) {
                        $p->photo = $pet_photo->photo ;
                    }

                }
            }


            return response()->json([
                'msg' => '',
                'pets' => $pets
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'. $ex->getTraceAsString()
            ]);
        }
    }

    public function get_user_cats(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required'
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [PET_USRCT]'
                ]);
            }


            $pets = Pet::where('user_id', $user->user_id)->where('status', 'A')->where('type', 'cat')->get();

            # Pet photo
            foreach($pets as $p) {

                $p->photo = '';

                $pet_photo = PetPhoto::where('pet_id', $p->pet_id)->orderBy('cdate', 'desc')->first();

                if ($pet_photo) {
                    //$p->photo = base64_encode($pet_photo->photo);
                    try{
                        $p->photo = base64_encode($pet_photo->photo);
                    } catch (\Exception $ex) {
                        $p->photo = $pet_photo->photo ;
                    }

                }
            }


            return response()->json([
                'msg' => '',
                'pets' => $pets
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'. $ex->getTraceAsString()
            ]);
        }
    }

    public function get_pet_photos(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'pet_id' => 'required'
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

//            $pet_photos = PetPhoto::where('pet_id', $request->pet_id)->get([
//                'photo_id',
//                'pet_id',
//                'cdate'
//            ]);
//
//            if (empty($pet_photos)) {
//                $pet_photos = [];
//            }
//
//            $new_photos = [];
//            if (count($pet_photos) > 0) {
//
//                foreach ($pet_photos as $o) {
//                    $photo_id = $o->photo_id;
//                    $pet_id = $o->pet_id;
//                    $cdate = $o->cdate;
//                    $url = 'http://demo.groomit.me/pet-photo/' . $photo_id;
//
//                    $new_photos[] = [
//                        'photo_id' => $photo_id,
//                        'pet_id' => $pet_id,
//                        'image_url' => $url,
//                        'cdate' => $cdate
//                    ];
//                }
//            }
//
//            return response()->json([
//                'msg' => '',
//                'pet_photos' => $new_photos
//            ]);


            $pet_photo = PetPhoto::where('pet_id', $request->pet_id)->orderBy('cdate', 'desc')->first();


            if ($pet_photo) {
                //$pet_photo->photo = base64_encode($pet_photo->photo);
                try{
                    $pet_photo->photo = base64_encode($pet_photo->photo);
                } catch (\Exception $ex) {
                    $pet_photo->photo = $pet_photo->photo ;
                }
            }

            return response()->json([
                'msg' => '',
                'pet_photo' => $pet_photo
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_breeds(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required'
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $breeds = Breed::orderBy('sorting')->where('breed_name', '<>', 'Others / Mixed')->get();
            if (empty($breeds)) {
                $breeds = [];
            }
            $breed_other = Breed::where('breed_name', 'Others / Mixed')->first();
            $breeds->push($breed_other);

            return response()->json([
                'msg' => '',
                'breeds' => $breeds
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_sizes(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required'
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

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $sizes = Size::all();
            if (empty($sizes)) {
                $sizes = [];
            }

            return response()->json([
                'msg' => '',
                'sizes' => $sizes
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}