<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Bookmark;
use App\Models\Kitab;

class ApiBookmarkController extends Controller
{
    /**
     * GET /api/bookmarks
     * Tampilkan semua bookmark user yang sedang login
     */
    public function index()
    {
        $user = Auth::user();

        $bookmarks = Bookmark::where('user_id', $user->id)
            ->where('bookmark_type', 'kitab') // Only fetch book collections
            ->whereHas('kitab', function ($query) {
                $query->published();
            })
            ->with(['kitab' => function($query) {
                $query->select(
                    'id_kitab', 
                    'judul',
                    'penulis',
                    'deskripsi',
                    'kategori',
                    'bahasa',
                    'file_pdf',
                    'cover',    
                    'views',
                    'downloads',
                    'viewed_by'
                );
            }])
            ->latest('created_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmarks retrieved successfully',
            'total' => $bookmarks->count(),
            'data' => $bookmarks->map(function($bookmark) {
                return [
                    'id_bookmark' => $bookmark->id_bookmark,
                    'user_id' => $bookmark->user_id,
                    'id_kitab' => $bookmark->id_kitab,
                    'created_at' => $bookmark->created_at,
                    'updated_at' => $bookmark->updated_at,
                    'kitab' => $bookmark->kitab ? [
                        'idKitab' => $bookmark->kitab->id_kitab, // Gunakan idKitab untuk Android
                        'judul' => $bookmark->kitab->judul,
                        'penulis' => $bookmark->kitab->penulis,
                        'deskripsi' => $bookmark->kitab->deskripsi,
                        'kategori' => $bookmark->kitab->kategori,
                        'bahasa' => $bookmark->kitab->bahasa,
                        'filePdf' => $bookmark->kitab->file_pdf, // Gunakan filePdf untuk Android
                        'cover' => $bookmark->kitab->cover,
                        'views' => $bookmark->kitab->views,
                        'downloads' => $bookmark->kitab->downloads,
                    ] : null
                ];
            })
        ], 200);
    }

    /**
     * POST /api/bookmarks/{id_kitab}/toggle
     * Toggle bookmark (tambah/hapus)
     */
    public function toggle($id_kitab)
    {
        $userId = Auth::id();

        // Cek apakah kitab ada
        $kitab = Kitab::published()->where('id_kitab', $id_kitab)->first();
        if (!$kitab) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kitab tidak ditemukan'
            ], 404);
        }

        // Cek apakah sudah di-bookmark
        $bookmark = Bookmark::where('user_id', $userId)
                        ->where('id_kitab', $id_kitab)
                        ->first();

        if ($bookmark) {
            // Hapus bookmark
            $bookmark->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Bookmark berhasil dihapus',
                'action' => 'removed',
                'is_bookmarked' => false
            ], 200);
        } else {
            // Tambah bookmark
            $newBookmark = Bookmark::create([
                'user_id' => $userId,
                'id_kitab' => $id_kitab,
                'bookmark_type' => 'kitab', // Default type for favorite
                'page_number' => null,
                'page_title' => null,
                'notes' => null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Bookmark berhasil ditambahkan',
                'action' => 'added',
                'is_bookmarked' => true,
                'data' => [
                    'id_bookmark' => $newBookmark->id_bookmark,
                    'user_id' => $newBookmark->user_id,
                    'id_kitab' => $newBookmark->id_kitab,
                    'created_at' => $newBookmark->created_at
                ]
            ], 201);
        }
    }

    /**
     * POST /api/bookmarks
     * Tambah bookmark (non-toggle)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_kitab' => 'required|exists:kitab,id_kitab'
        ]);

        $userId = Auth::id();
        $id_kitab = $request->id_kitab;

        if (!Kitab::published()->where('id_kitab', (int) $id_kitab)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kitab belum dipublikasikan atau tidak ditemukan',
            ], 422);
        }

        // Cek apakah sudah di-bookmark
        $exists = Bookmark::where('user_id', $userId)
                        ->where('id_kitab', $id_kitab)
                        ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'exists',
                'message' => 'Kitab sudah ada di bookmark',
                'is_bookmarked' => true
            ], 200);
        }

        // Simpan ke database
        $bookmark = Bookmark::create([
            'user_id' => $userId,
            'id_kitab' => $id_kitab,
            'bookmark_type' => 'kitab'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark berhasil ditambahkan',
            'is_bookmarked' => true,
            'data' => [
                'id_bookmark' => $bookmark->id_bookmark,
                'user_id' => $bookmark->user_id,
                'id_kitab' => $bookmark->id_kitab,
                'created_at' => $bookmark->created_at
            ]
        ], 201);
    }

    /**
     * GET /api/bookmarks/check/{id_kitab}
     * Cek apakah kitab sudah di-bookmark
     */
    public function check($id_kitab)
    {
        $userId = Auth::id();

        $bookmark = Bookmark::where('user_id', $userId)
                        ->where('id_kitab', $id_kitab)
                        ->first();

        return response()->json([
            'status' => 'success',
            'is_bookmarked' => $bookmark ? true : false,
            'bookmark_id' => $bookmark ? $bookmark->id_bookmark : null
        ], 200);
    }

    /**
     * DELETE /api/bookmarks/{id_kitab}
     * Hapus satu bookmark berdasarkan id_kitab
     */
    public function destroy($id_kitab)
    {
        $user = Auth::user();
        
        $bookmark = Bookmark::where('user_id', $user->id)
            ->where('id_kitab', $id_kitab)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Bookmark berhasil dihapus'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Bookmark tidak ditemukan'
        ], 404);
    }

    /**
     * DELETE /api/bookmarks/clear-all
     * Hapus semua bookmark user
     */
    public function destroyAll()
    {
        $user = Auth::user();
        $count = Bookmark::where('user_id', $user->id)->count();
        
        if ($count > 0) {
            Bookmark::where('user_id', $user->id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil menghapus {$count} bookmark",
                'deleted_count' => $count
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Tidak ada bookmark untuk dihapus',
            'deleted_count' => 0
        ], 200);
    }

    /**
     * GET /api/bookmarks/stats
     * Statistik bookmark user
     */
    public function stats()
    {
        $user = Auth::user();
        
        $totalBookmarks = Bookmark::where('user_id', $user->id)->count();
        
        $bookmarksByCategory = Bookmark::where('bookmarks.user_id', $user->id)
            ->join('kitab', 'bookmarks.id_kitab', '=', 'kitab.id_kitab')
            ->select('kitab.kategori', \DB::raw('count(*) as total'))
            ->groupBy('kitab.kategori')
            ->orderBy('total', 'desc')
            ->get();

        $recentBookmarks = Bookmark::where('user_id', $user->id)
            ->with('kitab:id_kitab,judul,penulis,cover')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function($bookmark) {
                return [
                    'id_bookmark' => $bookmark->id_bookmark,
                    'kitab' => $bookmark->kitab,
                    'created_at' => $bookmark->created_at
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_bookmarks' => $totalBookmarks,
                'by_category' => $bookmarksByCategory,
                'recent_bookmarks' => $recentBookmarks
            ]
        ], 200);
    }
}
