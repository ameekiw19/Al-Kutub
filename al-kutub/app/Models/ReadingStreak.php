<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_read_date',
        'total_days',
        'streak_history',
    ];

    protected $casts = [
        'last_read_date' => 'date',
        'streak_history' => 'array',
    ];

    /**
     * Get user that owns the streak
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update streak when user reads
     */
    public function updateStreak(): void
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $lastRead = $this->last_read_date ? $this->last_read_date->startOfDay() : null;

        if (!$lastRead) {
            // First time reading
            $this->current_streak = 1;
            $this->total_days = 1;
            $this->longest_streak = 1;
        } elseif ($lastRead->eq($today)) {
            // Already read today, don't update streak
            // Just ensure total_days is correct
        } elseif ($lastRead->eq($yesterday)) {
            // Read yesterday, continue streak
            $this->current_streak++;
            $this->total_days++;
            if ($this->current_streak > $this->longest_streak) {
                $this->longest_streak = $this->current_streak;
            }
        } else {
            // Streak broken, start new streak
            $this->current_streak = 1;
            $this->total_days++;
        }

        $this->last_read_date = now();
        
        // Update streak history
        $history = $this->streak_history ?? [];
        $history[$today->format('Y-m-d')] = $this->current_streak;
        $this->streak_history = array_slice($history, -30, true, true); // Keep last 30 days
        
        $this->save();
    }

    /**
     * Check if user has read today
     */
    public function hasReadToday(): bool
    {
        if (!$this->last_read_date) return false;
        return $this->last_read_date->isToday();
    }

    /**
     * Get streak status message
     */
    public function getStatusMessage(): string
    {
        if ($this->current_streak == 0) {
            return 'Mulai baca hari ini untuk memulai streak!';
        } elseif ($this->current_streak == 1) {
            return 'Hari ke-1! Baca lagi besok untuk melanjutkan streak!';
        } elseif ($this->current_streak < 7) {
            return "🔥 {$this->current_streak} hari berturut-turut! Pertahankan!";
        } elseif ($this->current_streak < 30) {
            return "🔥🔥 {$this->current_streak} hari! Luar biasa!";
        } else {
            return "🔥🔥🔥 LEGEND! {$this->current_streak} hari streak!";
        }
    }

    /**
     * Get or create user's streak
     */
    public static function getOrCreate(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_read_date' => null,
                'total_days' => 0,
                'streak_history' => [],
            ]
        );
    }

    /**
     * Get user's streak statistics
     */
    public static function getUserStatistics(int $userId): array
    {
        $streak = static::where('user_id', $userId)->first();
        
        if (!$streak) {
            return [
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_days' => 0,
                'has_read_today' => false,
                'status_message' => 'Mulai baca hari ini!',
            ];
        }

        return [
            'current_streak' => $streak->current_streak,
            'longest_streak' => $streak->longest_streak,
            'total_days' => $streak->total_days,
            'has_read_today' => $streak->hasReadToday(),
            'status_message' => $streak->getStatusMessage(),
        ];
    }

    /**
     * Get leaderboard (top users by streak)
     */
    public static function getLeaderboard(int $limit = 10): array
    {
        return static::with('user')
            ->orderByDesc('current_streak')
            ->limit($limit)
            ->get()
            ->map(function($streak) {
                return [
                    'user_id' => $streak->user_id,
                    'username' => $streak->user->username,
                    'current_streak' => $streak->current_streak,
                    'longest_streak' => $streak->longest_streak,
                ];
            })
            ->toArray();
    }
}
