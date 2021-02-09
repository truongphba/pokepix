<?php

namespace App\Http\Controllers\Cms;

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
}
