<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 10/20/16
 * Time: 3:55 PM
 */

namespace App\Http\Controllers\Groomer;

use App\Lib\AppointmentProcessor;
use App\Lib\EarningProcessor;
use App\Lib\ImageProcessor;
use App\Lib\GroomerScheduleProcessor;

use App\Model\AppointmentList;
use App\Model\ProfitSharing;
use App\Model\Groomer;
use App\Model\ProductGroomer;
use App\Model\ProductGroomerOrder;
use App\Model\ProductGroomerCart;
use App\Model\PromoCode;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Lib\Helper;

class GroomerController extends Controller
{
    public function signup(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'passwd' => 'required',
                'passwd_confirm' => 'required|same:passwd',
                'phone' => 'required|regex:/^\d{10}$/',
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required',
                'device_token' => ''
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

            ### first of all check if email already exists ###
            $groomer = Groomer::where('email', strtolower($request->email))->where('status', 'A')->first();
            if (!empty($groomer)) {
                return response()->json([
                    'msg' => 'Email already taken'
                ]);
            }

            $groomer = new Groomer;

            $groomer->first_name = $request->first_name;
            $groomer->last_name = $request->last_name;
            $groomer->email = strtolower($request->email);

            $groomer->phone = $request->phone;
            $groomer->address1 = $request->address1;
            $groomer->address2 = $request->address2;
            $groomer->city = $request->city;
            $groomer->state = $request->state;
            $groomer->zip = $request->zip;
            $groomer->passwd = \Crypt::encrypt($request->passwd);
            if (!empty($request->photo)) {
                $groomer->profile_photo = ImageProcessor::optimize(base64_decode($request->photo));
            }
            $groomer->cdate = Carbon::now();

            $groomer->device_token = $request->device_token;

            $groomer->save();

            $email_token = \Crypt::encrypt($request->email);

            if (!empty($groomer->photo)) {
                //$groomer->photo = base64_encode($groomer->photo);
                try{
                    $groomer->photo = base64_encode($groomer->photo);
                } catch (\Exception $ex) {
                    $groomer->photo = $groomer->photo ;
                }
            }

            return response()->json([
                'msg' => '',
                'token' => $email_token,
                'groomer' => $groomer
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function login(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'email' => 'required|email',
                'passwd' => 'required',
                'device_token' => ''
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

            $groomer = Groomer::where('email', strtolower($request->email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Invalid email or password provided'
                ]);
            }

            if (!empty($request->device_token)) {
                $groomer->device_token = $request->device_token;
                $groomer->save();
            }

            if (!empty($groomer->passwd)) {
                $decrypted_passwd = \Crypt::decrypt($groomer->passwd);
            } else {
                $decrypted_passwd = "";
            }

            if ($decrypted_passwd != $request->passwd) {
                return response()->json([
                    'msg' => 'Invalid email or password provided'
                ]);
            }

            $email_token = \Crypt::encrypt($request->email);

            if (!empty($groomer->profile_photo)) {
                //$groomer->photo = base64_encode($groomer->profile_photo);
                try{
                    $groomer->photo = base64_encode($groomer->profile_photo);
                } catch (\Exception $ex) {
                    $groomer->photo = $groomer->profile_photo ;
                }

            }

            return response()->json([
                'msg' => '',
                'token' => $email_token,
                'groomer' => $groomer
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_availability(Request $request) {

        try {

            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token' => 'required',
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                  'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $groomer_id = $groomer->groomer_id;

            $sdate = $request->sdate;
            $edate = $request->edate;

            $distance_type = $request->distance_type;
            $x = $request->x;
            $y = $request->y;

            $availabilities = GroomerScheduleProcessor::get_availability_by_date($groomer_id, $sdate, $edate);
            $appointments   = GroomerScheduleProcessor::get_appointments_by_date($groomer_id, $sdate, $edate,  $distance_type, $x, $y);
            $open_appointments = AppointmentProcessor::get_open_appointments2($groomer_id, $distance_type, $x, $y );

            if (empty($open_appointments)) {
                $open_appointments = [];
            }
            return response()->json([
                'msg' => '',
                'groomer_id'    => $groomer_id,
                'sdate'         => $sdate,
                'edate'         => $edate,
                'availabilities' => $availabilities,
                'appointments'  => $appointments,
                'open_appointments' => $open_appointments
            ]);

        } catch (\Exception $ex) {
            Helper::log('########### GET AVAILABILITY # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());

            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function set_availability(Request $request) {

        try {

            $v = Validator::make($request->all(), [
              'api_key' => 'required',
              'token' => 'required',
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                  'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $groomer_id = $groomer->groomer_id;

            $availabilities = $request->availabilities;
            $availabilities = json_decode (json_encode ($availabilities), FALSE);

            GroomerScheduleProcessor::set_availability_by_date($groomer_id, $availabilities);

            return response()->json([
              'msg' => ''
            ]);

        } catch (\Exception $ex) {
            Helper::log('########### GET AVAILABILITY # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_categories(Request $request)
    {

        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $categories = DB::select("
                select distinct prod_type
                from product_groomer
                where status = 'A'
                order by 1
            ", [
            ]);


            return response()->json([
                'msg' => '',
                'categories' => $categories
            ]);

        } catch (\Exception $ex) {
            Helper::log('########### GET PRODUCTS # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function get_products(Request $request)
    {

        try {

            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $products = ProductGroomer::where('status','A');

            if (!empty($request->prod_type)) {
                $products = $products->where('prod_type', $request->prod_type);
            }
            if (!empty($request->search)){
                $products = $products->whereRaw('LOWER(prod_name) like \'%' . strtolower($request->search) . '%\'');
            }

            $products = $products->get();

            return response()->json([
                'msg' => '',
                'products' => $products
            ]);

        } catch (\Exception $ex) {
            Helper::log('########### GET PRODUCTS # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function cart_add_product(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'pr_id' => 'required',
                'qty' => 'required'
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }
            $groomer_id = $groomer->groomer_id;
            $pr_id = $request->pr_id;
            $qty = $request->qty;

            ProductGroomerCart::add_to_cart($groomer_id, $pr_id, $qty);

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            Helper::log('########### GET AVAILABILITY # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function cart_delete_product(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [
                'api_key' => 'required',
                'token' => 'required',
                'del_pr_id' => 'required'
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $groomer_id = $groomer->groomer_id;
            $pr_id = $request->del_pr_id;

            ProductGroomerCart::delete_from_cart($groomer_id, $pr_id);

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            Helper::log('########### GET AVAILABILITY # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function cart_get(Request $request)
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $groomer_id = $groomer->groomer_id;

            $list = ProductGroomerCart::select('product_groomer_cart.*', 'product_groomer.*', 'product_groomer_cart.cdate as cdate', 'product_groomer_cart.id as id')
                ->where('groomer_id', $groomer_id)
                ->join('product_groomer', 'product_groomer.id', '=', 'product_groomer_cart.prod_gr_id')
                ->get();

            return response()->json([
                'msg' => '',
                'list' => $list
            ]);
        } catch (\Exception $ex) {
            Helper::log('########### GET AVAILABILITY # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    // do it later
    public function order_create(Request $request)
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $ret = DB::insert("
                insert into product_groomer_order (
                    groomer_id, prod_gr_id, prod_type, prod_name, size, price, 
                    street, zip, city, state, qty, status, created_by, cdate
                ) 
                select 
                    a.groomer_id,
                    prod_gr_id, 
                    b.prod_type, 
                    b.prod_name, 
                    b.size, 
                    b.price, 
                    c.street, 
                    c.zip, 
                    c.city, 
                    c.state, 
                    a.qty,
                    'N', 
                    concat('Groomer:', a.groomer_id), 
                    :cdate
                from product_groomer_cart as a
                    inner join product_groomer b on a.prod_gr_id = b.id
                    inner join groomer c on a.groomer_id = c.groomer_id 
                where a.groomer_id = :id
            ", [
                'id' => $groomer->groomer_id,
                'cdate' => Carbon::now()
            ]);

            if ($ret < 1) {
                return response()->json([
                    'msg' => 'Error in process'
                ]);
            }

            $groomer_id = $groomer->groomer_id;

            $ret = ProductGroomerCart::where('groomer_id', $groomer_id)->delete();

            if ($ret < 0) {
                \Illuminate\Support\Facades\DB::rollback();
                return 'Failed to clear old profit sharing detail record';
            }

            return response()->json([
                'msg' => ''
            ]);
        } catch (\Exception $ex) {
            Helper::log('########### Groomer Order Create # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function order_list(Request $request)
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
            Helper::log('### email ##' . $email);

            $groomer = ProductGroomerOrder::where('groomer_id', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $groomer_id = $groomer->groomer_id;

            $list = ProductGroomerCart::where('groomer_id', $groomer_id)->get();

            return response()->json([
                'msg' => '',
                'list' => $list
            ]);
        } catch (\Exception $ex) {
            Helper::log('########### GET AVAILABILITY # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function order_detail(Request $request)
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
            Helper::log('### email ##' . $email);

            $groomer = Groomer::where('email', strtolower($email))->where('status', 'A')->first();
            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again'
                ]);
            }

            $groomer_id = $groomer->groomer_id;

            $list = ProductGroomerCart::where('groomer_id', $groomer_id)->get();

            return response()->json([
                'msg' => '',
                'list' => $list
            ]);
        } catch (\Exception $ex) {
            Helper::log('########### GET AVAILABILITY # EXCEPTION ########', $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString());
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


}