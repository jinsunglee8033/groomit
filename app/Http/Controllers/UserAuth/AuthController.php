<?php

namespace App\Http\Controllers\UserAuth;

use App\Model\Address;
use App\Model\AllowedZip;
use App\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Redirect;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/user';
    protected $guard = 'user';

    /**
     * Create a new authentication controller instance.
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
        Session::put('backUrl', URL::previous());

    }

    /**
     * Show appointments list page when logined user
     * @return mixed
     */
    public function showLoginForm(){

        if (Auth::guard('user')->check()) {
            return Redirect::route('user.appointment.select-service');
        }
        return view('user.login');
    }

    /**
     * @return mixed
     */
    public function showRegistrationForm(){

        return view('user.registration');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $register = false)
    {

        if ($register) {
            return Validator::make($data, [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'passwd' => 'required',
                'passwd_confirm' => 'required|same:passwd',
                'phone' => 'required|regex:/^\d{10}$/',
                'address1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|regex:/^\d{5}$/',
                'vendor_token' => ''
            ]);
        } else {
            return Validator::make($data, [
                'email' => 'required|email',
                'passwd' => 'required',
                'login_channel' => 'required|in:i'
            ]);
        }

    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
//        switch ($data['login_channel']) {
//            case 'i':
//                if (!empty($data['passwd'])) {
//                    $data['passwd'] = \Crypt::encrypt($data['passwd']);
//                }
//                break;
//            case 'f':
//                $data['fb_token'] = $data['vendor_token'];
//                break;
//            case 'g':
//                $data['gg_token'] = $data['vendor_token'];
//                break;
//        }

        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'passwd' => \Crypt::encrypt($data['passwd']),
            'phone' => $data['phone'],
            //'hear_from' => $data['heard_from'],
            'zip' => $data['zip'],
            //'fb_token' => $data['fb_token'],
            //'gg_token' => $data['gg_token']
        ]);
    }

    /**
     * Register a new user
     * @param Request $request
     * @return mixed
     */
    public function registration(Request $request) {
        try {
            $data = $request->all();
            $v = $this->validator($data, true);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return Redirect::back()->withErrors([$msg]);
            }

            $chk_user = User::where('email', $request->email)->first();
            if (!empty($chk_user)) {
                return Redirect::back()->withErrors(['Duplicated email!']);
            }

            $user = $this->create($data);


            $allowed_zip = AllowedZip::where('zip', $request->zip)->first();
            $county = empty($allowed_zip) ? null : $allowed_zip->county_name;

            # save address
            $address = new Address();
            $address->user_id = $user->user_id;
            $address->address1 = $request->address1;
            $address->address2 = $request->address2;
            $address->city = $request->city;
            $address->county = $county;
            $address->state = $request->state;
            $address->zip = $request->zip;
            $address->default_address = 'Y';

            $address = $address->getGeolocation($address);

            $address->save();


            return Redirect::route('user.appointment.select-pet',['pet_type' => Session::get('appointment:service')->pet_type]);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            return Redirect::back()->withErrors([$msg]);
        }
    }

    /**
     * User Login
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request) {
        try {

            $v = $this->validator($request->all(), false);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return Redirect::route('user.login')->with('alert', $msg);
            }

            $credential = array(
                'email' => $request->email,
                'password' => $request->passwd
            );


            # redirect to user.appointment for now
            //return Redirect::route('user.appointment');

            if (Auth::guard('user')->attempt($credential)) {

                if (Session::get('backUrl') == 'user.appointment.login-signup') {
                    // Redirect to select pet page
                    return Redirect::route('user.appointment.select-pet', ['pet_type' => Session::get('appointment:service')->pet_type]);
                } else {
                    // Redirect to previous page
                    return Redirect::intended(Session::get('backUrl'));
                }


            } else {

                return Redirect::intended(Session::get('backUrl'))->withErrors(
                    $this->getFailedLoginMessage()
                );
            }

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();

            return response()->json([
                'msg' => $msg
            ]);
        }
    }

    /**
     * User logout
     * @return mixed
     */
    public function logout() {
        Auth::guard('user')->logout();
        return Redirect::intended('/user/appointment/select-service');
    }

}
