<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Bookmark;
use App\Models\Kitab;

class BookmarkController extends Controller
{
    // === TAMPILKAN SEMUA BOOKMARK USER ===
    public function index()
    {
        $user = Auth::user();

        // Ambil semua kitab yang dibookmark user ini
        $bookmarks = Bookmark::where('user_id', $user->id)
            ->where('bookmark_type', 'kitab')
            ->with('kitab') // relasi ke tabel kitab
            ->latest()
            ->get();

        return view('Bookmark', compact('bookmarks'));
    }

    // === TAMBAH BOOKMARK (TOGGLE MODE) ===
    public function store($id_kitab)
    {
        $userId = Auth::id();

        // Cek apakah sudah di-bookmark sebelumnya
        $exists = Bookmark::where('user_id', $userId)
                        ->where('id_kitab', $id_kitab)
                        ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'exists',
                'message' => 'Kitab sudah ada di bookmark!'
            ], 200);
        }

        // Simpan ke database
        Bookmark::create([
            'user_id' => $userId,
            'id_kitab' => $id_kitab,
            'bookmark_type' => 'kitab',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark berhasil ditambahkan!'
        ], 201);
    }

    


    // === HAPUS SEMUA BOOKMARK USER ===
    public function destroyAll()
    {
        $user = Auth::user();
        Bookmark::where('user_id', $user->id)->delete();

        return back()->with('success', 'Semua bookmark berhasil dihapus.');
    }

    // === HAPUS SATU BOOKMARK (optional) ===
    public function destroy($id_kitab)
    {
        $user = Auth::user();
        Bookmark::where('user_id', $user->id)
            ->where('id_kitab', $id_kitab)
            ->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bookmark dihapus.'
            ]);
        }

        return back()->with('success', 'Bookmark dihapus.');
    }
}
