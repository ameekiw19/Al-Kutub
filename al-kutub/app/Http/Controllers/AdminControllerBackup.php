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
use session;

class AdminController extends Controller
{
    private $fcmService;

    public function __construct(FcmService $fcmService = null)
    {
        $this->fcmService = $fcmService;
    }
    public function HomeAdmin()
    {
        // Import DashboardController untuk analytics data
        $dashboardController = new \App\Http\Controllers\DashboardController();
        
        // Get overview statistics
        $overviewStats = $dashboardController->getOverviewStats()->getData(true);
        
        // Statistik utama dari dashboard analytics
        $total_kitab = $overviewStats['total_kitab'];
        $total_user = $overviewStats['total_users'];
        $total_kategori = Kitab::select('kategori')->distinct()->count();
        $total_download = $overviewStats['total_downloads'];
        $total_views = $overviewStats['total_views'];
        $total_bookmarks = $overviewStats['total_bookmarks'];
        $active_users_today = $overviewStats['active_users_today'];

        // Get popular kitabs from dashboard
        $kitab_populer = $dashboardController->getPopularKitabs()->getData(true);

        // Aktivitas terbaru (dari tabel history)
        $log_aktivitas = History::with(['user', 'kitab'])
            ->latest('last_read_at')
            ->take(10)
            ->get();

        // User baru yang bergabung
        $user_baru = User::where('role', 'user')->latest()->take(5)->get();

        // Data untuk chart - User Registration (12 months)
        $userRegData = $dashboardController->getUserRegistrationData()->getData(true);
        $grafik_user_reg = $userRegData['data'];
        $tanggal_user_reg = $userRegData['labels'];

        // Data untuk chart - Kitab Views (30 days)
        $viewsData = $dashboardController->getKitabViewsData()->getData(true);
        $grafik_views = $viewsData['data'];
        $tanggal_views = $viewsData['labels'];

        // Get reading statistics
        $readingStats = $dashboardController->getReadingStats()->getData(true);

        // Get category distribution for additional stats
        $categoryData = $dashboardController->getCategoryDistribution()->getData(true);

        // Get user activity data (7 days)
        $userActivityData = $dashboardController->getUserActivityData()->getData(true);

        // Get engagement metrics
        $engagementMetrics = $dashboardController->getEngagementMetrics()->getData(true);

        // Get top downloaded kitabs
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

    
    function CRUDAdmin()
    {
        $kitabs = Kitab::all(); // ambil semua data kitab dari database
        return view('AdminCRUD', compact('kitabs')); // kirim ke view
    }



    function TambahKitab()
    {
        return view('TambahKitab');    
    }
    public function AddKitab(Request $request)
    {
        \Log::info('AddKitab method called', [
            'request_data' => $request->all(),
            'has_files' => [
                'pdf' => $request->hasFile('file_pdf'),
                'cover' => $request->hasFile('cover')
            ]
        ]);

        try {
            // Validasi input dasar saja
            $validated = $request->validate([
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:255',
                'deskripsi' => 'required',
                'kategori' => 'required',
                'bahasa' => 'required|string|max:100',
            ]);

            \Log::info('Basic validation passed', ['validated_data' => $validated]);

            // Buat folder jika belum ada
            $pdfPath = public_path('pdf');
            $coverPath = public_path('cover');
            
            if (!file_exists($pdfPath)) {
                mkdir($pdfPath, 0755, true);
                \Log::info('Created PDF directory', ['path' => $pdfPath]);
            }
            if (!file_exists($coverPath)) {
                mkdir($coverPath, 0755, true);
                \Log::info('Created cover directory', ['path' => $coverPath]);
            }

            // Simpan file PDF
            $pdf = $request->file('file_pdf');
            $pdfName = time() . '_' . str_replace(' ', '_', $pdf->getClientOriginalName());
            $pdf->move($pdfPath, $pdfName);
            \Log::info('PDF file saved', ['filename' => $pdfName, 'path' => $pdfPath]);

            // Simpan file Cover
            $cover = $request->file('cover');
            $coverName = time() . '_' . str_replace(' ', '_', $cover->getClientOriginalName());
            $cover->move($coverPath, $coverName);
            \Log::info('Cover file saved', ['filename' => $coverName, 'path' => $coverPath]);

            // Simpan ke database langsung
            $kitabData = [
                'judul' => $validated['judul'],
                'penulis' => $validated['penulis'],
                'deskripsi' => $validated['deskripsi'],
                'kategori' => $validated['kategori'],
                'bahasa' => $validated['bahasa'],
                'file_pdf' => $pdfName,
                'cover' => $coverName,
                'views' => 0,
                'downloads' => 0,
                'viewed_by' => json_encode([]),
            ];

            \Log::info('Creating kitab with data', ['kitab_data' => $kitabData]);
            $kitab = Kitab::create($kitabData);
            \Log::info('Kitab created successfully', ['kitab_id' => $kitab->id_kitab]);

            // Buat notifikasi di database
            $notificationData = [
                'title' => 'Kitab Baru Tersedia!',
                'message' => "Kitab '{$validated['judul']}' oleh {$validated['penulis']} telah ditambahkan. Yuk baca sekarang!",
                'type' => 'new_kitab',
                'action_url' => "/kitab/{$kitab->id_kitab}",
                'data' => json_encode([
                    'kitab_id' => $kitab->id_kitab,
                    'judul' => $kitab->judul,
                    'penulis' => $kitab->penulis,
                    'kategori' => $kitab->kategori,
                    'cover' => $kitab->cover,
                    'created_at' => $kitab->created_at->toISOString()
                ])
            ];

            try {
                $notification = AppNotification::create($notificationData);
                \Log::info('Database notification created successfully', ['notification_id' => $notification->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to create database notification: ' . $e->getMessage());
            }

            // 🚀 BROADCAST REAL-TIME EVENT 🚀
            try {
                \Log::info('Broadcasting NewKitabAdded event', ['kitab_id' => $kitab->id_kitab]);
                broadcast(new NewKitabAdded($kitab, $notificationData));
                \Log::info('NewKitabAdded event broadcasted successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast NewKitabAdded event: ' . $e->getMessage());
            }

            // 📱 KIRIM FCM NOTIFICATION KE SEMUA USER 📱
            if ($this->fcmService) {
                try {
                    \Log::info('Attempting to send FCM for new kitab', ['kitab_id' => $kitab->id_kitab]);
                    $fcmResult = $this->fcmService->sendNewKitabNotification($kitab);
                    
                    \Log::info('FCM notification process completed', [
                        'kitab_id' => $kitab->id_kitab,
                        'is_success' => $fcmResult['success'] ?? false,
                        'result_data' => $fcmResult
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send FCM notification for new kitab: ' . $e->getMessage(), [
                        'kitab_id' => $kitab->id_kitab,
                        'exception' => $e
                    ]);
                }
            } else {
                \Log::warning('FCM Service not available');
            }

            // Log audit (dengan try-catch)
            try {
                if (class_exists('App\Models\AuditLog')) {
                    AuditLog::logAdminAction('kitab_created', $kitab, [
                        'judul' => $validated['judul'],
                        'penulis' => $validated['penulis'],
                        'kategori' => $validated['kategori']
                    ]);
                    \Log::info('Audit log created successfully');
                }
            } catch (\Exception $e) {
                \Log::error('Failed to create audit log: ' . $e->getMessage());
            }

            // Return response
            // Return success response
            if ($request->ajax()) {
                \Log::info('Returning AJAX success response');
                return response()->json([
                    'success' => true,
                    'message' => 'Kitab berhasil ditambahkan!',
                    'kitab' => [
                        'id_kitab' => $kitab->id_kitab,
                        'judul' => $kitab->judul,
                        'penulis' => $kitab->penulis,
                        'kategori' => $kitab->kategori,
                        'bahasa' => $kitab->bahasa,
                        'file_pdf' => $kitab->file_pdf,
                        'cover' => $kitab->cover,
                        'created_at' => $kitab->created_at->format('Y-m-d H:i:s')
                    ]
                ]);
            }
            
            \Log::info('Returning redirect response');
            return redirect('manejemenkitab')->with('success', 'Kitab berhasil ditambahkan!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in AddKitab: ' . $e->getMessage(), [
                'errors' => $e->errors()
        $kitab = Kitab::find($id_kitab);

        if (!$kitab) {
            abort(404, 'Kitab tidak ditemukan');
        }

        return view('EditKitab', compact('kitab'));
    }


    public function updateKitab(Request $request, $id_kitab)
    {
        try {
            // Cari kitab berdasarkan ID
            $kitab = Kitab::findOrFail($id_kitab);

            // Validasi input
            $validated = $request->validate([
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:255',
                'kategori' => 'required|string|max:255',
                'bahasa' => 'required|string|max:100',
                'deskripsi' => 'required|string',
                'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'file_pdf' => 'nullable|mimes:pdf|max:20480', // 20MB
            ]);

            // --- COVER ---
            if ($request->hasFile('cover')) {
                // Hapus cover lama jika ada
                if ($kitab->cover && file_exists(public_path('cover/' . $kitab->cover))) {
                    unlink(public_path('cover/' . $kitab->cover));
                }

                // Simpan cover baru
                $coverName = time() . '_' . $request->file('cover')->getClientOriginalName();
                $request->file('cover')->move(public_path('cover'), $coverName);
                $kitab->cover = $coverName;
            }

            // --- FILE PDF ---
            if ($request->hasFile('file_pdf')) {
                // Hapus file lama jika ada
                if ($kitab->file_pdf && file_exists(public_path('pdf/' . $kitab->file_pdf))) {
                    unlink(public_path('pdf/' . $kitab->file_pdf));
                }

                // Simpan PDF baru
                $pdfName = time() . '_' . $request->file('file_pdf')->getClientOriginalName();
                $request->file('file_pdf')->move(public_path('pdf'), $pdfName);
                $kitab->file_pdf = $pdfName;
            }

            // Update field lainnya
            $kitab->judul = $validated['judul'];
            $kitab->penulis = $validated['penulis'];
            $kitab->kategori = $validated['kategori'];
            $kitab->bahasa = $validated['bahasa'];
            $kitab->deskripsi = $validated['deskripsi'];

            $kitab->save();

            return response()->json([
                'success' => true,
                'message' => 'Kitab berhasil diperbarui!',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function DeleteKitab($id_kitab)
    {
        $kitab = Kitab::find($id_kitab);

        if (!$kitab) {
            return response()->json(['success' => false, 'message' => 'Kitab tidak ditemukan.'], 404);
        }

        // Hapus file cover & PDF jika ada
        if ($kitab->cover && file_exists(public_path('cover/' . $kitab->cover))) {
            unlink(public_path('cover/' . $kitab->cover));
        }
        if ($kitab->file_pdf && file_exists(public_path('pdf/' . $kitab->file_pdf))) {
            unlink(public_path('pdf/' . $kitab->file_pdf));
        }

        // Log the kitab deletion before deleting
        AuditLog::logAdminAction('kitab_deleted', $kitab, [
            'judul' => $kitab->judul,
            'penulis' => $kitab->penulis,
            'kategori' => $kitab->kategori
        ]);

        // Hapus data dari database
        $kitab->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kitab berhasil dihapus!'
        ]);
    }

        public function Kitab($id_kitab)
    {
        $kitab = Kitab::findOrFail($id_kitab);

        // 📊 Analytics: Views over the last 30 days
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

        // 📑 Bookmark Rate
        $total_bookmarks = Bookmark::where('id_kitab', $id_kitab)->count();
        $bookmark_rate = $kitab->views > 0 ? round(($total_bookmarks / $kitab->views) * 100, 1) : 0;

        // 🕒 Reading Stats
        $total_sessions = History::where('kitab_id', $id_kitab)->count();
        $avg_progress = History::where('kitab_id', $id_kitab)->avg('current_page') ?? 0;

        // 💬 Comments for this Kitab
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

    function manejemenuser()
    {
        $users = User::select('id', 'username', 'email', 'deskripsi', 'phone', 'role', 'created_at')
            ->withCount('bookmarks')
            ->get();

        // User stats for summary cards
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

    // 🔔 Tampilan form notifikasi manual
    public function NotificationForm()
    {
        // Mengambil riwayat notifikasi manual terakhir
        $notifications = AppNotification::where('type', 'manual_broadcast')
            ->latest()
            ->take(10)
            ->get();

        return view('NotificationBroadcast', compact('notifications'));
    }

    // 🚀 Proses kirim notifikasi manual (Broadcast)
    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:255',
            'action_url' => 'nullable|string|max:255',
        ]);

        try {
            // 1. Simpan ke database
            $notif = AppNotification::create([
                'title' => $request->title,
                'message' => $request->message,
                'type' => 'manual_broadcast',
                'action_url' => $request->action_url ?? '/home'
            ]);

            // 2. Kirim via FCM Service
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

    /**
     * 💬 Halaman Manajemen Komentar & Saran
     */
    public function comments()
    {
        $comments = Comment::with(['user', 'kitab'])
            ->latest()
            ->paginate(15);

        // Statistik komentar
        $total_comments = Comment::count();
        $kitab_comments = Comment::whereNotNull('id_kitab')->count();
        $general_feedback = Comment::whereNull('id_kitab')->count();

        return view('admin.comments', compact('comments', 'total_comments', 'kitab_comments', 'general_feedback'));
    }

    /**
     * 🗑️ Proses Hapus Komentar
     */
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

    /**
     * 🚮 Hapus User
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Jangan biarkan admin hapus dirinya sendiri
            if ($user->id === auth()->id()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat menghapus akun sendiri!'], 400);
            }

            // Hapus data terkait (opsional, tergantung relasi cascade di DB)
            // Bookmark::where('user_id', $id)->delete();
            // History::where('user_id', $id)->delete();
            // Comment::where('user_id', $id)->delete();

            // Log the user deletion before deleting
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

    /**
     * 🎭 Update Role User (Admin/User)
     */
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

            // Log the role update
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
