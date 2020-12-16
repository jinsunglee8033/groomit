<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/20/16
 * Time: 4:39 PM
 */

namespace App\Http\Controllers;

use App\Lib\ImageProcessor;
use App\Lib\PaymentProcessor;
use App\Lib\PromoCodeProcessor;
use App\Model\PromoCode;
use App\Model\User;
use App\Model\UserBilling;
use App\Model\UserPhoto;
use App\Model\AppointmentList;
use Illuminate\Http\Request;
use Validator;
use App\Lib\Helper;
use Log;
use Carbon\Carbon;
use DB;
use App\Lib\Converge;
use App\Lib\CreditProcessor;

class UserController extends Controller
{

    public function remove_billing(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'billing_id' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [USR_BL]'
                ]);
            }

            $user_billing = UserBilling::find($request->billing_id);
            if (empty($user_billing)) {
                return response()->json([
                    'msg' => 'Invalid billing ID provided'
                ]);
            }

            if ($user_billing->user_id != $user->user_id) {
                return response()->json([
                    'msg' => 'Provided token does not match'
                ]);
            }

            /*$ret = Converge::remove_token($user_billing->card_token);
            if (!empty($ret['error_msg'])) {
                return response()->json([
                    'msg' => 'Credit card token removal failed: ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']'
                ]);
            }*/

            $user_billing->status = 'D';
            $user_billing->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_billing(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'billing_id' => 'required',
                //'card_type' => 'required|in:V,M,A',
                'card_number' => 'required|regex:/^\d{15,16}$/',
                'card_holder' => 'required',
                'expire_mm' => 'required|regex:/^\d{2}$/',
                'expire_yy' => 'required|regex:/^\d{2}$/',
                'cvv' => 'required|regex:/^\d{3,4}$/',
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
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
                    'msg' => 'Your session has expired. Please login again. [USR_UPDBL]'
                ]);
            }

            $ret = PaymentProcessor::update_card(
                $user->user_id,
                $request->billing_id,
                $request->card_holder,
                $request->card_number,
                $request->expire_mm,
                $request->expire_yy,
                $request->cvv,
                $request->address1,
                $request->address2,
                $request->city,
                $request->state,
                $request->zip,
                $request->default_card
            );

            if (!empty($ret['msg'])) {
                return response()->json([
                    'msg' => $ret['msg']
                ]);
            }

            $user_billing = $ret['card'];

            return response()->json([
                'msg' => '',
                'billing_id' => $user_billing->billing_id
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function add_billing(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                //'card_type' => 'required|in:V,M,A',
                'card_number' => 'required|regex:/^\d{15,16}$/',
                'card_holder' => 'required',
                'expire_mm' => 'required|regex:/^\d{2}$/',
                'expire_yy' => 'required|regex:/^\d{2}$/',
                'cvv' => 'required|regex:/^\d{3,4}$/',
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|regex:/^\d{5}$/'            ]);

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
                    'msg' => 'Your session has expired. Please login again. [USR_ADDBL]'
                ]);
            }

            $ret = PaymentProcessor::add_card(
                $user->user_id,
                $request->card_holder,
                $request->card_number,
                $request->expire_mm,
                $request->expire_yy,
                $request->cvv,
                $request->address1,
                $request->address2,
                $request->city,
                $request->state,
                $request->zip,
                $request->default_card
            );

            if (!empty($ret['msg'])) {
                return response()->json([
                    'msg' => $ret['msg']
                ]);
            }

            $user_billing = $ret['card'];

            return response()->json([
                'msg' => '',
                'billing_id' => $user_billing->billing_id
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_user_billing(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [USR_USRBL]'
                ]);
            }

            $user_billings = UserBilling::where('user_id', $user->user_id)->where('status', 'A')->get();

            return response()->json([
                'msg' => '',
                'user_billings' => $user_billings
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_my_pet_photos(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [USR_PETPHT]'
                ]);
            }

            $pets = DB::select("
                select
                    a.pet_id,
                    a.name,
                    a.dob,
                    timestampdiff(month, a.dob, curdate()) as age,
                    a.gender,
                    b.photo_id,
                    b.photo,
                    concat(c.address1, ' ', c.address2, ' ', c.city, ' ', c.state, ' ', c.zip) as address
                from pet a 
                    inner join pet_photo b on a.pet_id = b.pet_id
                    inner join user c on a.user_id = c.user_id
                where a.user_id = :user_id
            ", [
                'user_id' => $user->user_id
            ]);

            Log::info('### pets ###', [
                'pets' => var_export($pets, true)
            ]);

            foreach ($pets as $o) {
                $o->photo = base64_encode($o->photo);
                $year = intval($o->age / 12);
                $month = intval($o->age % 12);
                $o->age = ($year > 0 ? $year . ' year' . ($year > 1) ? 's' : ' ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');
            }

            return response()->json([
                'msg' => '',
                'pets' => $pets
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_popular_photos(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [USR_PPLPHT]'
                ]);
            }

            $pets = DB::select("
                select
                    a.pet_id, 
                    a.name,
                    a.dob,
                    timestampdiff(month, a.dob, curdate()) as age,
                    a.gender,
                    b.photo_id,
                    b.photo,
                    concat(c.address1, ' ', c.address2, ' ', c.city, ' ', c.state, ' ', c.zip) as address
                from pet a 
                    inner join pet_photo b on a.pet_id = b.pet_id
                    inner join user c on a.user_id = c.user_id
                where b.liked >= 10
            ");

            Log::info('### pets ###', [
                'pets' => var_export($pets, true)
            ]);

            foreach ($pets as $o) {
                $o->photo = base64_encode($o->photo);
                $year = intval($o->age / 12);
                $month = intval($o->age % 12);
                $o->age = ($year > 0 ? $year . ' year' . ($year > 1) ? 's' : ' ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');
            }

            return response()->json([
                'msg' => '',
                'pets' => $pets
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_recent_groomed_photos(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [USR_RCTGRPHT]'
                ]);
            }

            $pets = DB::select("
                select
                    a.pet_id, 
                    a.name,
                    a.dob,
                    timestampdiff(month, a.dob, curdate()) as age,
                    a.gender,
                    b.photo_id,
                    b.photo,
                    concat(c.address1, ' ', c.address2, ' ', c.city, ' ', c.state, ' ', c.zip) as address
                from pet a 
                    inner join pet_photo b on a.pet_id = b.pet_id
                    inner join user c on a.user_id = c.user_id
                where a.last_groomed_date >= curdate() - interval 10 day
            ");

            Log::info('### pets ###', [
                'pets' => var_export($pets, true)
            ]);

            foreach ($pets as $o) {
                $o->photo = base64_encode($o->photo);

                $year = intval($o->age / 12);
                $month = intval($o->age % 12);
                $o->age = ($year > 0 ? $year . ' year' . ($year > 1) ? 's' : ' ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');
            }

            return response()->json([
                'msg' => '',
                'pets' => $pets
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_profile_basic(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                //'email' => 'required|email',
                'phone' => 'required|regex:/^\d{10}$/'
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
                    'msg' => 'Your session has expired. Please login again. [USR_UPD]'
                ]);
            }

//            $new_email = strtolower($request->email);
//            if ($email != $new_email) {
//                $user_existing = User::where('email', $new_email)->where('user_id', '!=', $user->user_id)->first();
//                if ($user_existing) {
//                    return response()->json([
//                        'msg' => 'New email address is already taken by another user'
//                    ]);
//                }
//            }


            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            //$user->email = $new_email;
            $user->phone = $request->phone;

            $user->save();

            if (!empty($request->photo)) {
                $ret = DB::statement("
                    delete from user_photo
                    where user_id = :user_id
                ", [
                    'user_id' => $user->user_id
                ]);

                $user_photo = new UserPhoto;
                $user_photo->user_id = $user->user_id;
                $user_photo->photo = ImageProcessor::optimize(base64_decode($request->photo));
                $user_photo->save();

                $user->photo = base64_encode($user_photo->photo);
            }

            return response()->json([
                'msg' => '',
                'user' => $user
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function reset_password(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'current_password' => 'required',
                'password' => 'same:confirm_password'
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
                    'msg' => 'Your session has expired. Please login again. [USR_RSTPW]'
                ]);
            }

            if ($request->current_password != \Crypt::decrypt($user->passwd)) {
                return response()->json([
                    'msg' => 'Current Password does not match'
                ]);
            }

            if ($request->confirm_password == $request->password && $request->password != '') {
                $user->passwd = \Crypt::encrypt($request->password);
            }

            $user->save();

            # get user photo
            $user_photo = UserPhoto::where('user_id', $user->user_id)->first();
            if (!empty($user_photo)) {
                $user->photo = base64_encode($user_photo->photo);
            } else {
                $user->photo = null;
            }

            return response()->json([
                'msg' => '',
                'user' => $user
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_cards(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [USR_CC]'
                ]);
            }

            $cards = UserBilling::where('user_id', $user->user_id)->where('status', 'A')->get();

            foreach ($cards as $card) {
//                switch ($card->card_type) {
//                    case 'V':
//                        $card->type = 'visa';
//                        break;
//                    case 'A':
//                        $card->type = 'amex';
//                        break;
//                    case 'M':
//                        $card->type = 'master';
//                        break;
//                    case 'D':
//                        $card->type = 'discover';
//                        break;
//                }

                $card->expire_mm = str_pad($card->expire_mm, 2, '0', STR_PAD_LEFT);
                $card->expire_yy = str_pad($card->expire_yy, 2, '0', STR_PAD_LEFT);

                //$card->card_number = str_pad(substr($card->card_number, 8), strlen($card->card_number), '*', STR_PAD_LEFT);
            }

            $available_credit = CreditProcessor::getAvailableCredit($user->user_id);

            ### find unused groupon code ###
            $codes = PromoCode::where('user_id', $user->user_id)
                ->where('type', 'G')
                ->where('total_month', '>', 1)
                ->get();

            $promo_code = null;
            if (count($codes) > 0) {
                foreach ($codes as $o) {
                    $used_cnt = PromoCodeProcessor::getUsedCount($o->code);
                    $month_left = $o->total_month - $used_cnt;

                    Helper::log('### multi-month gorupon check ###', [
                        'code' => $o->code,
                        'total_month' => $o->total_month,
                        'used_cnt' => $used_cnt,
                        'month_left' => $month_left
                    ]);

                    if ($month_left > 0) {
                        $promo_code = $o;
                        break;
                    }
                }
            }

            return response()->json([
                'msg' => '',
                'cards' => $cards,
                'available_credit' => $available_credit,
                'groupon_code' => $promo_code
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_favorite_groomers(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [USR_FAVGRM]'
                ]);
            }

            $groomers = DB::select("
                select b.* 
                from user_favorite_groomer a 
                    inner join groomer b on a.groomer_id = b.groomer_id
                where a.user_id = :user_id    
            ", [
                'user_id' => $user->user_id
            ]);

            foreach ($groomers as $groomer) {
                //$groomer->profile_photo = base64_encode($groomer->profile_photo);
                $app = AppointmentList::where('user_id', $user->user_id)
                    ->where('groomer_id', $groomer->groomer_id)
                    ->where('status', 'P')
                    ->orderBy('accepted_date', 'desc')
                    ->first();

                if (!empty($app) && $app->accepted_date) {
                    $groomer->last_groomed_date = Carbon::parse($app->accepted_date)->format('Y-m-d');
                }
            }

            return response()->json([
                'msg' => '',
                'groomers' => $groomers
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function remove_favorite_groomer(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [USR_RMFAVGRM]'
                ]);
            }

            $ret = DB::statement("
                delete from user_favorite_groomer
                where user_id = :user_id
                and groomer_id = :groomer_id
            ", [
                'user_id' => $user->user_id,
                'groomer_id' => $request->groomer_id
            ]);

            if ($ret < 1) {
                return response()->json([
                    'msg' => 'Failed to remove favorite'
                ]);
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    // Make unique default card
    private function check_default_card($card)
    {
        if ($card->default_card == 'Y') {

            UserBilling::where('user_id', '=', $card->user_id)
                ->where('billing_id', '!=', $card->billing_id)
                ->where('default_card', '=', 'Y')
                ->update(array('default_card' => 'N'));
        }
    }

    public function update_device_token(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'device_token' => 'required'
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
                    'msg' => 'Your session has expired. Please login again. [USR_UPDDVTK]'
                ]);
            }

            $user->device_token = $request->device_token;
            $user->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function showProfilePhoto($id) {

        $user_photo = UserPhoto::find($id);
        $photo = '';
        if (!empty($user_photo)) {
            $photo = $user_photo->photo;
        }
        return response($photo, 200)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Transfer-Encoding', 'binary')
            ->header('Content-Length', strlen($photo));
    }

    public function get_available_credit(Request $request) {
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
                    'msg' => 'Your session has expired. Please login again. [USR_UPDDVTK]'
                ]);
            }

            # get available credit
            $available_credit = CreditProcessor::getAvailableCredit($user->user_id);

            return response()->json([
                'msg' => '',
                'available_credit' => $available_credit
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
}