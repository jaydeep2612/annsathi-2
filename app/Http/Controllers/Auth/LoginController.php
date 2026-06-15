<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class LoginController extends Controller
{
    /**
     * Show the application's login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact your administrator.',
                ]);
            }

            return $this->redirectUser($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Redirect users to their respective panels based on their role.
     */
    protected function redirectUser($user)
    {
        if ($user->is_super_admin) {
            return redirect()->intended('/super-admin');
        }

        if ($user->hasRole('super_admin')) {
            return redirect()->intended('/super-admin');
        }

        if ($user->hasRole('restaurant_admin')) {
            return redirect()->intended('/restaurant-admin');
        }

        if ($user->hasRole('manager')) {
            return redirect()->intended('/manager');
        }

        if ($user->hasRole('chef')) {
            return redirect()->intended('/kitchen');
        }

        if ($user->hasRole('waiter')) {
            return redirect()->intended('/waiter');
        }

        // Default fallback if no specific role matches
        return redirect()->intended('/admin');
    }
}
