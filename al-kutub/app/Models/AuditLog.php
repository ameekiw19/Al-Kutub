<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';
    
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime'
    ];

    public $timestamps = false;

    /**
     * Log an action
     */
    public static function log($action, $model = null, $userId = null, $oldValues = null, $newValues = null)
    {
        $log = new self();
        $log->action = $action;
        $log->user_id = $userId ?? auth()->id();
        $log->ip_address = request()->ip();
        $log->user_agent = request()->userAgent();
        $log->created_at = now();

        if ($model) {
            $log->model_type = get_class($model);
            $log->model_id = $model->id ?? null;
        }

        if ($oldValues) {
            $log->old_values = $oldValues;
        }

        if ($newValues) {
            $log->new_values = $newValues;
        }

        $log->save();
        return $log;
    }

    /**
     * Log admin actions
     */
    public static function logAdminAction($action, $model = null, $details = null)
    {
        return self::log($action, $model, auth()->id(), null, $details);
    }

    /**
     * Log authentication events
     */
    public static function logAuth($action, $userId = null, $details = null)
    {
        $log = new self();
        $log->action = $action;
        $log->user_id = $userId;
        $log->ip_address = request()->ip();
        $log->user_agent = request()->userAgent();
        $log->new_values = $details;
        $log->created_at = now();
        $log->save();
        
        return $log;
    }

    /**
     * Get user relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get model relationship (polymorphic)
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Scope for admin actions
     */
    public function scopeAdminActions($query)
    {
        return $query->whereHas('user', function($q) {
            $q->where('role', 'admin');
        });
    }

    /**
     * Scope for specific model
     */
    public function scopeForModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        return $query;
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted action description
     */
    public function getActionDescriptionAttribute()
    {
        $descriptions = [
            'login' => 'User logged in',
            'logout' => 'User logged out',
            'login_failed' => 'Failed login attempt',
            '2fa_enabled' => 'Two-factor authentication enabled',
            '2fa_disabled' => 'Two-factor authentication disabled',
            '2fa_verified' => 'Two-factor authentication verified',
            'password_changed' => 'Password changed',
            'profile_updated' => 'Profile updated',
            'notification_settings_updated' => 'Notification settings updated',
            'kitab_created' => 'Kitab created',
            'kitab_updated' => 'Kitab updated',
            'kitab_deleted' => 'Kitab deleted',
            'user_created' => 'User created',
            'user_updated' => 'User updated',
            'user_deleted' => 'User deleted',
            'role_updated' => 'User role updated',
            'notification_sent' => 'Notification sent',
            'comment_deleted' => 'Comment deleted',
        ];

        return $descriptions[$this->action] ?? $this->action;
    }

    /**
     * Check if action is security related
     */
    public function isSecurityAction()
    {
        $securityActions = [
            'login', 'logout', 'login_failed', '2fa_enabled', 
            '2fa_disabled', '2fa_verified', 'password_changed'
        ];
        
        return in_array($this->action, $securityActions);
    }

    /**
     * Check if action is admin related
     */
    public function isAdminAction()
    {
        $adminActions = [
            'kitab_created', 'kitab_updated', 'kitab_deleted',
            'user_created', 'user_updated', 'user_deleted', 'role_updated',
            'notification_sent', 'comment_deleted'
        ];
        
        return in_array($this->action, $adminActions);
    }
}
