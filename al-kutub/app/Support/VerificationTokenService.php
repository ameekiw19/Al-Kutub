<?php

namespace App\Support;

use App\Models\User;

class VerificationTokenService
{
    private const DEFAULT_TTL_MINUTES = 60;

    /**
     * Generate a temporary challenge token for verification status/resend flow.
     */
    public static function issue(User $user): string
    {
        $timestamp = now()->timestamp;
        $signature = self::sign($user->id, $user->email, $timestamp);

        return base64_encode($user->id . ':' . $timestamp . ':' . $signature);
    }

    /**
     * Resolve token to an actual user if token is still valid.
     */
    public static function resolve(?string $token): ?User
    {
        if (!$token) {
            return null;
        }

        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return null;
        }

        $parts = explode(':', $decoded);
        if (count($parts) !== 3) {
            return null;
        }

        [$userId, $timestamp, $signature] = $parts;

        if (!ctype_digit((string) $userId) || !ctype_digit((string) $timestamp)) {
            return null;
        }

        $issuedAt = (int) $timestamp;
        $expiresAt = $issuedAt + (self::ttlMinutes() * 60);
        if (now()->timestamp > $expiresAt) {
            return null;
        }

        $user = User::find((int) $userId);
        if (!$user) {
            return null;
        }

        $expected = self::sign($user->id, $user->email, $issuedAt);
        if (!hash_equals($expected, (string) $signature)) {
            return null;
        }

        return $user;
    }

    private static function ttlMinutes(): int
    {
        $configured = (int) config('auth.verification.expire', self::DEFAULT_TTL_MINUTES);
        return $configured > 0 ? $configured : self::DEFAULT_TTL_MINUTES;
    }

    private static function sign(int $userId, string $email, int $timestamp): string
    {
        $payload = $userId . '|' . strtolower(trim($email)) . '|' . $timestamp;
        $key = (string) config('app.key');

        return hash_hmac('sha256', $payload, $key);
    }
}
