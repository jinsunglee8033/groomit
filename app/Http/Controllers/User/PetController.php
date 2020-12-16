<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/13/18
 * Time: 3:23 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\GroomerDocument;
use App\Model\Pet;
use App\Model\PetPhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Redirect;

class PetController extends Controller
{

    public function add(Request $request) {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'type' => 'required',
                'name' => 'required',
                'age' => 'required',
                'gender' => 'required|in:M,F',
                'breed' => 'required_if:type,dog',
                'size' => 'required_if:type,dog|in:2,3,4,5',
                'temperment' => 'required',
                'vaccinated' => 'required',
                'vet' => '',
                'vet_phone' => '',
                'last_groom' => 'required_if:type,dog',
                'coat_type' => 'required_if:type,dog',
                'pet_photo' => ''
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }
                DB::rollBack();
                $this->output_error($msg);
            }

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

            $user_id = Auth::guard('user')->user()->user_id;

            $pet = Pet::where('user_id', $user_id)
                ->where('type', $request->type)
                ->whereRaw("lower(trim(name)) = ?", [strtolower(trim($request->name))])
                ->first();

            if (!empty($pet)) {
                DB::rollBack();
                $this->output_error('A ' . $request->type . ' with same name already exists');
            }

            $pet = new Pet;
            $pet->user_id = $user_id;
            $pet->name = $request->name;
            $pet->type = $request->type;
            $pet->age = $request->age;
            $pet->gender = $request->gender;
            $pet->breed = $request->breed;
            $pet->size = $request->size;
            $pet->special_note = $request->special_note;
            $pet->vaccinated = $request->vaccinated;
            $pet->vet = $request->vet;
            $pet->vet_phone = $request->vet_phone;

            if(!empty($pet_certificate)) {
                $pet->vaccinated_image = $pet_certificate;
                $pet->vaccinated_image_name = $certificate_name;
            }

            $pet->status = 'A';
            $pet->last_groom = $request->last_groom;
            $pet->temperament = $request->temperment;
            $pet->coat_type = $request->coat_type;
            $pet->cdate = Carbon::now();

            $pet->save();

            if (!empty($pet_image)) {
                $pet_photo = new PetPhoto;
                $pet_photo->pet_id = $pet->pet_id;
                $pet_photo->photo = $pet_image;
                $pet_photo->cdate = Carbon::now();
                $pet_photo->save();
            }

            DB::commit();

            $this->output_success();

        } catch (\Exception $ex) {

            DB::rollback();

            $this->output_error($ex->getMessage() . ' [' . $ex->getCode() . ']');

        }
    }

    public function update(Request $request) {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'pet_id' => 'required',
                'type' => 'required',
                'name' => 'required',
                'age' => 'required',
                'gender' => 'required|in:M,F',
                'breed' => 'required_if:type,dog',
                'size' => 'required_if:type,dog|in:2,3,4,5',
                'temperment' => 'required',
                'vaccinated' => 'required',
                'vet' => '',
                'vet_phone' => '',
                'last_groom' => 'required_if:type,dog',
                'coat_type' => 'required_if:type,dog',
                'pet_photo' => 'mimes:jpeg,png,bmp,tiff,gif,jpg'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                DB::rollback();

                $this->output_error($msg);
            }

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

            $user_id = Auth::guard('user')->user()->user_id;

            $pet = Pet::find($request->pet_id);
            if (empty($pet)) {

                DB::rollback();

                $this->output_error('Unable to find pet with ID: ' . $request->pet_id);
            }

            $pet->user_id = $user_id;
            $pet->name = $request->name;
            $pet->type = $request->type;
            $pet->age = $request->age;
            $pet->gender = $request->gender;
            $pet->breed = $request->breed;
            $pet->size = $request->size;
            $pet->special_note = $request->special_note;
            $pet->vaccinated = $request->vaccinated;

            if(!empty($pet_certificate)) {
                $pet->vaccinated_image = $pet_certificate;
                $pet->vaccinated_image_name = $certificate_name;
            }

            $pet->vet = $request->vet;
            $pet->vet_phone = $request->vet_phone;
            $pet->status = 'A';
            $pet->last_groom = $request->last_groom;
            $pet->temperament = $request->temperment;
            $pet->coat_type = $request->coat_type;
            $pet->cdate = Carbon::now();

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

            $this->output_success();

        } catch (\Exception $ex) {

            DB::rollback();
            $this->output_error($ex->getMessage() . ' [' . $ex->getCode() . ']');
        }
    }

    public function load(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'pet_id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $pet = Pet::find($request->pet_id);
            if (empty($pet)) {
                return response()->json([
                    'msg' => 'Unable to find pet'
                ]);
            }

            if (!empty($pet->vaccinated_image)) {
                try{
                    $pet->vaccinated_image = base64_encode($pet->vaccinated_image);
                } catch (\Exception $ex) {
                    //$pet->vaccinated_image = $pet->vaccinated_image ;
                    //no changes.
                }
            }

            $photo = PetPhoto::where('pet_id', $pet->pet_id)->first();

            if (!empty($photo)) {
                try{
                    $pet->photo = base64_encode($photo->photo);
                } catch (\Exception $ex) {
                    $pet->photo = $photo->photo ;
                }
            }

            if (!empty($pet->vaccinated_image)) {
                try{
                    $pet->certificate = base64_encode($pet->vaccinated_image);
                } catch (\Exception $ex) {
                    $pet->certificate = $pet->vaccinated_image;
                }
            }

            return response()->json([
                'msg' => '',
                'data' => $pet
            ]);

        } catch (\Exception $ex) {
            //Helper::log('### photo :outer exception ###', $ex->getMessage() . ' [' . $ex->getCode() . ']' );

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);

        }
    }

    private function output_error($msg) {
        echo "<script>";
        echo "parent.myApp.hideLoading();";
        echo "parent.myApp.showError(\"" . str_replace("\"", "'", $msg) . "\");";
        echo "</script>";
        exit;
    }

    private function output_success() {
        echo "<script>";
        echo "parent.myApp.hideLoading();";
        echo "parent.close_modal();";
        echo "</script>";
        exit;
    }

}