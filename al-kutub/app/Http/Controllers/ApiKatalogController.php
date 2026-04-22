<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kitab;
use App\Models\Bookmark;
use Illuminate\Support\Facades\Auth;

class ApiKatalogController extends Controller
{
    /**
     * Menampilkan daftar kategori dan kitab (dengan pagination)
     * GET /api/katalog?page=1&per_page=20
     */
    public function index(Request $request)
    {
        $kategori = [
            'aqidah', 'tauhid', 'fiqih', 'hadis', 'tafsir', 'bahasa-arab', 'qowaid-lughah'
        ];

        $perPage = min((int) $request->get('per_page', 20), 50);
        $kitab = Kitab::latest()->paginate($perPage);
        $bahasa = Kitab::select('bahasa')->distinct()->pluck('bahasa');

        $bookmarkedIds = [];
        if (Auth::check()) {
            $bookmarkedIds = Auth::user()->bookmarks()->pluck('id_kitab')->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kategori' => $kategori,
                'kitab' => $kitab->items(),
                'bahasa' => $bahasa,
                'bookmarkedIds' => $bookmarkedIds
            ],
            'pagination' => [
                'current_page' => $kitab->currentPage(),
                'last_page' => $kitab->lastPage(),
                'per_page' => $kitab->perPage(),
                'total' => $kitab->total(),
            ]
        ], 200);
    }

    /**
     * Filter kitab berdasarkan kategori dan bahasa (dengan pagination)
     * GET /api/katalog/filter?kategori=fiqih&bahasa=arab&page=1&per_page=20
     */
    public function filter(Request $request)
    {
        $kategori = $request->input('kategori');
        $bahasa = $request->input('bahasa');
        $perPage = min((int) $request->get('per_page', 20), 50);

        $query = Kitab::query();

        if ($kategori) {
            $query->where('kategori', $kategori);
        }
        
        if ($bahasa) {
            $query->where('bahasa', $bahasa);
        }

        $kitab = $query->latest()->paginate($perPage);

        $bookmarkedIds = [];
        if (Auth::check()) {
            $bookmarkedIds = Auth::user()->bookmarks()->pluck('id_kitab')->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kitab' => $kitab->items(),
                'bookmarkedIds' => $bookmarkedIds,
                'filter' => [
                    'kategori' => $kategori,
                    'bahasa' => $bahasa
                ]
            ],
            'pagination' => [
                'current_page' => $kitab->currentPage(),
                'last_page' => $kitab->lastPage(),
                'per_page' => $kitab->perPage(),
                'total' => $kitab->total(),
            ]
        ], 200);
    }
    
    // 👁️ Increment view count
    public function incrementView($id_kitab)
    {
        $kitab = Kitab::where('id_kitab', $id_kitab)->firstOrFail();
        
        // Increment views
        $kitab->increment('views');
        
        // Track unique viewers jika user login
        if (Auth::check()) {
            $userId = Auth::id();
            $viewedBy = $kitab->viewed_by;
            if (is_string($viewedBy)) {
                $viewedBy = json_decode($viewedBy, true);
            }
            $viewedBy = is_array($viewedBy) ? $viewedBy : [];
            
            // Tambahkan user ID jika belum ada
            if (!in_array($userId, $viewedBy)) {
                $viewedBy[] = $userId;
                $kitab->viewed_by = $viewedBy;
                $kitab->save();
            }
        }
        
        return response()->json([
            'success' => true,
            'views' => $kitab->views
        ]);
    }
}