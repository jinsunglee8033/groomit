<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 5/17/16
 * Time: 10:41 AM
 */

namespace App\Http\Middleware;

use App\Model\RequestLog;
use App\Model\RequestReferal;
use Closure;
use Illuminate\Support\Facades\Log;


class LogAfterRequest {

    public function handle($request, \Closure  $next)
    {
        return $next($request);
    }

    //Called after response is done at Middleware.
    public function terminate($request, $response)
    {
        Log::info('### APP.REQUESTS ###', [
            'PATH' => $request->path(),
            'REQUEST' => $request->all(),//except('image', 'photo'),
            'METHOD' => $request->getMethod(),
            'RESPONSE' => (starts_with($request->path(), 'api') || $request->path() == 'sms-reply') ? $response->getContent() : 'Hidden',
            'UNIQUE_ID' => isset($_SERVER['UNIQUE_ID']) ? $_SERVER['UNIQUE_ID'] : ''
        ]);

        ### log($url, $user_id, $appointment_id, $groomer_id, $req, $res, $ip_addr)
        //Save to DB
        RequestLog::log(
            $request->path(),
            $request->user_id,
            $request->appointment_id,
            $request->groomer_id,
            json_encode($request->all()),
          (starts_with($request->path(), 'api') || starts_with($request->path(), 'user/api') || $request->path() == 'sms-reply') ? $response->getContent() : 'Hidden',
            $request->ip()
        );

        RequestReferal::log($request->ip());
    }

}
