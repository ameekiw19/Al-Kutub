<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kitab;
use App\Models\SearchHistory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function suggestions(Request $request)
    {
        $query = $this->normalizeQuery($request->get('query', ''));
        $limit = max(1, min((int) $request->get('limit', 10), 10));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'message' => 'Query terlalu pendek untuk menampilkan saran',
                'data' => [],
            ], 200);
        }

        $titleSuggestions = $this->buildTextSuggestions('judul', $query, 'query', $limit * 2, 'MAX(views)');
        $authorSuggestions = $this->buildTextSuggestions('penulis', $query, 'author', $limit * 2, 'COUNT(*)');
        $categorySuggestions = $this->buildTextSuggestions('kategori', $query, 'category', $limit * 2, 'COUNT(*)');
        $languageSuggestions = $this->buildTextSuggestions('bahasa', $query, 'language', $limit * 2, 'COUNT(*)');

        $seen = [];
        $suggestions = collect([
            $titleSuggestions,
            $authorSuggestions,
            $categorySuggestions,
            $languageSuggestions,
        ])->flatten(1)->filter(function ($item) use (&$seen) {
            $key = mb_strtolower($item['text']);
            if (isset($seen[$key])) {
                return false;
            }

            $seen[$key] = true;
            return true;
        })->take($limit)->values();

        return response()->json([
            'success' => true,
            'message' => 'Saran pencarian berhasil dimuat',
            'data' => $suggestions,
        ], 200);
    }

    public function history(Request $request)
    {
        $items = SearchHistory::where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function (SearchHistory $item) {
                return $this->mapHistoryItem($item);
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pencarian berhasil dimuat',
            'data' => $items,
        ], 200);
    }

    public function storeHistory(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:255',
            'filters' => 'nullable|array',
            'result_count' => 'nullable|integer|min:0',
        ]);

        $normalizedQuery = $this->normalizeQuery($validated['query']);
        if ($normalizedQuery === '') {
            return response()->json([
                'success' => false,
                'message' => 'Query pencarian tidak boleh kosong',
            ], 422);
        }

        $userId = $request->user()->id;
        $existingHistory = SearchHistory::where('user_id', $userId)
            ->whereRaw('LOWER(query) = ?', [mb_strtolower($normalizedQuery)])
            ->first();

        $payload = [
            'query' => $normalizedQuery,
            'filters' => $validated['filters'] ?? null,
            'result_count' => $validated['result_count'] ?? 0,
        ];

        $status = 201;
        $message = 'Riwayat pencarian berhasil disimpan';

        if ($existingHistory) {
            $existingHistory->fill($payload);
            $existingHistory->updated_at = now();
            $existingHistory->save();
            $history = $existingHistory;
            $status = 200;
            $message = 'Riwayat pencarian berhasil diperbarui';
        } else {
            $history = SearchHistory::create($payload + ['user_id' => $userId]);
        }

        $this->trimHistory($userId);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $this->mapHistoryItem($history->fresh()),
        ], $status);
    }

    public function clearHistory(Request $request)
    {
        $deleted = SearchHistory::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Semua riwayat pencarian berhasil dihapus',
            'data' => [
                'deleted' => $deleted,
            ],
        ], 200);
    }

    public function destroyHistory(Request $request, $id)
    {
        $history = SearchHistory::where('user_id', $request->user()->id)->find($id);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'Riwayat pencarian tidak ditemukan',
            ], 404);
        }

        $history->delete();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pencarian berhasil dihapus',
            'data' => [
                'id' => (int) $id,
            ],
        ], 200);
    }

    private function buildTextSuggestions(
        string $column,
        string $query,
        string $type,
        int $limit,
        string $aggregateExpression
    ) {
        $lowerQuery = mb_strtolower($query);

        return Kitab::published()
            ->selectRaw("{$column} as text, {$aggregateExpression} as score")
            ->whereNotNull($column)
            ->where($column, 'like', '%' . $query . '%')
            ->groupBy($column)
            ->orderByRaw("CASE WHEN LOWER({$column}) LIKE ? THEN 0 ELSE 1 END", [$lowerQuery . '%'])
            ->orderByDesc('score')
            ->limit($limit)
            ->get()
            ->map(function ($item) use ($type) {
                $text = trim((string) $item->text);
                return [
                    'id' => $this->buildSuggestionId($type, $text),
                    'text' => $text,
                    'type' => $type,
                    'count' => $type === 'query' ? null : (int) $item->score,
                ];
            })
            ->filter(function ($item) {
                return $item['text'] !== '';
            })
            ->values();
    }

    private function buildSuggestionId(string $type, string $text): string
    {
        return $type . ':' . md5(mb_strtolower($text));
    }

    private function normalizeQuery(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function trimHistory(int $userId): void
    {
        $staleIds = SearchHistory::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->pluck('id')
            ->slice(20)
            ->values();

        if ($staleIds->isNotEmpty()) {
            SearchHistory::whereIn('id', $staleIds->all())->delete();
        }
    }

    private function mapHistoryItem(SearchHistory $item): array
    {
        return [
            'id' => (int) $item->id,
            'query' => $item->query,
            'filters' => $item->filters,
            'result_count' => (int) $item->result_count,
            'created_at' => optional($item->created_at)->toISOString(),
            'updated_at' => optional($item->updated_at)->toISOString(),
        ];
    }
}
