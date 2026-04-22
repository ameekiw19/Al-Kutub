<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_type',
        'target_minutes',
        'target_pages',
        'current_minutes',
        'current_pages',
        'start_date',
        'end_date',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get user that owns the goal
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get progress percentage for minutes
     */
    public function getMinutesProgressAttribute(): float
    {
        if ($this->target_minutes == 0) return 0;
        return min(100, round(($this->current_minutes / $this->target_minutes) * 100, 2));
    }

    /**
     * Get progress percentage for pages
     */
    public function getPagesProgressAttribute(): float
    {
        if ($this->target_pages == 0) return 0;
        return min(100, round(($this->current_pages / $this->target_pages) * 100, 2));
    }

    /**
     * Get overall progress percentage (average of minutes and pages)
     */
    public function getOverallProgressAttribute(): float
    {
        return round(($this->minutes_progress + $this->pages_progress) / 2, 2);
    }

    /**
     * Check if goal is completed
     */
    public function checkCompletion(): bool
    {
        $isCompleted = $this->current_minutes >= $this->target_minutes 
                    && $this->current_pages >= $this->target_pages;
        
        if ($isCompleted && !$this->is_completed) {
            $this->is_completed = true;
            $this->completed_at = now();
            $this->save();
        }
        
        return $isCompleted;
    }

    /**
     * Add reading progress to goal
     */
    public function addProgress(int $minutes, int $pages): void
    {
        $this->current_minutes += $minutes;
        $this->current_pages += $pages;
        $this->save();
        $this->checkCompletion();
    }

    /**
     * Get or create today's daily goal
     */
    public static function getOrCreateDailyGoal(int $userId, int $defaultMinutes = 30, int $defaultPages = 10): self
    {
        $today = now()->startOfDay();
        
        $goal = static::where('user_id', $userId)
            ->where('goal_type', 'daily')
            ->where('start_date', $today)
            ->first();

        if (!$goal) {
            $goal = static::create([
                'user_id' => $userId,
                'goal_type' => 'daily',
                'target_minutes' => $defaultMinutes,
                'target_pages' => $defaultPages,
                'current_minutes' => 0,
                'current_pages' => 0,
                'start_date' => $today,
                'end_date' => null,
                'is_completed' => false,
            ]);
        }

        return $goal;
    }

    /**
     * Get current weekly goal
     */
    public static function getOrCreateWeeklyGoal(int $userId, int $defaultMinutes = 210, int $defaultPages = 70): self
    {
        $startOfWeek = now()->startOfWeek(); // Monday
        $endOfWeek = now()->endOfWeek(); // Sunday
        
        $goal = static::where('user_id', $userId)
            ->where('goal_type', 'weekly')
            ->where('start_date', $startOfWeek)
            ->first();

        if (!$goal) {
            $goal = static::create([
                'user_id' => $userId,
                'goal_type' => 'weekly',
                'target_minutes' => $defaultMinutes,
                'target_pages' => $defaultPages,
                'current_minutes' => 0,
                'current_pages' => 0,
                'start_date' => $startOfWeek,
                'end_date' => $endOfWeek,
                'is_completed' => false,
            ]);
        }

        return $goal;
    }

    /**
     * Get user's goals statistics
     */
    public static function getUserStatistics(int $userId): array
    {
        $totalGoals = static::where('user_id', $userId)->count();
        $completedGoals = static::where('user_id', $userId)->where('is_completed', true)->count();
        $completionRate = $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100, 2) : 0;

        return [
            'total_goals' => $totalGoals,
            'completed_goals' => $completedGoals,
            'completion_rate' => $completionRate,
        ];
    }
}
