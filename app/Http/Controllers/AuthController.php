<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginView()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('login.main', ['layout' => 'login']);
    }

    public function login()
    {
        $credentials = [
            'email'    => request('email'),
            'password' => request('password'),
        ];

        if (Auth::attempt($credentials, request()->has('remember'))) {
            request()->session()->regenerate();
          

            return redirect()->intended('/');
        }

        return back()->withErrors(['password' => 'Email atau password salah.']);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('login');
    }
}
