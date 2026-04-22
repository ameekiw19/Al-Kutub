<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TwoFactorAuth;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TwoFactorController extends Controller
{
    /**
     * Show 2FA setup page
     */
    public function showSetup()
    {
        $user = Auth::user();
        
        // Check if already enabled
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('2fa.manage')->with('info', '2FA is already enabled');
        }

        // Generate secret key
        $secretKey = TwoFactorAuth::generateSecretKey();
        $backupCodes = TwoFactorAuth::generateBackupCodes();
        
        // Store in session temporarily
        Session::put('2fa_setup_secret', $secretKey);
        Session::put('2fa_setup_backup_codes', $backupCodes);

        // Get QR code URL
        $qrCodeUrl = 'otpauth://totp/' . urlencode('Al-Kutub:' . $user->email) . '?secret=' . $secretKey . '&issuer=Al-Kutub';

        return view('2fa.setup', compact('secretKey', 'backupCodes', 'qrCodeUrl'));
    }

    /**
     * Enable 2FA
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ]);

        $user = Auth::user();
        $secretKey = Session::get('2fa_setup_secret');
        $backupCodes = Session::get('2fa_setup_backup_codes');

        if (!$secretKey) {
            return redirect()->route('2fa.setup')->with('error', 'Setup session expired. Please try again.');
        }

        // Create temporary 2FA object to verify code
        $tempTwoFactor = new TwoFactorAuth();
        $tempTwoFactor->secret_key = $secretKey;

        if (!$tempTwoFactor->verifyOTP($request->code)) {
            return redirect()->route('2fa.setup')->with('error', 'Invalid verification code');
        }

        // Enable 2FA for user
        $user->enableTwoFactor($secretKey, $backupCodes);

        // Clear session
        Session::forget(['2fa_setup_secret', '2fa_setup_backup_codes']);

        return redirect()->route('2fa.manage')->with('success', 'Two-factor authentication has been enabled successfully!');
    }

    /**
     * Show 2FA management page
     */
    public function showManage()
    {
        $user = Auth::user();
        $twoFactor = $user->twoFactorAuth;

        if (!$twoFactor || !$twoFactor->is_enabled) {
            return redirect()->route('2fa.setup')->with('info', 'Please set up two-factor authentication first');
        }

        return view('2fa.manage', compact('twoFactor'));
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'code' => 'required|digits:6'
        ]);

        $user = Auth::user();

        // Verify password
        if (!\Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Invalid password');
        }

        // Verify 2FA code
        if (!$user->twoFactorAuth || !$user->twoFactorAuth->verifyOTP($request->code)) {
            return redirect()->back()->with('error', 'Invalid verification code');
        }

        // Disable 2FA
        $user->disableTwoFactor();

        return redirect()->route('2fa.setup')->with('success', 'Two-factor authentication has been disabled');
    }

    /**
     * Show 2FA verification page
     */
    public function showVerification()
    {
        if (!Session::has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('2fa.verify');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ]);

        if (!Session::has('2fa_user_id')) {
            return redirect()->route('login');
        }

        $userId = Session::get('2fa_user_id');
        $user = \App\Models\User::find($userId);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        // Check if using backup code
        if (strlen($request->code) === 8) {
            if (!$user->twoFactorAuth->verifyBackupCode($request->code)) {
                return redirect()->back()->with('error', 'Invalid backup code');
            }
        } else {
            // Verify OTP
            if (!$user->twoFactorAuth->verifyOTP($request->code)) {
                return redirect()->back()->with('error', 'Invalid verification code');
            }
        }

        // Log successful 2FA verification
        AuditLog::log('2fa_verified', $user, $user->id, null, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Complete login
        Auth::login($user);
        Session::forget('2fa_user_id');

        // Log login
        AuditLog::logAuth('login', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            '2fa_verified' => true
        ]);

        return redirect()->intended('home');
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(Request $request)
    {
        $request->validate([
            'password' => 'required'
        ]);

        $user = Auth::user();

        // Verify password
        if (!\Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Invalid password');
        }

        // Generate new backup codes
        $newBackupCodes = TwoFactorAuth::generateBackupCodes();
        $user->twoFactorAuth->backup_codes = $newBackupCodes;
        $user->twoFactorAuth->save();

        // Log the action
        AuditLog::log('backup_codes_regenerated', $user, $user->id, null, [
            'count' => count($newBackupCodes)
        ]);

        return redirect()->back()->with('success', 'Backup codes have been regenerated. Save them in a secure place!');
    }
}
