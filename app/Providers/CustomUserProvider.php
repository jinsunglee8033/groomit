<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/9/18
 * Time: 3:07 PM
 */

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class CustomUserProvider extends EloquentUserProvider
{
    /**
     * @param UserContract $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password']; // will depend on the name of the input on the login form

        if (!empty($user->passwd)) {
            $decrypted_passwd = \Crypt::decrypt($user->passwd);
        } else {
            $decrypted_passwd = "";
        }

        return $decrypted_passwd == $plain;
    }
}