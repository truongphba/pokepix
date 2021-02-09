<?php

namespace App\Http\Middleware;

use Closure;

class CheckDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $device = $request->header('Device');

        if( empty($device) )
        {
            return response()->json(['message' => 'Device not empty!'], 200);
        }

        return $next($request);
    }
}
