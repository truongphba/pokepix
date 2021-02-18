<?php

namespace App\Http\Controllers\Cms;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function username()
    {
        return 'username';
    }

    public function login()
    {
        if (Auth::guard('account')->check()) {
            $accountRole = Account::where('id', Auth::guard('account')->id())->first()->role;
            if ($accountRole == 1) {
              return redirect('/cms/users');
            }
        }
        return view('cms.login');
    }

    public function loginProcess(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $login = [
            'username' => $request->username,
            'password' => $request->password,
        ];
        if (Auth::guard('account')->attempt($login)) {
            return redirect('/cms/users');
        } else {
            return redirect()->back()->with('error', 'Username or Password incorrect');
        }
    }

    public function logout(){
        Auth::guard('account')->logout();
        return redirect('/cms/login');
    }
}
