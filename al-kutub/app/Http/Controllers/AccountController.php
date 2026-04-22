<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\History;
use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\AuditLog;

class AccountController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        // Hitung statistik berdasarkan user
        $kitabDibaca = History::where('user_id', $user->id)->count();
        $bookmark = Bookmark::where('user_id', $user->id)->count();
        $komentar = Comment::where('user_id', $user->id)->count();

        // Kirim ke view
        return view('AccountUser', compact('user', 'kitabDibaca', 'bookmark', 'komentar'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
        ]);

        // Check if password is being changed
        $passwordChanged = false;
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            $validated['password'] = bcrypt($request->password);
            $passwordChanged = true;
        }

        // Store old values for audit
        $oldValues = [
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'deskripsi' => $user->deskripsi,
        ];

        $emailChanged = isset($validated['email']) && $validated['email'] !== $user->email;

        $user->update($validated);

        if ($emailChanged && $user->requiresEmailVerification()) {
            $user->email_verified_at = null;
            $user->is_verified_by_admin = false;
            $user->admin_verified_at = null;
            $user->admin_verified_by = null;
            $user->save();
        }

        // Log the action
        if ($passwordChanged) {
            AuditLog::logAuth('password_changed', $user->id, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        AuditLog::log('profile_updated', $user, $user->id, $oldValues, $validated);

        if ($emailChanged && $user->requiresEmailVerification()) {
            AuditLog::logAuth('email_changed_admin_reapproval_required', $user->id, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'status',
                'Email berubah. Akun menunggu verifikasi admin sebelum bisa login kembali.'
            );
        }

        if ($emailChanged && !$user->requiresEmailVerification()) {
            return redirect('/my-account')->with('success', 'Email admin berhasil diperbarui tanpa verifikasi ulang.');
        }

        return redirect('/my-account')->with('success', 'Profil berhasil diperbarui!');
    }
}
