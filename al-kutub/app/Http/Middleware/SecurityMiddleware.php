<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Middleware ini untuk security monitoring:
     * - Block IP yang memiliki terlalu banyak failed login attempts
     * - Log suspicious activity
     * - Rate limiting protection
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();

        // Check if IP should be blocked due to suspicious activity
        // Note: Fitur ini hanya aktif jika tabel failed_login_attempts ada
        try {
            if (\App\Models\FailedLoginAttempt::isBlocked($ipAddress, 5, 5)) {
                // Log blocked request
                \App\Models\AuditLog::logAuth('request_blocked_ip_suspicious', null, [
                    'ip_address' => $ipAddress,
                    'user_agent' => $request->userAgent(),
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);

                // Return 429 Too Many Requests
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan login gagal. IP Anda diblokir sementara. Hubungi admin jika ini kesalahan.',
                    'blocked' => true,
                ], 429);
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet or database not available
            // Skip IP blocking - fitur akan aktif setelah tabel dibuat
        }

        // Check for extremely high request rate (potential DDoS/brute force)
        // More than 100 requests per minute from same IP
        $requestRateKey = 'request_rate:' . $ipAddress;
        $requestRate = cache()->remember($requestRateKey, 60, function () {
            return 1;
        });

        if ($requestRate > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak request. Silakan coba lagi nanti.',
            ], 429);
        }

        // Increment request counter
        cache()->increment($requestRateKey);

        return $next($request);
    }

    /**
     * Get the number of failed login attempts for an IP.
     */
    public static function getFailedAttempts(string $ipAddress, int $minutes = 5): int
    {
        try {
            return \App\Models\FailedLoginAttempt::countByIp($ipAddress, $minutes);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Clear failed login attempts for an IP.
     */
    public static function clearFailedAttempts(string $ipAddress): int
    {
        try {
            return \App\Models\FailedLoginAttempt::where('ip_address', $ipAddress)->delete();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
