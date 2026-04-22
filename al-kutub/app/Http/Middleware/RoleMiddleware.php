<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Cek kalau belum login
        if (!Auth::check()) {
            if ($this->expectsApiJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }
            return redirect('login');
        }

        // Ambil user login
        $user = Auth::user();

        // Cocokkan role
        if ($user->role !== $role) {
            if ($this->expectsApiJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            // Bisa redirect sesuai kebutuhan, misal:
            if ($user->role === 'admin') {
                return redirect('/admin/home');
            } elseif ($user->role === 'user') {
                return redirect('/home');
            } else {
                abort(403, 'Unauthorized access.');
            }
        }

        return $next($request);
    }

    private function expectsApiJson(Request $request): bool
    {
        if ($request->is('api/*')) {
            return true;
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return true;
        }

        $acceptHeader = strtolower((string) $request->header('Accept', ''));
        $xRequestedWith = strtolower((string) $request->header('X-Requested-With', ''));

        return strpos($acceptHeader, 'application/json') !== false
            || $xRequestedWith === 'xmlhttprequest';
    }
}
