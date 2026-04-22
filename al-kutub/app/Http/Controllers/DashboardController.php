<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Kitab;
use App\Models\History;
use App\Models\Bookmark;
use App\Models\AppNotification;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display dashboard analytics page
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Get overview statistics
     */
    public function getOverviewStats()
    {
        // Cache untuk performa (5 menit)
        $stats = Cache::remember('dashboard_overview_stats', 300, function () {
            return [
                'total_users' => User::count(),
                'total_kitab' => Kitab::count(),
                'total_views' => (int) Kitab::sum('views'),
                'total_downloads' => (int) Kitab::sum('downloads'),
                'active_users_today' => History::whereDate('last_read_at', today())->distinct('user_id')->count('user_id'),
                'new_users_this_month' => User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'total_bookmarks' => Bookmark::count(),
                'total_notifications' => AppNotification::count(),
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get user registration data for chart
     */
    public function getUserRegistrationData()
    {
        $data = Cache::remember('dashboard_user_registration', 300, function () {
            // Last 12 months
            $months = collect();
            for ($i = 11; $i >= 0; $i--) {
                $months->push(now()->subMonths($i)->format('Y-m'));
            }

            $registrations = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $labels = [];
            $data = [];
            foreach ($months as $month) {
                $labels[] = Carbon::createFromFormat('Y-m', $month)->format('M Y');
                $data[] = $registrations[$month] ?? 0;
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        });

        return response()->json($data);
    }

    /**
     * Get kitab views data for chart
     */
    public function getKitabViewsData()
    {
        $data = Cache::remember('dashboard_kitab_views', 300, function () {
            // Last 30 days
            $days = collect();
            for ($i = 29; $i >= 0; $i--) {
                $days->push(now()->subDays($i)->format('Y-m-d'));
            }

            // Get history data for last 30 days
            $views = History::selectRaw('DATE(last_read_at) as date, COUNT(*) as count')
                ->where('last_read_at', '>=', now()->subDays(29)->startOfDay())
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $labels = [];
            $data = [];
            foreach ($days as $day) {
                $labels[] = Carbon::createFromFormat('Y-m-d', $day)->format('d M');
                $data[] = $views[$day] ?? 0;
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        });

        return response()->json($data);
    }

    /**
     * Get popular kitabs
     */
    public function getPopularKitabs()
    {
        $kitabs = Cache::remember('dashboard_popular_kitabs', 300, function () {
            return Kitab::select('id_kitab', 'judul', 'penulis', 'views', 'downloads')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($kitab) {
                    return [
                        'id_kitab' => $kitab->id_kitab,
                        'judul' => $kitab->judul,
                        'penulis' => $kitab->penulis,
                        'views' => (int) $kitab->views,
                        'downloads' => (int) $kitab->downloads,
                    ];
                });
        });

        return response()->json($kitabs);
    }

    /**
     * Get category distribution
     */
    public function getCategoryDistribution()
    {
        $categories = Cache::remember('dashboard_category_distribution', 300, function () {
            return Kitab::select('kategori', DB::raw('count(*) as count'))
                ->groupBy('kategori')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function ($category) {
                    return [
                        'kategori' => $category->kategori ?? 'Uncategorized',
                        'count' => (int) $category->count,
                    ];
                });
        });

        return response()->json($categories);
    }

    /**
     * Get user activity data
     */
    public function getUserActivityData()
    {
        $data = Cache::remember('dashboard_user_activity', 300, function () {
            // Last 7 days
            $days = collect();
            for ($i = 6; $i >= 0; $i--) {
                $days->push(now()->subDays($i)->format('Y-m-d'));
            }

            $activity = History::selectRaw('DATE(last_read_at) as date, COUNT(DISTINCT user_id) as active_users')
                ->where('last_read_at', '>=', now()->subDays(6)->startOfDay())
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('active_users', 'date')
                ->toArray();

            $labels = [];
            $data = [];
            foreach ($days as $day) {
                $labels[] = Carbon::createFromFormat('Y-m-d', $day)->format('D');
                $data[] = $activity[$day] ?? 0;
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        });

        return response()->json($data);
    }

    /**
     * Get reading statistics
     */
    public function getReadingStats()
    {
        $stats = Cache::remember('dashboard_reading_stats', 300, function () {
            return [
                'total_reading_time' => (int) (History::sum('reading_time_minutes') ?? 0),
                'avg_reading_time' => round(History::avg('reading_time_minutes') ?? 0, 2),
                'most_active_reader' => History::select('user_id', DB::raw('COUNT(*) as reading_sessions'))
                    ->groupBy('user_id')
                    ->orderBy('reading_sessions', 'desc')
                    ->first(),
                'pages_read_today' => (int) (History::whereDate('last_read_at', today())->sum('current_page') ?? 0),
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get top downloaded kitabs
     */
    public function getTopDownloadedKitabs()
    {
        $kitabs = Cache::remember('dashboard_top_downloads_monthly', 300, function () {
            $topLogs = \App\Models\DownloadLog::select('kitab_id', DB::raw('count(*) as monthly_downloads'))
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->groupBy('kitab_id')
                ->orderBy('monthly_downloads', 'desc')
                ->limit(5)
                ->get();

            $results = [];
            foreach ($topLogs as $log) {
                $kitab = Kitab::find($log->kitab_id);
                if ($kitab) {
                    $results[] = [
                        'id_kitab' => $kitab->id_kitab,
                        'judul' => $kitab->judul,
                        'penulis' => $kitab->penulis,
                        'views' => (int) $kitab->views,
                        'downloads' => (int) $log->monthly_downloads, // monthly metric
                        'cover' => $kitab->cover,
                    ];
                }
            }
            
            // Evaluated if there are no downloads this month, fallback to top 5 all time just so dashboard isn't completely empty
            if (count($results) === 0) {
                return collect(Kitab::select('id_kitab', 'judul', 'penulis', 'views', 'downloads', 'cover')
                    ->orderBy('downloads', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($kitab) {
                        return [
                            'id_kitab' => $kitab->id_kitab,
                            'judul' => $kitab->judul,
                            'penulis' => $kitab->penulis,
                            'views' => (int) $kitab->views,
                            'downloads' => (int) $kitab->downloads,
                            'cover' => $kitab->cover,
                        ];
                    }));
            }

            return collect($results);
        });

        return response()->json($kitabs);
    }

    /**
     * Get downloads trend (last 30 days)
     */
    public function getDownloadsTrend()
    {
        $data = Cache::remember('dashboard_downloads_trend', 300, function () {
            // Since we don't have a separate downloads table with timestamps,
            // we'll use kitab created_at and downloads count as approximation
            // For real trend, we'd need a downloads log table
            $days = collect();
            for ($i = 29; $i >= 0; $i--) {
                $days->push(now()->subDays($i)->format('Y-m-d'));
            }

            // Use history as proxy for reading activity trend
            $activity = History::selectRaw('DATE(last_read_at) as date, COUNT(*) as count')
                ->where('last_read_at', '>=', now()->subDays(29)->startOfDay())
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $labels = [];
            $data = [];
            foreach ($days as $day) {
                $labels[] = Carbon::createFromFormat('Y-m-d', $day)->format('d M');
                $data[] = $activity[$day] ?? 0;
            }

            // Also get total downloads per category
            $downloadsByCategory = Kitab::select('kategori', DB::raw('SUM(downloads) as total_downloads'))
                ->groupBy('kategori')
                ->orderBy('total_downloads', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'kategori' => $item->kategori ?? 'Uncategorized',
                        'total_downloads' => (int) $item->total_downloads,
                    ];
                });

            return [
                'labels' => $labels,
                'data' => $data,
                'by_category' => $downloadsByCategory,
            ];
        });

        return response()->json($data);
    }

    /**
     * Get engagement metrics
     */
    public function getEngagementMetrics()
    {
        $metrics = Cache::remember('dashboard_engagement', 300, function () {
            $totalUsers = User::count();
            $totalBookmarks = Bookmark::count();
            $totalReadingSessions = History::count();

            // Users active in last 7 days
            $activeUsersWeek = History::where('last_read_at', '>=', now()->subDays(7))
                ->distinct('user_id')
                ->count('user_id');

            // Most bookmarked kitab
            $mostBookmarked = Bookmark::select('id_kitab', DB::raw('COUNT(*) as bookmark_count'))
                ->groupBy('id_kitab')
                ->orderBy('bookmark_count', 'desc')
                ->first();

            $mostBookmarkedKitab = null;
            if ($mostBookmarked) {
                $kitab = Kitab::find($mostBookmarked->id_kitab);
                if ($kitab) {
                    $mostBookmarkedKitab = [
                        'judul' => $kitab->judul,
                        'penulis' => $kitab->penulis,
                        'bookmark_count' => (int) $mostBookmarked->bookmark_count,
                    ];
                }
            }

            // Top readers
            $topReaders = History::select('user_id', DB::raw('COUNT(*) as sessions'))
                ->groupBy('user_id')
                ->orderBy('sessions', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $user = User::find($item->user_id);
                    return [
                        'username' => $user->username ?? 'Unknown',
                        'sessions' => (int) $item->sessions,
                    ];
                });

            return [
                'avg_bookmarks_per_user' => $totalUsers > 0 ? round($totalBookmarks / $totalUsers, 1) : 0,
                'avg_reading_per_user' => $totalUsers > 0 ? round($totalReadingSessions / $totalUsers, 1) : 0,
                'retention_rate' => $totalUsers > 0 ? round(($activeUsersWeek / $totalUsers) * 100, 1) : 0,
                'active_users_week' => $activeUsersWeek,
                'most_bookmarked' => $mostBookmarkedKitab,
                'top_readers' => $topReaders,
            ];
        });

        return response()->json($metrics);
    }

    /**
     * Export data to CSV
     */
    public function exportData(Request $request)
    {
        $type = $request->input('type', 'overview');
        
        switch ($type) {
            case 'users':
                return $this->exportUsers();
            case 'kitabs':
                return $this->exportKitabs();
            case 'history':
                return $this->exportHistory();
            case 'overview':
            default:
                return $this->exportOverview();
        }
    }

    private function exportUsers()
    {
        $users = User::select('id', 'username', 'email', 'role', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "users_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['ID', 'Username', 'Email', 'Role', 'Created At']);
            
            // Data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->username,
                    $user->email,
                    $user->role,
                    $user->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportKitabs()
    {
        $kitabs = Kitab::select('id_kitab', 'judul', 'penulis', 'kategori', 'views', 'downloads', 'created_at')
            ->orderBy('views', 'desc')
            ->get();

        $filename = "kitabs_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($kitabs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['ID', 'Judul', 'Penulis', 'Kategori', 'Views', 'Downloads', 'Created At']);
            
            foreach ($kitabs as $kitab) {
                fputcsv($file, [
                    $kitab->id_kitab,
                    $kitab->judul,
                    $kitab->penulis,
                    $kitab->kategori,
                    $kitab->views,
                    $kitab->downloads,
                    $kitab->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportHistory()
    {
        $history = History::with(['user:id,username', 'kitab:id_kitab,judul'])
            ->select('id', 'user_id', 'kitab_id', 'last_read_at', 'reading_time_minutes', 'created_at')
            ->orderBy('last_read_at', 'desc')
            ->limit(1000) // Limit to prevent memory issues
            ->get();

        $filename = "history_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($history) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['ID', 'User', 'Kitab', 'Last Read', 'Reading Time (minutes)', 'Created At']);
            
            foreach ($history as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->user->username ?? 'Unknown',
                    $item->kitab->judul ?? 'Unknown',
                    $item->last_read_at->format('Y-m-d H:i:s'),
                    $item->reading_time_minutes ?? 0,
                    $item->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportOverview()
    {
        $data = [
            'Metric' => 'Value',
            'Total Users' => User::count(),
            'Total Kitabs' => Kitab::count(),
            'Total Views' => Kitab::sum('views'),
            'Total Downloads' => Kitab::sum('downloads'),
            'Total Bookmarks' => Bookmark::count(),
            'Total History Records' => History::count(),
            'Total Notifications' => AppNotification::count(),
            'Active Users Today' => History::whereDate('last_read_at', today())->distinct('user_id')->count('user_id'),
            'New Users This Month' => User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'Export Date' => now()->format('Y-m-d H:i:s'),
        ];

        $filename = "overview_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            foreach ($data as $key => $value) {
                fputcsv($file, [$key, $value]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear dashboard cache
     */
    public function clearCache()
    {
        Cache::forget('dashboard_overview_stats');
        Cache::forget('dashboard_user_registration');
        Cache::forget('dashboard_kitab_views');
        Cache::forget('dashboard_popular_kitabs');
        Cache::forget('dashboard_category_distribution');
        Cache::forget('dashboard_user_activity');
        Cache::forget('dashboard_reading_stats');
        Cache::forget('dashboard_top_downloads');
        Cache::forget('dashboard_downloads_trend');
        Cache::forget('dashboard_engagement');

        return response()->json(['message' => 'Dashboard cache cleared successfully']);
    }
}
