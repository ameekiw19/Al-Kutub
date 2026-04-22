<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Kitab;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ApiPageBookmarkController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $kitabId = (int) $request->query('kitab_id', 0);

        $query = Bookmark::query()
            ->where('user_id', $userId)
            ->where('bookmark_type', 'page');

        if ($kitabId > 0) {
            $query->where('id_kitab', $kitabId);
        }

        $rows = $query
            ->orderBy('id_kitab')
            ->orderBy('page_number')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Marker halaman berhasil diambil',
            'data' => $rows->map(fn (Bookmark $bookmark) => $this->toPayload($bookmark)),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kitab_id' => 'required|integer|exists:kitab,id_kitab',
            'page_number' => 'required|integer|min:1',
            'label' => 'nullable|string|max:120',
            'client_updated_at' => 'nullable|date',
        ]);

        $kitabId = (int) $validated['kitab_id'];
        $pageNumber = (int) $validated['page_number'];
        $label = trim((string) ($validated['label'] ?? ''));
        if ($label === '') {
            $label = "Halaman {$pageNumber}";
        }

        if (!Kitab::published()->where('id_kitab', $kitabId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kitab belum dipublikasikan atau tidak ditemukan',
            ], 422);
        }

        $clientUpdatedAt = $request->filled('client_updated_at')
            ? Carbon::parse($request->input('client_updated_at'))
            : null;

        $userId = auth()->id();
        $existing = Bookmark::query()
            ->where('user_id', $userId)
            ->where('id_kitab', $kitabId)
            ->where('bookmark_type', 'page')
            ->where('page_number', $pageNumber)
            ->first();

        $created = false;
        if ($existing) {
            $serverUpdatedAt = $existing->updated_at ? Carbon::parse($existing->updated_at) : null;
            $isStaleSnapshot = $clientUpdatedAt !== null &&
                $serverUpdatedAt !== null &&
                $clientUpdatedAt->lt($serverUpdatedAt);

            if ($isStaleSnapshot && $existing->page_title !== $label) {
                return response()->json([
                    'success' => true,
                    'message' => 'Snapshot marker lama diabaikan, label terbaru dipertahankan',
                    'data' => $this->toPayload($existing),
                ], 200);
            }

            $existing->update([
                'page_title' => $label,
                'notes' => null,
            ]);
            $bookmark = $existing->fresh();
        } else {
            $bookmark = Bookmark::create([
                'user_id' => $userId,
                'id_kitab' => $kitabId,
                'bookmark_type' => 'page',
                'page_number' => $pageNumber,
                'page_title' => $label,
                'notes' => null,
            ]);
            $created = true;
        }

        return response()->json([
            'success' => true,
            'message' => 'Marker halaman disimpan',
            'data' => $this->toPayload($bookmark),
        ], $created ? 201 : 200);
    }

    public function destroy(int $kitab_id, int $page_number)
    {
        $userId = auth()->id();
        $kitabId = $kitab_id;
        $pageNumber = $page_number;

        $bookmark = Bookmark::query()
            ->where('user_id', $userId)
            ->where('id_kitab', $kitabId)
            ->where('bookmark_type', 'page')
            ->where('page_number', $pageNumber)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Marker halaman dihapus',
            'data' => [
                'kitab_id' => $kitabId,
                'page_number' => $pageNumber,
            ],
        ]);
    }

    private function toPayload(Bookmark $bookmark): array
    {
        $pageNumber = (int) ($bookmark->page_number ?? 0);

        return [
            'id' => $bookmark->id_bookmark,
            'user_id' => (int) $bookmark->user_id,
            'kitab_id' => (int) $bookmark->id_kitab,
            'page_number' => $pageNumber,
            'label' => $bookmark->page_title ?: "Halaman {$pageNumber}",
            'created_at' => optional($bookmark->created_at)->toIso8601String(),
            'updated_at' => optional($bookmark->updated_at)->toIso8601String(),
        ];
    }
}
