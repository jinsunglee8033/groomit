<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 3/29/17
 * Time: 4:37 PM
 */

namespace App\Http\Controllers\Groomer;

use App\Lib\Helper;
use App\Model\Application;
use App\Model\ApplicationAvailability;
use App\Model\ApplicationPetPhoto;
use App\Model\ApplicationTool;
use App\Model\Tool;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Log;
use Validator;
use Illuminate\Support\Facades\Input;

class ApplicationController extends Controller
{

    public function application_temp(Request $request) {

        try {

            $full_name = $request->first_name_temp;
            $last_name = $request->last_name_temp;
            $email = $request->email_temp;
            $phone = $request->phone_temp;

            $ret = Application::where('email', $email)->first();
            if (!empty($ret)){
                return back()->withErrors([
                    'exception' => 'Already Exist Email.'
                ])->withInput();
            }

            $app = new Application;
            $app->first_name    = $full_name;
            $app->last_name     = $last_name;
            $app->email         = strtolower($email);
            $app->phone         = $phone;
            $app->mobile_phone  = $phone;
            $app->status        = 'N';
            $app->cdate         = Carbon::now();
            $app->save();

            return view('application', [
                'full_name' => $full_name,
                'last_name' => $last_name,
                'email'     => $email,
                'phone'     => $phone,
                'pre_save'  => 'Y'
            ]);

        } catch (\Exception $ex) {
            return back()->withErrors([
                'exception' => $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString()
            ])->withInput();
        }
    }

    public function post(Request $request) {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'full_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'phone' => 'required|regex:/^\d{10}$/',
//                'password' => 'required',
                'groomer_how_knew_groomit' => 'required',
                'street' => 'required',
                //'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|regex:/^\d{5}$/',

                'relocation' => 'required|in:Y,N',

//                'groomer_exp' => 'required|in:Y,N',
                'groomer_target' => 'required|in:C,D,B', // New
                'comfortable' => 'required|in:Y,N',
                'groomer_edu' => 'required|in:Y,N',
                // 'groomer_edu_dog_grooming' => 'required_if:groomer_edu,Y',
                // 'groomer_edu_cat_grooming' => 'required_if:groomer_edu,Y',
                // 'groomer_edu_pet_safety_cpr' => 'required_if:groomer_edu,Y',
                // 'groomer_edu_breed_standards' => 'required_if:groomer_edu,Y',
                // 'groomer_edu_other' => 'required_if:groomer_edu,Y',
//                'groomer_edu_note' => 'required_if:groomer_edu_other,Y',
                // 'groomer_edu_intereseted' => 'nullable|in:Y',
                'groomer_exp_years' => 'required|in:1,2,5,10',
                //'groom_per_month' => 'required|in:10,50,100',
                // 'special_area' => 'required|in:Y,N',
                // 'special_area_note' => 'required_if:special_area,Y',
                // 'have_reference' => 'required|in:Y,N',
                // 'reference_name' => 'required_if:have_reference,Y',
                // 'reference_phone' => 'required_if:have_reference,Y|regex:/^\d{10}$/',
                // 'other_jobs_exp' => '',
                // 'have_groomed_photo' => 'required',
//                'groomer_references' => 'required',
//                'groomed_photo' => 'required',
                'agree_to_bg_check' => 'required|in:Y',
                'profile_photo' => 'required',
                'have_tool' => 'required|in:Y,N'
            ]);

            if ($v->fails()) {
                DB::rollback();
                return back()->withErrors($v)->withInput();
                //This does not keep old data. Need tests.
            }

            $at_least_one_checked = false;
            for ($i=0; $i<=6; $i++) {
                for ($j=8; $j<=24; $j++) {
                    $key = 'wd' . $i . '_h' . str_pad($j, 2, '0', STR_PAD_LEFT);
                    Log::info('### key : ' . $key . ' ###');
                    if ($request->get($key) == 'Y') {
                        $at_least_one_checked = true;
                        break;
                    }
                }

                if ($at_least_one_checked) {
                    break;
                }
            }

            if (!$at_least_one_checked) {
                DB::rollback();
                return back()->withErrors([
                    'exception' => 'Please setup your availability'
                ])->withInput();
            }

            if (!$request->groomer_edu_interested) {
                $request->groomer_edu_interested = 'N';
            }

            if ($request->pre_save != 'Y') {
                $app = new Application;
            } else {
                $app = Application::where('first_name', $request->full_name)
                    ->where('last_name', $request->last_name)
                    ->where('email', strtolower($request->email))
                    ->where('phone', $request->phone)
                    ->first();
            }

            $app->first_name    = $request->full_name;
            $app->last_name     = $request->last_name;
            $app->email         = strtolower($request->email);
            $app->phone         = $request->phone;
            $app->mobile_phone  = $request->phone; // phone -> mobile phone
            $app->groomer_how_knew_groomit = $request->groomer_how_knew_groomit; //How did you hear about us?

            $password = rand ( 10000000 , 99999999 );
            $app->password      = \Crypt::encrypt($password);

            $app->f_account     = $request->f_account;
            $app->i_account     = $request->i_account;
            $app->street        = $request->street ;//$request->address1;
            $app->zip           = $request->zip;
            $app->city          = $request->city;
            $app->state         = $request->state;

            // 1. What area do you service?
            $app->service_ny    = $request->service_ny;
            $app->service_nj    = $request->service_nj;
            $app->service_ct    = $request->service_ct;
            $app->service_miami = $request->service_miami;
            $app->service_philladelphia = $request->service_philladelphia;
            $app->service_sandiego      = $request->service_sandiego;
            $app->service_other_area    = $request->service_other_area;

            // 2. Are you willing to relocate?
            $app->relocate      = $request->relocation;

            // 3. Have you worked as Bather or Groomer Before?
            $app->bather_exp = $request->bather_exp;
            $app->groomer_exp   = $request->groomer_exp;

            // 4. Where did you learn your skills?
            $app->groomer_exp_note = $request->groomer_exp_note;

            // 5. Do you service Dogs or Cats (D-dog, C-cat, B-both)
            $app->groomer_target = $request->groomer_target;

            if( $request->groomer_target == 'D'){
                $app->dog = 'Y';
            }elseif ($request->groomer_target == 'C'){
                $app->cat = 'Y';
            }elseif ($request->groomer_target == 'B'){
                $app->dog = 'Y';
                $app->cat = 'Y';
            }

            // 6. Are you comfortable to groom within customers home/office?
            $app->comfortable = $request->comfortable;

            // 7. Do you have a drivers license (optional)?
            $app->driver_license = $request->driver_license;

            // 8. Safety is our priority, Do you agree on a 3rd party background check ( Learn More )?
            $app->agree_to_bg_check = $request->agree_to_bg_check;

            // 9. Do you have any certifications?
            $app->groomer_edu   = $request->groomer_edu;

            // 10. Certification in:
            $app->groomer_edu_dog_grooming = $request->groomer_edu_dog_grooming;
            $app->groomer_edu_cat_grooming = $request->groomer_edu_cat_grooming;
            $app->groomer_edu_pet_safety_cpr = $request->groomer_edu_pet_safety_cpr;
            $app->groomer_edu_breed_standards = $request->groomer_edu_breed_standards;

            // 11. How many years of professional experience as a bather/groomer do you have?
            $app->groomer_exp_years = $request->groomer_exp_years;

            // 12. Reference:
//            $app->groomer_references = $request->groomer_references;

            // 13. Tell us about yourself
            $app->groomer_edu_note = $request->groomer_edu_note;

            ### profile photo ###
            $key = 'profile_photo';
            if (Input::hasFile($key) && Input::file($key)->isValid()) {
                $path = Input::file($key)->getRealPath();

                Log::info('### FILE ###', [
                    'key' => $key,
                    'path' => $path
                ]);

                $contents = file_get_contents($path);
                $name = Input::file($key)->getClientOriginalName();

                //$app->profile_photo = base64_encode($contents);
                try{
                    $app->profile_photo = base64_encode($contents);
                } catch (\Exception $ex) {
                    $app->profile_photo = $contents ;
                }
            } else {
                DB::rollback();
                return back()->withErrors([
                    $key => 'Invalid profile photo provided'
                ])->withInput();
            }

            // $app->available_in_my_area = $request->notify_availability;
            // $app->bio           = $request->bio;
            // $app->groomer_edu_interested = $request->groomer_edu_interested;
            // $app->groom_per_month = $request->groom_per_month;
            // $app->special_area  = $request->special_area;
            // $app->special_area_note = $request->special_area_note;
            // $app->have_reference = $request->have_reference;
            // $app->reference_name = $request->reference_name;
            // $app->reference_phone = $request->reference_phone;
            // $app->other_jobs_exp = $request->other_jobs_exp;
            // $app->have_groomed_photo = $request->have_groomed_photo;

            $app->have_tool = $request->have_tool;
            $app->status = 'N';
            $app->cdate = Carbon::now();
            $app->save();

            // 14. photos of your recent groomings (groomed_photo)
            ### groomed photo ###
            $key = 'groomed_photo';
            if (Input::hasFile($key) && Input::file($key)->isValid()) {
                $path = Input::file($key)->getRealPath();

                Log::info('### FILE ###', [
                    'key' => $key,
                    'path' => $path
                ]);

                $contents = file_get_contents($path);
                $name = Input::file($key)->getClientOriginalName();

                $pp = new ApplicationPetPhoto;
                $pp->application_id = $app->id;
                $pp->data = base64_encode($contents);
//                try{
//                    $pp->profile_photo = base64_encode($contents);
//                } catch (\Exception $ex) {
//                    $pp->profile_photo = $contents ;
//                }
                $pp->save();

            }
//            else {
//                DB::rollback();
//                return back()->withErrors([
//                    $key => 'Invalid groomed photo provided'
//                ])->withInput();
//            }

            // 15. Do you have your own grooming tools
            if ($request->have_tool == 'Y') {
                ### tools ###
                $tools = Tool::all();
                foreach ($tools as $o) {
                    $key = 'tool_' . $o->id;
                    if ($request->get($key) == 'Y') {
                        $t = new ApplicationTool;
                        $t->application_id = $app->id;
                        $t->tool_id = $o->id;
                        $t->save();
                    }
                }
            }

            // 16. Please, enter your potential availability.
            ### Schedule ###
            for ($i=0; $i<=6; $i++) {
                for ($j=8; $j<=24; $j++) {
                    $key = 'wd' . $i . '_h' . str_pad($j, 2, '0', STR_PAD_LEFT);
                    if ($request->get($key) == 'Y') {

                        $aa = new ApplicationAvailability;
                        $aa->application_id = $app->id;
                        $aa->weekday = $i;
                        $aa->hour = $j;
                        $aa->save();
                    }
                }
            }

            DB::commit();

            ### SEND EMAIL
            $data = [];
            $data['name']       = $app->first_name;
            $data['message']    = 'Thank you for your interest to work with Groomit. We will be in touch with you very soon.';

            $data['email']      = $app->email;
            $data['subject']    = 'Thank you for your interest to work with Groomit. We will be in touch with you very soon.';

            Helper::log('##### EMAIL DATA #####', [
                'data' => $data
            ]);

            Helper::send_html_mail('contact_reply', $data);

            $data['email']      = 'lars@groomit.me';
            $data['subject']    = '[GROOMER APPLICATION] NEW ARRIVED !!';
            Helper::send_html_mail('contact_reply', $data);
//            $data['email']      = 'zabair@groomit.me';
//            Helper::send_html_mail('contact_reply', $data);
            $data['email']      = 'help@groomit.me';
            Helper::send_html_mail('contact_reply', $data);
            $data['email']      = 'faez@groomit.me';
            Helper::send_html_mail('contact_reply', $data);

            return back()->with([
                'success' => 'Y'
            ]);

        } catch (\Exception $ex) {
            DB::rollback();
            return back()->withErrors([
                'exception' => $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString()
            ])->withInput();
        }
    }

}