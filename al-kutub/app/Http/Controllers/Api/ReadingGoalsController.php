<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingGoal;
use App\Models\ReadingStreak;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingGoalsController extends Controller
{
    /**
     * Get user's current reading goals
     */
    public function getGoals(Request $request)
    {
        $user = Auth::user();
        
        // Get daily goal
        $dailyGoal = ReadingGoal::getOrCreateDailyGoal($user->id);
        
        // Get weekly goal
        $weeklyGoal = ReadingGoal::getOrCreateWeeklyGoal($user->id);
        
        // Get goals statistics
        $statistics = ReadingGoal::getUserStatistics($user->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Reading goals retrieved successfully',
            'data' => [
                'daily_goal' => [
                    'id' => $dailyGoal->id,
                    'type' => 'daily',
                    'target_minutes' => $dailyGoal->target_minutes,
                    'target_pages' => $dailyGoal->target_pages,
                    'current_minutes' => $dailyGoal->current_minutes,
                    'current_pages' => $dailyGoal->current_pages,
                    'minutes_progress' => $dailyGoal->minutes_progress,
                    'pages_progress' => $dailyGoal->pages_progress,
                    'overall_progress' => $dailyGoal->overall_progress,
                    'is_completed' => $dailyGoal->is_completed,
                    'start_date' => $dailyGoal->start_date->format('Y-m-d'),
                ],
                'weekly_goal' => [
                    'id' => $weeklyGoal->id,
                    'type' => 'weekly',
                    'target_minutes' => $weeklyGoal->target_minutes,
                    'target_pages' => $weeklyGoal->target_pages,
                    'current_minutes' => $weeklyGoal->current_minutes,
                    'current_pages' => $weeklyGoal->current_pages,
                    'minutes_progress' => $weeklyGoal->minutes_progress,
                    'pages_progress' => $weeklyGoal->pages_progress,
                    'overall_progress' => $weeklyGoal->overall_progress,
                    'is_completed' => $weeklyGoal->is_completed,
                    'start_date' => $weeklyGoal->start_date->format('Y-m-d'),
                    'end_date' => $weeklyGoal->end_date ? $weeklyGoal->end_date->format('Y-m-d') : null,
                ],
                'statistics' => $statistics,
            ]
        ], 200);
    }

    /**
     * Update reading goal progress
     */
    public function updateProgress(Request $request)
    {
        $request->validate([
            'minutes' => 'required|integer|min:0',
            'pages' => 'required|integer|min:0',
            'goal_type' => 'nullable|in:daily,weekly',
        ]);

        $user = Auth::user();
        $minutes = $request->input('minutes', 0);
        $pages = $request->input('pages', 0);
        $goalType = $request->input('goal_type', 'daily');

        // Update daily goal
        if ($goalType === 'daily' || $goalType === 'both') {
            $dailyGoal = ReadingGoal::getOrCreateDailyGoal($user->id);
            $dailyGoal->addProgress($minutes, $pages);
        }

        // Update weekly goal
        if ($goalType === 'weekly' || $goalType === 'both') {
            $weeklyGoal = ReadingGoal::getOrCreateWeeklyGoal($user->id);
            $weeklyGoal->addProgress($minutes, $pages);
        }

        // Update reading streak
        $streak = ReadingStreak::getOrCreate($user->id);
        $streak->updateStreak();

        // Get updated goals
        return $this->getGoals($request);
    }

    /**
     * Get user's reading streak
     */
    public function getStreak(Request $request)
    {
        $user = Auth::user();
        $statistics = ReadingStreak::getUserStatistics($user->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Reading streak retrieved successfully',
            'data' => $statistics,
        ], 200);
    }

    /**
     * Get streak leaderboard
     */
    public function getLeaderboard(Request $request)
    {
        $limit = $request->input('limit', 10);
        $leaderboard = ReadingStreak::getLeaderboard($limit);
        
        // Get current user's rank
        $user = Auth::user();
        $userStreak = ReadingStreak::where('user_id', $user->id)->first();
        $userRank = $userStreak ? 
            ReadingStreak::where('current_streak', '>', $userStreak->current_streak)->count() + 1 : null;
        
        return response()->json([
            'success' => true,
            'message' => 'Leaderboard retrieved successfully',
            'data' => [
                'leaderboard' => $leaderboard,
                'user_rank' => [
                    'rank' => $userRank,
                    'current_streak' => $userStreak->current_streak ?? 0,
                    'username' => $user->username,
                ]
            ]
        ], 200);
    }

    /**
     * Update user's reading goals settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'daily_target_minutes' => 'nullable|integer|min:5|max:480',
            'daily_target_pages' => 'nullable|integer|min:1|max:100',
            'weekly_target_minutes' => 'nullable|integer|min:30|max:2000',
            'weekly_target_pages' => 'nullable|integer|min:7|max:700',
        ]);

        $user = Auth::user();
        
        // Update or create daily goal with new targets
        $dailyGoal = ReadingGoal::getOrCreateDailyGoal($user->id);
        if ($request->has('daily_target_minutes')) {
            $dailyGoal->target_minutes = $request->input('daily_target_minutes');
        }
        if ($request->has('daily_target_pages')) {
            $dailyGoal->target_pages = $request->input('daily_target_pages');
        }
        $dailyGoal->save();

        // Update or create weekly goal with new targets
        $weeklyGoal = ReadingGoal::getOrCreateWeeklyGoal($user->id);
        if ($request->has('weekly_target_minutes')) {
            $weeklyGoal->target_minutes = $request->input('weekly_target_minutes');
        }
        if ($request->has('weekly_target_pages')) {
            $weeklyGoal->target_pages = $request->input('weekly_target_pages');
        }
        $weeklyGoal->save();

        return response()->json([
            'success' => true,
            'message' => 'Reading goals settings updated successfully',
            'data' => [
                'daily_goal' => [
                    'target_minutes' => $dailyGoal->target_minutes,
                    'target_pages' => $dailyGoal->target_pages,
                ],
                'weekly_goal' => [
                    'target_minutes' => $weeklyGoal->target_minutes,
                    'target_pages' => $weeklyGoal->target_pages,
                ],
            ]
        ], 200);
    }

    /**
     * Get reading achievements
     */
    public function getAchievements(Request $request)
    {
        $user = Auth::user();
        $streak = ReadingStreak::getOrCreate($user->id);
        $goalsStats = ReadingGoal::getUserStatistics($user->id);
        
        $achievements = [
            [
                'id' => 'first_read',
                'name' => 'Pembaca Pemula',
                'description' => 'Baca kitab pertama kali',
                'icon' => '📖',
                'unlocked' => $streak->total_days >= 1,
                'progress' => min(100, ($streak->total_days / 1) * 100),
            ],
            [
                'id' => 'week_streak',
                'name' => 'Konsisten Seminggu',
                'description' => 'Baca 7 hari berturut-turut',
                'icon' => '🔥',
                'unlocked' => $streak->longest_streak >= 7,
                'progress' => min(100, ($streak->longest_streak / 7) * 100),
            ],
            [
                'id' => 'month_streak',
                'name' => 'Konsisten Sebulan',
                'description' => 'Baca 30 hari berturut-turut',
                'icon' => '🔥🔥',
                'unlocked' => $streak->longest_streak >= 30,
                'progress' => min(100, ($streak->longest_streak / 30) * 100),
            ],
            [
                'id' => 'goal_master',
                'name' => 'Master Goals',
                'description' => 'Selesaikan 10 goals',
                'icon' => '🏆',
                'unlocked' => $goalsStats['completed_goals'] >= 10,
                'progress' => min(100, ($goalsStats['completed_goals'] / 10) * 100),
            ],
            [
                'id' => 'dedicated_reader',
                'name' => 'Pembaca Dedikasi',
                'description' => 'Total 50 hari baca',
                'icon' => '⭐',
                'unlocked' => $streak->total_days >= 50,
                'progress' => min(100, ($streak->total_days / 50) * 100),
            ],
            [
                'id' => 'legend',
                'name' => 'Legend',
                'description' => 'Streak 100 hari',
                'icon' => '👑',
                'unlocked' => $streak->longest_streak >= 100,
                'progress' => min(100, ($streak->longest_streak / 100) * 100),
            ],
        ];

        $unlockedCount = collect($achievements)->where('unlocked', true)->count();
        
        return response()->json([
            'success' => true,
            'message' => 'Achievements retrieved successfully',
            'data' => [
                'achievements' => $achievements,
                'unlocked_count' => $unlockedCount,
                'total_count' => count($achievements),
                'completion_percentage' => round(($unlockedCount / count($achievements)) * 100, 2),
            ]
        ], 200);
    }
}
