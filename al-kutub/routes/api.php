<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ApiAuth;
use App\Http\Controllers\ApiKatalogController;
use App\Http\Controllers\ApiHistoryController;
use App\Http\Controllers\ApiBookmarkController;
use App\Http\Controllers\ApiAccountController;
use App\Http\Controllers\ApiFcmController;
use App\Http\Controllers\ApiNotificationController;
use App\Http\Controllers\ApiTwoFactorController;
use App\Http\Controllers\ApiAuditController;
use App\Http\Controllers\ApiReadingNoteController;
use App\Http\Controllers\ApiPageBookmarkController;
use App\Http\Controllers\Api\KitabTranscriptController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\ThemeController;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
| Semua route API dibungkus dengan prefix v1 untuk versioning.
| Android/klien harus memakai base URL: /api/v1/...
*/

Route::prefix('v1')->group(function () {

// ===== AUTHENTICATION ROUTES (Public) =====
// Apply security middleware to monitor and block suspicious IPs
Route::middleware('security')->group(function () {
    Route::post('/login', [ApiAuth::class, 'login']);
    Route::post('/login/verify-2fa', [ApiAuth::class, 'verify2FA']);
    Route::post('/register', [ApiAuth::class, 'register']);
});

// ===== PUBLIC KITAB ROUTES (No Auth Required) =====
Route::get('/kitab/recommendations', [ApiController::class, 'getRecommendations']); // 💡 Recommendations
Route::get('/kitab', [ApiController::class, 'index']); // Get all kitab
Route::get('/kitab/search', [ApiController::class, 'search']); // 🔍 Search kitab
Route::get('/search/suggestions', [SearchController::class, 'suggestions']); // 🔎 Search suggestions
Route::get('/kitab/{id_kitab}', [ApiController::class, 'show']); // Get kitab detail
Route::get('/kitab/{id_kitab}/transcript', [KitabTranscriptController::class, 'show']); // Get kitab transcript
Route::get('/kitab/{id_kitab}/related', [ApiController::class, 'getRelated']); // Get related kitab
Route::get('/kitab/{id_kitab}/comments', [ApiController::class, 'getComments']); // Get comments (public)

// ===== PUBLIC KATALOG ROUTES =====
Route::get('/katalog', [ApiKatalogController::class, 'index']);
Route::get('/katalog/filter', [ApiKatalogController::class, 'filter']);

// ===== PROTECTED ROUTES (Requires Authentication) =====
Route::middleware('auth:sanctum')->group(function () {
    
    // ===== THEME ROUTES =====
    Route::get('/theme', [ThemeController::class, 'getTheme']); // Get user theme
    Route::post('/theme', [ThemeController::class, 'updateTheme']); // Update user theme
    Route::get('/theme/server', [ThemeController::class, 'getServerTheme']); // Get server theme
    Route::post('/theme/sync', [ThemeController::class, 'syncTheme']); // Sync theme
    
    // User Profile (Legacy)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [ApiAuth::class, 'logout']);
    Route::get('/me', [ApiAuth::class, 'me']);

    // ===== ACCOUNT MANAGEMENT (NEW) =====
    Route::prefix('account')->group(function () {
        Route::get('/', [ApiAccountController::class, 'getAccount']);
        Route::put('/', [ApiAccountController::class, 'updateProfile']);
        Route::get('/history', [ApiAccountController::class, 'getHistory']);
        Route::get('/bookmarks', [ApiAccountController::class, 'getBookmarks']);
        Route::get('/comments', [ApiAccountController::class, 'getComments']);
        Route::post('/logout', [ApiAccountController::class, 'logout']);
    });

    // Kitab Actions
    Route::post('/kitab/{id_kitab}/view', [ApiController::class, 'incrementView']);
    Route::get('/kitab/{id_kitab}/download', [ApiController::class, 'download']);
    
    // Comments
    Route::post('/kitab/{id_kitab}/comment', [ApiController::class, 'storeComment']);
    Route::delete('/comment/{id}', [ApiController::class, 'destroyComment']);
    
    // Ratings
    Route::post('/kitab/{id_kitab}/rate', [ApiController::class, 'rateKitab']);
    Route::get('/kitab/{id_kitab}/my-rating', [ApiController::class, 'getMyRating']);
    
    // History
    Route::get('/history/stats/summary', [ApiHistoryController::class, 'statistics']);
    Route::get('/history', [ApiHistoryController::class, 'index']);
    Route::post('/history', [ApiHistoryController::class, 'store']);
    Route::delete('/historyclearall', [ApiHistoryController::class, 'clearAll']);

    // Search History
    Route::get('/search/history', [SearchController::class, 'history']);
    Route::post('/search/history', [SearchController::class, 'storeHistory']);
    Route::delete('/search/history', [SearchController::class, 'clearHistory']);
    Route::delete('/search/history/{id}', [SearchController::class, 'destroyHistory']);
    
    // ===== FCM NOTIFICATION ROUTES =====
    Route::prefix('fcm')->group(function () {
        Route::post('/token', [ApiFcmController::class, 'saveToken']); // Save FCM token
        Route::delete('/token', [ApiFcmController::class, 'removeToken']); // Remove FCM token (logout)
        Route::post('/test', [ApiFcmController::class, 'testNotification']); // Test notification
        Route::get('/tokens', [ApiFcmController::class, 'getUserTokens']); // Get user's tokens
    });

    // ⬇️ ROUTE DINAMIS PALING BAWAH
    Route::get('/history/{id}', [ApiHistoryController::class, 'show']);
    Route::delete('/history/{id}', [ApiHistoryController::class, 'destroy']);

    // Bookmarks
    Route::get('/bookmarks/stats', [ApiBookmarkController::class, 'stats']);
    Route::get('/bookmarks/check/{id_kitab}', [ApiBookmarkController::class, 'check']);
    Route::get('/bookmarks', [ApiBookmarkController::class, 'index']);
    Route::post('/bookmarks', [ApiBookmarkController::class, 'store']);
    Route::post('/bookmarks/{id_kitab}/toggle', [ApiBookmarkController::class, 'toggle']);
    Route::delete('/bookmarks/clear-all', [ApiBookmarkController::class, 'destroyAll']);
    Route::delete('/bookmarks/{id_kitab}', [ApiBookmarkController::class, 'destroy']);

    // Page Markers (Bookmark Halaman)
    Route::get('/page-bookmarks', [ApiPageBookmarkController::class, 'index']);
    Route::post('/page-bookmarks', [ApiPageBookmarkController::class, 'store']);
    Route::delete('/page-bookmarks/{kitab_id}/{page_number}', [ApiPageBookmarkController::class, 'destroy']);

    // ===== TWO-FACTOR AUTHENTICATION ROUTES =====
    Route::prefix('2fa')->group(function () {
        Route::get('/status', [ApiTwoFactorController::class, 'status']); // Get 2FA status
        Route::post('/setup', [ApiTwoFactorController::class, 'setup']); // Setup 2FA
        Route::post('/enable', [ApiTwoFactorController::class, 'enable']); // Enable 2FA
        Route::post('/disable', [ApiTwoFactorController::class, 'disable']); // Disable 2FA
        Route::post('/verify', [ApiTwoFactorController::class, 'verify']); // Verify 2FA code
        Route::get('/backup-codes', [ApiTwoFactorController::class, 'getBackupCodes']); // Get backup codes
        Route::post('/regenerate-backup-codes', [ApiTwoFactorController::class, 'regenerateBackupCodes']); // Regenerate backup codes
        Route::post('/verify-backup-code', [ApiTwoFactorController::class, 'verifyBackupCode']); // Verify backup code
    });

    // ===== AUDIT LOGGING ROUTES =====
    Route::prefix('audit')->group(function () {
        Route::get('/', [ApiAuditController::class, 'index']); // Get user's audit logs
        Route::get('/security', [ApiAuditController::class, 'securityLogs']); // Get security logs
        Route::get('/stats', [ApiAuditController::class, 'stats']); // Get audit statistics
    });

    // ===== READING NOTES ROUTES =====
    Route::prefix('reading-notes')->group(function () {
        Route::get('/', [ApiReadingNoteController::class, 'index']); // Get user's reading notes
        Route::post('/', [ApiReadingNoteController::class, 'store']); // Create new reading note
        Route::get('/stats', [ApiReadingNoteController::class, 'stats']); // Get reading notes statistics
        Route::get('/{readingNote}', [ApiReadingNoteController::class, 'show']); // Get specific reading note
        Route::put('/{readingNote}', [ApiReadingNoteController::class, 'update']); // Update reading note
        Route::delete('/{readingNote}', [ApiReadingNoteController::class, 'destroy']); // Delete reading note
    });

    // ===== READING GOALS & STREAKS ROUTES =====
    Route::prefix('reading-goals')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ReadingGoalsController::class, 'getGoals']); // Get user's goals
        Route::post('/update-progress', [App\Http\Controllers\Api\ReadingGoalsController::class, 'updateProgress']); // Update progress
        Route::get('/settings', [App\Http\Controllers\Api\ReadingGoalsController::class, 'getGoals']); // Get goals settings
        Route::put('/settings', [App\Http\Controllers\Api\ReadingGoalsController::class, 'updateSettings']); // Update goals settings
        Route::get('/achievements', [App\Http\Controllers\Api\ReadingGoalsController::class, 'getAchievements']); // Get achievements
    });

    Route::prefix('reading-streak')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ReadingGoalsController::class, 'getStreak']); // Get user's streak
        Route::get('/leaderboard', [App\Http\Controllers\Api\ReadingGoalsController::class, 'getLeaderboard']); // Get leaderboard
    });

    // ===== THEME ROUTES =====
    Route::prefix('theme')->group(function () {
        Route::get('/', [ThemeController::class, 'getTheme']); // Get theme preference
        Route::post('/', [ThemeController::class, 'updateTheme']); // Update theme preference
        Route::post('/toggle', [ThemeController::class, 'toggleTheme']); // Toggle theme
    });
    
});

// ===== Admin Kitab AJAX Routes (simple version for testing)
Route::prefix('admin/kitab')->group(function () {
    Route::post('/store', [App\Http\Controllers\Api\AdminKitabControllerSimple::class, 'store']);
    Route::get('/stats', [App\Http\Controllers\Api\AdminKitabControllerSimple::class, 'getStats']);
});

// ===== NOTIFICATION ROUTES (Public for polling) =====
Route::prefix('notifications')->group(function () {
    Route::get('/latest', [App\Http\Controllers\Api\NotificationController::class, 'latest']);
    Route::get('/new-kitabs', [App\Http\Controllers\Api\NotificationController::class, 'newKitabs']);
});

// ===== NOTIFICATION ROUTES (Protected) =====
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/unread-count', [App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
    Route::post('/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
});
Route::get('/notifications', [ApiNotificationController::class, 'index']);
Route::get('/notifications/latest', [ApiNotificationController::class, 'latest']);

}); // end Route::prefix('v1')

/*
|--------------------------------------------------------------------------
| LEGACY ROUTES (Backward compatibility)
|--------------------------------------------------------------------------
| /api/login, /api/kitab, dll tetap berfungsi untuk Android/klien lama.
| Deprecated: update ke /api/v1/...
*/
Route::post('/login', [ApiAuth::class, 'login']);
Route::post('/login/verify-2fa', [ApiAuth::class, 'verify2FA']);
Route::post('/register', [ApiAuth::class, 'register']);

Route::get('/kitab', [ApiController::class, 'index']);
Route::get('/kitab/search', [ApiController::class, 'search']);
Route::get('/kitab/{id_kitab}', [ApiController::class, 'show']);
Route::get('/kitab/{id_kitab}/related', [ApiController::class, 'getRelated']);
Route::get('/kitab/{id_kitab}/comments', [ApiController::class, 'getComments']);
Route::get('/katalog', [ApiKatalogController::class, 'index']);
Route::get('/katalog/filter', [ApiKatalogController::class, 'filter']);
