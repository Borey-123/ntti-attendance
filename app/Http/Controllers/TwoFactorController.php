<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    public function toggle(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => __('Unauthenticated.')], 401);
        }

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => __('Incorrect current password.')], 422);
            }
        }

        if ($request->has('enabled')) {
            $user->two_factor_enabled = filter_var($request->enabled, FILTER_VALIDATE_BOOLEAN);
        } else {
            $user->two_factor_enabled = !$user->two_factor_enabled;
        }

        if (!$user->two_factor_enabled) {
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
        }
        $user->save();

        AuditLog::log(
            '2fa_toggle',
            $user->two_factor_enabled ? 'Enabled Two-Factor Authentication' : 'Disabled Two-Factor Authentication'
        );

        return response()->json([
            'success' => true,
            'two_factor_enabled' => $user->two_factor_enabled,
            'message' => $user->two_factor_enabled 
                ? __('Two-Factor Authentication has been enabled.') 
                : __('Two-Factor Authentication has been disabled.'),
        ]);
    }

    public function generateOtp(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->two_factor_enabled) {
            return response()->json(['success' => false, 'message' => __('2FA is not enabled for this user.')], 400);
        }

        // Generate 6-digit OTP code
        $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        return response()->json([
            'success' => true,
            'code' => $code, // For local demonstration / screen display
            'expires_at' => $user->two_factor_expires_at->toIso8601String(),
            'message' => __('Verification OTP generated successfully.'),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        if (!$user || !$user->two_factor_enabled) {
            return response()->json(['success' => true]); // Bypass if not enabled
        }

        if ($user->two_factor_code !== $request->code || now()->greaterThan($user->two_factor_expires_at)) {
            return response()->json(['success' => false, 'message' => __('Invalid or expired OTP code.')], 422);
        }

        // Clear code on successful verification
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        session(['2fa_verified' => true]);

        AuditLog::log('2fa_verified', 'Successfully verified 2FA security OTP.');

        return response()->json(['success' => true, 'message' => __('OTP verification successful.')]);
    }
}
