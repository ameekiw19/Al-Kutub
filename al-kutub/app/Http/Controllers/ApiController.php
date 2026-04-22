<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kitab;
use App\Models\Comment;
use App\Models\Rating;

class ApiController extends Controller
{
    // GET semua kitab (dengan pagination)
    public function index(Request $request)
    {
        $baseUrl = config('app.url') . '/';
        $perPage = min((int) $request->get('per_page', 20), 50);
        $paginated = Kitab::latest()->paginate($perPage);

        $data = $paginated->map(function($item) use ($baseUrl) {
            return [
                'idKitab' => $item->id_kitab,
                'judul' => $item->judul,
                'penulis' => $item->penulis,
                'cover' => $baseUrl . 'cover/' . $item->cover,
                'kategori' => $item->kategori,
                'deskripsi' => $item->deskripsi,
                'bahasa' => $item->bahasa,
                'filePdf' => $baseUrl . 'pdf/' . $item->file_pdf,
                'views' => $item->views,
                'downloads' => $item->downloads,
                'averageRating' => $item->averageRating(),
                'ratingsCount' => $item->ratingsCount(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua kitab',
            'data' => $data,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        ]);
    }

    // GET detail kitab
    public function show($id_kitab)
    {
        $baseUrl = config('app.url') . '/';
        $item = Kitab::find($id_kitab);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Kitab tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kitab ditemukan',
            'data' => [
                'idKitab' => $item->id_kitab,
                'judul' => $item->judul,
                'penulis' => $item->penulis,
                'cover' => $baseUrl . 'cover/' . $item->cover,
                'kategori' => $item->kategori,
                'deskripsi' => $item->deskripsi,
                'bahasa' => $item->bahasa,
                'filePdf' => $baseUrl . 'pdf/' . $item->file_pdf,
                'views' => $item->views,
                'downloads' => $item->downloads,
                'averageRating' => $item->averageRating(),
                'ratingsCount' => $item->ratingsCount(),
            ]
        ]);
    }

    // 📊 Increment view count (tanpa tracking user)
    public function incrementView($id_kitab)
    {
        $kitab = Kitab::find($id_kitab);

        if (!$kitab) {
            return response()->json([
                'success' => false,
                'message' => 'Kitab tidak ditemukan'
            ], 404);
        }

        $kitab->increment('views');

        return response()->json([
            'success' => true,
            'message' => 'View count berhasil ditambahkan',
            'views' => (int) $kitab->views
        ], 200);
    }

    // 📥 Download PDF
    public function download($id_kitab)
    {
        $kitab = Kitab::find($id_kitab);

        if (!$kitab) {
            return response()->json([
                'success' => false,
                'message' => 'Kitab tidak ditemukan'
            ], 404);
        }

        $path = public_path('pdf/' . $kitab->file_pdf);

        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File PDF tidak ditemukan'
            ], 404);
        }

        // Increment download count
        $kitab->increment('downloads');

        $user = auth('sanctum')->user();
        \App\Models\DownloadLog::create([
            'kitab_id' => $id_kitab,
            'user_id' => $user ? $user->id : null,
        ]);

        return response()->download($path, $kitab->judul . '.pdf');
    }

    // 📚 Get related kitab berdasarkan kategori
    public function getRelated($id_kitab)
    {
        $kitab = Kitab::find($id_kitab);

        if (!$kitab) {
            return response()->json([
                'success' => false,
                'message' => 'Kitab tidak ditemukan'
            ], 404);
        }

        $baseUrl = config('app.url') . '/';
        
        $related = Kitab::where('kategori', $kitab->kategori)
            ->where('id_kitab', '!=', $id_kitab)
            ->take(3)
            ->get()
            ->map(function($item) use ($baseUrl) {
                return [
                    'idKitab' => $item->id_kitab,
                    'judul' => $item->judul,
                    'penulis' => $item->penulis,
                    'cover' => $baseUrl . 'cover/' . $item->cover,
                    'kategori' => $item->kategori,
                    'pembaca' => (int) $item->views
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar kitab terkait',
            'data' => $related
        ], 200);
    }

    // 💬 GET semua komentar untuk kitab tertentu
    public function getComments($id_kitab)
    {
        $kitab = Kitab::find($id_kitab);

        if (!$kitab) {
            return response()->json([
                'success' => false,
                'message' => 'Kitab tidak ditemukan'
            ], 404);
        }

        $comments = Comment::where('id_kitab', $id_kitab)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($comment) {
                return [
                    'id' => $comment->id,
                    'username' => $comment->user->username,
                    'avatar' => strtoupper(substr($comment->user->username, 0, 1)),
                    'text' => $comment->isi_comment,
                    'date' => $comment->created_at->diffForHumans(),
                    'created_at' => $comment->created_at->toIso8601String(),
                    'is_owner' => auth()->check() && auth()->id() === $comment->user_id
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar komentar',
            'data' => $comments
        ], 200);
    }

    // 💬 POST komentar baru (requires authentication)
    public function storeComment(Request $request, $id_kitab)
    {
        $user = auth()->user();

        // Cek apakah user sudah login
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Cek apakah kitab exists
        $kitab = Kitab::find($id_kitab);
        if (!$kitab) {
            return response()->json([
                'success' => false,
                'message' => 'Kitab tidak ditemukan'
            ], 404);
        }

        // Validasi input
        $request->validate([
            'isi_komentar' => 'required|string|max:1000',
        ]);

        // Buat komentar baru
        $comment = Comment::create([
            'id_kitab' => $id_kitab,
            'user_id' => $user->id,
            'isi_comment' => $request->isi_komentar,
        ]);


        // Load relasi user
        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil ditambahkan',
            'data' => [
                'id' => $comment->id,
                'username' => $comment->user->username,
                'avatar' => strtoupper(substr($comment->user->username, 0, 1)),
                'text' => $comment->isi_comment,
                'date' => $comment->created_at->diffForHumans(),
                'created_at' => $comment->created_at->toIso8601String(),
                'is_owner' => true
            ]
        ], 201);
    }

    // 🗑️ DELETE komentar (requires authentication & ownership)
    public function destroyComment($id)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login terlebih dahulu'
            ], 401);
        }

        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Komentar tidak ditemukan'
            ], 404);
        }

        $user = auth()->user();

        // Cek apakah user adalah pemilik komentar
        if (!$user || $comment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus komentar ini'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil dihapus'
        ], 200);
    }

    // 🔍 Advanced Search kitab dengan multi-filter & sorting
    public function search(Request $request)
    {
        $searchTerm = $this->normalizeSearchText($request->get('search', $request->get('query', '')));
        $limit = max(1, min((int) $request->get('limit', 20), 50));
        $offset = max(0, (int) $request->get('offset', 0));
        $sortByInput = (string) $request->get('sort_by', 'relevance');

        if (!$this->isValidSearchSortBy($sortByInput)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter sort_by tidak valid.',
            ], 422);
        }

        if ($this->hasInvalidYearRange($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter rentang tahun tidak valid.',
            ], 422);
        }

        $categories = $this->normalizeFilterList($request->get('categories'));
        $authors = $this->normalizeFilterList($request->get('authors'));
        $languages = $this->normalizeFilterList($request->get('languages'));

        if (empty($categories) && $request->filled('kategori')) {
            $categories = $this->normalizeFilterList($request->get('kategori'));
        }

        if (empty($languages) && $request->filled('bahasa')) {
            $languages = $this->normalizeFilterList($request->get('bahasa'));
        }

        $sortBy = $this->normalizeSortBy($sortByInput);
        $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $baseUrl = rtrim(config('app.url'), '/') . '/';

        $kitabQuery = Kitab::published();

        if ($searchTerm !== '') {
            $kitabQuery->where(function ($query) use ($searchTerm) {
                $query->where('judul', 'like', "%{$searchTerm}%")
                    ->orWhere('penulis', 'like', "%{$searchTerm}%")
                    ->orWhere('deskripsi', 'like', "%{$searchTerm}%");
            });
        }

        if (!empty($categories)) {
            $kitabQuery->whereIn('kategori', $categories);
        }

        if (!empty($authors)) {
            $kitabQuery->whereIn('penulis', $authors);
        }

        if (!empty($languages)) {
            $kitabQuery->whereIn('bahasa', $languages);
        }

        switch ($sortBy) {
            case 'title_asc':
                $kitabQuery->orderBy('judul', 'asc');
                break;
            case 'title_desc':
                $kitabQuery->orderBy('judul', 'desc');
                break;
            case 'author_asc':
                $kitabQuery->orderBy('penulis', 'asc');
                break;
            case 'author_desc':
                $kitabQuery->orderBy('penulis', 'desc');
                break;
            case 'newest':
                $kitabQuery->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $kitabQuery->orderBy('created_at', 'asc');
                break;
            case 'views':
                $kitabQuery->orderBy('views', $sortOrder);
                break;
            case 'downloads':
                $kitabQuery->orderBy('downloads', $sortOrder);
                break;
            case 'relevance':
            default:
                if ($searchTerm !== '') {
                    $lowerSearchTerm = strtolower($searchTerm);
                    $kitabQuery->orderByRaw(
                        "CASE
                            WHEN LOWER(judul) = ? THEN 0
                            WHEN LOWER(judul) LIKE ? THEN 1
                            WHEN LOWER(penulis) LIKE ? THEN 2
                            ELSE 3
                        END",
                        [
                            $lowerSearchTerm,
                            $lowerSearchTerm . '%',
                            '%' . $lowerSearchTerm . '%',
                        ]
                    );
                }
                $kitabQuery->orderBy('views', 'desc');
                break;
        }

        $total = (clone $kitabQuery)->count();

        $results = $kitabQuery
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($item) use ($baseUrl) {
                return [
                    'idKitab' => $item->id_kitab,
                    'judul' => $item->judul,
                    'penulis' => $item->penulis,
                    'cover' => $baseUrl . 'cover/' . $item->cover,
                    'kategori' => $item->kategori,
                    'deskripsi' => $item->deskripsi,
                    'bahasa' => $item->bahasa,
                    'filePdf' => $baseUrl . 'pdf/' . $item->file_pdf,
                    'views' => (int) $item->views,
                    'downloads' => (int) $item->downloads,
                    'averageRating' => $item->averageRating(),
                    'ratingsCount' => $item->ratingsCount(),
                ];
            })
            ->values();

        $publishedKitab = Kitab::published();
        $availableCategories = (clone $publishedKitab)
            ->select('kategori')
            ->whereNotNull('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter()
            ->values();

        $availableAuthors = (clone $publishedKitab)
            ->select('penulis')
            ->whereNotNull('penulis')
            ->distinct()
            ->orderBy('penulis')
            ->pluck('penulis')
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter()
            ->values();

        $availableLanguages = (clone $publishedKitab)
            ->select('bahasa')
            ->whereNotNull('bahasa')
            ->distinct()
            ->orderBy('bahasa')
            ->pluck('bahasa')
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'message' => !empty($searchTerm)
                ? 'Hasil pencarian untuk: ' . $searchTerm
                : 'Daftar kitab dengan filter',
            'data' => $results,
            'total' => $total,
            'count' => $results->count(),
            'filters' => [
                'categories' => $availableCategories,
                'authors' => $availableAuthors,
                'languages' => $availableLanguages,
            ],
        ], 200);
    }

    private function normalizeSearchText($value): string
    {
        return trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');
    }

    private function normalizeFilterList($value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            $items = $value;
        } else {
            $items = explode(',', (string) $value);
        }

        return collect($items)
            ->map(function ($item) {
                return trim((string) $item);
            })
            ->filter()
            ->unique(function ($item) {
                return strtolower($item);
            })
            ->values()
            ->all();
    }

    private function normalizeSortBy($sortBy): string
    {
        $normalized = strtolower(trim((string) $sortBy));

        switch ($normalized) {
            case 'latest':
                return 'newest';
            case 'title':
                return 'title_asc';
            case 'author':
                return 'author_asc';
            default:
                return $normalized ?: 'relevance';
        }
    }

    private function isValidSearchSortBy(string $sortBy): bool
    {
        $normalized = strtolower(trim($sortBy));

        return in_array($normalized, [
            '',
            'relevance',
            'views',
            'downloads',
            'newest',
            'latest',
            'oldest',
            'title',
            'title_asc',
            'title_desc',
            'author',
            'author_asc',
            'author_desc',
        ], true);
    }

    private function hasInvalidYearRange(Request $request): bool
    {
        $minYear = $request->get('min_year');
        $maxYear = $request->get('max_year');

        if ($minYear === null || $maxYear === null) {
            return false;
        }

        if (!is_numeric($minYear) || !is_numeric($maxYear)) {
            return true;
        }

        return (int) $minYear > (int) $maxYear;
    }

    // ⭐ Rate a kitab (1-5 stars, upsert)
    public function rateKitab(Request $request, $id_kitab)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $kitab = Kitab::find($id_kitab);
        if (!$kitab) {
            return response()->json(['success' => false, 'message' => 'Kitab tidak ditemukan'], 404);
        }

        $rating = Rating::updateOrCreate(
            ['user_id' => $user->id, 'id_kitab' => $id_kitab],
            ['rating' => $request->rating]
        );

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil disimpan',
            'data' => [
                'rating' => $rating->rating,
                'averageRating' => $kitab->averageRating(),
                'ratingsCount' => $kitab->ratingsCount(),
            ]
        ], 200);
    }

    // ⭐ Get current user's rating for a kitab
    public function getMyRating($id_kitab)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $rating = Rating::where('user_id', $user->id)
            ->where('id_kitab', $id_kitab)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'myRating' => $rating ? $rating->rating : 0,
            ]
        ], 200);
    }

    // 💡 Get Smart Recommendations
    public function getRecommendations(Request $request)
    {
        $baseUrl = config('app.url') . '/';
        // Ambil user secara opsional (bisa guest)
        $user = auth('sanctum')->user();

        $recommendedKitabs = collect();
        $excludedKitabIds = [];

        if ($user) {
            // 1. Ambil kitab dari history
            $historyKitabIds = \App\Models\History::where('user_id', $user->id)
                ->pluck('kitab_id')->toArray();
            
            // 2. Ambil kitab dari bookmark
            $bookmarkKitabIds = \App\Models\Bookmark::where('user_id', $user->id)
                ->pluck('id_kitab')->toArray();

            $excludedKitabIds = array_unique(array_merge($historyKitabIds, $bookmarkKitabIds));

            // 3. Cari kategori yang paling sering dibaca/disimpan
            $favoriteCategories = Kitab::whereIn('id_kitab', $excludedKitabIds)
                ->whereNotNull('kategori')
                ->select('kategori', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('kategori')
                ->orderBy('total', 'desc')
                ->pluck('kategori')
                ->take(3)
                ->toArray();

            // 4. Cari kitab lain dalam kategori favorit yang belum pernah dibaca/dibookmark
            if (!empty($favoriteCategories)) {
                $recommendedKitabs = Kitab::published()
                    ->whereIn('kategori', $favoriteCategories)
                    ->whereNotIn('id_kitab', $excludedKitabIds)
                    ->orderBy('views', 'desc')
                    ->take(10)
                    ->get();
            }
        }

        // 5. Jika rekomendasi kurang dari 10, isi dengan kitab terpopuler secara global (trending)
        if ($recommendedKitabs->count() < 10) {
            $limit = 10 - $recommendedKitabs->count();
            
            $trendingIdsToExclude = array_merge($excludedKitabIds, $recommendedKitabs->pluck('id_kitab')->toArray());

            $trendingKitabs = Kitab::published()
                ->whereNotIn('id_kitab', $trendingIdsToExclude)
                ->orderBy('views', 'desc')
                ->take($limit)
                ->get();
            
            $recommendedKitabs = $recommendedKitabs->merge($trendingKitabs);
        }

        $formattedData = $recommendedKitabs->map(function($item) use ($baseUrl) {
            return [
                'idKitab' => $item->id_kitab,
                'judul' => $item->judul,
                'penulis' => $item->penulis,
                'cover' => $baseUrl . 'cover/' . $item->cover,
                'kategori' => $item->kategori,
                'deskripsi' => $item->deskripsi,
                'bahasa' => $item->bahasa,
                'filePdf' => $baseUrl . 'pdf/' . $item->file_pdf,
                'views' => (int) $item->views,
                'downloads' => (int) $item->downloads,
                'averageRating' => $item->averageRating(),
                'ratingsCount' => $item->ratingsCount(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi kitab untuk Anda',
            'data' => $formattedData,
        ], 200);
    }
}
