<?php

namespace App\Http\Controllers;

use App\Lib\AppointmentProcessor;
use App\Lib\CreditProcessor;
use App\Lib\Helper;
use App\Lib\ProfitSharingProcessor;
use App\Lib\PromoCodeProcessor;;
use App\Model\Address;
use App\Model\AllowedZip;
use App\Model\Appointment;
use App\Model\AppointmentList;
use App\Model\AppointmentPet;
use App\Model\AppointmentPhoto;
use App\Model\AppointmentProduct;
use App\Model\Constants;
use App\Model\Groomer;
use App\Model\CCTrans;
use App\Model\Pet;
use App\Model\PetPhoto;
use App\Model\Product;
use App\Model\ProductDenom;
use App\Model\TaxZip;
use App\Model\User;
use App\Model\UserBilling;
use App\Model\Message;
use App\Model\Tax;
use App\Model\PromoCode;
use App\Model\Credit;
use App\Model\ReservedCredit;
use App\Model\ZipQuery;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Validator;
use DateTime;
use DateTimeZone;
use App\Lib\Converge;
use Auth;

class AppointmentController extends Controller
{

    public function groomer_works(Request $request) { // Not using anywhere for now
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'groomer_id' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [APP_GRMW]'
                ]);
            }

            $appointments = Appointment::where('groomer_id', $request->groomer_id)
                ->where('status', 'S')
                ->select([
                    'appointment_id',
                    'user_id',
                    'groomer_id',
                    'reserved_at',
                    'special_request',
                    'address1',
                    'address2',
                    'city',
                    'state',
                    'zip',
                    'sub_total',
                    'tax',
                    'total',
                    'status',
                    'rating',
                    'rating_comments'
                ])->orderBy('reserved_at', 'desc')
                ->get();

            foreach ($appointments as $ap) {

                $ap->status_name = '';
                if (array_key_exists($ap->status, Constants::$appointment_status)) {
                    $ap->status_name = Constants::$appointment_status[$ap->status];
                }

                $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name', 'profile_photo', 'phone')->first();
                if (!empty($ap->groomer)) {
                    if (!empty($ap->groomer->profile_photo)) {
                        try{
                            $ap->groomer->profile_photo = base64_encode($ap->groomer->profile_photo);
                        } catch (\Exception $ex) {
                            $ap->groomer->profile_photo = $ap->groomer->profile_photo ;
                        }
                    }
                }

                $ret = DB::select("
                    select avg(rating) avg_rating, count(*) total_appts
                    from appointment_list
                    where groomer_id = :groomer_id
                    and status = 'P'
                ", [
                    'groomer_id' => $ap->groomer_id
                ]);

                $ap->groomer->overall_rating = 0;
                $ap->groomer->total_appts = 0; //Total # of appointments completed, regardless of ratings.
                if (count($ret) > 0) {
                    $ap->groomer->overall_rating = $ret[0]->avg_rating;
                    $ap->groomer->total_appts = $ret[0]->total_appts;
                }

                $ap->pets = DB::select("
                    select 
                        a.pet_id, 
                        c.name as pet_name,
                        c.dob as pet_dob,
                        d.breed_name as breed,
                        timestampdiff(month, c.dob, curdate()) as age,
                        b.prod_id as package_id,
                        b.prod_name as package_name,
                        a.amt as price
                    from appointment_product a 
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on a.pet_id = c.pet_id
                        inner join breed d on c.breed = d.breed_id
                    where a.appointment_id = :appointment_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                ", [
                    'appointment_id' => $ap->appointment_id
                ]);

                foreach ($ap->pets as $p) {

                    $year = intval($p->age / 12);
                    $month = intval($p->age % 12);
                    $p->age = ($year > 0 ? $year . ' year' . ($year > 1) ? 's' : ' ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');

                    $p->addons = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = :pet_id
                        and b.prod_type = 'A'
                    ", [
                        'appointment_id' => $ap->appointment_id,
                        'pet_id' => $p->pet_id
                    ]);

                    $p->photos = PetPhoto::where('pet_id', $p->pet_id)->get();
                    foreach ($p->photos as $photo) {
                        try{
                            $photo->photo = base64_encode($photo->photo);
                        } catch (\Exception $ex) {
                            $photo->photo = $photo->photo;
                        }
                    }

                    if (count($p->photos) > 0) {
                        $p->photo = $p->photos[0]->photo;
                    }



                    $p->before_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                        ->where('pet_id', $p->pet_id)
                        ->where('type', 'B')
                        ->select('image')
                        ->first();

                    if (!empty($p->before_image)) {
                        $p->before_image = base64_encode($p->before_image->image);
                    }

                    $p->after_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                        ->where('pet_id', $p->pet_id)
                        ->where('type', 'A')
                        ->select('image')
                        ->first();

                    if (!empty($p->after_image)) {
                        $p->after_image = base64_encode($p->after_image->image);
                    }
                }

                if (count($ap->pets) > 0) {
                    $ap->first_pet = $ap->pets[0];
                }

                $owner_pets = Pet::where('user_id', $ap->user_id)->get();

                $owner_photos = [];
                foreach ($owner_pets as $p) {
                    $pet_photo = PetPhoto::where('pet_id', $p->pet_id)->get();
                    foreach ($pet_photo as $pp) {
                        try{
                            $photo = base64_encode($pp->photo);
                        } catch (\Exception $ex) {
                            $photo = $pp->photo;
                        }
                        $owner_photos[] = $photo;
                    }
                }

                $ap->owner_photos = $owner_photos;
            }


            return response()->json([
                'msg' => '',
                'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function tip(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required',
                'tip' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [APP_TIP]'
                ]);
            }

            $msg = AppointmentProcessor::tip($user->user_id, $request->appointment_id, $request->tip);
            return response()->json([
                'msg' => $msg
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function rate(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required',
                'rating' => 'required|regex:/^\d+$/'
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
                    'msg' => 'Your session has expired. Please login again. [APP_RT]'
                ]);
            }

            $msg = AppointmentProcessor::rate($user->user_id, $request->appointment_id, $request->rating);
            return response()->json([
                'msg' => $msg
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function mark_as_favorite(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required',
                'add_to_favorite' => 'required|in:Y,N'
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
                    'msg' => 'Your session has expired. Please login again. [APP_FV]'
                ]);
            }

            $msg = AppointmentProcessor::mark_as_favorite($user->user_id, $request->appointment_id, $request->add_to_favorite);
            return response()->json([
                'msg' => $msg
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function cancel_old(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [APP_CNC]'
                ]);
            }

            $ap = Appointment::where('user_id', $user->user_id)
                ->where('appointment_id', $request->appointment_id)->first();
            if (empty($ap)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            if ($ap->status != 'N' && $ap->reserved_at < Carbon::now()->addHours(24)) {
                return response()->json([
                    'msg' => 'Appointment can be cancelled only withn 24 hours of reserved date'
                ]);
            }

            # send notification if groomer has been assigned.
            $groomer = Groomer::where('groomer_id', $ap->groomer_id)->first();
            $push_msg = "Customer : " . $user->name . " has cancelled appointment with you reserved at " . $ap->reserved_at;
            if (!empty($groomer) && !empty($groomer->device_token)) {
                $ret = Helper::send_notification('groomer', $push_msg, $groomer->device_token);
                if (!empty($ret)) {
                    return response()->json([
                        'msg' => 'Failed to notify groomer with cancellation'
                    ]);
                }
            }

            if (!empty($groomer) && empty($groomer->device_token)) {
                Helper::send_mail('tech@groomit.me', '[groomit] groomer device token is empty upon appointment cancellation', $groomer->email);
            }

            $ap->status = 'C';
            $ap->save();

//            # save message
//            $message = new Message;
//            $message->message_type = 'C';
//            $message->appointment_id = $ap->appointment_id;
//            $message->groomer_id = $groomer->groomer_id;
//            $message->user_id = $user->user_id;
//            $message->message = $push_msg;
//            $message->cdate = Carbon::now();
//            $message->save();

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

    public function get_packages(Request $request)
    {
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

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_PKG]'
                ]);
            }

            ## user address needed for zip
            $address = Address::where('user_id', $user->user_id)
                ->where('status', 'A')
                ->first();

            $zip = '';
            if (!empty($address)) {
                $zip = $address->zip;
            }

            $pet = Pet::find($request->pet_id);
            if (empty($pet)) {
                return response()->json([
                    'msg' => 'Invalid pet ID provided'
                ]);
            }

            $packages = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where b.size_id = :size_id
                and a.prod_type = 'P'
                #and a.prod_id not in (28, 29)
                and a.status = 'A'
                and b.status = 'A'
                and a.pet_type = :pet_type
            ", [
                'size_id' => $pet->size,
                'zip' => $zip,
                'pet_type' => $pet->type
            ]);

            $add_ons = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'A'
                and a.status = 'A'
                and b.status = 'A'
                and a.pet_type = :pet_type
                order by a.seq
            ", [
                'zip' => $zip,
                'pet_type' => $pet->type
            ]);

            Helper::log('### add_ons ###', $add_ons);

            if (is_array($packages)) {
                foreach ($packages as $o) {
                    $o->add_ons = $add_ons;
                }
            }

            return response()->json([
                'msg' => '',
                'packages' => $packages
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_cat_service(Request $request)
    {
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

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_PKG]'
                ]);
            }

            ## user address needed for zip
            $address = Address::where('user_id', $user->user_id)
                ->where('status', 'A')
                ->first();

            $zip = '';
            if (!empty($address)) {
                $zip = $address->zip;
            }

            $group_id = 1;
            if (!empty($zip)) {
                $allowed_zip = AllowedZip::where('zip', $zip)->first();
                if (!empty($allowed_zip)) {
                    $group_id = $allowed_zip->group_id;
                }
            }

            $service = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, 2, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'P'
                #and a.prod_id not in (28, 29)
                and a.status = 'A'
                and b.status = 'A'
                and a.pet_type = 'cat'
                and b.group_id = :group_id
            ", [
                'zip' => $zip,
                'group_id' => $group_id
            ]);

            return response()->json([
                'msg' => '',
                'service' => $service
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    public function recent_old(Request $request)
    {// Not using anywhere for now
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

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_RCT]'
                ]);
            }

            ### status list ###
            # - N : New
            # - G : Groomer assigned
            # - W : Work in progress
            # - C : Cancelled
            # - S : Work Completed
            # - F : Failed ( Maybe payment failure ? )

            ### list of information to be returned ###
            # - appoitment general
            # - groomer information
            # - pet images ( before / after )

            $appointments = Appointment::where('user_id', $user->user_id)
                ->whereIn('status', ['S'])
                ->where('reserved_at', '>=', Carbon::today()->toDateString())
                ->select([
                    'appointment_id',
                    'groomer_id',
                    'reserved_at',
                    'special_request',
                    'address1',
                    'address2',
                    'city',
                    'state',
                    'zip',
                    'sub_total',
                    'tax',
                    'total',
                    'status',
                    'rating',
                    'rating_comments'
                ])->orderBy('reserved_at', 'desc')
                ->get();

            foreach ($appointments as $ap) {

                $ap->status_name = '';
                if (array_key_exists($ap->status, Constants::$appointment_status)) {
                    $ap->status_name = Constants::$appointment_status[$ap->status];
                }

                $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name', 'profile_photo', 'phone')->first();
                if (!empty($ap->groomer)) {
                    if (!empty($ap->groomer->profile_photo)) {
                        //$ap->groomer->profile_photo = base64_encode($ap->groomer->profile_photo);
                        try{
                            $ap->groomer->profile_photo = base64_encode($ap->groomer->profile_photo);
                        } catch (\Exception $ex) {
                            $ap->groomer->profile_photo = $ap->groomer->profile_photo ;
                        }
                    }
                }

                $ret = DB::select("
                    select avg(rating) avg_rating, count(*) total_appts
                    from appointment_list
                    where groomer_id = :groomer_id
                    and status = 'P'
                ", [
                    'groomer_id' => $ap->groomer_id
                ]);

                $ap->groomer->overall_rating = 0;
                $ap->groomer->total_appts = 0;
                if (count($ret) > 0) {
                    $ap->groomer->overall_rating = $ret[0]->avg_rating;
                    $ap->groomer->total_appts = $ret[0]->total_appts;
                }

                $ap->pets = DB::select("
                    select 
                        a.pet_id, 
                        c.name as pet_name,
                        c.dob as pet_dob,
                        timestampdiff(month, c.dob, curdate()) as age,
                        b.prod_id as package_id,
                        b.prod_name as package_name,
                        a.amt as price
                    from appointment_product a 
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on a.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                ", [
                    'appointment_id' => $ap->appointment_id
                ]);

                foreach ($ap->pets as $p) {

                    $year = intval($p->age / 12);
                    $month = intval($p->age % 12);
                    $p->age = ($year > 0 ? $year . ' year' . ($year > 1) ? 's' : ' ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');

                    $p->addons = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = :pet_id
                        and b.prod_type = 'A'
                        and b.pet_type = :pet_type
                    ", [
                        'appointment_id' => $ap->appointment_id,
                        'pet_id' => $p->pet_id,
                        'pet_type' => $p->tyoe
                    ]);

                    $p->photos = PetPhoto::where('pet_id', $p->pet_id)->get();
                    foreach ($p->photos as $photo) {
                        //$photo->photo = base64_encode($photo->photo);
                        try{
                            $photo->photo = base64_encode($photo->photo);
                        } catch (\Exception $ex) {
                            $photo->photo = $photo->photo ;
                        }
                    }

                    if (count($p->photos) > 0) {
                        $p->photo = $p->photos[0]->photo;
                    }

                    $p->before_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                        ->where('pet_id', $p->pet_id)
                        ->where('type', 'B')
                        ->select('image')
                        ->first();

                    if (!empty($p->before_image)) {
                        $p->before_image = base64_encode($p->before_image->image);
                    }

                    $p->after_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                        ->where('pet_id', $p->pet_id)
                        ->where('type', 'A')
                        ->select('image')
                        ->first();

                    if (!empty($p->after_image)) {
                        $p->after_image = base64_encode($p->after_image->image);
                    }
                }

                if (count($ap->pets) > 0) {
                    $ap->first_pet = $ap->pets[0];
                }
            }

            return response()->json([
                'msg' => '',
                'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function upcoming_old(Request $request)
    {// Not using anywhere for now
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

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_UPC]'
                ]);
            }

            ### status list ###
            #<option value="N" selected>Groomer Not Assigned Yet</option>
            #<option value="D" >Groomer Assigned</option>
            #<option value="W" >Work In Progress</option>
            #<option value="C" >Cancelled</option>                           ***
            #<option value="S" >Work Completed</option>                      ***
            #<option value="F" >Payment Failure</option>                     ***
            #<option value="R" >Failed to hold amount. Please retry after updating customer credit card.</option>
            #<option value="L" >Cancelled &amp; Rescheduled</option>         ***
            #<option value="P" >Payment Completed</option>                   ***

            ### list of information to be returned ###
            # - appoitment general
            # - groomer information
            # - pet images ( before / after )

            $appointments = Appointment::where('user_id', $user->user_id)
                ->whereNotIn('status', ['C', 'F', 'S'])
                ->where('reserved_at', '>=', Carbon::today()->toDateString())
                ->select([
                    'appointment_id',
                    'groomer_id',
                    'reserved_at',
                    'special_request',
                    'address1',
                    'address2',
                    'city',
                    'state',
                    'zip',
                    'sub_total',
                    'tax',
                    'total',
                    'status',
                    'rating',
                    'rating_comments'
                ])->orderBy('reserved_at', 'desc')
                ->get();

            foreach ($appointments as $ap) {

                $ap->status_name = '';
                if (array_key_exists($ap->status, Constants::$appointment_status)) {
                    $ap->status_name = Constants::$appointment_status[$ap->status];
                }

                $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name', 'profile_photo', 'phone')->first();
                if (!empty($ap->groomer)) {
                    if (!empty($ap->groomer->profile_photo)) {
                        //$ap->groomer->profile_photo = base64_encode($ap->groomer->profile_photo);
                        try{
                            $ap->groomer->profile_photo= base64_encode($ap->groomer->profile_photo);
                        } catch (\Exception $ex) {
                            $ap->groomer->profile_photo = $ap->groomer->profile_photo ;
                        }
                    }

                    $ret = DB::select("
                        select avg(rating) avg_rating, count(*) total_appts
                        from appointment_list
                        where groomer_id = :groomer_id
                        and status = 'P'
                    ", [
                        'groomer_id' => $ap->groomer_id
                    ]);

                    $ap->groomer->overall_rating = 0;
                    $ap->groomer->total_appts = 0;
                    if (count($ret) > 0) {
                        $ap->groomer->overall_rating = $ret[0]->avg_rating;
                        $ap->groomer->total_appts = $ret[0]->total_appts;
                    }
                }

                $ap->pets = DB::select("
                    select 
                        a.pet_id, 
                        c.name as pet_name,
                        c.dob as pet_dob,
                        timestampdiff(month, c.dob, curdate()) as age,
                        b.prod_id as package_id,
                        b.prod_name as package_name,
                        a.amt as price
                    from appointment_product a 
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on a.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                ", [
                    'appointment_id' => $ap->appointment_id
                ]);

                foreach ($ap->pets as $p) {

                    $year = intval($p->age / 12);
                    $month = intval($p->age % 12);
                    $p->age = ($year > 0 ? $year . ' year' . ($year > 1) ? 's' : ' ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');

                    $p->addons = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = :pet_id
                        and b.prod_type = 'A'
                        and b.pet_type = :pet_type
                    ", [
                        'appointment_id' => $ap->appointment_id,
                        'pet_id' => $p->pet_id,
                        'pet_type' => $p->type
                    ]);

                    $p->photos = PetPhoto::where('pet_id', $p->pet_id)->get();
                    foreach ($p->photos as $photo) {
                        $photo->photo = base64_encode($photo->photo);
                        try{
                            $photo->photo = base64_encode($photo->photo);
                        } catch (\Exception $ex) {
                            $photo->photo = $photo->photo ;
                        }
                    }

                    if (count($p->photos) > 0) {
                        $p->photo = $p->photos[0]->photo;
                    }



                    $p->before_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                        ->where('pet_id', $p->pet_id)
                        ->where('type', 'B')
                        ->select('image')
                        ->first();

                    if (!empty($p->before_image)) {
                        $p->before_image = base64_encode($p->before_image->image);
                    }

                    $p->after_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                        ->where('pet_id', $p->pet_id)
                        ->where('type', 'A')
                        ->select('image')
                        ->first();

                    if (!empty($p->after_image)) {
                        $p->after_image = base64_encode($p->after_image->image);
                    }
                }

                if (count($ap->pets) > 0) {
                    $ap->first_pet = $ap->pets[0];
                }
            }

            return response()->json([
                'msg' => '',
                'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    //================== V2. ===================//

    public function get_by_id(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [APP_GID]'
                ]);
            }

            $ap = AppointmentList::where("appointment_id",$request->appointment_id)->first();

            if ($ap->groomer_id) {
                $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name', 'profile_photo', 'phone')->first();
                if (!empty($ap->groomer)) {
                    if (!empty($ap->groomer->profile_photo)) {
                        //$ap->groomer->profile_photo = base64_encode($ap->groomer->profile_photo);
                        try{
                            $ap->groomer->profile_photo = base64_encode($ap->groomer->profile_photo);
                        } catch (\Exception $ex) {
                            $ap->groomer->profile_photo  = $ap->groomer->profile_photo ;
                        }

                    }

                    $ret = DB::select("
                        select avg(rating) avg_rating, count(*) total_appts
                        from appointment_list
                        where groomer_id = :groomer_id
                        and status = 'P'
                    ", [
                        'groomer_id' => $ap->groomer_id
                    ]);

                    $ap->groomer->overall_rating = 0;
                    $ap->groomer->total_appts = 0;
                    if (count($ret) > 0) {
                        $ap->groomer->overall_rating = $ret[0]->avg_rating;
                        $ap->groomer->total_appts = $ret[0]->total_appts;
                    }

                    $fav = DB::select("
                        select groomer_id
                        from user_favorite_groomer
                        where user_id = :user_id
                        and groomer_id = :groomer_id
                    ", [
                        'user_id' => $ap->user_id,
                        'groomer_id' => $ap->groomer_id
                    ]);

                    if (count($fav) > 0) {
                        $ap->groomer->favorite = true;
                    } else {
                        $ap->groomer->favorite = false;
                    }
                }
            }


            $ap->product = DB::select("
                    select 
                        b.prod_name as package_name
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                ", [
                'appointment_id' => $ap->appointment_id
            ]);

            return response()->json([
                'msg' => '',
                'appointment' => $ap
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_list_by_pet(Request $request) { /// Not using anywhere for now
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
                    'msg' => 'Your session has expired. Please login again. [APP_LSTPET]'
                ]);
            }

            ### status list ###
            # - N : Groomer not assigned yet
            # - G : Groomer assigned
            # - W : Work in progress
            # - C : Cancelled
            # - S : Work Completed
            # - F : Failed ( Maybe payment failure ? )

            ### list of information to be returned ###
            # - appointment general
            # - groomer information
            # - pet images ( before / after )

            $pet_id = $request->pet_id;
            $pet = Pet::find($pet_id);
            if (empty($pet)) {
                return response()->json([
                    'msg' => 'Invalid pet ID provided'
                ]);
            }

            $appointments = DB::select("
                    select 
                        a.*,
                        b.prod_id as package_id,
                        b.prod_name as package_name
                    from appointment_list a 
                        inner join appointment_pet ap on ap.appointment_id = a.appointment_id
                        inner join appointment_product apd on apd.appointment_id = a.appointment_id
                        inner join product b on b.prod_id = apd.prod_id
                    where ap.pet_id = :pet_id
                    and apd.appointment_id = ap.appointment_id
                    and b.prod_type = 'P'
                    and b.pet_type = :pet_type
                    order by a.accepted_date desc, a.appointment_id desc
                ", ['pet_id' => $pet_id, 'pet_type' => $pet->type]);


            foreach ($appointments as $ap) {

                $ap->status_name = '';
                if (array_key_exists($ap->status, Constants::$appointment_status)) {
                    $ap->status_name = Constants::$appointment_status[$ap->status];
                }

                $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name', 'profile_photo', 'phone')->first();
                if (!empty($ap->groomer)) {
                    if (!empty($ap->groomer->profile_photo)) {
                        //$ap->groomer->profile_photo = base64_encode($ap->groomer->profile_photo);
                        try{
                            $ap->groomer->profile_photo = base64_encode($ap->groomer->profile_photo) ;
                        } catch (\Exception $ex) {
                            $ap->groomer->profile_photo  = $ap->groomer->profile_photo  ;
                        }

                    }

                    $ret = DB::select("
                        select avg(rating) avg_rating, count(*) total_appts
                        from appointment_list
                        where groomer_id = :groomer_id
                        and status = 'P'
                    ", [
                        'groomer_id' => $ap->groomer_id
                    ]);

                    $ap->groomer->overall_rating = 0;
                    $ap->groomer->total_appts = 0;
                    if (count($ret) > 0) {
                        $ap->groomer->overall_rating = $ret[0]->avg_rating;
                        $ap->groomer->total_appts = $ret[0]->total_appts;
                    }
                }

                $ap->address = Address::find($ap->address_id);

                $ap->addons = DB::select("
                    select 
                        b.prod_id,
                        b.prod_name,
                        a.amt as price
                    from appointment_product a
                        inner join product b on a.prod_id = b.prod_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = :pet_id
                    and b.prod_type = 'A'
                    and b.pet_type = :pet_type
                ", [
                    'appointment_id' => $ap->appointment_id,
                    'pet_id' => $pet_id,
                    'pet_type' => $pet->type
                ]);

                $ap->shampoo = DB::select("
                    select 
                        b.prod_id,
                        b.prod_name,
                        a.amt as price
                    from appointment_product a
                        inner join product b on a.prod_id = b.prod_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = :pet_id
                    and b.prod_type = 'S'
                    and b.pet_type = :pet_type
                ", [
                    'appointment_id' => $ap->appointment_id,
                    'pet_id' => $pet_id,
                    'pet_type' => $pet->type
                ]);


                $ap->photos = PetPhoto::where('pet_id', $pet_id)->get();
                foreach ($ap->photos as $photo) {
                    //$photo->photo = base64_encode($photo->photo);
                    try{
                        $photo->photo = base64_encode($photo->photo);
                    } catch (\Exception $ex) {
                        $photo->photo = $photo->photo ;
                    }

                }

                if (count($ap->photos) > 0) {
                    $ap->photo = $ap->photos[0]->photo;
                }


                $ap->before_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                    ->where('pet_id', $pet_id)
                    ->where('type', 'B')
                    ->select('image')
                    ->first();

                if (!empty($ap->before_image)) {
                    $ap->before_image = base64_encode($ap->before_image->image);
                }

                $ap->after_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                    ->where('pet_id', $pet_id)
                    ->where('type', 'A')
                    ->select('image')
                    ->first();

                if (!empty($ap->after_image)) {
                    $ap->after_image = base64_encode($ap->after_image->image);
                }
            }

            return response()->json([
                'msg' => '',
                'appointments' => $appointments
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

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_SZ]'
                ]);
            }

            $sizes = DB::select("
                select
                    size_id,
                    size_name,
                    size_desc
                from size
                order by size_id
            ");


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

    public function get_package_addon(Request $request)
    {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'size_id' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [APP_PKGAO]'
                ]);
            }

            $zip = '';
            $address = Address::where('user_id', $user->user_id)
                ->where('status', 'A')
                ->first();
            if (!empty($address)) {
                $zip = $address->zip;
            }

            $group_id = 1;
            if (!empty($zip)) {
                $allowed_zip = AllowedZip::where('zip', $zip)->first();
                if (!empty($allowed_zip)) {
                    $group_id = $allowed_zip->group_id;
                }
            }

            $packages = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where b.size_id = :size_id
                and a.prod_type = 'P'
                and a.status = 'A'
                #and a.prod_id not in (28, 29)
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'dog'
            ", [
                'size_id' => $request->size_id,
                'zip' => $zip,
                'group_id' => $group_id
            ]);

            $add_ons = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where b.size_id = :size_id
                and a.prod_type = 'A'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'dog'
                order by a.seq
            ", [
                'size_id' => $request->size_id,
                'zip' => $zip,
                'group_id' => $group_id
            ]);

            $shampoos = DB::select("
                select
                    a.prod_id,
                    a.prod_name,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'S'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'dog'
            ", [
                'zip' => $zip,
                'group_id' => $group_id
            ]);

            Helper::log('### add_ons ###', $add_ons);

            return response()->json([
                'msg' => '',
                'packages' => $packages,
                'add_ons' => $add_ons,
                'shampoos' => $shampoos
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    ## For new Addon Page
    public function get_addons(Request $request)
    {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'package_id' => '',
                'size_id' => ''
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
                    'msg' => 'Your session has expired. Please login again. [APP_AO]'
                ]);
            }

            $zip = '';
            $address = Address::where('user_id', $user->user_id)
                ->where('status', 'A')
                ->first();
            if (!empty($address)) {
                $zip = $address->zip;
            }

            $group_id = 1;
            if (!empty($zip)) {
                $allowed_zip = AllowedZip::where('zip', $zip)->first();
                if (!empty($allowed_zip)) {
                    $group_id = $allowed_zip->group_id;
                }
            }

            $no_dematting_query = "";
            if ($request->package_id == 2) {
                $no_dematting_query = "
                    and a.prod_id not in (14,7)
                ";
            }

//            if ($request->package_id == 1) {
//                $no_dematting_query .= "
//                    and a.prod_id not in (11, 22)
//                ";
//            }

            if ($request->package_id == 16) {
                $no_dematting_query .= "
                    and a.prod_id not in (20)
                ";
            }

            $package = Product::find($request->package_id);
            $pet_type = 'dog';
            if (!empty($package)) {
                $pet_type = $package->pet_type;
            }

            $add_ons = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where (a.size_required = 'Y' and b.size_id = :size_id or a.size_required = 'N')
                and a.prod_type = 'A'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = :pet_type
                $no_dematting_query
                order by a.seq
            ", [
                'size_id' => $request->size_id,
                'zip' => $zip,
                'group_id' => $group_id,
                'pet_type' => $pet_type
            ]);

            $shampoos = DB::select("
                select
                    a.prod_id,
                    a.prod_name,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'S'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = :pet_type
            ", [
                'zip' => $zip,
                'group_id' => $group_id,
                'pet_type' => $pet_type
            ]);

            Helper::log('### add_ons ###', $add_ons);

            return response()->json([
                'msg' => '',
                'add_ons' => $add_ons,
                'shampoos' => $shampoos
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function add(Request $request)
    {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment' => 'required'
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

            Helper::send_mail('jun@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Old version App. Stopped', $email);
            return response()->json([
                'msg' => "We are sorry but your current version is out of date. Please download and install the latest version from App Store."
            ]);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_ADD]'
                ]);
            }

            # appointment request. Not used any longer
            $ar = json_decode($request->appointment);
            $ret = AppointmentProcessor::add_appointment($ar, $user, 'A');

            return response()->json([
                'msg' => $ret['msg'],
                'appointment_id' => empty($ret['appointment_id']) ? '' : $ret['appointment_id']
            ]);

        } catch (\Exception $ex) {

            DB::rollback();

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function upcoming(Request $request)
    {
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

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_UPC]'
                ]);
            }

            $arr_appointments = AppointmentProcessor::get_upcoming($user->user_id);

            return response()->json([
                'msg' => '',
                'appointments' => $arr_appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']' . $ex->getTraceAsString()
            ]);
        }
    }

    public function recent(Request $request)
    {
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

            Log::info('### email ##' . $email);

            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_RCT]'
                ]);
            }

            $appointments = AppointmentProcessor::get_recent($user->user_id);

            return response()->json([
                'msg' => '',
                'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_tax(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'state' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [APP_TX]'
                ]);
            }

            $tax = Tax::where("state",$request->state)->first();

            if (!$tax) {
                $tax = new \stdClass();
                $tax->rates = 0;
            }

            return response()->json([
                'msg' => '',
                'tax' => $tax
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_tax_via_zip(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'zip' => 'required|regex:/^\d{5}$/'
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
                    'msg' => 'Your session has expired. Please login again. [APP_TX]'
                ]);
            }

            $tax = TaxZip::where("zip",$request->zip)->first();
            if (empty($tax)) {
                $tax = new \stdClass();
                $tax->rates = 7.75;
            }

            return response()->json([
                'msg' => '',
                'tax' => $tax
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    //This api seems to be out of date.
    public function cancel(Request $request) {
        try {
            return response()->json([
                'msg' => 'Please upgrade into the latest version'
            ]);

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment_id' => 'required'
                //'note' => 'required|max:255' // added on 2017-12-05
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
                    'msg' => 'Your session has expired. Please login again. [APP_CNC]'
                ]);
            }

            $ap = AppointmentList::where('user_id', $user->user_id)
              ->where('appointment_id', $request->appointment_id)->first();
            if (empty($ap)) {
                DB::rollback();
                return response()->json([
                  'msg' => 'Invalid appointment ID provided'
                ]);
            }

            $res = AppointmentProcessor::cancel($ap, $user, $request->note);

            return response()->json($res);

        } catch (\Exception $ex) {

            DB::rollback();

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] . ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function edit(Request $request)
    {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'appointment' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [APP_EDT]'
                ]);
            }

            # appointment request
            $ar = json_decode($request->appointment);

            $package = AppointmentProduct::where('appointment_id', $ar->appointment_id)->first();
            if (!empty($package) && in_array($package->prod_id, [28, 29])) {
                return response()->json([
                  'msg' => 'This is Non-Refundable Booking. Update is not allowed !!'
                ]);
            }

            $msg = AppointmentProcessor::edit($user, $ar->appointment_id, $ar->datetime, $ar->time);
            return response()->json([
                'msg' => $msg,
                'appointment_id' => $ar->appointment_id
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function checkPromoCode(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'promo_code' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [APP_CHKPC]'
                ]);
            }

            $promo_code = PromoCode::find(strtoupper($request->promo_code));
            if (empty($promo_code)) {
                return response()->json([
                    'msg' => 'Promotion code you entered does not exists in our system'
                ]);
            }

            $package_list = null;
            if (!empty($request->pets)) {
                $pets = json_decode($request->pets);
                $package_list = [];
                foreach ($pets as $pet) {
                    $package_list[] = isset($pet->info->package) ? $pet->info->package->prod_id : '';
                }
            }

            $msg = PromoCodeProcessor::checkIfUsed($user->user_id, $promo_code, null, $package_list);
            if (!empty($msg)) {
                return response()->json([
                    'msg' => $msg
                ]);
            }

            return response()->json([
                'msg' => '',
                'code' => $promo_code
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function check_address(Request $request) {
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

            /*if ($request->new_version != 'Y') {
                return response()->json([
                    'msg' => 'Please Upgrade to the Latest Version!'
                ]);
            }*/

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_CHKADR]'
                ]);
            }

            // check if there is address for the user
            $cnt = Address::where('user_id', $user->user_id)
                ->where('status', '!=', 'D')
                ->where(DB::raw("ifnull(zip, '')"), '!=', '')
                ->count();

            return response()->json([
                'msg' => '',
                'ask_zip' => $cnt == 0 ? 'Y' : 'N'
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function check_if_cats_allowed(Request $request) {
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

            /*if ($request->new_version != 'Y') {
                return response()->json([
                    'msg' => 'Please Upgrade to the Latest Version!'
                ]);
            }*/

            if (!Helper::check_app_key($request->api_key)) {
                return response()->json([
                    'msg' => 'Invalid API key provided'
                ]);
            }

            $email = \Crypt::decrypt($request->token);
            $user = User::where('email', strtolower($email))->first();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [APP_CHKADR]'
                ]);
            }

            $msg = AppointmentProcessor::check_if_cats_allowed($user);

            return response()->json([
                'msg' => $msg
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function save_address_zip_only(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'zip' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [APP_SVZIP]'
                ]);
            }

            if( !isset($request->zip) || strlen($request->zip) > 10){
                return response()->json([
                    'msg' => 'We are sorry but we cannot recognize your location. Please try again'
                ]);
                exit;
            }

            ### check if address is available ###
            $zip = AllowedZip::where('zip', $request->zip)
                ->whereRaw("lower(available) = 'x'")
                ->first();

            ### save zip code query log for later use ###
            $q = new ZipQuery;
            $q->path = isset($request->path) ? $request->path : '-';
            $q->zip = $request->zip;
            $q->address1 = isset($request->address1)? $request->address1 : '' ;
            $q->city = isset($request->city)? $request->city : '' ;
            $q->state =isset($request->state)? $request->state : '' ;
            $q->full_address =isset($request->address)? $request->address : '' ;
            $q->cdate = Carbon::now();
            $q->save();

            if (empty($zip)) {
                return response()->json([
                    'msg' => "Sorry! Groomit isn't available at this location yet!"
                ]);
            }else{
                if($q->state == ''){ //if no input on state
                    $q->city = $zip->city_name;
                    $q->state = $zip->state_abbr;
                    $q->save();
                }
            }

            $addr = Address::where('user_id', $user->user_id)
                ->where('status', 'A')
                ->first();

            ### now save address ###
            if (empty($addr)) {
                $addr = new Address;
            }

            //$allowed_zip = AllowedZip::where('zip', $request->zip)->first();
            $county = empty($zip) ? null : $zip->county_name;


            $addr->user_id = $user->user_id;
            $addr->name = '';
            $addr->county = $county;
            $addr->zip = $request->zip;
            $addr->default_address = 'Y';
            $addr->status = 'A';
            $addr->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    //================== package tour ===================//

    public function tour_get_sizes(Request $request) {
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

            $sizes = DB::select("
                select
                    size_id,
                    size_name,
                    size_desc
                from size
                order by size_id
            ");


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

    public function tour_get_package_addon(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'size_id' => 'required',
                'zip' => ''
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

            $group_id = 1;
            if (!empty($request->zip)) {
                $zip = AllowedZip::where('zip', $request->zip)
                    ->whereRaw("lower(available) = 'x'")
                    ->first();
                if (empty($zip)) {
                    return response()->json([
                        'msg' => "Sorry, Groomit isn't available at this location yet"
                    ]);
                }

                $group_id = $zip->group_id;
            }

            $packages = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where b.size_id = :size_id
                and a.prod_type = 'P'
                and a.prod_id not in (28, 29)
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'dog'
            ", [
                'size_id' => $request->size_id,
                'zip' => $request->zip,
                'group_id' => $group_id
            ]);

            $add_ons = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where b.size_id = :size_id
                and a.prod_type = 'A'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'dog'
                order by a.seq
            ", [
                'size_id' => $request->size_id,
                'zip' => $request->zip,
                'group_id' => $group_id
            ]);

            $shampoos = DB::select("
                select
                    a.prod_id,
                    a.prod_name,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'S'
                and a.status = 'A'
                and b.status = 'A'
                and b.group_id = :group_id
                and a.pet_type = 'dog'
            ", [
                'zip' => $request->zip,
                'group_id' => $group_id
            ]);

            Helper::log('### add_ons ###', $add_ons);

            return response()->json([
                'msg' => '',
                'packages' => $packages,
                'add_ons' => $add_ons,
                'shampoos' => $shampoos
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


}
