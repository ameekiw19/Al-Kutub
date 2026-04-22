<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\ReadingGoal;
use App\Models\ReadingNote;
use App\Models\ReadingStreak;
use Illuminate\Http\Request;

class ReadingInsightsController extends Controller
{
    public function goals()
    {
        $user = auth()->user();

        $dailyGoal = ReadingGoal::getOrCreateDailyGoal($user->id);
        $weeklyGoal = ReadingGoal::getOrCreateWeeklyGoal($user->id);
        $streak = ReadingStreak::getOrCreate($user->id);
        $goalStats = ReadingGoal::getUserStatistics($user->id);

        $histories = History::forUser($user->id)
            ->with('kitab:id_kitab,judul,kategori')
            ->orderByDesc('last_read_at')
            ->get();

        $summary = [
            'books_read' => $histories->pluck('kitab_id')->filter()->unique()->count(),
            'pages_read' => (int) $histories->sum(function ($history) {
                return max((int) ($history->current_page ?? 0), 0);
            }),
            'minutes_read' => (int) $histories->sum('reading_time_minutes'),
            'notes_count' => ReadingNote::forUser($user->id)->count(),
        ];

        $achievements = $this->buildAchievements($streak, $goalStats);
        $recentHistory = $histories->take(5);

        return view('reading-goals.index', compact(
            'dailyGoal',
            'weeklyGoal',
            'streak',
            'goalStats',
            'summary',
            'achievements',
            'recentHistory'
        ));
    }

    public function updateGoals(Request $request)
    {
        $validated = $request->validate([
            'daily_target_minutes' => 'required|integer|min:5|max:480',
            'daily_target_pages' => 'required|integer|min:1|max:300',
            'weekly_target_minutes' => 'required|integer|min:30|max:3000',
            'weekly_target_pages' => 'required|integer|min:7|max:1500',
        ]);

        $userId = auth()->id();

        $dailyGoal = ReadingGoal::getOrCreateDailyGoal($userId);
        $dailyGoal->update([
            'target_minutes' => $validated['daily_target_minutes'],
            'target_pages' => $validated['daily_target_pages'],
        ]);

        $weeklyGoal = ReadingGoal::getOrCreateWeeklyGoal($userId);
        $weeklyGoal->update([
            'target_minutes' => $validated['weekly_target_minutes'],
            'target_pages' => $validated['weekly_target_pages'],
        ]);

        return redirect()
            ->route('reading-goals.index')
            ->with('success', 'Target reading goals berhasil diperbarui.');
    }

    public function statistics()
    {
        $user = auth()->user();

        $histories = History::forUser($user->id)
            ->with('kitab:id_kitab,judul,kategori,penulis')
            ->orderByDesc('last_read_at')
            ->get();

        $streak = ReadingStreak::getOrCreate($user->id);
        $goalStats = ReadingGoal::getUserStatistics($user->id);
        $notesCount = ReadingNote::forUser($user->id)->count();

        $totalBooksRead = $histories->pluck('kitab_id')->filter()->unique()->count();
        $totalPagesRead = (int) $histories->sum(function ($history) {
            return max((int) ($history->current_page ?? 0), 0);
        });
        $totalMinutesRead = (int) $histories->sum('reading_time_minutes');
        $daysActive = $histories
            ->filter(fn ($history) => $history->last_read_at)
            ->map(fn ($history) => $history->last_read_at->format('Y-m-d'))
            ->unique()
            ->count();
        $averagePagesPerDay = $daysActive > 0 ? round($totalPagesRead / $daysActive, 1) : 0;
        $thisMonthBooks = $histories
            ->filter(fn ($history) => $history->last_read_at && $history->last_read_at->isCurrentMonth())
            ->pluck('kitab_id')
            ->filter()
            ->unique()
            ->count();

        $monthlyProgress = collect(range(5, 0))->map(function ($offset) use ($histories) {
            $month = now()->subMonths($offset);
            $items = $histories->filter(function ($history) use ($month) {
                return $history->last_read_at
                    && $history->last_read_at->month === $month->month
                    && $history->last_read_at->year === $month->year;
            });

            return [
                'label' => $month->translatedFormat('M Y'),
                'books' => $items->pluck('kitab_id')->filter()->unique()->count(),
                'pages' => (int) $items->sum(function ($history) {
                    return max((int) ($history->current_page ?? 0), 0);
                }),
                'minutes' => (int) $items->sum('reading_time_minutes'),
            ];
        });

        $maxMonthlyPages = max(1, (int) $monthlyProgress->max('pages'));

        $categoryBreakdown = $histories
            ->filter(fn ($history) => optional($history->kitab)->kategori)
            ->groupBy(fn ($history) => $history->kitab->kategori)
            ->map(function ($items, $category) {
                return [
                    'label' => $category,
                    'books' => $items->pluck('kitab_id')->filter()->unique()->count(),
                    'pages' => (int) $items->sum(function ($history) {
                        return max((int) ($history->current_page ?? 0), 0);
                    }),
                    'minutes' => (int) $items->sum('reading_time_minutes'),
                ];
            })
            ->sortByDesc('pages')
            ->take(6)
            ->values();

        $recentSessions = $histories->take(6);
        $achievements = $this->buildAchievements($streak, $goalStats);

        return view('reading-statistics.index', compact(
            'streak',
            'goalStats',
            'notesCount',
            'totalBooksRead',
            'totalPagesRead',
            'totalMinutesRead',
            'daysActive',
            'averagePagesPerDay',
            'thisMonthBooks',
            'monthlyProgress',
            'maxMonthlyPages',
            'categoryBreakdown',
            'recentSessions',
            'achievements'
        ));
    }

    public function leaderboard(Request $request)
    {
        $limit = $request->input('limit', 10);
        $leaderboard = ReadingStreak::getLeaderboard($limit);
        
        $user = auth()->user();
        $userStreak = ReadingStreak::where('user_id', $user->id)->first();
        
        $userRank = null;
        if ($userStreak) {
            $userRank = ReadingStreak::where('current_streak', '>', $userStreak->current_streak)->count() + 1;
        }

        return view('reading-leaderboard.index', compact('leaderboard', 'userRank', 'userStreak', 'user'));
    }

    private function buildAchievements(ReadingStreak $streak, array $goalStats): array
    {
        return [
            [
                'id' => 'first_read',
                'name' => 'Pembaca Pemula',
                'description' => 'Mulai membaca minimal 1 hari.',
                'icon' => 'fa-book-open',
                'unlocked' => $streak->total_days >= 1,
                'progress' => min(100, $streak->total_days * 100),
            ],
            [
                'id' => 'week_streak',
                'name' => 'Konsisten Seminggu',
                'description' => 'Baca 7 hari berturut-turut',
                'icon' => 'fa-fire',
                'unlocked' => $streak->longest_streak >= 7,
                'progress' => min(100, round(($streak->longest_streak / 7) * 100)),
            ],
            [
                'id' => 'month_streak',
                'name' => 'Konsisten Sebulan',
                'description' => 'Baca 30 hari berturut-turut',
                'icon' => 'fa-star',
                'unlocked' => $streak->longest_streak >= 30,
                'progress' => min(100, round(($streak->longest_streak / 30) * 100)),
            ],
            [
                'id' => 'goal_master',
                'name' => 'Master Goals',
                'description' => 'Selesaikan 10 goals',
                'icon' => 'fa-trophy',
                'unlocked' => ($goalStats['completed_goals'] ?? 0) >= 10,
                'progress' => min(100, round((($goalStats['completed_goals'] ?? 0) / 10) * 100)),
            ],
            [
                'id' => 'dedicated_reader',
                'name' => 'Pembaca Dedikasi',
                'description' => 'Total 50 hari baca',
                'icon' => 'fa-medal',
                'unlocked' => $streak->total_days >= 50,
                'progress' => min(100, round(($streak->total_days / 50) * 100)),
            ],
            [
                'id' => 'legend',
                'name' => 'Legend',
                'description' => 'Streak 100 hari',
                'icon' => 'fa-crown',
                'unlocked' => $streak->longest_streak >= 100,
                'progress' => min(100, round(($streak->longest_streak / 100) * 100)),
            ],
        ];
    }
}
