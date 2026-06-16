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
        $targetPath = '/admin';

        if ($user->is_super_admin || $user->hasRole('super_admin')) {
            $targetPath = '/super-admin';
        } elseif ($user->hasRole('restaurant_admin')) {
            $targetPath = '/restaurant-admin';
        } elseif ($user->hasRole('manager')) {
            $targetPath = '/manager';
        } elseif ($user->hasRole('chef')) {
            $targetPath = '/kitchen';
        } elseif ($user->hasRole('waiter')) {
            $targetPath = '/waiter';
        }

        $intended = session()->get('url.intended');

        if ($intended) {
            $intendedPath = parse_url($intended, PHP_URL_PATH);
            if ($intendedPath && str_starts_with($intendedPath, $targetPath)) {
                return redirect()->intended($targetPath);
            }
            session()->forget('url.intended');
        }

        return redirect($targetPath);
    }
}
