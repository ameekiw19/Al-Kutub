<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FailedLoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Session;

class Login extends Controller
{
    /**
     * Show login form.
     */
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }else{
            return view('Login');
        }
    }

    /**
     * Handle login attempt with rate limiting and security monitoring.
     */
    public function actionlogin(Request $request)
    {
        // Validate input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $username = $request->username;

        // Check rate limiting: Max 5 attempts per minute per IP
        $key = 'login:' . $ipAddress;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            // Log blocked attempt due to rate limiting
            AuditLog::logAuth('login_blocked_rate_limit', null, [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'username' => $username,
                'seconds_remaining' => $seconds,
            ]);

            return redirect()
                ->route('login')
                ->with([
                    'error' => 'Terlalu banyak percobaan login.',
                    'countdown' => $seconds,
                    'countdown_timestamp' => now()->addSeconds($seconds)->timestamp,
                ]);
        }

        // Check if IP is blocked due to suspicious activity
        if (FailedLoginAttempt::isBlocked($ipAddress, 5, 5)) {
            AuditLog::logAuth('login_blocked_ip_suspicious', null, [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'username' => $username,
                'reason' => 'IP blocked due to suspicious activity',
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'Terlalu banyak percobaan login gagal. IP Anda diblokir sementara. Hubungi admin jika ini kesalahan.');
        }

        $data = [
            'username' => $username,
            'password' => $request->password,
        ];

        if (Auth::attempt($data)) {
            $user = Auth::user();

            // Clear rate limiter on successful login
            RateLimiter::clear($key);

            // Check email verification status
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                AuditLog::logAuth('login_blocked_unapproved_account', $user->id, [
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                ]);

                return redirect()
                    ->route('login')
                    ->with('error', 'Akun Anda belum diverifikasi admin. Silakan tunggu persetujuan.');
            }

            // Check if user has 2FA enabled
            if ($user->hasTwoFactorEnabled()) {
                // Log successful authentication but require 2FA
                AuditLog::logAuth('login_authenticated', $user->id, [
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    '2fa_required' => true
                ]);

                // Store user ID in session for 2FA verification
                Session::put('2fa_user_id', $user->id);

                // Logout the user temporarily
                Auth::logout();

                // Redirect to 2FA verification
                return redirect()->route('2fa.verify');
            }

            // Log successful login
            AuditLog::logAuth('login', $user->id, [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                '2fa_required' => false
            ]);

            return redirect()->route('home');
        } else {
            // Log failed login attempt to database
            FailedLoginAttempt::log($ipAddress, $username, $userAgent, 'invalid_credentials');

            // Hit rate limiter
            RateLimiter::hit($key, 60); // 60 seconds decay

            // Log failed login attempt to audit
            AuditLog::logAuth('login_failed', null, [
                'username' => $username,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'reason' => 'invalid_credentials'
            ]);

            // Get remaining attempts
            $remaining = 5 - RateLimiter::attempts($key);
            $errorSuffix = $remaining > 0 ? " ({$remaining} percobaan tersisa)" : '';

            Session::flash('error', 'Username atau Password Salah' . $errorSuffix);
            return redirect()->route('login');
        }
    }

    public function actionlogout()
    {
        $user = Auth::user();
        
        if ($user) {
            // Log logout
            AuditLog::logAuth('logout', $user->id, [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        Auth::logout();
        return redirect()->route('login');
    }
}
