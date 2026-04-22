<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    use HasFactory;

    protected $table = 'app_notifications';

    protected $fillable = [
        'title',
        'message',
        'type',      // 'info', 'new_kitab', 'promo', etc.
        'action_url', // URL optional (misal link ke detail kitab)
        'data',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function userReads()
    {
        return $this->hasMany(NotificationUserRead::class, 'notification_id');
    }
}
