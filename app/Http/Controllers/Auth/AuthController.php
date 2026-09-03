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
        $user = User::where($field, $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            RateLimiter::clear($key);

            // Check if 2FA is enabled for this admin account
            if ($user->two_factor_enabled) {
                $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $user->two_factor_code = $code;
                $user->two_factor_expires_at = now()->addMinutes(10);
                $user->save();

                session([
                    '2fa_pending_user_id' => $user->id,
                    '2fa_remember' => $request->boolean('remember'),
                ]);

                SecurityLog::record('Admin Login 2FA Required', $user->name);

                return redirect()->route('login.2fa');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            
            SecurityLog::record('Admin Login', Auth::user()->name);
            
            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($key, 60);
        SecurityLog::record('Failed Login Attempt', $credentials['email'], "IP: " . $request->ip());

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput($request->except('password'));
    }

    public function show2FaForm(Request $request)
    {
        $pendingUserId = session('2fa_pending_user_id');
        if (!$pendingUserId) {
            return redirect()->route('login');
        }

        $user = User::find($pendingUserId);
        if (!$user) {
            session()->forget(['2fa_pending_user_id', '2fa_remember']);
            return redirect()->route('login');
        }

        return view('auth.2fa', compact('user'));
    }

    public function verify2Fa(Request $request)
    {
        $pendingUserId = session('2fa_pending_user_id');
        if (!$pendingUserId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string',
        ]);

        $user = User::find($pendingUserId);
        if (!$user) {
            session()->forget(['2fa_pending_user_id', '2fa_remember']);
            return redirect()->route('login');
        }

        if ($user->two_factor_code !== $request->code || now()->greaterThan($user->two_factor_expires_at)) {
            return back()->withErrors(['code' => __('Invalid or expired OTP code.')]);
        }

        // OTP verified successfully
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        $remember = session('2fa_remember', false);
        session()->forget(['2fa_pending_user_id', '2fa_remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();
        session(['2fa_verified' => true]);

        SecurityLog::record('Admin Login 2FA Verified', $user->name);

        return redirect()->intended(route('dashboard'));
    }

    public function cancel2Fa(Request $request)
    {
        session()->forget(['2fa_pending_user_id', '2fa_remember']);
        return redirect()->route('login');
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
