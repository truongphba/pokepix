<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class DeviceUser
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
        $token = $request->token;
        $device = $request->header('Device');
        $user = User::where('token', $token)->first();
        if(!$user){
            $user = User::whereDeviceId($device)->first();
        }
        if( !$user )
        {
            return response()->json(['message' => 'User not found!'], 200);
        }
        $request->merge(['user' => $user]);
        return $next($request);
    }
}
