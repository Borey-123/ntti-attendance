<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Models\SecurityLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = 'admin_login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['email' => "Too many login attempts. Please try again in {$seconds} seconds."]);
        }

        // Support login by email or name
        $field = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        if (Auth::attempt([$field => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($key);
            
            SecurityLog::record('Admin Login', Auth::user()->name);
            
            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($key, 60);
        SecurityLog::record('Failed Login Attempt', $credentials['email'], "IP: " . $request->ip());

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        $name = Auth::user()->name ?? 'Unknown';
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        SecurityLog::record('Admin Logout', $name);
        
        return redirect()->route('login');
    }

    // API token login for ESP32 / fetch JS
    public function apiLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = 'api_login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json(['status' => 'error', 'message' => "Too many attempts. Try again in {$seconds} seconds."], 429);
        }

        $user = User::where('name', $request->username)->orWhere('email', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials.'], 401);
        }

        RateLimiter::clear($key);
        $token = $user->createToken('ntti-admin')->plainTextToken;

        return response()->json(['status' => 'success', 'token' => $token, 'user' => $user->name]);
    }
}
