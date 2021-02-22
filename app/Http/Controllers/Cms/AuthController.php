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
        ],[
            'username.required' => 'Bắt buộc phải nhập tên đăng nhập',
            'password.required' => 'Bắt buộc phải nhập tên mật khẩu',
        ]);

        $login = [
            'username' => $request->username,
            'password' => $request->password,
        ];
        if (Auth::guard('account')->attempt($login)) {
            return redirect('/cms/users');
        } else {
            return redirect()->back()->with('error', 'Tên đăng nhập hoặc mật khẩu không chính xác');
        }
    }

    public function logout(){
        Auth::guard('account')->logout();
        return redirect('/cms/login');
    }
}
