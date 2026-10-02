<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('authentication.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name'     => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'name' => 'The provided credentials do not match our records.',
        ])->onlyInput('name');
    }

    /**
     * Send the site root to the dashboard.
     *
     * This is a controller rather than an inline closure in routes/web.php
     * because `php artisan route:cache` cannot serialise closures, and the
     * cached route file is what keeps route resolution off the filesystem
     * in production.
     */
    public function root()
    {
        return redirect('/dashboard');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
