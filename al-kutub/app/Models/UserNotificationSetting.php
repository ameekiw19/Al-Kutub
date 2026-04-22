<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationSetting extends Model
{
    use HasFactory;

    protected $table = 'user_notification_settings';

    protected $fillable = [
        'user_id',
        'enable_notifications',
        'new_book_notifications',
        'update_notifications',
        'reminder_notifications',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'sound_enabled',
        'vibration_enabled',
        'led_enabled',
        'notification_style',
        'categories',
    ];

    protected $casts = [
        'enable_notifications' => 'boolean',
        'new_book_notifications' => 'boolean',
        'update_notifications' => 'boolean',
        'reminder_notifications' => 'boolean',
        'quiet_hours_enabled' => 'boolean',
        'sound_enabled' => 'boolean',
        'vibration_enabled' => 'boolean',
        'led_enabled' => 'boolean',
        'categories' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
