<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Login failed.');
        }

        $user = \App\Models\User::where('email', $googleUser->getEmail())->first();

        // Strict Whitelist Check
        if (!$user) {
            return redirect('/login')->with('error', 'Akses ditolak. Email Anda (' . $googleUser->getEmail() . ') tidak terdaftar di sistem. Hubungi Admin.');
        }

        $user->update([
            'google_id' => $googleUser->getId(),
            // 'name' => $googleUser->getName(),
        ]);

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect('/dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->with('error', 'The provided credentials do not match our records.');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
