<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ApiAuditController extends Controller
{
    /**
     * Get user's audit logs
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = AuditLog::where('user_id', $user->id)
            ->with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pagination
        $limit = $request->get('limit', 20);
        $logs = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'logs' => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'has_more' => $logs->hasMorePages(),
                ]
            ]
        ]);
    }

    /**
     * Get security-related logs for the user
     */
    public function securityLogs(Request $request)
    {
        $user = $request->user();
        
        $securityActions = [
            'login', 'login_failed', 'logout', '2fa_enabled', '2fa_disabled', 
            '2fa_verified', '2fa_verification_failed', 'password_changed',
            'backup_codes_regenerated', 'backup_code_used'
        ];

        $query = AuditLog::where('user_id', $user->id)
            ->whereIn('action', $securityActions)
            ->orderBy('created_at', 'desc');

        // Apply date filters
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pagination
        $limit = $request->get('limit', 20);
        $logs = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'logs' => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'has_more' => $logs->hasMorePages(),
                ]
            ]
        ]);
    }

    /**
     * Get audit statistics for the user
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        // Overall stats
        $totalLogs = AuditLog::where('user_id', $user->id)->count();
        $todayLogs = AuditLog::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        // Security stats
        $securityActions = [
            'login', 'login_failed', 'logout', '2fa_enabled', '2fa_disabled', 
            '2fa_verified', 'password_changed'
        ];
        
        $securityLogs = AuditLog::where('user_id', $user->id)
            ->whereIn('action', $securityActions)
            ->count();

        // Recent activity (last 7 days)
        $recentActivity = AuditLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'action', 'created_at', 'ip_address']);

        // Activity by type
        $loginStats = AuditLog::where('user_id', $user->id)
            ->whereIn('action', ['login', 'login_failed'])
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        // 2FA stats
        $twoFactorStats = AuditLog::where('user_id', $user->id)
            ->whereIn('action', ['2fa_enabled', '2fa_disabled', '2fa_verified'])
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        // Daily activity for last 30 days
        $dailyActivity = AuditLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_logs' => $totalLogs,
                    'today_logs' => $todayLogs,
                    'security_logs' => $securityLogs,
                ],
                'login_stats' => [
                    'successful_logins' => $loginStats['login'] ?? 0,
                    'failed_logins' => $loginStats['login_failed'] ?? 0,
                ],
                'two_factor_stats' => [
                    'enabled' => $twoFactorStats['2fa_enabled'] ?? 0,
                    'disabled' => $twoFactorStats['2fa_disabled'] ?? 0,
                    'verified' => $twoFactorStats['2fa_verified'] ?? 0,
                ],
                'recent_activity' => $recentActivity->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'created_at' => $log->created_at->toISOString(),
                        'ip_address' => $log->ip_address,
                    ];
                }),
                'daily_activity' => $dailyActivity,
            ]
        ]);
    }
}
