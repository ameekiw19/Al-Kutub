<?php

namespace App\Http\Controllers;

use App\Models\Kitab;
use App\Models\SearchHistory;
use Illuminate\Http\Request;

class ApiSearchController extends Controller
{
    public function suggestions(Request $request)
    {
        $query = trim((string) $request->get('query', ''));
        $limit = min((int) $request->get('limit', 10), 20);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'message' => 'Search suggestions',
                'data' => [],
            ]);
        }

        $suggestions = collect();

        $titleMatches = Kitab::published()->where('judul', 'like', "%{$query}%")
            ->limit($limit)
            ->pluck('judul')
            ->map(function ($text) {
                return ['text' => $text, 'type' => 'query'];
            });

        $authorMatches = Kitab::published()->where('penulis', 'like', "%{$query}%")
            ->limit($limit)
            ->pluck('penulis')
            ->unique()
            ->map(function ($text) {
                return ['text' => $text, 'type' => 'author'];
            });

        $categoryMatches = Kitab::published()->where('kategori', 'like', "%{$query}%")
            ->limit($limit)
            ->pluck('kategori')
            ->unique()
            ->map(function ($text) {
                return ['text' => $text, 'type' => 'category'];
            });

        $languageMatches = Kitab::published()->where('bahasa', 'like', "%{$query}%")
            ->limit($limit)
            ->pluck('bahasa')
            ->unique()
            ->map(function ($text) {
                return ['text' => $text, 'type' => 'language'];
            });

        $suggestions = $suggestions
            ->concat($titleMatches)
            ->concat($authorMatches)
            ->concat($categoryMatches)
            ->concat($languageMatches)
            ->unique(function ($item) {
                return strtolower($item['type'] . ':' . $item['text']);
            })
            ->take($limit)
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Search suggestions',
            'data' => $suggestions,
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $limit = min((int) $request->get('limit', 20), 50);

        $items = SearchHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'query' => $item->query,
                    'created_at' => optional($item->created_at)->toIso8601String(),
                    'result_count' => (int) $item->result_count,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Search history loaded',
            'data' => $items,
        ]);
    }

    public function storeHistory(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:255',
            'filters' => 'nullable|array',
            'result_count' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $query = trim($validated['query']);
        $filters = $validated['filters'] ?? [];
        $resultCount = (int) ($validated['result_count'] ?? 0);

        // Keep last search by query up-to-date to avoid duplicates.
        SearchHistory::where('user_id', $user->id)
            ->where('query', $query)
            ->delete();

        $history = SearchHistory::create([
            'user_id' => $user->id,
            'query' => $query,
            'filters' => $filters,
            'result_count' => $resultCount,
        ]);

        // Keep only latest 50 items for each user.
        $oldIds = SearchHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->skip(50)
            ->pluck('id');
        if ($oldIds->isNotEmpty()) {
            SearchHistory::whereIn('id', $oldIds)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Search history saved',
            'data' => [
                'id' => $history->id,
                'query' => $history->query,
            ],
        ], 201);
    }

    public function deleteHistory(Request $request, $id)
    {
        $deleted = SearchHistory::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'History item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Search history deleted',
            'data' => ['id' => (int) $id],
        ]);
    }

    public function clearHistory(Request $request)
    {
        $deletedCount = SearchHistory::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'All search history cleared',
            'data' => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }
}
