<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/20/19
 * Time: 2:00 PM
 */

namespace App\Http\Controllers\Groomer\Web;

use App\Admin;
use App\Model\Groomer;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use Log;
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
    protected $redirectTo = '/groomer';
    protected $guard = 'groomer';

    /**
     * Create a new authentication controller instance.
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);

    }

    /**
     * Show appointments list page when logined user
     * @return mixed
     */
    public function showLoginForm(){

        if (Auth::guard('groomer')->check()) {
            return Redirect::route('groomer');
        }
        return view('groomer.login');
    }

    /**
     * @return mixed
     */
    public function showRegistrationForm(){

        return view('groomer.registration');
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
              'name' => 'required|max:255',
              'email' => 'required|email|max:255|unique:admin,email',
              'password' => 'required|min:6|confirmed',
            ]);
        } else {
            return Validator::make($data, [
              'email' => 'required|email|max:255',
              'password' => 'required|min:6',
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
        return Admin::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'password' => bcrypt($data['password']),
          'status' => 'I', // 'A' : active, 'I' : inactive
        ]);
    }

    /**
     * Register a new admin user
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
                return Redirect::route('admin.registration')->with('alert', $msg);
            }

            $this->create($data);

            return Redirect::route('admin.admins');

        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    /**
     * Admin Login
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

                return Redirect::route('groomer.login')->with('alert', $msg);
            }

//            $credentials = $this->getCredentials($request);
//            print_r($credentials);
//            exit;

            $groomer = \App\Groomer::where('email', strtolower($request->email))
              ->whereIn('status', ['A', 'P'])
              ->first();
            if (empty($groomer)) {
                $msg = 'Invalid email or password provided';
                return Redirect::route('groomer.login')->with('alert', $msg);
            }

            if (!empty($groomer->password)) {
                $decrypted_passwd = \Crypt::decrypt($groomer->password);
            } else {
                $decrypted_passwd = "";
            }

            if ($decrypted_passwd != $request->password) {
                $msg = 'Invalid email or password provided.';
                return Redirect::route('groomer.login')->with('alert', $msg);
            }

//            print_r($groomer);
//            exit;

            Auth::guard('groomer')->login($groomer);

            return Redirect::route('groomer');


        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    /**
     * Admin logout
     * @return mixed
     */
    public function logout() {
        Auth::guard('groomer')->logout();
        return Redirect::route('groomer.login');
    }

}
