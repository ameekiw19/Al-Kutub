<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kitab;
use App\Models\Comment;
use App\Models\Bookmark;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use App\Models\DownloadedKitab;
use App\Models\History;
use App\Services\KitabTranscriptService;

class UserController extends Controller
{
    // 🏠 Halaman Home User
   public function HomeUser(Request $request)
    {
        $search = $request->input('search');
        $kategoriFilter = $request->input('kategori');
        $penulisFilter = $request->input('penulis');
        $bahasaFilter = $request->input('bahasa');
        $sortFilter = $request->input('sort', 'newest');

        $query = Kitab::query();

        // 📌 filter kategori
        if ($kategoriFilter) {
            $query->where('kategori', $kategoriFilter);
        }

        // 📌 filter penulis
        if ($penulisFilter) {
            $query->where('penulis', $penulisFilter);
        }

        // 📌 filter bahasa
        if ($bahasaFilter) {
            $query->where('bahasa', $bahasaFilter);
        }

        // 🔍 filter search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                ->orWhere('penulis', 'like', "%{$search}%")
                ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // 🔄 Sorting
        switch ($sortFilter) {
            case 'oldest':
                $query->oldest();
                break;
            case 'title_asc':
                $query->orderBy('judul', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('judul', 'desc');
                break;
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            case 'downloads':
                $query->orderBy('downloads', 'desc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        // 📚 hasil kitab (dengan pagination)
        $kitab = $query->paginate(12)->withQueryString();

        // 💥 kitab populer
        $populer = Kitab::all()
            ->filter(fn($k) => is_array($k->viewed_by) && count($k->viewed_by) >= 2)
            ->sortByDesc(fn($k) => count($k->viewed_by))
            ->take(10);

        // ⭐ bookmark user
        $bookmarkedIds = auth()->check()
            ? Bookmark::where('user_id', auth()->id())
                ->where('bookmark_type', 'kitab')
                ->pluck('id_kitab')->toArray()
            : [];

        // 📖 Lanjutkan Membaca (History terakhir)
        $history = [];
        $rekomendasi = collect();
        if (auth()->check()) {
            $history = History::where('user_id', auth()->id())
                ->with('kitab')
                ->latest('last_read_at')
                ->take(5)
                ->get();

            // 📌 Rekomendasi: kategori dari history → rating tertinggi → views tertinggi
            $historyIds = $history->pluck('kitab_id')->filter()->unique()->values()->all();
            $kategoriDariHistory = $history->pluck('kitab.kategori')->filter()->unique()->values()->all();

            $rekomendasi = Kitab::query()
                ->when(count($kategoriDariHistory) > 0, function ($q) use ($kategoriDariHistory) {
                    $q->whereIn('kategori', $kategoriDariHistory);
                })
                ->when(count($historyIds) > 0, function ($q) use ($historyIds) {
                    $q->whereNotIn('id_kitab', $historyIds);
                })
                ->inRandomOrder()
                ->take(4)
                ->get();

            if ($rekomendasi->count() < 8) {
                $need = 8 - $rekomendasi->count();
                $excludeIds = $rekomendasi->pluck('id_kitab')->merge($historyIds)->unique()->all();
                $subSelect = '(SELECT COALESCE(AVG(rating), 0) FROM ratings WHERE ratings.id_kitab = kitab.id_kitab)';
                $topRated = Kitab::query()
                    ->selectRaw("kitab.*, {$subSelect} as avg_rating")
                    ->when(count($excludeIds) > 0, fn($q) => $q->whereNotIn('kitab.id_kitab', $excludeIds))
                    ->orderByRaw("{$subSelect} DESC")
                    ->take($need)
                    ->get();

                $rekomendasi = $rekomendasi->merge($topRated);
            }

            if ($rekomendasi->count() < 8) {
                $excludeIds = $rekomendasi->pluck('id_kitab')->merge($historyIds)->unique()->all();
                $topViews = Kitab::whereNotIn('id_kitab', $excludeIds)
                    ->orderByDesc('views')
                    ->take(8 - $rekomendasi->count())
                    ->get();
                $rekomendasi = $rekomendasi->merge($topViews);
            }

            $rekomendasi = $rekomendasi->unique('id_kitab')->take(8);
        } else {
            // Guest: top rating + top views
            $subSelect = '(SELECT COALESCE(AVG(rating), 0) FROM ratings WHERE ratings.id_kitab = kitab.id_kitab)';
            $rekomendasi = Kitab::query()
                ->selectRaw("kitab.*, {$subSelect} as avg_rating")
                ->orderByRaw("{$subSelect} DESC")
                ->orderByDesc('kitab.views')
                ->take(8)
                ->get();
        }

        return view('HomeUser', compact(
            'kitab',
            'populer',
            'bookmarkedIds',
            'kategoriFilter',
            'history',
            'rekomendasi'
        ));
    }


    public function searchAjax(Request $request)
    {
        $search = $request->get('query');

        $results = Kitab::where('judul', 'like', "%{$search}%")
            ->orWhere('penulis', 'like', "%{$search}%")
            ->take(5)
            ->get(['id_kitab', 'judul', 'penulis', 'cover']);

        return response()->json($results);
    }


    // Filter kategori khusus
    public function FilterKategori($kategori)
    {
        $kitab = Kitab::where('kategori', $kategori)->latest()->paginate(12)->withQueryString();
        $kategoriList = Kitab::select('kategori')->distinct()->pluck('kategori');

        // 💥 ambil 10 kitab populer juga
        $populer = Kitab::all()
                    ->filter(fn($k) => is_array($k->viewed_by) && count($k->viewed_by) >= 2)
                    ->sortByDesc(fn($k) => count($k->viewed_by))
                    ->take(10);

        $bookmarkedIds = auth()->check()
            ? Bookmark::where('user_id', auth()->id())->where('bookmark_type', 'kitab')->pluck('id_kitab')->toArray()
            : [];
        $history = [];
        $rekomendasi = collect();
        if (auth()->check()) {
            $history = History::where('user_id', auth()->id())->with('kitab')->latest('last_read_at')->take(5)->get();
        }

        return view('HomeUser', [
            'kitab' => $kitab,
            'kategori' => $kategoriList,
            'selectedKategori' => $kategori,
            'populer' => $populer,
            'bookmarkedIds' => $bookmarkedIds,
            'history' => $history,
            'rekomendasi' => $rekomendasi,
        ]);
    }


    // 🧐 View kitab detail
    public function view(KitabTranscriptService $transcriptService, $id_kitab)
    {
        $kitab = Kitab::findOrFail($id_kitab);
        $user_id = Auth::id();

        // pastikan viewed_by adalah array
        $viewedBy = $kitab->viewed_by;
        if (is_string($viewedBy)) {
            $viewedBy = json_decode($viewedBy, true);
        }
        $viewedBy = is_array($viewedBy) ? $viewedBy : [];

        if (!in_array($user_id, $viewedBy)) {
            $viewedBy[] = $user_id;
            $kitab->viewed_by = $viewedBy;
            $kitab->increment('views'); // auto update DB 
            $kitab->save(); // simpan updated array viewed_by
        }

        // ✨ Simpan ke history bacaan
        if ($user_id) {
            \App\Models\History::updateOrCreate(
                [
                    'user_id' => $user_id,
                    'kitab_id' => $id_kitab
                ],
                [
                    'last_read_at' => now()
                ]
            );
        }

        $related = Kitab::where('kategori', $kitab->kategori)
                        ->where('id_kitab', '!=', $id_kitab)
                        ->take(3)
                        ->get();

        $komentar = Comment::where('id_kitab', $id_kitab)
                        ->with('user')
                        ->latest()
                        ->get();

        $ratingsByUser = Rating::where('id_kitab', $id_kitab)
            ->pluck('rating', 'user_id');

        $komentar->transform(function ($comment) use ($ratingsByUser) {
            $comment->setAttribute('user_rating', (int) ($ratingsByUser[$comment->user_id] ?? 0));
            return $comment;
        });

        $isBookmarked = false;
        $userRating = 0;

        if ($user_id) {
            $isBookmarked = Bookmark::where('user_id', $user_id)
                ->where('id_kitab', $id_kitab)
                ->exists();
            
            $rating = Rating::where('user_id', $user_id)
                ->where('id_kitab', $id_kitab)
                ->first();
            $userRating = $rating ? $rating->rating : 0;
        }

        $averageRating = $kitab->averageRating();
        $ratingsCount = $kitab->ratingsCount();
        $transcriptPayload = $transcriptService->buildPayload($kitab);

        return view('ViewKitab', compact('kitab', 'related', 'komentar', 'isBookmarked', 'userRating', 'averageRating', 'ratingsCount', 'transcriptPayload'));
    }


    // Simpan komentar baru (AJAX)
    public function store(Request $request, $id_kitab)
    {
        $request->validate([
            'isi_komentar' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);
        $kitab = Kitab::findOrFail($id_kitab);

        $comment = Comment::create([
            'id_kitab' => $id_kitab,
            'user_id' => Auth::id(),
            'isi_comment' => $request->isi_komentar,
        ]);

        // Sinkronkan rating dari form komentar agar nilainya konsisten.
        $userRating = 0;
        $ratingValue = $request->filled('rating') ? (int) $request->input('rating') : null;
        if ($ratingValue !== null) {
            Rating::updateOrCreate(
                ['user_id' => Auth::id(), 'id_kitab' => $id_kitab],
                ['rating' => $ratingValue]
            );
            $userRating = $ratingValue;
        } else {
            $existingRating = Rating::where('user_id', Auth::id())
                ->where('id_kitab', $id_kitab)
                ->first();
            $userRating = $existingRating ? (int) $existingRating->rating : 0;
        }

        if (!($request->ajax() || $request->expectsJson())) {
            return redirect()
                ->route('kitab.view', $id_kitab)
                ->with('success', 'Ulasan berhasil dikirim.');
        }

        return response()->json([
            'success' => true,
            'comment' => [
                'username' => $comment->user->username,
                'avatar' => strtoupper(substr($comment->user->username, 0, 1)),
                'text' => $comment->isi_comment,
                'date' => $comment->created_at->diffForHumans(),
                'rating' => $userRating,
            ],
            'myRating' => $userRating,
            'averageRating' => $kitab->averageRating(),
            'ratingsCount' => $kitab->ratingsCount(),
        ]);
    }

    // Fetch comments untuk real-time updates
    public function fetchComments($id_kitab)
    {
        $ratingsByUser = Rating::where('id_kitab', $id_kitab)
            ->pluck('rating', 'user_id');

        $comments = Comment::where('id_kitab', $id_kitab)
            ->with(['user', 'kitab'])
            ->latest()
            ->get()
            ->map(function ($comment) use ($ratingsByUser) {
                return [
                    'id' => $comment->id_comment,
                    'username' => $comment->user->username,
                    'avatar' => strtoupper(substr($comment->user->username, 0, 1)),
                    'text' => $comment->isi_comment,
                    'date' => $comment->created_at->diffForHumans(),
                    'rating' => (int) ($ratingsByUser[$comment->user_id] ?? 0),
                    'created_at' => $comment->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'comments' => $comments,
            'total' => $comments->count(),
        ]);
    }

    // Download PDF
    public function download($id_kitab)
    {
        $kitab = Kitab::findOrFail($id_kitab);
        $path = public_path('pdf/' . $kitab->file_pdf);

        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // 📥 Catat Download
        if (Auth::check()) {
             DownloadedKitab::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'kitab_id' => $id_kitab
                ],
                [
                    'file_path' => 'pdf/' . $kitab->file_pdf,
                    'file_size' => filesize($path),
                    'downloaded_at' => now(),
                    'last_accessed_at' => now(),
                    'is_cached' => false,
                    'device_info' => json_encode(['agent' => request()->header('User-Agent')])
                ]
            );
        }

        // Increment download count di kitab
        $kitab->increment('downloads');
        
        \App\Models\DownloadLog::create([
            'kitab_id' => $id_kitab,
            'user_id' => Auth::id() ?: null,
        ]);

        return response()->download($path, $kitab->judul . '.pdf');
    }

    // ⭐ Rate Kitab (Web)
    public function rate(Request $request, $id_kitab)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $kitab = Kitab::findOrFail($id_kitab);
        
        // Update user rating
        $ratingValue = (int) $request->input('rating');
        $rating = Rating::updateOrCreate(
            ['user_id' => Auth::id(), 'id_kitab' => $id_kitab],
            ['rating' => $ratingValue]
        );

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas penilaian Anda!',
            'myRating' => $rating->rating,
            'averageRating' => $kitab->averageRating(),
            'ratingsCount' => $kitab->ratingsCount(),
        ]);
    }
}
