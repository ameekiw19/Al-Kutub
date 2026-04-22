<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    use HasApiTokens;
    

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'password',
        'role',
        'email',
        'deskripsi',
        'phone',
        'is_verified_by_admin',
        'admin_verified_at',
        'admin_verified_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'admin_verified_at' => 'datetime',
        'is_verified_by_admin' => 'boolean',
    ];

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the two factor authentication record associated with the user.
     */
    public function twoFactorAuth()
    {
        return $this->hasOne(TwoFactorAuth::class);
    }

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get user's notification settings.
     */
    public function notificationSetting()
    {
        return $this->hasOne(UserNotificationSetting::class);
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class, 'user_id', 'id');
    }

    /**
     * Check if user has 2FA enabled
     */
    public function hasTwoFactorEnabled()
    {
        return $this->twoFactorAuth && $this->twoFactorAuth->is_enabled;
    }

    /**
     * Enable 2FA for user
     */
    public function enableTwoFactor($secretKey, $backupCodes = null)
    {
        $twoFactor = $this->twoFactorAuth ?? new TwoFactorAuth();
        $twoFactor->user_id = $this->id;
        $twoFactor->secret_key = $secretKey;
        $twoFactor->backup_codes = $backupCodes ?? TwoFactorAuth::generateBackupCodes();
        $twoFactor->enabled_at = now();
        $twoFactor->is_enabled = true;
        $twoFactor->save();

        // Log the action
        AuditLog::log('2fa_enabled', $this, $this->id, null, [
            'enabled_at' => $twoFactor->enabled_at
        ]);

        return $twoFactor;
    }

    /**
     * Disable 2FA for user
     */
    public function disableTwoFactor()
    {
        if ($this->twoFactorAuth) {
            $this->twoFactorAuth->is_enabled = false;
            $this->twoFactorAuth->secret_key = null;
            $this->twoFactorAuth->backup_codes = null;
            $this->twoFactorAuth->save();

            // Log the action
            AuditLog::log('2fa_disabled', $this, $this->id, null, [
                'disabled_at' => now()
            ]);
        }

        return $this;
    }

    /**
     * Log user activity
     */
    public function logActivity($action, $details = null)
    {
        return AuditLog::log($action, $this, $this->id, null, $details);
    }

    /**
     * Use custom verification notification with public signed route.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmailNotification());
    }

    /**
     * Admin accounts are exempt from email verification gating.
     */
    public function requiresEmailVerification(): bool
    {
        return strtolower((string) $this->role) !== 'admin';
    }

    /**
     * Override default behavior so admin can always pass verification checks.
     */
    public function hasVerifiedEmail()
    {
        if (!$this->requiresEmailVerification()) {
            return true;
        }

        return (bool) $this->is_verified_by_admin;
    }

}
