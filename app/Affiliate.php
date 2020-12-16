<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Redirect;
use Illuminate\Support\Facades\Auth;

class Affiliate extends Authenticatable
{
    protected $table = 'affiliate';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'aff_id';


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    public function getAuthPassword() {
        return $this->password;
    }

    /**
     * Check valid admin user and record last login date
     * @param $admin_id
     * @return mixed
     */
    public function confirmLogin($aff_id) {

        try {
            $affiliate = Affiliate::find($aff_id);

            // I: invalid affiliate user
            if ($affiliate->status == 'I') {
                Auth::guard('affiliate')->logout();
                Redirect::route('affiliate.login')->with('alert', 'Invalid affiliate user');
            }
            // Save current date time for last login date
            $affiliate->last_login_date = Carbon::now();
            $affiliate->save();

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

}
