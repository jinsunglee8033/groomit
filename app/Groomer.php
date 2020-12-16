<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/20/19
 * Time: 2:04 PM
 */

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Redirect;
use Illuminate\Support\Facades\Auth;

class Groomer extends Authenticatable
{
    protected $table = 'groomer';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'groomer_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name', 'email', 'password', 'status', 'last_login_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'password', 'remember_token',
    ];

    public function getAuthPassword() {
        return $this->password;
    }

    /**
     * Check valid admin user and record last login date
     * @param $admin_id
     * @return mixed
     */
    public function confirmLogin($groomer_id) {

        try {
            $groomer = Groomer::find($groomer_id);

            // I: invalid admin user
            if ($groomer->status == 'I') {
                Auth::guard('admin')->logout();
                Redirect::route('admin.login')->with('alert', 'Invalid Groomer');
            }
            // Save currunt date time for last login date
            $groomer->last_login_date = Carbon::now();
            $groomer->save();

        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}
