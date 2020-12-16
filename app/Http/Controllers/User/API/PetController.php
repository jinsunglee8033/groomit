<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/15/18
 * Time: 6:17 AM
 */

namespace App\Http\Controllers\User\API;

use App\Console\Commands\UserStat;
use App\Http\Controllers\Controller;
use App\Lib\Helper;

use App\Lib\ImageProcessor;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use stdClass;

class PetController extends Controller
{
    public function show(Request $request) {

        ############### START VALIDATION ###########
        ############################################
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
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $pets = DB::select("
            select 
                pet_id, name, type, age, gender, breed, size, temperament, special_note, matted, sprayed,
                vaccinated, vaccinated_exp_date, vaccinated_image, vaccinated_image_name, vet, vet_phone, last_groom, coat_type
              from pet 
             where user_id = :user_id
               and status = 'A'
            ", [
          'user_id' => $user->user_id
        ]);

        if (!empty($pets) && count($pets) > 0) {
            foreach ($pets as $p) {
                $photo = PetPhoto::where('pet_id', $p->pet_id)->first();

                if (!empty($photo)) {
                    try{
                        $p->photo = base64_encode($photo->photo);
                    } catch (\Exception $ex) {
                        $p->photo = $photo->photo ;
                    }
                }

                if (!empty($p->vaccinated_image)) {
                    try{
                        $p->vaccinated_image = base64_encode($p->vaccinated_image);
                    } catch (\Exception $ex) {
                        $p->vaccinated_image = $p->vaccinated_image ;
                    }

                }
            }
        }

        return response()->json([
            'code' => '0',
            'pets' => $pets
        ]);
    }

    public function available_type(Request $request) {

        ############### START VALIDATION ###########
        ############################################
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
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $dogs = Pet::where('user_id', $user->user_id)->where('type', 'dog')->count();
        $cats = Pet::where('user_id', $user->user_id)->where('type', 'cat')->count();

        return response()->json([
            'code' => '0',
            'dogs' => $dogs,
            'cats' => $cats
        ]);
    }

    public function dogs(Request $request) {
        return $this->pet_list_by_type($request, 'dog');
    }

    public function cats(Request $request) {
        return $this->pet_list_by_type($request, 'cat');
    }

    private function pet_list_by_type(Request $request, $type) {

        ############### START VALIDATION ###########
        ############################################
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
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $pets = DB::select("
            select 
                pet_id, name, type, age, gender, breed, size, temperament, special_note, matted, sprayed,
                vaccinated, vaccinated_exp_date, vaccinated_image, vaccinated_image_name, vet, vet_phone, last_groom, coat_type
              from pet 
             where user_id = :user_id
               and type = :type
               and status = 'A'
            ", [
            'user_id' => $user->user_id,
            'type'  => $type
        ]);

        if (!empty($pets) && count($pets) > 0) {
            foreach ($pets as $p) {
                $photo = PetPhoto::where('pet_id', $p->pet_id)->first();

                if (!empty($photo)) {
                    try{
                        $p->photo = base64_encode($photo->photo);
                    } catch (\Exception $ex) {
                        $p->photo = $photo->photo ;
                    }
                }

                try{
                    $p->vaccinated_image = base64_encode($p->vaccinated_image);
                } catch (\Exception $ex) {
                    $p->vaccinated_image = $p->vaccinated_image ;
                }
            }
        }

        return response()->json([
            'code'  => '0',
            'pets'  => $pets
        ]);
    }

    public function save(Request $request) {

        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
              'api_key'     => 'required',
              'token'       => 'required',
              'type'        => 'required',
              'name'        => 'required',
              'age'         => 'required',
              'gender'      => 'required|in:M,F',
              'breed'       => 'required_if:type,dog',
              'size'        => 'required_if:type,dog|in:2,3,4,5',
              'temperament' => 'required',
              'vaccinated'  => 'required',
              'vet'         => '',
              'vet_phone'   => '',
              'last_groom'  => 'required_if:type,dog',
              'coat_type'   => 'required_if:type,dog',
              'photo'       => ''
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;


            $pet = Pet::find($request->pet_id);

            if (empty($pet)) {
                $pet = new Pet();
            }

            $pet->user_id       = $user->user_id;
            $pet->name          = $request->name;
            if ($request->pet_dob) {
                $pet->dob       = $request->pet_dob;
            }
            $pet->type          = $request->type;
            $pet->age           = $request->age;
            $pet->gender        = $request->gender;
            $pet->breed         = $request->breed;
            $pet->size          = $request->size;
            $pet->special_note  = $request->special_note;
            $pet->vaccinated    = $request->vaccinated;
            $pet->vet           = $request->vet;
            $pet->vet_phone     = $request->vet_phone;
            $pet->status        = 'A';
            $pet->last_groom    = $request->last_groom;
            $pet->temperament   = $request->temperament;
            $pet->coat_type     = $request->coat_type;
            $pet->cdate         = Carbon::now();
            $pet->save();

            if (!empty($request->photo)) {
                $pet_photo = PetPhoto::where('pet_id', $pet->pet_id)->first();

                if (!empty($pet_photo)) {
                    //$pet_photo->photo   = ImageProcessor::optimize(base64_decode($request->photo));
                    $pet_photo->photo   = base64_decode($request->photo);
                    $pet_photo->cdate   = Carbon::now();
                    $pet_photo->update();
                } else {
                    $pet_photo = new PetPhoto();
                    $pet_photo->pet_id  = $pet->pet_id;
                    $pet_photo->photo   = base64_decode($request->photo);
                    $pet_photo->cdate   = Carbon::now();
                    $pet_photo->save();
                }
            }

            if (!empty($request->vaccinated_image)) {
                //$pet->vaccinated_image = ImageProcessor::optimize(base64_decode($request->vaccinated_image));
                $pet->vaccinated_image = base64_decode($request->vaccinated_image);
                $pet->update();
            }

            return response()->json([
                'code'    => '0',
                'msg'     => ''
            ]);

        } catch (\Exception $ex) {

            DB::rollback();

            Helper::log('#### EXCEPTION #####', $ex->getTraceAsString());

            return response()->json([
              'code'    => 'EX',
              'msg'     => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
    }

    public function remove(Request $request) {

        try {

            ############### START VALIDATION ###########
            ############################################
            $v = Validator::make($request->all(), [
                'api_key'   => 'required',
                'token'     => 'required',
                'pet_id'    => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                  'code' => '-1',
                  'msg' => $msg
                ]);
            }

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                  'code' => '-2',
                  'msg' => 'Your session has expired. Please login again. [ADR_GID]'
                ]);
            }
            ############################################
            ###############  END VALIDATION  ###########

            ## FOR API CALL LOG
            $request->user_id = $user->user_id;


            $pet = Pet::find($request->pet_id);
            if (!empty($pet)) {
                if ($pet->user_id != $user->user_id) {
                    return response()->json([
                      'code' => '-2',
                      'msg' => 'You can not delete the pet.'
                    ]);
                }

                $pet->status = 'D';
                $pet->update();
            }

            return response()->json([
              'code'    => '0',
              'msg'     => ''
            ]);

        } catch (\Exception $ex) {

            return response()->json([
              'code'    => 'EX',
              'msg'     => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function breed_list(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
          'api_key'   => 'required',
          'token'     => 'required'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $breeds = DB::select("
            select 
                breed_id, breed_name
              from breed
             order by sorting asc
            ");

        return response()->json([
          'code'    => '0',
          'breeds'  => $breeds
        ]);

    }

    public function get_promo_images(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
            'api_key'   => 'required',
            'token'     => 'required'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
                'code' => '-1',
                'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
                'code' => '-2',
                'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        $user_stat = DB::select("
            select
                book_cnt
              from user_stat
             where user_id = :user_id
        ", [
            'user_id' => $user->user_id
        ]);

        $arr = array();

        if (empty($user_stat[0])){
            // First time

//            $images = new stdClass();
//            $images->url = 'https://www.groomit.me/images/CA-promo-Halloween-2020.jpg';
//            $images->link = '';
//            $images->order = 1;
//            array_push($arr,$images);

            $images = new stdClass();
            $images->url = 'https://www.groomit.me/images/wefunder-promo.jpg';
            $images->link = 'https://wefunder.com/groomit';
            $images->order = 1;
            array_push($arr,$images);

//            $images = new stdClass();
//            $images->url = 'https://www.groomit.me/images/promo_dog_new2.jpg';
//            $images->link = '';
//            $images->order = 2;
//            array_push($arr,$images);
//
//            $images = new stdClass();
//            $images->url = 'https://www.groomit.me/images/promo_cat_new2.jpg';
//            $images->link = '';
//            $images->order = 3;
//            array_push($arr,$images);

//            $images = new stdClass();
//            $images->url = 'https://www.groomit.me/images/promo_how_it_works.jpg';
//            $images->order = 3;
//            array_push($arr,$images);
//
//            $images = new stdClass();
//            $images->url = 'https://www.groomit.me/images/promo_benefits.jpg';
//            $images->order = 4;
//            array_push($arr,$images);

        }else{
            // Exist

//            $images = new stdClass();
//            $images->url = 'https://www.groomit.me/images/CA-promo-Halloween-2020.jpg';
//            $images->link = '';
//            $images->order = 1;
//            array_push($arr,$images);

            $images = new stdClass();
            $images->url = 'https://www.groomit.me/images/wefunder-promo.jpg';
            $images->link = 'https://wefunder.com/groomit';
            $images->order = 1;
            array_push($arr,$images);

//            $images = new stdClass();
//            $images->url = 'https://www.groomit.me/images/promo_dog_new2.jpg';
//            $images->link = '';
//            $images->order = 2;
//            array_push($arr,$images);
//
//            $images = new stdClass();
//            $images->url = 'https://www.groomit.me/images/promo_cat_new2.jpg';
//            $images->link = '';
//            $images->order = 3;
//            array_push($arr,$images);


        }

        return response()->json([
            'code'    => '0',
            'images'  => $arr
        ]);

    }

    public function size_list(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
          'api_key'   => 'required',
          'token'     => 'required'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;


        $sizes = DB::select("
            select size_id, size_name, size_desc, size from size
            ");

        return response()->json([
           'code' => '0',
           'sizes' => $sizes
        ]);
    }

    public function temperment_list(Request $request) {

        ############### START VALIDATION ###########
        ############################################
        $v = Validator::make($request->all(), [
          'api_key'   => 'required',
          'token'     => 'required'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        if (!Helper::check_app_key($request->api_key)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Invalid API key provided'
            ]);
        }

        $email = \Crypt::decrypt($request->token);
        $user = User::where('email', strtolower($email))->first();
        if (empty($user)) {
            return response()->json([
              'code' => '-2',
              'msg' => 'Your session has expired. Please login again. [ADR_GID]'
            ]);
        }
        ############################################
        ###############  END VALIDATION  ###########

        ## FOR API CALL LOG
        $request->user_id = $user->user_id;

        return response()->json([
            'code' => '0',
            'temperments' => [
                'Friendly',
                'Anxious',
                'Fatigue',
                'Aggressive'
            ]
        ]);
    }

}
