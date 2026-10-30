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
        $credentials = request()->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, request()->boolean('remember'))) {
            request()->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()
            ->withInput(request()->only('email', 'remember'))
            ->withErrors(['password' => 'Email atau password salah.']);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('login');
    }
}
