<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FailedLoginAttempt;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ApiAuth extends Controller
{
    private ?bool $refreshTokenTableAvailable = null;
    private const LOGIN_LOCK_PREFIX = 'api-login-lock:';
    private const TWO_FA_LOCK_PREFIX = 'api-2fa-lock:';

    /**
     * Login user (Session based)
     */
    
    public function login(Request $request)
    {
        $usernameInput = (string) $request->input('username', '');
        $loginLimiterKey = $this->loginLimiterKey($request, $usernameInput);
        if ($limitResponse = $this->ensureRateLimitNotExceeded(
            $loginLimiterKey,
            (int) env('AUTH_LOGIN_MAX_ATTEMPTS', 5),
            (int) env('AUTH_LOGIN_LOCK_MINUTES', 5),
            'Terlalu banyak percobaan login. Coba lagi dalam :seconds detik.'
        )) {
            return $limitResponse;
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_id' => 'nullable|string|max:120',
            'device_name' => 'nullable|string|max:120',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($loginLimiterKey, (int) env('AUTH_LOGIN_LOCK_MINUTES', 5) * 60);

            // Log failed login attempt to database for security monitoring
            FailedLoginAttempt::log(
                $request->ip(),
                $request->input('username'),
                $request->userAgent(),
                'invalid_credentials'
            );

            // Log failed login attempt
            if ($user) {
                AuditLog::logAuth('login_failed', $user->id, [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        RateLimiter::clear($loginLimiterKey);

        if (!$user->hasVerifiedEmail()) {
            AuditLog::logAuth('login_blocked_unapproved_account', $user->id, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akun belum diverifikasi admin.',
                'data' => $this->buildAuthPayload($user, [
                    'requires_2fa' => false,
                    'requires_email_verification' => false,
                    'verification_token' => null,
                    'requires_admin_approval' => true,
                ]),
            ], 200);
        }

        // Check if user has 2FA enabled
        if ($user->hasTwoFactorEnabled()) {
            // Log successful password authentication (but not full login yet)
            AuditLog::logAuth('login_password_verified', $user->id, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password verified. 2FA verification required.',
                'data' => [
                    'id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email,
                    'requires_2fa' => true,
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'temp_token' => $this->generateTempToken($user),
                    'requires_email_verification' => false,
                    'verification_token' => null,
                ]
            ], 200);
        }

        // Log successful login
        AuditLog::logAuth('login', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $sessionContext = $this->resolveSessionContext($request);
        $tokenResult = $user->createToken($sessionContext['token_name']);
        $token = $tokenResult->plainTextToken;
        $refreshIssued = $this->issueRefreshToken(
            $user,
            $sessionContext,
            $tokenResult->accessToken->id ?? null
        );
        $refreshToken = $refreshIssued['plain_text'] ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => $this->buildAuthPayload($user, [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'session_id' => $refreshIssued['record']->id ?? null,
                'requires_2fa' => false,
                'requires_email_verification' => false,
                'verification_token' => null,
            ]),
        ], 200);
    }

    /**
     * Verify 2FA code during login
     */
    public function verify2FA(Request $request)
    {
        $twoFaLimiterKey = $this->twoFaLimiterKey($request, (string) $request->input('user_id', 'unknown'));
        if ($limitResponse = $this->ensureRateLimitNotExceeded(
            $twoFaLimiterKey,
            (int) env('AUTH_2FA_MAX_ATTEMPTS', 5),
            (int) env('AUTH_2FA_LOCK_MINUTES', 5),
            'Terlalu banyak percobaan kode 2FA. Coba lagi dalam :seconds detik.'
        )) {
            return $limitResponse;
        }

        $request->validate([
            'user_id' => 'required|integer',
            'code' => 'required|string',
            'temp_token' => 'required|string',
            'device_id' => 'nullable|string|max:120',
            'device_name' => 'nullable|string|max:120',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum diverifikasi admin.'
            ], 403);
        }

        // Verify temp token
        if (!$this->verifyTempToken($user, $request->temp_token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired session'
            ], 401);
        }

        $twoFactor = $user->twoFactorAuth;

        if (!$twoFactor || !$twoFactor->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled for this user'
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
            RateLimiter::hit($twoFaLimiterKey, (int) env('AUTH_2FA_LOCK_MINUTES', 5) * 60);

            // Log failed 2FA attempt to database for security monitoring
            FailedLoginAttempt::log(
                $request->ip(),
                $user->username,
                $request->userAgent(),
                'invalid_2fa_code'
            );

            // Log failed 2FA verification
            AuditLog::logAuth('2fa_verification_failed', $user->id, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);
        }

        RateLimiter::clear($twoFaLimiterKey);

        // Update last used timestamp
        $twoFactor->last_used_at = now();
        $twoFactor->save();

        // Log successful login with 2FA
        AuditLog::logAuth('login', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            '2fa_verified' => true
        ]);

        $sessionContext = $this->resolveSessionContext($request);
        $tokenResult = $user->createToken($sessionContext['token_name']);
        $token = $tokenResult->plainTextToken;
        $refreshIssued = $this->issueRefreshToken(
            $user,
            $sessionContext,
            $tokenResult->accessToken->id ?? null
        );
        $refreshToken = $refreshIssued['plain_text'] ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil dengan 2FA',
            'data' => $this->buildAuthPayload($user, [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'session_id' => $refreshIssued['record']->id ?? null,
                'requires_2fa' => false,
                'requires_email_verification' => false,
                'verification_token' => null,
            ]),
        ], 200);
    }

    /**
     * Generate temporary token for 2FA verification
     */
    private function generateTempToken($user)
    {
        $timestamp = now()->timestamp;
        $hash = hash('sha256', $user->id . $timestamp . config('app.key'));
        return base64_encode($user->id . ':' . $timestamp . ':' . $hash);
    }

    /**
     * Verify temporary token
     */
    private function verifyTempToken($user, $tempToken)
    {
        try {
            $decoded = base64_decode($tempToken);
            $parts = explode(':', $decoded);
            
            if (count($parts) !== 3) {
                return false;
            }

            [$userId, $timestamp, $hash] = $parts;

            // Verify user ID
            if ($userId != $user->id) {
                return false;
            }

            // Verify timestamp (token expires after 5 minutes)
            if (now()->timestamp - $timestamp > 300) {
                return false;
            }

            // Verify hash
            $expectedHash = hash('sha256', $user->id . $timestamp . config('app.key'));
            return hash_equals($expectedHash, $hash);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Register new user (Session based)
     */
   public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->input('phone'),
            'deskripsi' => $request->input('deskripsi'),
            'role' => 'user',
            'email_verified_at' => null,
            'is_verified_by_admin' => false,
            'admin_verified_at' => null,
            'admin_verified_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Akun menunggu verifikasi admin.',
            'data' => $this->buildAuthPayload($user, [
                'token' => null,
                'requires_2fa' => false,
                'requires_email_verification' => false,
                'verification_token' => null,
                'requires_admin_approval' => true,
            ]),
        ], 201);
    }


    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $currentToken = $user->currentAccessToken();
            $currentAccessTokenId = $currentToken?->id;
            $currentToken?->delete();

            if ($this->canUseRefreshTokens()) {
                $refreshTokenInput = trim((string) $request->input('refresh_token', ''));

                if ($refreshTokenInput !== '') {
                    RefreshToken::where('user_id', $user->id)
                        ->where('token_hash', hash('sha256', $refreshTokenInput))
                        ->whereNull('revoked_at')
                        ->update([
                            'revoked_at' => now(),
                            'revoked_reason' => 'logout',
                        ]);
                } elseif ($currentAccessTokenId !== null) {
                    RefreshToken::where('user_id', $user->id)
                        ->where('access_token_id', $currentAccessTokenId)
                        ->whereNull('revoked_at')
                        ->update([
                            'revoked_at' => now(),
                            'revoked_reason' => 'logout',
                        ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    public function logoutAllDevices(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 401);
        }

        $user->tokens()->delete();

        if ($this->canUseRefreshTokens()) {
            RefreshToken::where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->update([
                    'revoked_at' => now(),
                    'revoked_reason' => 'logout_all_devices',
                ]);
        }

        AuditLog::logAuth('logout_all_devices', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Semua sesi perangkat berhasil dicabut',
        ], 200);
    }

    public function listSessions(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 401);
        }

        if (!$this->canUseRefreshTokens()) {
            return response()->json([
                'success' => true,
                'message' => 'Refresh token belum aktif',
                'data' => [],
            ], 200);
        }

        $currentAccessTokenId = $user->currentAccessToken()?->id;

        $sessions = RefreshToken::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (RefreshToken $token) => $this->mapSessionPayload($token, $currentAccessTokenId))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Daftar sesi berhasil diambil',
            'data' => $sessions,
        ], 200);
    }

    public function currentSession(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 401);
        }

        if (!$this->canUseRefreshTokens()) {
            return response()->json([
                'success' => true,
                'message' => 'Refresh token belum aktif',
                'data' => null,
            ], 200);
        }

        $currentAccessTokenId = $user->currentAccessToken()?->id;
        $currentDeviceId = trim((string) $request->header('X-Device-Id', ''));

        $session = null;
        if ($currentAccessTokenId !== null) {
            $session = RefreshToken::where('user_id', $user->id)
                ->where('access_token_id', $currentAccessTokenId)
                ->orderByDesc('id')
                ->first();
        }

        if (!$session && $currentDeviceId !== '') {
            $session = RefreshToken::where('user_id', $user->id)
                ->where('device_id', $currentDeviceId)
                ->orderByDesc('id')
                ->first();
        }

        return response()->json([
            'success' => true,
            'message' => $session ? 'Sesi aktif berhasil diambil' : 'Sesi aktif tidak ditemukan',
            'data' => $session ? $this->mapSessionPayload($session, $currentAccessTokenId, true) : null,
        ], 200);
    }

    public function revokeSession(Request $request, int $sessionId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 401);
        }

        if (!$this->canUseRefreshTokens()) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token belum aktif di server',
            ], 503);
        }

        $currentAccessTokenId = $user->currentAccessToken()?->id;
        $session = RefreshToken::where('user_id', $user->id)->where('id', $sessionId)->first();
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak ditemukan',
            ], 404);
        }

        if ($currentAccessTokenId !== null && $session->access_token_id === $currentAccessTokenId) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi aktif saat ini tidak dapat dicabut dari daftar. Gunakan logout perangkat ini.',
            ], 409);
        }

        if ($session->revoked_at === null) {
            $session->revoked_at = now();
            $session->revoked_reason = 'user_revoked';
            $session->save();
        }

        if ($session->access_token_id !== null) {
            $user->tokens()->where('id', $session->access_token_id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi perangkat berhasil dicabut',
        ], 200);
    }

    /**
     * Revoke current session berdasarkan refresh token aktif milik user.
     */
    public function revokeCurrentSessionByRefreshToken(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 401);
        }

        if (!$this->canUseRefreshTokens()) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token belum aktif di server',
            ], 503);
        }

        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $tokenHash = hash('sha256', (string) $request->input('refresh_token'));
        $session = RefreshToken::where('user_id', $user->id)
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token tidak ditemukan untuk user ini',
            ], 404);
        }

        $now = now();
        if ($session->revoked_at === null) {
            $session->revoked_at = $now;
            $session->revoked_reason = 'logout_current_session';
        }
        $session->last_seen_at = $now;
        $session->last_used_at = $now;
        $session->save();

        if ($session->access_token_id !== null) {
            $user->tokens()->where('id', $session->access_token_id)->delete();
        } else {
            $user->currentAccessToken()?->delete();
        }

        AuditLog::logAuth('logout_current_session_refresh_token', $user->id, [
            'session_id' => $session->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi saat ini berhasil dicabut',
        ], 200);
    }

    /**
     * Refresh access token menggunakan refresh token.
     */
    public function refreshToken(Request $request)
    {
        if (!$this->canUseRefreshTokens()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi perlu login ulang. Refresh token belum aktif di server.'
            ], 503);
        }

        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        $tokenHash = hash('sha256', (string) $request->refresh_token);
        $refreshToken = RefreshToken::with('user')
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$refreshToken) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token tidak valid'
            ], 401);
        }

        if ($refreshToken->revoked_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token sudah tidak aktif'
            ], 401);
        }

        if ($refreshToken->expires_at->isPast()) {
            $refreshToken->update([
                'revoked_at' => now(),
                'revoked_reason' => 'expired',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Refresh token kadaluarsa'
            ], 401);
        }

        $user = $refreshToken->user;
        if (!$user) {
            $refreshToken->update([
                'revoked_at' => now(),
                'revoked_reason' => 'user_not_found',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum diverifikasi admin.'
            ], 403);
        }

        $sessionContext = [
            'device_id' => $refreshToken->device_id,
            'device_name' => $refreshToken->device_name,
            'user_agent' => $refreshToken->user_agent,
            'ip_address' => $request->ip() ?: $refreshToken->ip_address,
            'token_name' => $refreshToken->device_name ?: 'android',
        ];

        $tokenResult = $user->createToken($sessionContext['token_name']);
        $token = $tokenResult->plainTextToken;
        $newRefreshIssued = $this->issueRefreshToken(
            $user,
            $sessionContext,
            $tokenResult->accessToken->id ?? null,
            $refreshToken
        );
        $newRefreshToken = $newRefreshIssued['plain_text'] ?? null;

        $refreshToken->update([
            'revoked_at' => now(),
            'revoked_reason' => 'rotated',
            'last_used_at' => now(),
            'last_seen_at' => now(),
            'replaced_by_token_id' => $newRefreshIssued['record']->id ?? null,
        ]);

        if ($refreshToken->access_token_id !== null) {
            $user->tokens()->where('id', $refreshToken->access_token_id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diperbarui',
            'data' => $this->buildAuthPayload($user, [
                'token' => $token,
                'refresh_token' => $newRefreshToken,
                'session_id' => $newRefreshIssued['record']->id ?? null,
                'requires_2fa' => false,
                'requires_email_verification' => false,
                'verification_token' => null,
            ]),
        ], 200);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    private function buildAuthPayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'email' => $user->email,
            'token' => null,
            'refresh_token' => null,
            'requires_2fa' => false,
            'temp_token' => null,
            'user_id' => $user->id,
            'requires_email_verification' => false,
            'verification_token' => null,
            'requires_admin_approval' => false,
            'session_id' => null,
        ], $overrides);
    }

    private function issueRefreshToken(
        User $user,
        array $sessionContext = [],
        ?int $accessTokenId = null,
        ?RefreshToken $replaces = null
    ): ?array
    {
        if (!$this->canUseRefreshTokens()) {
            return null;
        }

        $plainTextToken = Str::random(96);

        $created = RefreshToken::create([
            'user_id' => $user->id,
            'device_id' => $sessionContext['device_id'] ?? null,
            'device_name' => $sessionContext['device_name'] ?? null,
            'user_agent' => $sessionContext['user_agent'] ?? null,
            'ip_address' => $sessionContext['ip_address'] ?? null,
            'token_hash' => hash('sha256', $plainTextToken),
            'access_token_id' => $accessTokenId,
            'expires_at' => now()->addDays(30),
            'last_used_at' => now(),
            'last_seen_at' => now(),
            'revoked_at' => null,
            'revoked_reason' => null,
            'replaced_by_token_id' => null,
        ]);

        if ($replaces !== null) {
            $replaces->replaced_by_token_id = $created->id;
            $replaces->save();
        }

        return [
            'plain_text' => $plainTextToken,
            'record' => $created,
        ];
    }

    private function resolveSessionContext(Request $request): array
    {
        $deviceName = trim((string) ($request->input('device_name') ?: $request->header('X-Device-Name')));
        if ($deviceName === '') {
            $deviceName = 'android';
        }

        $deviceId = trim((string) ($request->input('device_id') ?: $request->header('X-Device-Id')));
        if ($deviceId === '') {
            $deviceId = substr(hash('sha256', ($request->ip() ?? 'unknown') . '|' . ($request->userAgent() ?? 'unknown')), 0, 40);
        }

        return [
            'device_id' => $deviceId,
            'device_name' => $deviceName,
            'user_agent' => Str::limit((string) ($request->userAgent() ?? ''), 255, ''),
            'ip_address' => $request->ip(),
            'token_name' => $deviceName,
        ];
    }

    private function loginLimiterKey(Request $request, string $username): string
    {
        return self::LOGIN_LOCK_PREFIX . strtolower(trim($username)) . '|' . ($request->ip() ?? 'unknown');
    }

    private function twoFaLimiterKey(Request $request, string $userId): string
    {
        return self::TWO_FA_LOCK_PREFIX . trim($userId) . '|' . ($request->ip() ?? 'unknown');
    }

    private function ensureRateLimitNotExceeded(
        string $key,
        int $maxAttempts,
        int $decayMinutes,
        string $messageTemplate
    ) {
        if (!RateLimiter::tooManyAttempts($key, max(1, $maxAttempts))) {
            return null;
        }

        $seconds = RateLimiter::availableIn($key);

        return response()->json([
            'success' => false,
            'message' => str_replace(':seconds', (string) max(1, $seconds), $messageTemplate),
            'retry_after_seconds' => max(1, $seconds),
            'lock_window_seconds' => max(1, $decayMinutes) * 60,
        ], 429);
    }

    private function canUseRefreshTokens(): bool
    {
        if ($this->refreshTokenTableAvailable !== null) {
            return $this->refreshTokenTableAvailable;
        }

        try {
            $this->refreshTokenTableAvailable = Schema::hasTable('refresh_tokens');
        } catch (\Throwable $e) {
            $this->refreshTokenTableAvailable = false;
        }

        return $this->refreshTokenTableAvailable;
    }

    private function mapSessionPayload(
        RefreshToken $token,
        ?int $currentAccessTokenId,
        bool $forceCurrent = false
    ): array {
        return [
            'id' => $token->id,
            'device_id' => $token->device_id,
            'device_name' => $token->device_name ?: 'Perangkat tidak diketahui',
            'ip_address' => $token->ip_address,
            'last_seen_at' => optional($token->last_seen_at)->toISOString(),
            'created_at' => optional($token->created_at)->toISOString(),
            'expires_at' => optional($token->expires_at)->toISOString(),
            'revoked_at' => optional($token->revoked_at)->toISOString(),
            'is_active' => $token->revoked_at === null && $token->expires_at !== null && $token->expires_at->isFuture(),
            'is_current' => $forceCurrent || ($currentAccessTokenId !== null && $token->access_token_id === $currentAccessTokenId),
        ];
    }

}
