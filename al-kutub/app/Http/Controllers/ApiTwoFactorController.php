<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TwoFactorAuth;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Validator;

class ApiTwoFactorController extends Controller
{
    /**
     * Get 2FA status for current user
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $twoFactor = $user->twoFactorAuth;
        $backupCodesCount = $twoFactor ? count($twoFactor->getBackupCodesArray()) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $twoFactor ? $twoFactor->is_enabled : false,
                'enabled_at' => $twoFactor ? $twoFactor->enabled_at : null,
                'last_used_at' => $twoFactor ? $twoFactor->last_used_at : null,
                'backup_codes_count' => $backupCodesCount,
            ]
        ]);
    }

    /**
     * Setup 2FA - Generate secret and QR code URL
     */
    public function setup(Request $request)
    {
        $user = $request->user();
        
        // Check if 2FA is already enabled
        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'success' => false,
                'message' => '2FA is already enabled'
            ], 400);
        }

        // Generate new secret and backup codes
        $secretKey = TwoFactorAuth::generateSecretKey();
        $backupCodes = TwoFactorAuth::generateBackupCodes();
        $qrCodeUrl = TwoFactorAuth::generateQrCodeUrl($user->email, $secretKey);

        // Store or update two factor auth record
        $twoFactor = TwoFactorAuth::updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret_key' => $secretKey,
                'backup_codes' => $backupCodes,
                'is_enabled' => false,
            ]
        );

        // Log the setup initiation
        AuditLog::logAuth('2fa_setup_initiated', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'secret_key' => $secretKey,
                'qr_code_url' => $qrCodeUrl,
                'backup_codes' => $backupCodes,
                'manual_entry_key' => $secretKey,
            ]
        ]);
    }

    /**
     * Enable 2FA after verification
     */
    public function enable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $twoFactor = $user->twoFactorAuth;

        if (!$twoFactor || !$twoFactor->secret_key) {
            return response()->json([
                'success' => false,
                'message' => '2FA setup not initiated'
            ], 400);
        }

        // Verify the code
        $code = preg_replace('/\s+/', '', trim((string) $request->input('code')));
        if (!$twoFactor->verifyCode($code)) {
            // Log failed verification
            AuditLog::logAuth('2fa_enable_failed', $user->id, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);
        }

        // Enable 2FA
        $twoFactor->is_enabled = true;
        $twoFactor->enabled_at = now();
        $twoFactor->save();

        // Log successful enable
        AuditLog::logAuth('2fa_enabled', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => '2FA enabled successfully',
            'data' => [
                'enabled_at' => $twoFactor->enabled_at,
            ]
        ]);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'code' => 'required|string|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify password
        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 400);
        }

        $twoFactor = $user->twoFactorAuth;

        if (!$twoFactor || !$twoFactor->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled'
            ], 400);
        }

        // Verify the code
        $code = preg_replace('/\s+/', '', trim((string) $request->input('code')));
        if (!$twoFactor->verifyCode($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);
        }

        // Disable 2FA
        $twoFactor->is_enabled = false;
        $twoFactor->save();

        // Log successful disable
        AuditLog::logAuth('2fa_disabled', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => '2FA disabled successfully'
        ]);
    }

    /**
     * Verify 2FA code (for login)
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Code is required'
            ], 422);
        }

        $user = $request->user();
        $twoFactor = $user->twoFactorAuth;

        if (!$twoFactor || !$twoFactor->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled'
            ], 400);
        }

        $code = strtoupper(preg_replace('/\s+/', '', trim((string) $request->code)));
        $isValid = false;

        // Check if it's a 6-digit TOTP code
        if (strlen($code) === 6 && ctype_digit($code)) {
            $isValid = $twoFactor->verifyCode($code);
        } 
        // Check if it's an 8-digit backup code
        elseif (strlen($code) === 8 && ctype_alnum($code)) {
            $isValid = $twoFactor->useBackupCode($code);
        }

        if (!$isValid) {
            // Log failed verification
            AuditLog::logAuth('2fa_verification_failed', $user->id, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);
        }

        // Update last used timestamp
        $twoFactor->last_used_at = now();
        $twoFactor->save();

        // Log successful verification
        AuditLog::logAuth('2fa_verified', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => '2FA verification successful'
        ]);
    }

    /**
     * Get backup codes
     */
    public function getBackupCodes(Request $request)
    {
        $user = $request->user();
        $twoFactor = $user->twoFactorAuth;

        if (!$twoFactor || !$twoFactor->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled'
            ], 400);
        }

        $backupCodes = $twoFactor->getBackupCodesArray();

        return response()->json([
            'success' => true,
            'message' => 'Backup codes loaded successfully',
            'data' => [
                'backup_codes' => $backupCodes,
                'remaining_count' => count($backupCodes),
            ]
        ]);
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Password is required',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify password
        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 400);
        }

        $twoFactor = $user->twoFactorAuth;

        if (!$twoFactor || !$twoFactor->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled'
            ], 400);
        }

        // Generate new backup codes
        $newBackupCodes = TwoFactorAuth::generateBackupCodes();
        $twoFactor->backup_codes = $newBackupCodes;
        $twoFactor->save();

        // Log backup codes regeneration
        AuditLog::logAuth('backup_codes_regenerated', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Backup codes regenerated successfully',
            'data' => [
                'backup_codes' => $newBackupCodes,
                'remaining_count' => count($newBackupCodes),
            ]
        ]);
    }

    /**
     * Verify backup code specifically
     */
    public function verifyBackupCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:8|alpha_num',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid backup code format',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $twoFactor = $user->twoFactorAuth;

        if (!$twoFactor || !$twoFactor->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled'
            ], 400);
        }

        $code = strtoupper(preg_replace('/\s+/', '', trim((string) $request->input('code'))));
        if (!$twoFactor->useBackupCode($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid backup code'
            ], 400);
        }

        // Update last used timestamp
        $twoFactor->last_used_at = now();
        $twoFactor->save();

        // Log backup code usage
        AuditLog::logAuth('backup_code_used', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Backup code verified successfully'
        ]);
    }
}
