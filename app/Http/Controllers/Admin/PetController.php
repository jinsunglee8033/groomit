<?php

namespace App\Http\Controllers\Admin;

use App\Model\AppointmentPet;
use App\Model\VWPetGroomerNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\User;
use App\Model\Size;
use App\Model\Breed;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Input;
use Log;
use Validator;
use Redirect;
use Excel;


class PetController extends Controller
{
    public function pets(Request $request) {

        try {

            $sdate = Carbon::today()->subMonths(2);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $query = Pet::select('pet.pet_id',
                'pet.user_id',
                'pet.name',
                'pet.type',
                'pet.size',
                'pet.dob',
                'pet.age',
                'pet.gender',
                'pet.breed',
                'pet.cdate')
            ->selectRaw('(TIMESTAMPDIFF(YEAR, IfNull(pet.dob, pet.cdate - interval pet.age year), curdate())) as new_dob');
            ;
            if (!empty($sdate) && empty($request->id)) {
                $query = $query->where('pet.cdate', '>=', $sdate);
            }

            if (!empty($edate) && empty($request->id)) {
                $query = $query->where('pet.cdate', '<=', $edate);
            }

            if (!empty($request->name)) {
                $query = $query->whereRaw('LOWER(pet.name) like \'%' . strtolower($request->name) . '%\'');
            }

            if (!empty($request->size)) {
                $query = $query->where('pet.size', 'like', '%' . $request->size . '%');
            }

            if (!empty($request->pet_type)) {
                $query = $query->where('pet.type', '=', $request->pet_type);
            }

            if (!empty($request->owner)) {
                $query = $query->leftJoin('user', 'user.user_id', '=', 'pet.user_id')
                    ->whereRaw('(LOWER(user.first_name) like \'%' . strtolower($request->owner) . '%\' or LOWER(user.last_name) like \'%' . strtolower($request->owner) . '%\')');
            }

            if (!empty($request->gender)) {
                $query = $query->where('pet.gender', 'like', '%' .$request->gender . '%');
            }

            if (!empty($request->breed)) {
                $query = $query->where('pet.breed', 'like', '%' .$request->breed . '%');
            }

            if ($request->excel == 'Y') {
                $pets = $query->orderBy('cdate', 'desc')->get();
                Excel::create('pets', function($excel) use($pets) {

                    $excel->sheet('reports', function($sheet) use($pets) {

                        $data = [];
                        foreach ($pets as $a) {

                            $owner = User::where('user_id', $a->user_id)->first();
                            if(!empty($owner)) {
                                $a->owner = empty($owner->first_name) ? '' : $owner->first_name . ' ' . empty($owner->last_name) ? '' : $owner->last_name;
                                $a->size_name = empty($a->size) ? '' : Size::where('size_id', $a->size)->first()->size_name;
                                $a->breed_name = empty($a->breed) ? '' : Breed::where('breed_id', $a->breed)->first()->breed_name;
                                $row = [
                                    'Pet ID' => $a->pet_id,
                                    'User ID' => $a->owner,
                                    'Name' => $a->name,
                                    'Size' => $a->size_name,
                                    'Age' => $a->new_dob,
                                    'Gender' => $a->gender,
                                    'Breed' => $a->breed_name,
                                    'Date' => $a->cdate
                                ];

                                $data[] = $row;
                            }

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $total = $query->count();

            $pets = $query->orderBy('cdate', 'desc')
                ->paginate(20);

            foreach ($pets as $p) {
                $owner = User::where('user_id', $p->user_id)->first();
                if($owner) {
                    $p->owner = $owner->first_name . ' ' . $owner->last_name;
                }
                if ($p->size) {
                    $p->size = Size::where('size_id', $p->size)->first()->size_name;
                }
                if ($p->breed) {
                    $p->breed  = Breed::where('breed_id', $p->breed)->first()->breed_name;
                }
                if ($p->type != 'cat') {
                    $p->type = 'dog';
                }
            }


            return view('admin.pets', [
                'msg' => '',
                'pets' => $pets,
                'sdate' => $sdate->format('Y-m-d'),
                'edate' => $edate->format('Y-m-d'),
                'name' => $request->name,
                'gender' => $request->gender,
                'pet_type' => $request->pet_type,
                'breed' => $request->breed,
                'owner' => $request->owner,
                'size' => $request->size,
                'total' => $total
            ]);



        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'. $ex->getTraceAsString()
            ]);
        }

    }

    public function pet($id) {
        try {
            $p = Pet::select('pet.*')
                ->selectRaw('(TIMESTAMPDIFF(YEAR, IfNull(pet.dob, pet.cdate - interval pet.age year), curdate())) as new_dob')
                ->where('pet_id', $id)->first();


            $owner = User::where('user_id', $p->user_id)->first();
            $p->owner = $owner->first_name . ' ' . $owner->last_name;
            $p->size_name = empty($p->size) ?  '' : Size::where('size_id', $p->size)->first()->size_name;
            $p->breed_name  = empty($p->breed) ? '' : Breed::where('breed_id', $p->breed)->first()->breed_name;
            $p->dob_input = Carbon::parse($p->dob)->format('Y-m-d');

            $breeds = Breed::orderBy('sorting')->get();
            if (empty($breeds)) {
                $breeds = [];
            }

            $sizes = Size::all();
            if (empty($sizes)) {
                $sizes = [];
            }

            $pet_photo = PetPhoto::where('pet_id', $p->pet_id)->orderBy('cdate', 'desc')->first();

            $groomer_notes = VWPetGroomerNote::where('pet_id', $p->pet_id)
                ->whereRaw('ifnull(groomer_note, \'\') <> \'\'')
                ->orderBy('appointment_id', 'desc')->get();


            if ($pet_photo) {
                //$p->photo = base64_encode($pet_photo->photo);
                try{
                    $p->photo = base64_encode($pet_photo->photo);
                } catch (\Exception $ex) {
                    $p->photo = $pet_photo->photo ;
                }

            }

            if ($p->vaccinated_image_name) {
                try{
                    $p->vaccinated_image = base64_encode($p->vaccinated_image);
                } catch (\Exception $ex) {
                    $p->vaccinated_image = $p->vaccinated_image ;
                }
            }

            return view('admin.pet', [
                'msg' => '',
                'pet' => $p,
                'sizes' => $sizes,
                'breeds' => $breeds,
                'groomer_notes' => $groomer_notes
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function update(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'id' => 'required',
                'name' => 'required',
                'size' => 'required',
                'breed' => 'required',
                'vaccinated' => 'required',
                'dob' => 'required',
                'gender' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return $msg;
            }

            $key = 'vaccinated_image';
            $vaccinated_image = '';
            $vaccinated_image_name = '';
            if (Input::hasFile($key)) {

                if (!Input::file($key)->isValid()) {
                    return 'Upload file is not valid';
                }

                $path = Input::file($key)->getRealPath();
                //$vaccinated_image = base64_decode(file_get_contents($path));
                $vaccinated_image = file_get_contents($path);
                $vaccinated_image_name = Input::file($key)->getClientOriginalName();
            }

            $c = Pet::findOrFail($request->id);
            $c->name = $request->name;
            $c->size = $request->size;
            $c->breed = $request->breed;
            $c->vaccinated = $request->vaccinated;
            $c->vaccinated_exp_date = $request->vaccinated_exp_date;

            if (!empty($vaccinated_image)) {
                $c->vaccinated_image = $vaccinated_image;
                $c->vaccinated_image_name = $vaccinated_image_name;
            }

            $c->dob = $request->dob;
            $c->gender = $request->gender;
            $c->vet = $request->vet;
            $c->vet_phone = $request->vet_phone;
            $c->special_note = $request->special_note;
            $c->coat_type = $request->coat_type;
            $c->last_groom = $request->last_groom;
            $c->temperament = $request->temperament;
            $c->save();

            return "Success";

        } catch (\Exception $ex) {

            return $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();
        }
    }

    public function remove($id) {

        try {
            if (!$id) {
                return 'Pet id is required.';
            }

            $c = Pet::find($id);
            $c->status = 'D';
            $c->save();

            return "Success";

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return $msg;
        }
    }
}
