<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only audit admin actions
        if (auth()->check() && auth()->user()->role === 'admin') {
            $this->auditAdminAction($request, $response);
        }
        
        return $response;
    }

    /**
     * Audit admin actions
     */
    private function auditAdminAction(Request $request, $response)
    {
        $method = $request->method();
        $route = $request->route();
        
        if (!$route) {
            return;
        }

        $routeName = $route->getName();
        $uri = $request->getRequestUri();
        
        // Define actions to audit
        $auditActions = [
            'POST' => [
                'admin.kitab.store' => 'kitab_created',
                'admin.kitab.addkitab' => 'kitab_created',
                'admin.kitab.update' => 'kitab_updated',
                'admin.update-user-role' => 'role_updated',
                'admin.notifications.send' => 'notification_sent',
                '2fa.enable' => '2fa_enabled',
                '2fa.disable' => '2fa_disabled',
            ],
            'PUT' => [
                'admin.updatekitab' => 'kitab_updated',
                'user.update' => 'profile_updated',
            ],
            'DELETE' => [
                'admin.kitab.destroy' => 'kitab_deleted',
                'admin.deletekitab' => 'kitab_deleted',
                'admin.user.delete' => 'user_deleted',
                'admin.comments.delete' => 'comment.delete',
                'kitab.bookmark.delete' => 'bookmark_deleted',
                'history.clear' => 'history_cleared',
            ]
        ];

        // Check if this action should be audited
        if (isset($auditActions[$method][$routeName])) {
            $action = $auditActions[$method][$routeName];
            $model = null;
            $details = [];

            // Get model and details based on action
            switch ($action) {
                case 'kitab_created':
                case 'kitab_updated':
                    $model = \App\Models\Kitab::find($request->route('id_kitab'));
                    $details = $request->only(['judul', 'penulis', 'kategori']);
                    break;
                    
                case 'kitab_deleted':
                    $model = \App\Models\Kitab::find($request->route('id_kitab'));
                    $details = ['deleted_kitab_id' => $request->route('id_kitab')];
                    break;
                    
                case 'user_deleted':
                    $model = \App\Models\User::find($request->route('id'));
                    $details = ['deleted_user_id' => $request->route('id')];
                    break;
                    
                case 'role_updated':
                    $model = \App\Models\User::find($request->route('id'));
                    $details = ['new_role' => $request->role];
                    break;
                    
                case 'notification_sent':
                    $details = $request->only(['title', 'message']);
                    break;
                    
                case 'comment.delete':
                    $details = ['comment_id' => $request->route('id')];
                    break;
            }

            // Log the action
            AuditLog::logAdminAction($action, $model, $details);
        }
    }
}
