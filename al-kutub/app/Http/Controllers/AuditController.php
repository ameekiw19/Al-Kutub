<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Kitab;

class AuditController extends Controller
{
    /**
     * Display audit logs for admin
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by security actions
        if ($request->has('security_only') && $request->security_only) {
            $securityActions = ['login', 'logout', 'login_failed', '2fa_enabled', '2fa_disabled', '2fa_verified', 'password_changed'];
            $query->whereIn('action', $securityActions);
        }

        $auditLogs = $query->paginate(50);
        $users = User::all();
        
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit.index', compact('auditLogs', 'users', 'actions'));
    }

    /**
     * Show audit log details
     */
    public function show($id)
    {
        $auditLog = AuditLog::with(['user', 'model'])->findOrFail($id);

        return view('admin.audit.show', compact('auditLog'));
    }

    /**
     * Get security audit logs
     */
    public function securityLogs(Request $request)
    {
        $query = AuditLog::with('user')
            ->whereIn('action', ['login', 'logout', 'login_failed', '2fa_enabled', '2fa_disabled', '2fa_verified', 'password_changed'])
            ->latest();

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $securityLogs = $query->paginate(50);

        return view('admin.audit.security', compact('securityLogs'));
    }

    /**
     * Get admin action logs
     */
    public function adminActionLogs(Request $request)
    {
        $query = AuditLog::with('user')
            ->whereIn('action', ['kitab_created', 'kitab_updated', 'kitab_deleted', 'user_created', 'user_updated', 'user_deleted', 'role_updated', 'notification_sent', 'comment_deleted'])
            ->latest();

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $adminLogs = $query->paginate(50);

        return view('admin.audit.admin', compact('adminLogs'));
    }

    /**
     * Export audit logs to CSV
     */
    public function export(Request $request)
    {
        $query = AuditLog::with('user');

        // Apply filters
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->get();

        $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'ID', 'User', 'Action', 'Model Type', 'Model ID', 
                'IP Address', 'User Agent', 'Created At', 'Details'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->username : 'N/A',
                    $log->action,
                    $log->model_type,
                    $log->model_id,
                    $log->ip_address,
                    $log->user_agent ? substr($log->user_agent, 0, 100) . '...' : 'N/A',
                    $log->created_at->format('Y-m-d H:i:s'),
                    json_encode($log->new_values)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get audit statistics
     */
    public function statistics()
    {
        $stats = [
            'total_logs' => AuditLog::count(),
            'today_logs' => AuditLog::whereDate('created_at', today())->count(),
            'security_events' => AuditLog::whereIn('action', ['login', 'logout', 'login_failed', '2fa_enabled', '2fa_disabled', '2fa_verified'])->count(),
            'admin_actions' => AuditLog::whereIn('action', ['kitab_created', 'kitab_updated', 'kitab_deleted', 'user_created', 'user_updated', 'user_deleted', 'role_updated'])->count(),
            'failed_logins' => AuditLog::where('action', 'login_failed')->count(),
            'successful_logins' => AuditLog::where('action', 'login')->count(),
            'users_with_2fa' => User::whereHas('twoFactorAuth', function($query) {
                $query->where('is_enabled', true);
            })->count(),
        ];

        // Recent activity
        $recentActivity = AuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Activity by day for last 7 days
        $activityByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $activityByDay[$date] = AuditLog::whereDate('created_at', $date)->count();
        }

        return view('admin.audit.statistics', compact('stats', 'recentActivity', 'activityByDay'));
    }
}
