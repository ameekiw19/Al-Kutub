<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FailedLoginAttempt extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'failed_login_attempts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ip_address',
        'username',
        'user_agent',
        'reason',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get failed login attempts by IP address within time window.
     */
    public static function countByIp(string $ipAddress, int $minutes = 5): int
    {
        try {
            return static::where('ip_address', $ipAddress)
                ->where('created_at', '>=', now()->subMinutes($minutes))
                ->count();
        } catch (\Exception $e) {
            // Table doesn't exist yet
            return 0;
        }
    }

    /**
     * Check if IP should be blocked based on failed attempts.
     */
    public static function isBlocked(string $ipAddress, int $threshold = 5, int $minutes = 5): bool
    {
        try {
            return self::countByIp($ipAddress, $minutes) >= $threshold;
        } catch (\Exception $e) {
            // Table doesn't exist yet
            return false;
        }
    }

    /**
     * Log a failed login attempt.
     */
    public static function log(string $ipAddress, ?string $username, ?string $userAgent, string $reason = 'invalid_credentials'): ?self
    {
        try {
            return static::create([
                'ip_address' => $ipAddress,
                'username' => $username,
                'user_agent' => $userAgent,
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            // Table doesn't exist yet or database not available
            // Log to audit instead as fallback
            try {
                AuditLog::logAuth('login_failed_no_table', null, [
                    'ip_address' => $ipAddress,
                    'username' => $username,
                    'user_agent' => $userAgent,
                    'reason' => $reason,
                    'note' => 'Failed to log to failed_login_attempts - table not found',
                ]);
            } catch (\Exception $e2) {
                // Ignore if audit also fails
            }
            return null;
        }
    }

    /**
     * Clean up old failed login attempts.
     */
    public static function cleanupOld(int $olderThan = 60): int
    {
        try {
            return static::where('created_at', '<', now()->subMinutes($olderThan))->delete();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get attempts count grouped by IP.
     */
    public static function getAttemptsByIp(int $minutes = 60): array
    {
        try {
            return static::where('created_at', '>=', now()->subMinutes($minutes))
                ->select('ip_address', DB::raw('count(*) as attempts'))
                ->groupBy('ip_address')
                ->orderByDesc('attempts')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
