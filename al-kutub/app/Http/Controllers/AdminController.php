<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kitab;
use App\Models\User;
use App\Models\History;
use App\Models\Comment;
use App\Models\Bookmark;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Services\FcmService;
use App\Events\NewKitabAdded;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    private $fcmService;

    public function __construct(FcmService $fcmService = null)
    {
        $this->fcmService = $fcmService;
    }

    public function HomeAdmin()
    {
        $dashboardController = new \App\Http\Controllers\DashboardController();
        $overviewStats = $dashboardController->getOverviewStats()->getData(true);

        $total_kitab = $overviewStats['total_kitab'];
        $total_user = $overviewStats['total_users'];
        $total_kategori = Kitab::select('kategori')->distinct()->count();
        $total_download = $overviewStats['total_downloads'];
        $total_views = $overviewStats['total_views'];
        $total_bookmarks = $overviewStats['total_bookmarks'];
        $active_users_today = $overviewStats['active_users_today'];

        $kitab_populer = $dashboardController->getPopularKitabs()->getData(true);
        $log_aktivitas = History::with(['user', 'kitab'])
            ->latest('last_read_at')
            ->take(10)
            ->get();

        $user_baru = User::where('role', 'user')->latest()->take(5)->get();

        $userRegData = $dashboardController->getUserRegistrationData()->getData(true);
        $grafik_user_reg = $userRegData['data'];
        $tanggal_user_reg = $userRegData['labels'];

        $viewsData = $dashboardController->getKitabViewsData()->getData(true);
        $grafik_views = $viewsData['data'];
        $tanggal_views = $viewsData['labels'];

        $readingStats = $dashboardController->getReadingStats()->getData(true);
        $categoryData = $dashboardController->getCategoryDistribution()->getData(true);
        $userActivityData = $dashboardController->getUserActivityData()->getData(true);
        $engagementMetrics = $dashboardController->getEngagementMetrics()->getData(true);
        $topDownloads = $dashboardController->getTopDownloadedKitabs()->getData(true);

        return view('AdminHome', compact(
            'total_kitab',
            'total_user',
            'total_kategori',
            'total_download',
            'total_views',
            'total_bookmarks',
            'active_users_today',
            'log_aktivitas',
            'user_baru',
            'kitab_populer',
            'grafik_user_reg',
            'tanggal_user_reg',
            'grafik_views',
            'tanggal_views',
            'readingStats',
            'categoryData',
            'userActivityData',
            'engagementMetrics',
            'topDownloads'
        ));
    }

    public function Kitab($id_kitab)
    {
        $kitab = Kitab::findOrFail($id_kitab);

        $days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $days->push(now()->subDays($i)->format('Y-m-d'));
        }

        $views = History::selectRaw('DATE(last_read_at) as date, COUNT(*) as count')
            ->where('kitab_id', $id_kitab)
            ->where('last_read_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $analytics_labels = [];
        $analytics_data = [];
        foreach ($days as $day) {
            $analytics_labels[] = \Carbon\Carbon::createFromFormat('Y-m-d', $day)->format('d M');
            $analytics_data[] = $views[$day] ?? 0;
        }

        $total_bookmarks = Bookmark::where('id_kitab', $id_kitab)->count();
        $bookmark_rate = $kitab->views > 0 ? round(($total_bookmarks / $kitab->views) * 100, 1) : 0;

        $total_sessions = History::where('kitab_id', $id_kitab)->count();
        $avg_progress = History::where('kitab_id', $id_kitab)->avg('current_page') ?? 0;

        $reviews = Comment::with('user')
            ->where('id_kitab', $id_kitab)
            ->latest()
            ->take(5)
            ->get();

        return view('Kitab', compact(
            'kitab',
            'analytics_labels',
            'analytics_data',
            'total_bookmarks',
            'bookmark_rate',
            'total_sessions',
            'avg_progress',
            'reviews'
        ));
    }

    public function manejemenuser()
    {
        $users = User::select('id', 'username', 'email', 'deskripsi', 'phone', 'role', 'created_at')
            ->withCount('bookmarks')
            ->get();

        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
        $activeUsersToday = History::whereDate('last_read_at', today())
            ->distinct('user_id')->count('user_id');

        return view('ManejemenUser', compact(
            'users', 'totalUsers', 'totalAdmins',
            'newUsersThisMonth', 'activeUsersToday'
        ));
    }

    public function NotificationForm()
    {
        $notifications = AppNotification::where('type', 'manual_broadcast')
            ->latest()
            ->take(10)
            ->get();

        return view('NotificationBroadcast', compact('notifications'));
    }

    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:255',
            'action_url' => 'nullable|string|max:255',
        ]);

        try {
            $notif = AppNotification::create([
                'title' => $request->title,
                'message' => $request->message,
                'type' => 'manual_broadcast',
                'action_url' => $request->action_url ?? '/home'
            ]);

            $fcmData = [
                'type' => 'manual_broadcast',
                'action_url' => $request->action_url ?? '/home'
            ];

            $result = $this->fcmService->sendToAll(
                $request->title,
                $request->message,
                $fcmData
            );

            \Log::info('Manual broadcast sent by admin', [
                'admin_id' => auth()->id(),
                'title' => $request->title,
                'fcm_success' => $result['success'] ?? false
            ]);

            return redirect()->back()->with('success', 'Notifikasi berhasil disiarkan ke semua pengguna!');
        } catch (\Exception $e) {
            \Log::error('Manual broadcast failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengirim notifikasi: ' . $e->getMessage());
        }
    }

    public function comments()
    {
        $comments = Comment::with(['user', 'kitab'])
            ->latest()
            ->paginate(15);

        $total_comments = Comment::count();
        $kitab_comments = Comment::whereNotNull('id_kitab')->count();
        $general_feedback = Comment::whereNull('id_kitab')->count();

        return view('admin.comments', compact('comments', 'total_comments', 'kitab_comments', 'general_feedback'));
    }

    public function deleteComment($id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $comment->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Komentar berhasil dihapus!'
                ]);
            }

            return redirect()->back()->with('success', 'Komentar berhasil dihapus!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus komentar: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menghapus komentar!');
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat menghapus akun sendiri!'], 400);
            }

            AuditLog::logAdminAction('user_deleted', $user, [
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role
            ]);

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateUserRole(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat mengubah role anda sendiri!'], 400);
            }

            $oldRole = $user->role;
            $user->role = $request->role === 'admin' ? 'admin' : 'user';
            $user->save();

            AuditLog::logAdminAction('role_updated', $user, [
                'username' => $user->username,
                'old_role' => $oldRole,
                'new_role' => $user->role
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role user berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui role: ' . $e->getMessage()
            ], 500);
        }
    }
}
