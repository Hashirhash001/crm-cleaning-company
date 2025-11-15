<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Authenticate the user login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validate credentials
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'The password field must be at least 8 characters.'
        ]);

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password']
        ];

        // Find user by email
        $user = User::where('email', $validated['email'])->first();

        // Check if user exists and is active
        if (!$user) {
            // User not found
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials do not match our records.'
                ], 401);
            }

            return back()
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])
                ->onlyInput('email');
        }

        // Check if user account is inactive
        if (!$user->is_active) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact the administrator.'
                ], 403);
            }

            return back()
                ->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the administrator.',
                ])
                ->onlyInput('email');
        }

        // Verify password
        if (!Hash::check($validated['password'], $user->password)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials do not match our records.'
                ], 401);
            }

            return back()
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])
                ->onlyInput('email');
        }

        // Authenticate the user
        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();

        // Check if AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'You have successfully logged in!'
            ]);
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'You have successfully logged in!');
    }

    /**
     * Log out the user from the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'))
            ->with('success', 'You have successfully logged out!');
    }
}
