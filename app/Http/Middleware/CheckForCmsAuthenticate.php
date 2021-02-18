<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckForCmsAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('account')->check()) {
            $accountRole = Account::where('id', Auth::guard('account')->id())->first()->role;
            if ($accountRole == 1) {
                return $next($request);
            }
        }
        return redirect('/cms/login');
    }
}
