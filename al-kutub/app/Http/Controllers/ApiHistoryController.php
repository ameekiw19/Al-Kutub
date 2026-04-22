<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\History;
use App\Models\Kitab;
use App\Models\ReadingGoal;
use App\Models\ReadingStreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiHistoryController extends Controller
{
    /**
     * Menampilkan semua riwayat bacaan user
     * GET /api/history
     */
    public function index(Request $request)
    {
        try {
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            // Get histories dengan relasi kitab
            // UPDATE: Menambahkan 'file_pdf' dan 'deskripsi' agar lengkap
            $histories = History::where('user_id', $userId)
                ->whereHas('kitab', function ($query) {
                    $query->published();
                })
                ->with(['kitab' => function($query) {
                    $query->select(
                        'id_kitab', 
                        'judul', 
                        'penulis', 
                        'kategori', 
                        'cover', 
                        'file_pdf', // Penting untuk link baca
                        'views', 
                        'downloads', 
                        'bahasa',
                        'deskripsi' // Opsional, jika ingin ditampilkan di list
                    );
                }])
                ->orderByDesc('last_read_at')
                ->get();

            // Group by date
            $groupedHistories = $this->groupByDate($histories);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat bacaan berhasil diambil',
                'data' => [
                    'total' => $histories->count(),
                    'histories' => $groupedHistories,
                    // Raw data untuk kebutuhan development/parsing lain
                    'raw_histories' => $histories->map(function($history) {
                        return $this->formatHistoryItem($history);
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat bacaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail single history
     * GET /api/history/{id}
     */
    public function show($id)
    {
        try {
            $history = History::where('id', $id)
                ->where('user_id', auth()->id())
                ->whereHas('kitab', function ($query) {
                    $query->published();
                })
                ->with('kitab') // Mengambil semua kolom kitab
                ->first();

            if (!$history) {
                return response()->json([
                    'success' => false,
                    'message' => 'Riwayat tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail riwayat berhasil diambil',
                'data' => $this->formatHistoryItem($history)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail riwayat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menambah atau update history (dipanggil saat klik 'Baca Sekarang')
     * POST /api/history
     */
    public function store(Request $request)
    {
        try {
            // VALIDASI KHUSUS: Cek ke tabel 'kitab' kolom 'id_kitab'
            $validator = Validator::make($request->all(), [
                'kitab_id' => 'required|exists:kitab,id_kitab',
                'current_page' => 'nullable|integer|min:0',
                'total_pages' => 'nullable|integer|min:0',
                'last_position' => 'nullable|string|max:255',
                'reading_time_minutes' => 'nullable|integer|min:0',
                'reading_time_added' => 'nullable|integer|min:0',
                'client_updated_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = auth()->id();
            $kitabId = $request->kitab_id;

            if (!Kitab::published()->where('id_kitab', (int) $kitabId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kitab belum dipublikasikan atau tidak ditemukan',
                ], 422);
            }

            $clientUpdatedAt = $request->filled('client_updated_at')
                ? Carbon::parse($request->input('client_updated_at'))
                : null;
            $incomingLastReadAt = $clientUpdatedAt ?: now();

            $incomingCurrentPage = $request->has('current_page')
                ? max(0, (int) $request->input('current_page'))
                : null;
            $incomingTotalPages = $request->has('total_pages')
                ? max(0, (int) $request->input('total_pages'))
                : null;
            $incomingLastPosition = $request->has('last_position')
                ? trim((string) $request->input('last_position'))
                : null;
            $incomingReadingTimeMinutes = $request->has('reading_time_minutes')
                ? max(0, (int) $request->input('reading_time_minutes'))
                : null;
            $incomingReadingTimeAdded = $request->has('reading_time_added')
                ? max(0, (int) $request->input('reading_time_added'))
                : null;

            // Cek history existing
            $history = History::where('user_id', $userId)
                ->where('kitab_id', $kitabId)
                ->first();

            if ($history) {
                $existingCurrentPage = (int) ($history->current_page ?? 0);
                $existingTotalPages = (int) ($history->total_pages ?? 0);
                $existingReadingTimeMinutes = (int) ($history->reading_time_minutes ?? 0);

                $resolvedCurrentPage = max($existingCurrentPage, $incomingCurrentPage ?? $existingCurrentPage);
                $resolvedTotalPages = $existingTotalPages;
                if ($incomingTotalPages !== null) {
                    $resolvedTotalPages = max($existingTotalPages, $incomingTotalPages, $resolvedCurrentPage);
                } elseif ($resolvedTotalPages > 0) {
                    $resolvedTotalPages = max($resolvedTotalPages, $resolvedCurrentPage);
                }

                $resolvedReadingTimeMinutes = $existingReadingTimeMinutes;
                if ($incomingReadingTimeAdded !== null) {
                    $resolvedReadingTimeMinutes = $existingReadingTimeMinutes + $incomingReadingTimeAdded;
                } elseif ($incomingReadingTimeMinutes !== null) {
                    $resolvedReadingTimeMinutes = max($existingReadingTimeMinutes, $incomingReadingTimeMinutes);
                }

                $resolvedLastPosition = $history->last_position;
                if ($incomingLastPosition !== null && $incomingLastPosition !== '') {
                    $candidatePage = $incomingCurrentPage ?? $resolvedCurrentPage;
                    if ($candidatePage >= $resolvedCurrentPage) {
                        $resolvedLastPosition = $incomingLastPosition;
                    }
                }

                $serverUpdatedAt = $history->updated_at ? Carbon::parse($history->updated_at) : null;
                $isStaleSnapshot = $clientUpdatedAt !== null &&
                    $serverUpdatedAt !== null &&
                    $clientUpdatedAt->lt($serverUpdatedAt);
                $hasForwardProgress = $resolvedCurrentPage > $existingCurrentPage ||
                    $resolvedTotalPages > $existingTotalPages ||
                    $resolvedReadingTimeMinutes > $existingReadingTimeMinutes;

                if ($isStaleSnapshot && !$hasForwardProgress && ($incomingReadingTimeAdded ?? 0) <= 0) {
                    $history->load('kitab');
                    return response()->json([
                        'success' => true,
                        'message' => 'Snapshot lama diabaikan, progres terbaru tetap dipertahankan',
                        'data' => $this->formatHistoryItem($history)
                    ], 200);
                }

                $existingLastReadAt = $history->last_read_at ? Carbon::parse($history->last_read_at) : null;
                $resolvedLastReadAt = $existingLastReadAt && $existingLastReadAt->greaterThan($incomingLastReadAt)
                    ? $existingLastReadAt
                    : $incomingLastReadAt;

                $dataToUpdate = [
                    'last_read_at' => $resolvedLastReadAt,
                    'current_page' => $resolvedCurrentPage,
                    'total_pages' => $resolvedTotalPages,
                    'last_position' => $resolvedLastPosition,
                    'reading_time_minutes' => $resolvedReadingTimeMinutes,
                ];

                $history->update($dataToUpdate);
                $message = 'Progres membaca disinkronkan';
                $statusCode = 200;
            } else {
                $initialCurrentPage = max(0, $incomingCurrentPage ?? 0);
                $initialTotalPages = max($initialCurrentPage, $incomingTotalPages ?? 0);
                $initialReadingTimeMinutes = $incomingReadingTimeAdded !== null
                    ? $incomingReadingTimeAdded
                    : max(0, $incomingReadingTimeMinutes ?? 0);
                $initialLastPosition = ($incomingLastPosition !== null && $incomingLastPosition !== '')
                    ? $incomingLastPosition
                    : null;

                $dataToCreate = [
                    'user_id' => $userId,
                    'kitab_id' => $kitabId,
                    'last_read_at' => $incomingLastReadAt,
                    'current_page' => $initialCurrentPage,
                    'total_pages' => $initialTotalPages,
                    'last_position' => $initialLastPosition,
                    'reading_time_minutes' => $initialReadingTimeMinutes,
                ];

                $history = History::create($dataToCreate);
                $message = 'Mulai membaca';
                $statusCode = 201;
            }

            // Auto-update reading goals dan streaks
            $readingMinutes = (int)($history->reading_time_minutes ?? 0);
            $readingPages = (int)($history->current_page ?? 0);

            if ($readingMinutes > 0 || $readingPages > 0) {
                // Update daily goal
                $dailyGoal = ReadingGoal::getOrCreateDailyGoal($userId);
                $dailyGoal->addProgress($readingMinutes, $readingPages);

                // Update weekly goal
                $weeklyGoal = ReadingGoal::getOrCreateWeeklyGoal($userId);
                $weeklyGoal->addProgress($readingMinutes, $readingPages);

                // Update streak
                $streak = ReadingStreak::getOrCreate($userId);
                $streak->updateStreak();
            }

            $history->load('kitab');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->formatHistoryItem($history)
            ], $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan riwayat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus satu item history
     * DELETE /api/history/{id}
     */
    public function destroy($id)
    {
        try {
            $history = History::where('id', $id)->where('user_id', auth()->id())->first();

            if (!$history) return response()->json(['success' => false, 'message' => 'Tidak ditemukan'], 404);

            $history->delete();
            return response()->json(['success' => true, 'message' => 'Berhasil dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal hapus', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus SEMUA history user
     * DELETE /api/history-clear-all
     */
    public function clearAll()
    {
        try {
            History::where('user_id', auth()->id())->delete();
            return response()->json(['success' => true, 'message' => 'Semua riwayat dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal hapus semua', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Statistik User
     * GET /api/history/stats/summary
     */
        public function statistics()
        {
            try {
                $userId = auth()->id();

                if (!$userId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized (token belum dikirim)'
                    ], 401);
                }

                $stats = [
                    'total_kitab' => History::where('user_id', $userId)->count(),
                    'today' => History::where('user_id', $userId)
                        ->whereDate('last_read_at', today())
                        ->count(),
                    'this_week' => History::where('user_id', $userId)
                        ->whereBetween('last_read_at', [
                            now()->startOfWeek(),
                            now()->endOfWeek()
                        ])
                        ->count(),
                ];

                $topCategories = History::where('user_id', $userId)
                    ->join('kitab', 'history.kitab_id', '=', 'kitab.id_kitab')
                    ->select('kitab.kategori', DB::raw('count(*) as total'))
                    ->groupBy('kitab.kategori')
                    ->orderByDesc('total')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => array_merge($stats, [
                        'top_categories' => $topCategories
                    ])
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        }

    // --- PRIVATE HELPERS ---

    private function groupByDate($histories)
    {
        $grouped = [];
        foreach ($histories as $history) {
            $date = Carbon::parse($history->last_read_at);
            if ($date->isToday()) $label = 'Hari Ini';
            elseif ($date->isYesterday()) $label = 'Kemarin';
            else $label = $date->locale('id')->isoFormat('dddd, D MMMM');

            $grouped[$label][] = $this->formatHistoryItem($history);
        }
        return $grouped;
    }

    // Helper formatting agar output JSON konsisten dan lengkap
    private function formatHistoryItem($history)
    {
        return [
            'id_history' => $history->id,
            'kitab_id' => $history->kitab_id,
            'last_read_at' => $history->last_read_at,
            'current_page' => $history->current_page,
            'total_pages' => $history->total_pages,
            'last_position' => $history->last_position,
            'reading_time_minutes' => $history->reading_time_minutes,
            'time_ago' => $this->getTimeAgo($history->last_read_at),
            'kitab' => $history->kitab ? [
                'idKitab' => $history->kitab->id_kitab, // Gunakan idKitab untuk Android
                'judul' => $history->kitab->judul,
                'penulis' => $history->kitab->penulis,
                'kategori' => $history->kitab->kategori,
                'bahasa' => $history->kitab->bahasa,
                'cover' => $history->kitab->cover, // URL cover
                'filePdf' => $history->kitab->file_pdf, // Gunakan filePdf untuk Android
                'deskripsi' => $history->kitab->deskripsi,
                'views' => $history->kitab->views,
                'downloads' => $history->kitab->downloads,
            ] : null
        ];
    }

    private function getTimeAgo($timestamp)
    {
        return Carbon::parse($timestamp)->locale('id')->diffForHumans();
    }
}
