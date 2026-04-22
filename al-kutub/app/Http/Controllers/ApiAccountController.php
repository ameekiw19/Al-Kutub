<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\History;
use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\User;

class ApiAccountController extends Controller
{
    /**
     * Tampilan web (tetap pakai view)
     */
    public function edit()
    {
        $user = Auth::user();

        $kitabDibaca = History::where('user_id', $user->id)->count();
        $bookmark = Bookmark::where('user_id', $user->id)->count();
        $komentar = Comment::where('user_id', $user->id)->count();

        return view('AccountUser', compact('user', 'kitabDibaca', 'bookmark', 'komentar'));
    }

    /**
     * API: Get data account user lengkap (profile + statistik + aktivitas)
     * GET /api/account
     */
    public function getAccount(Request $request)
    {
        $user = $request->user();

        // Hitung statistik
        $totalKitabDibaca = History::where('user_id', $user->id)
            ->distinct('kitab_id')
            ->count('kitab_id');
        
        $totalBookmark = Bookmark::where('user_id', $user->id)
            ->where('bookmark_type', 'kitab')
            ->count();
        
        $totalKomentar = Comment::where('user_id', $user->id)->count();

        // Ambil riwayat baca terbaru (5 terakhir)
        $historyTerbaru = History::where('user_id', $user->id)
            ->with(['kitab:id_kitab,judul,cover'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data akun berhasil diambil',
            'data' => [
                'profile' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'deskripsi' => $user->deskripsi ?? null,
                    'role' => $user->role,
                    'bergabung_sejak' => $user->created_at->format('d M Y'),
                ],
                'statistik' => [
                    'kitab_dibaca' => $totalKitabDibaca,
                    'bookmark' => $totalBookmark,
                    'komentar' => $totalKomentar,
                ],
                'aktivitas_terbaru' => $historyTerbaru
            ]
        ], 200);
    }

    /**
     * API: Get riwayat baca lengkap
     * GET /api/account/history
     */
    public function getHistory(Request $request)
    {
        $user = $request->user();

        $history = History::where('user_id', $user->id)
            ->with(['kitab:id_kitab,judul,cover,penulis'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat baca berhasil diambil',
            'data' => $history
        ], 200);
    }

    /**
     * API: Get daftar bookmark
     * GET /api/account/bookmarks
     */
    public function getBookmarks(Request $request)
    {
        $user = $request->user();

        $bookmarks = Bookmark::where('user_id', $user->id)
            ->where('bookmark_type', 'kitab')
            ->with(['kitab:id_kitab,judul,cover,penulis'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Bookmark berhasil diambil',
            'data' => $bookmarks
        ], 200);
    }

    /**
     * API: Get semua komentar user
     * GET /api/account/comments
     */
    public function getComments(Request $request)
    {
        $user = $request->user();

        $comments = Comment::where('user_id', $user->id)
            ->with(['kitab:id_kitab,judul'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil diambil',
            'data' => $comments
        ], 200);
    }

    /**
     * API: Update profile user
     * PUT /api/account
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'deskripsi' => 'nullable|string|max:500',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $validated['password'] = bcrypt($request->password);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => [
                'profile' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'deskripsi' => $user->deskripsi ?? null,
                    'role' => $user->role,
                    'bergabung_sejak' => $user->created_at->format('d M Y'),
                ],
            ]
        ], 200);
    }

    /**
     * API: Logout user
     * POST /api/account/logout
     */
    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ], 200);
    }
}