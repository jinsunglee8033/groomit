<?php

namespace App\Http\Controllers\AffiliateAuth;

use App\Affiliate;
use App\Lib\Helper;
use App\Model\AffiliateCode;
use App\Model\PromoCode;
use App\Model\UserLoginHistory;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
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
    protected $redirectTo = '/affiliate';
    protected $guard = 'affiliate';

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

        if (Auth::guard('affiliate')->check()) {
            return Redirect::route('affiliate.earnings');
        }
        return view('affiliate.login');
    }

    /**
     * @return mixed
     */
    public function showApplyForm(){

        return view('affiliate.apply-now');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $apply = false)
    {

        if ($apply) {
            return Validator::make($data, [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:affiliate,email',
                'password' => 'required|confirmed',
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
        $result = null;

        try {

            DB::beginTransaction();

            ## 1. Create affiliate account

            # input first_name and last_name if there is no business name

            $account = new Affiliate;
            $account->business_name = $data['business_name'];
            $account->email = $data['email'];
            $account->password = bcrypt($data['password']);
            $account->first_name = $data['first_name'];
            $account->last_name = $data['last_name'];
            $account->status = 'A'; // 'A' : active, 'I' : inactive
            $account->save();


            ## 2. Create a random affiliate code

            $res_code = AffiliateCode::newAffiliateCode($account->aff_id);

            DB::commit();

            if ($res_code) {
                $result = $account->aff_id;
            }

            return $result;

        } catch (\Exception $ex) {

            DB::RollBack();

            return $ex->getMessage() . ' [' . $ex->getCode() . ']';
        }

    }

    /**
     * Register a new affiliate user
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request) {
        try {
            $data = $request->all();
            $v = $this->validator($data, true);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return Redirect::route('affiliate.apply')->with('alert', $msg);
            }

            $res = $this->create($data);

            if (is_int($res)) {
                Auth::guard('affiliate')->loginUsingId($res, true);
                //Login with ID, that's why it didn't use ->login().
                return Redirect::route('affiliate.earnings');

            } else {
                return Redirect::route('affiliate.apply')->with('alert', $res);
            }

        } catch (\Exception $ex) {

            return Redirect::route('affiliate.apply')->with('alert', $ex->getMessage() . ' [' . $ex->getCode() . ']');

        }
    }

    /**
     * Affiliate Login
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
                return Redirect::route('affiliate.login')->with('alert', $msg);
            }

            $credentials = $this->getCredentials($request);
            if (Auth::guard('affiliate')->attempt($credentials)) {

                // Check valid affiliate user and record last login date
                $auth = Auth::guard('affiliate')->user();
                $auth->confirmLogin($auth->aff_id);

                // Redirect to appointments list page
                return Redirect::route('affiliate.earnings');

            } else {
                return Redirect::route('affiliate.login')->with('alert', 'Login failed.');
            }


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    /**
     * Affiliate logout
     * @return mixed
     */
    public function logout() {
        Auth::guard('affiliate')->logout();
        return Redirect::route('affiliate.login');
    }

    public function loginAs(Request $request) {

        Helper::log('### inside postLoginAs ###', [
            'aff_id' => $request->aff_id
        ]);

        $affiliate = \App\Affiliate::find($request->aff_id);

        if (empty($affiliate)) {
            return back()->withErrors([
                'exception' => 'Invalid Affiliate ID provided'
            ])->withInput();
        }

        if ($affiliate->status != 'A') {
            return back()->withErrors([
                'exception' => 'Affiliate is not in activate status'
            ])->withInput();
        }

        \Auth::guard('affiliate')->login($affiliate);

        return Redirect::route('affiliate.earnings');

    }

}
