<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminControllerFixed;
use App\Http\Controllers\Register;
use App\Http\Controllers\Login;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\KitabReaderController;
use App\Http\Controllers\ReadingInsightsController;
use App\Http\Controllers\ReadingNoteController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/phpinfo', function () {
    phpinfo();
});


//AUTH

// ================= Register =================
Route::get('register', [Register::class, 'register'])->name('register');
Route::post('register/action', [Register::class, 'actionregister'])->name('register.action');

// ================= Login =================
Route::get('login', [Login::class, 'login'])->name('login');
Route::post('login/action', [Login::class, 'actionlogin'])->name('login.action');

    // Logout
Route::get('/logout', [Login::class, 'actionlogout'])->middleware('auth')->name('logout');

// ================= 2FA Routes =================
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/manage', [TwoFactorController::class, 'showManage'])->name('2fa.manage');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('/2fa/regenerate-backup-codes', [TwoFactorController::class, 'regenerateBackupCodes'])->name('2fa.regenerate-backup-codes');
});

Route::get('/2fa/verify', [TwoFactorController::class, 'showVerification'])->name('2fa.verify');
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.post');

// ================== ADMIN ROUTE ==================
Route::middleware(['auth', 'role:admin', 'audit'])->prefix('admin')->group(function () {
    Route::get('home', [AdminController::class, 'HomeAdmin']);
    
    // AJAX Kitab Form
    Route::get('tambah-kitab-ajax', function() {
        return view('TambahKitabAjax');
    })->name('admin.tambah-kitab-ajax');
    
    // Test Route untuk Debug
    Route::get('test-dashboard', function() {
        $dashboardController = new \App\Http\Controllers\DashboardController();
        $overviewStats = $dashboardController->getOverviewStats()->getData(true);
        
        return response()->json([
            'status' => 'success',
            'data' => $overviewStats,
            'message' => 'Dashboard data loaded successfully'
        ]);
    });
    
    // Test View Rendering
    Route::get('test-view', function() {
        try {
            $controller = new \App\Http\Controllers\AdminController();
            $view = $controller->HomeAdmin();
            
            return response()->json([
                'status' => 'success',
                'view' => $view->name(),
                'data_count' => count($view->getData()),
                'message' => 'View rendered successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    });
    
    // Test Dashboard View
    Route::get('test-dashboard-view', function() {
        try {
            $controller = new \App\Http\Controllers\AdminController();
            $view = $controller->HomeAdmin();
            
            // Return TestDashboard view with same data
            return view('TestDashboard', $view->getData());
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    });
    
    // Dashboard Analytics
    Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('dashboard/stats/overview', [DashboardController::class, 'getOverviewStats']);
    Route::get('dashboard/stats/user-registration', [DashboardController::class, 'getUserRegistrationData']);
    Route::get('dashboard/stats/kitab-views', [DashboardController::class, 'getKitabViewsData']);
    Route::get('dashboard/stats/popular-kitabs', [DashboardController::class, 'getPopularKitabs']);
    Route::get('dashboard/stats/category-distribution', [DashboardController::class, 'getCategoryDistribution']);
    Route::get('dashboard/stats/user-activity', [DashboardController::class, 'getUserActivityData']);
    Route::get('dashboard/stats/reading-stats', [DashboardController::class, 'getReadingStats']);
    Route::get('dashboard/stats/top-downloads', [DashboardController::class, 'getTopDownloadedKitabs']);
    Route::get('dashboard/stats/downloads-trend', [DashboardController::class, 'getDownloadsTrend']);
    Route::get('dashboard/stats/engagement', [DashboardController::class, 'getEngagementMetrics']);
    Route::get('dashboard/export', [DashboardController::class, 'exportData']);
    Route::post('dashboard/clear-cache', [DashboardController::class, 'clearCache']);

    // Manajemen Kategori
    Route::get('categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('categories/create', [AdminCategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('categories/{id}/edit', [AdminCategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('categories/{id}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('categories/{id}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Manajemen Kitab
    Route::get('manejemenkitab', [AdminControllerFixed::class, 'CRUDAdmin']);
    Route::get('tambahkitab', [AdminControllerFixed::class, 'TambahKitab']);
    Route::post('addkitab', [AdminControllerFixed::class, 'AddKitab']);
    Route::get('editkitab/{id_kitab}', [AdminControllerFixed::class, 'EditKitab']);
    Route::post('updatekitab/{id_kitab}', [AdminControllerFixed::class, 'UpdateKitab']);
    Route::delete('deletekitab/{id_kitab}', [AdminControllerFixed::class, 'DeleteKitab']);
    Route::post('kitab/bulk-delete', [AdminControllerFixed::class, 'bulkDelete'])->name('admin.kitab.bulk-delete');
    Route::get('kitab/bulk-export', [AdminControllerFixed::class, 'bulkExport'])->name('admin.kitab.bulk-export');
    Route::post('kitab/{id_kitab}/import-transcript', [AdminControllerFixed::class, 'importTranscript'])->name('admin.kitab.import-transcript');
    Route::post('kitab/import-transcripts', [AdminControllerFixed::class, 'bulkImportTranscripts'])->name('admin.kitab.bulk-import-transcripts');

    // Detail Kitab
    Route::get('kitab/{id_kitab}', [AdminController::class, 'Kitab']);

    // Manajemen User
    Route::get('manejemenuser', [AdminController::class, 'manejemenuser']);
    Route::delete('delete-user/{id}', [AdminController::class, 'deleteUser'])->name('admin.user.delete');
    Route::post('update-user-role/{id}', [AdminController::class, 'updateUserRole'])->name('admin.user.updateRole');

    // Notifikasi Manual (Broadcast)
    Route::get('notifications', [AdminController::class, 'NotificationForm'])->name('admin.notifications');
    Route::post('notifications/send', [AdminController::class, 'sendBroadcast'])->name('admin.notifications.send');

    // Manajemen Komentar & Saran
    Route::get('comments', [AdminController::class, 'comments'])->name('admin.comments');
    Route::delete('delete-comment/{id}', [AdminController::class, 'deleteComment'])->name('admin.comments.delete');

    // Audit Logs
    Route::get('audit', [AuditController::class, 'index'])->name('admin.audit.index');
    Route::get('audit/{id}', [AuditController::class, 'show'])->name('admin.audit.show');
    Route::get('audit/security', [AuditController::class, 'securityLogs'])->name('admin.audit.security');
    Route::get('audit/admin-actions', [AuditController::class, 'adminActionLogs'])->name('admin.audit.admin');
    Route::get('audit/statistics', [AuditController::class, 'statistics'])->name('admin.audit.statistics');
    Route::get('audit/export', [AuditController::class, 'export'])->name('admin.audit.export');
});

// ================== USER ROUTE ==================

Route::middleware(['auth', 'role:user'])->group(function () {
    // HOME langsung tanpa prefix
    Route::get('/home', [UserController::class, 'HomeUser'])->name('home');
    Route::get('/search-kitabs', [UserController::class, 'searchAjax'])->name('search.ajax');


    Route::get('/kitab/view/{id_kitab}', [UserController::class, 'view'])->name('kitab.view');
    Route::get('/kitab/read/{id_kitab}', [KitabReaderController::class, 'show'])->name('kitab.read');
    Route::post('/kitab/read/{id_kitab}/progress', [KitabReaderController::class, 'saveProgress'])->name('kitab.read.progress');
    Route::get('/kitab/read/{id_kitab}/markers', [KitabReaderController::class, 'indexMarkers'])->name('kitab.read.markers.index');
    Route::post('/kitab/read/{id_kitab}/markers', [KitabReaderController::class, 'storeMarker'])->name('kitab.read.markers.store');
    Route::put('/kitab/read/{id_kitab}/markers/{bookmarkId}', [KitabReaderController::class, 'updateMarker'])->name('kitab.read.markers.update');
    Route::delete('/kitab/read/{id_kitab}/markers/{bookmarkId}', [KitabReaderController::class, 'destroyMarker'])->name('kitab.read.markers.destroy');
    Route::get('/kitab/download/{id_kitab}', [UserController::class, 'download'])->name('kitab.download');
    Route::post('/kitab/{id_kitab}/comment', [UserController::class, 'store'])->name('kitab.comment');
    Route::get('/kitab/{id_kitab}/comments/fetch', [UserController::class, 'fetchComments'])->name('kitab.comments.fetch');
    Route::delete('/comment/{id}', [UserController::class, 'destroy'])->name('comment.destroy');
    
    // Rating System
    Route::post('/kitab/{id_kitab}/rate', [UserController::class, 'rate'])->name('kitab.rate');
    
    Route::post('/kitab/{id_kitab}/view', [KategoriController::class, 'incrementView'])->name('kitab.incrementView');
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::get('/kategori/filter', [KategoriController::class, 'filter'])->name('kategori.filter');
    Route::post('/bookmark/{id_kitab}', [KategoriController::class, 'toggleBookmark'])->name('bookmark.toggle');

   // === BOOKMARK ===
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/kitab/bookmark/{id_kitab}', [BookmarkController::class, 'store'])->name('kitab.bookmark');
    Route::delete('/kitab/bookmark/delete/{id_kitab}', [BookmarkController::class, 'destroy'])->name('kitab.bookmark.delete');
    Route::delete('/bookmarks/clear', [BookmarkController::class, 'destroyAll'])->name('bookmarks.clear');

    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::delete('/history/clear', [HistoryController::class, 'clear'])->name('history.clear');

    // === NOTIFICATIONS ===
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Reading Notes
    Route::get('/reading-notes', [ReadingNoteController::class, 'index'])->name('reading-notes.index');
    Route::get('/reading-notes/create', [ReadingNoteController::class, 'create'])->name('reading-notes.create');
    Route::post('/reading-notes', [ReadingNoteController::class, 'store'])->name('reading-notes.store');
    Route::get('/reading-notes/{id}/edit', [ReadingNoteController::class, 'edit'])->name('reading-notes.edit');
    Route::put('/reading-notes/{id}', [ReadingNoteController::class, 'update'])->name('reading-notes.update');
    Route::delete('/reading-notes/{id}', [ReadingNoteController::class, 'destroy'])->name('reading-notes.destroy');

    // Reading Goals & Statistics
    Route::get('/reading-goals', [ReadingInsightsController::class, 'goals'])->name('reading-goals.index');
    Route::put('/reading-goals', [ReadingInsightsController::class, 'updateGoals'])->name('reading-goals.update');
    Route::get('/reading-statistics', [ReadingInsightsController::class, 'statistics'])->name('reading-statistics.index');
    Route::get('/reading-leaderboard', [ReadingInsightsController::class, 'leaderboard'])->name('reading-leaderboard.index');

    // My Account
    Route::get('/my-account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/my-account/update', [AccountController::class, 'update'])->name('user.update');
});


