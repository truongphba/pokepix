<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return string
     */
    protected function redirectTo($request, Closure $next)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
