<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kitab;
use App\Models\Bookmark;
use App\Models\CategoryKatalog;
use Illuminate\Support\Facades\Auth;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = CategoryKatalog::getActiveSlugs();
        if (empty($kategori)) {
            $kategori = ['aqidah', 'tauhid', 'fiqih', 'hadis', 'tafsir', 'bahasa-arab'];
        }

        $kitab = Kitab::latest()->paginate(12)->withQueryString();
        $bahasa = Kitab::select('bahasa')->distinct()->pluck('bahasa');

        $bookmarkedIds = [];
        if (auth()->check()) {
            $bookmarkedIds = auth()->user()->bookmarks()->pluck('id_kitab')->toArray();
        }

        return view('Kategori', compact('kategori', 'kitab', 'bahasa', 'bookmarkedIds'));
    }

    public function filter(Request $request)
    {
        $kategori = $request->input('kategori');
        $bahasa = $request->input('bahasa');

        $query = Kitab::query();

        if ($kategori) $query->where('kategori', $kategori);
        if ($bahasa) $query->where('bahasa', $bahasa);

        $perPage = min((int) $request->get('per_page', 12), 50);
        $kitab = $query->latest()->paginate($perPage);

        $bookmarkedIds = [];
        if (auth()->check()) {
            $bookmarkedIds = auth()->user()->bookmarks()->pluck('id_kitab')->toArray();
        }

        return response()->json([
            'kitab' => $kitab->items(),
            'bookmarkedIds' => $bookmarkedIds,
            'pagination' => [
                'current_page' => $kitab->currentPage(),
                'last_page' => $kitab->lastPage(),
                'per_page' => $kitab->perPage(),
                'total' => $kitab->total(),
            ]
        ]);
    }

    // 💾 Tambah / hapus bookmark
    public function toggleBookmark($id_kitab)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();
        $existing = Bookmark::where('id_user', $user->id)
                            ->where('id_kitab', $id_kitab)
                            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'removed']);
        } else {
            Bookmark::create([
                'id_user' => $user->id,
                'id_kitab' => $id_kitab
            ]);
            return response()->json(['status' => 'added']);
        }
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