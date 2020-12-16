<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/29/19
 * Time: 3:46 PM
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotGroomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'groomer')
    {
        if (!Auth::guard($guard)->check()) {
            return redirect('/groomer/login');
        }

        return $next($request);
    }
}
