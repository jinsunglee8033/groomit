<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Redirect;
use Illuminate\Support\Facades\Auth;

class Admin extends Authenticatable
{
    protected $table = 'admin';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'admin_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'status', 'group', 'last_login_date',
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
    public function confirmLogin($admin_id) {

        try {
            $admin = Admin::find($admin_id);

            // I: invalid admin user
            if ($admin->status == 'I') {
                Auth::guard('admin')->logout();
                Redirect::route('admin.login')->with('alert', 'Invalid admin user');
            }
            // Save currunt date time for last login date
            $admin->last_login_date = Carbon::now();
            $admin->save();

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}
