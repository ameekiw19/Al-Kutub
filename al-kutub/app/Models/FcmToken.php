<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{
    use HasFactory;

    protected $table = 'fcm_tokens';

    protected $fillable = [
        'user_id',
        'device_token',
        'device_type', // 'android', 'ios'
        'app_version',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get active tokens for all users
     */
    public static function getActiveTokens()
    {
        return self::where('is_active', true)->pluck('device_token')->toArray();
    }

    /**
     * Get active tokens for specific user
     */
    public static function getUserTokens($userId)
    {
        return self::where('user_id', $userId)
                   ->where('is_active', true)
                   ->pluck('device_token')
                   ->toArray();
    }

    /**
     * Deactivate all tokens for a user
     */
    public static function deactivateUserTokens($userId)
    {
        return self::where('user_id', $userId)->update(['is_active' => false]);
    }

    /**
     * Deactivate specific token
     */
    public static function deactivateToken($deviceToken)
    {
        return self::where('device_token', $deviceToken)->update(['is_active' => false]);
    }
}
