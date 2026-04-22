<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\History;
use App\Models\Kitab;
use App\Services\KitabTranscriptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KitabReaderController extends Controller
{
    public function show(KitabTranscriptService $transcriptService, int $id_kitab)
    {
        $kitab = Kitab::published()->findOrFail($id_kitab);
        $userId = Auth::id();

        $history = History::query()
            ->where('user_id', $userId)
            ->where('kitab_id', $id_kitab)
            ->first();

        $resumePage = max(1, (int) ($history?->current_page ?? 1));

        $markers = Bookmark::query()
            ->where('user_id', $userId)
            ->where('id_kitab', $id_kitab)
            ->where('bookmark_type', 'page')
            ->orderBy('page_number')
            ->get()
            ->map(fn (Bookmark $bookmark) => $this->markerPayload($bookmark))
            ->values();

        return view('ReadKitab', [
            'kitab' => $kitab,
            'resumePage' => $resumePage,
            'initialMarkers' => $markers,
            'transcriptPayload' => $transcriptService->buildPayload($kitab),
        ]);
    }

    public function saveProgress(Request $request, int $id_kitab): JsonResponse
    {
        $kitab = Kitab::published()->findOrFail($id_kitab);
        $validated = $request->validate([
            'current_page' => 'required|integer|min:1',
            'total_pages' => 'nullable|integer|min:1',
            'last_position' => 'nullable|string|max:120',
        ]);

        $currentPage = (int) $validated['current_page'];
        $totalPages = isset($validated['total_pages']) ? (int) $validated['total_pages'] : null;
        $lastPosition = trim((string) ($validated['last_position'] ?? ''));
        if ($lastPosition === '') {
            $lastPosition = "page:{$currentPage}";
        }

        $history = History::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'kitab_id' => $kitab->id_kitab,
            ],
            [
                'last_read_at' => now(),
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'last_position' => $lastPosition,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Progress tersimpan',
            'data' => [
                'kitab_id' => $kitab->id_kitab,
                'current_page' => (int) ($history->current_page ?? $currentPage),
                'total_pages' => (int) ($history->total_pages ?? ($totalPages ?? 0)),
                'last_read_at' => optional($history->last_read_at)->toIso8601String(),
            ],
        ]);
    }

    public function indexMarkers(int $id_kitab): JsonResponse
    {
        Kitab::published()->findOrFail($id_kitab);

        $markers = Bookmark::query()
            ->where('user_id', Auth::id())
            ->where('id_kitab', $id_kitab)
            ->where('bookmark_type', 'page')
            ->orderBy('page_number')
            ->get()
            ->map(fn (Bookmark $bookmark) => $this->markerPayload($bookmark))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $markers,
        ]);
    }

    public function storeMarker(Request $request, int $id_kitab): JsonResponse
    {
        Kitab::published()->findOrFail($id_kitab);

        $validated = $request->validate([
            'page_number' => 'required|integer|min:1',
            'label' => 'nullable|string|max:120',
        ]);

        $pageNumber = (int) $validated['page_number'];
        $label = trim((string) ($validated['label'] ?? ''));
        if ($label === '') {
            $label = "Halaman {$pageNumber}";
        }

        $bookmark = Bookmark::query()
            ->where('user_id', Auth::id())
            ->where('id_kitab', $id_kitab)
            ->where('bookmark_type', 'page')
            ->where('page_number', $pageNumber)
            ->first();

        $created = false;
        if ($bookmark) {
            $bookmark->update([
                'page_title' => $label,
            ]);
            $bookmark = $bookmark->fresh();
        } else {
            $bookmark = Bookmark::create([
                'user_id' => Auth::id(),
                'id_kitab' => $id_kitab,
                'bookmark_type' => 'page',
                'page_number' => $pageNumber,
                'page_title' => $label,
            ]);
            $created = true;
        }

        return response()->json([
            'success' => true,
            'message' => $created ? 'Marker ditambahkan' : 'Marker diperbarui',
            'data' => $this->markerPayload($bookmark),
        ], $created ? 201 : 200);
    }

    public function updateMarker(Request $request, int $id_kitab, int $bookmarkId): JsonResponse
    {
        Kitab::published()->findOrFail($id_kitab);

        $validated = $request->validate([
            'label' => 'nullable|string|max:120',
        ]);

        $bookmark = Bookmark::query()
            ->where('id_bookmark', $bookmarkId)
            ->where('user_id', Auth::id())
            ->where('id_kitab', $id_kitab)
            ->where('bookmark_type', 'page')
            ->firstOrFail();

        $label = trim((string) ($validated['label'] ?? ''));
        if ($label === '') {
            $label = 'Halaman ' . ((int) $bookmark->page_number);
        }

        $bookmark->update([
            'page_title' => $label,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Label marker diperbarui',
            'data' => $this->markerPayload($bookmark->fresh()),
        ]);
    }

    public function destroyMarker(int $id_kitab, int $bookmarkId): JsonResponse
    {
        Kitab::published()->findOrFail($id_kitab);

        $bookmark = Bookmark::query()
            ->where('id_bookmark', $bookmarkId)
            ->where('user_id', Auth::id())
            ->where('id_kitab', $id_kitab)
            ->where('bookmark_type', 'page')
            ->first();

        if ($bookmark) {
            $bookmark->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Marker dihapus',
        ]);
    }

    private function markerPayload(Bookmark $bookmark): array
    {
        $page = (int) ($bookmark->page_number ?? 0);

        return [
            'id' => (int) $bookmark->id_bookmark,
            'page_number' => $page,
            'label' => $bookmark->page_title ?: "Halaman {$page}",
            'created_at' => optional($bookmark->created_at)->toIso8601String(),
            'updated_at' => optional($bookmark->updated_at)->toIso8601String(),
        ];
    }
}
