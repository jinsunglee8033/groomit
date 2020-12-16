<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    //For ALL HTTP requests.
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\LogAfterRequest::class,
        \App\Http\Middleware\Cors::class,
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \App\Http\Middleware\VerifyCsrfToken::class,

    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    //In order to apply multiple middlewares easily at routing.
    protected $middlewareGroups = [
        'web' => [
//            \App\Http\Middleware\EncryptCookies::class,
//            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
//            //\Illuminate\Session\Middleware\StartSession::class,
//            //\Illuminate\View\Middleware\ShareErrorsFromSession::class,
//            \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    //In order to use the middleware for a few routes only. Need to assign shorthand key first, and use it at routing.
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'cors' => \App\Http\Middleware\Cors::class,
        'admin' => \App\Http\Middleware\RedirectIfNotAdmin::class,
        'affiliate' => \App\Http\Middleware\RedirectIfNotAffiliate::class,
        'user' => \App\Http\Middleware\RedirectIfNotUser::class,
        'groomer' => \App\Http\Middleware\RedirectIfNotGroomer::class,

    ];
}
