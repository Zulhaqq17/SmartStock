<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * MODULE 1: User Authentication and Access Management
 * Owner: TODO (name)
 *
 * Basic login/logout is already working so everyone can test. Module 1 owner extends it:
 * forgot/reset password, Manager "add user" page (see UserController), failed-login lockout, etc.
 */
class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // is_active => true means deactivated users cannot log in
        if (Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
