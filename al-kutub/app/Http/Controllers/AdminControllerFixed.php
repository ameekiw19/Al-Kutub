<?php

namespace App\Http\Controllers;

use App\Models\Kitab;
use App\Models\CategoryKatalog;
use App\Models\AppNotification;
use App\Events\NewKitabAdded;
use App\Services\FcmService;
use App\Services\KitabTranscriptImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminControllerFixed extends Controller
{
    protected $fcmService;
    protected $transcriptImportService;

    public function __construct(
        FcmService $fcmService,
        KitabTranscriptImportService $transcriptImportService
    )
    {
        $this->fcmService = $fcmService;
        $this->transcriptImportService = $transcriptImportService;
    }

    public function HomeAdmin()
    {
        $totalKitabs = Kitab::count();
        $totalUsers = \App\Models\User::count();
        $totalDownloads = Kitab::sum('downloads');
        $totalViews = Kitab::sum('views');

        return view('HomeAdmin', compact(
            'totalKitabs',
            'totalUsers',
            'totalDownloads',
            'totalViews'
        ));
    }

    public function CRUDAdmin()
    {
        $query = Kitab::query()->latest();

        if (Schema::hasTable('kitab_transcript_segments')) {
            $query->withCount([
                'transcriptSegments as transcript_segments_count' => function ($builder) {
                    $builder->where('is_active', true);
                },
            ]);
        }

        $kitabs = $query->paginate(10);
        return view('AdminCRUD', compact('kitabs'));
    }

    public function TambahKitab()
    {
        $categories = CategoryKatalog::getActiveForSelect();
        return view('TambahKitab', compact('categories'));
    }

    public function AddKitab(Request $request)
    {
        Log::info('AddKitab method called', [
            'request_data' => $request->all(),
            'has_files' => [
                'pdf' => $request->hasFile('file_pdf'),
                'cover' => $request->hasFile('cover')
            ]
        ]);

        try {
            // Validasi input dasar saja
            $validated = $request->validate([
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:255',
                'deskripsi' => 'required',
                'kategori' => 'required',
                'bahasa' => 'required|string|max:100',
            ]);

            Log::info('Basic validation passed', ['validated_data' => $validated]);

            // Validasi file wajib
            if (!$request->hasFile('file_pdf') || !$request->hasFile('cover')) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File PDF dan Cover wajib diupload.',
                        'errors' => [
                            'file_pdf' => $request->hasFile('file_pdf') ? [] : ['File PDF wajib diupload.'],
                            'cover' => $request->hasFile('cover') ? [] : ['Cover wajib diupload.']
                        ]
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'File PDF dan Cover wajib diupload.');
            }

            // Buat folder jika belum ada
            $pdfPath = public_path('pdf');
            $coverPath = public_path('cover');

            if (!file_exists($pdfPath)) {
                mkdir($pdfPath, 0755, true);
                Log::info('Created PDF directory', ['path' => $pdfPath]);
            }
            if (!file_exists($coverPath)) {
                mkdir($coverPath, 0755, true);
                Log::info('Created cover directory', ['path' => $coverPath]);
            }

            // Simpan file PDF
            $pdf = $request->file('file_pdf');
            $pdfName = time() . '_' . str_replace(' ', '_', $pdf->getClientOriginalName());
            $pdf->move($pdfPath, $pdfName);
            Log::info('PDF file saved', ['filename' => $pdfName, 'path' => $pdfPath]);

            // Simpan file Cover
            $cover = $request->file('cover');
            $coverName = time() . '_' . str_replace(' ', '_', $cover->getClientOriginalName());
            $cover->move($coverPath, $coverName);
            Log::info('Cover file saved', ['filename' => $coverName, 'path' => $coverPath]);

            // Simpan ke database langsung
            $kitabData = [
                'judul' => $validated['judul'],
                'penulis' => $validated['penulis'],
                'deskripsi' => $validated['deskripsi'],
                'kategori' => $validated['kategori'],
                'bahasa' => $validated['bahasa'],
                'file_pdf' => $pdfName,
                'cover' => $coverName,
                'views' => 0,
                'downloads' => 0,
                'viewed_by' => json_encode([]),
            ];

            Log::info('Creating kitab with data', ['kitab_data' => $kitabData]);
            $kitab = Kitab::create($kitabData);
            Log::info('Kitab created successfully', ['kitab_id' => $kitab->id_kitab]);

            $transcriptNotice = null;
            try {
                $importResult = $this->transcriptImportService->import($kitab, true);
                $transcriptNotice = "Transcript awal dibuat ({$importResult['page_segments']} halaman, {$importResult['chapter_segments']} bab).";
            } catch (\Throwable $error) {
                Log::warning('Automatic transcript import failed after kitab creation', [
                    'kitab_id' => $kitab->id_kitab,
                    'error' => $error->getMessage(),
                ]);
                $transcriptNotice = 'Kitab tersimpan, tetapi transcript otomatis belum berhasil dibuat.';
            }

            // Buat notifikasi di database
            $notificationData = [
                'title' => 'Kitab Baru Tersedia!',
                'message' => "Kitab '{$validated['judul']}' oleh {$validated['penulis']} telah ditambahkan. Yuk baca sekarang!",
                'type' => 'new_kitab',
                'action_url' => "/kitab/{$kitab->id_kitab}",
                'data' => json_encode([
                    'kitab_id' => $kitab->id_kitab,
                    'judul' => $kitab->judul,
                    'penulis' => $kitab->penulis,
                    'created_at' => $kitab->created_at->toISOString()
                ])
            ];

            try {
                $notification = AppNotification::create($notificationData);
                Log::info('Notification created', ['notification_id' => $notification->id]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification: ' . $e->getMessage());
            }

            // Broadcast event
            try {
                broadcast(new NewKitabAdded($kitab, [
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'type' => $notificationData['type'],
                    'action_url' => $notificationData['action_url']
                ]));
                Log::info('NewKitabAdded event broadcasted successfully');
            } catch (\Exception $e) {
                Log::error('Failed to broadcast event: ' . $e->getMessage());
            }

            // Kirim FCM notification
            try {
                $fcmResult = $this->fcmService->sendNewKitabNotification($kitab);
                Log::info('FCM notification sent', ['result' => $fcmResult]);
            } catch (\Exception $e) {
                Log::error('FCM notification failed: ' . $e->getMessage());
            }

            // Buat audit log
            try {
                \App\Models\AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'create',
                    'table_name' => 'kitab',
                    'record_id' => $kitab->id_kitab,
                    'old_values' => null,
                    'new_values' => json_encode($kitab->toArray()),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                Log::info('Audit log created successfully');
            } catch (\Exception $e) {
                Log::error('Failed to create audit log: ' . $e->getMessage());
            }

            // Return response
            if ($request->ajax()) {
                Log::info('Returning AJAX success response');
                return response()->json([
                    'success' => true,
                    'message' => 'Kitab berhasil ditambahkan! ' . $transcriptNotice,
                    'kitab' => [
                        'id_kitab' => $kitab->id_kitab,
                        'judul' => $kitab->judul,
                        'penulis' => $kitab->penulis,
                        'kategori' => $kitab->kategori,
                        'bahasa' => $kitab->bahasa,
                        'file_pdf' => $kitab->file_pdf,
                        'cover' => $kitab->cover,
                        'created_at' => $kitab->created_at->format('Y-m-d H:i:s')
                    ]
                ]);
            }

            Log::info('Returning redirect response');
            return redirect('manejemenkitab')->with('success', 'Kitab berhasil ditambahkan! ' . $transcriptNotice);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in AddKitab: ' . $e->getMessage(), [
                'errors' => $e->errors()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Validasi gagal. Silakan periksa kembali input Anda.');

        } catch (\Exception $e) {
            Log::error('Error in AddKitab: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan kitab: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan kitab. Silakan coba lagi.');
        }
    }

    public function EditKitab($id_kitab)
    {
        $kitab = Kitab::find($id_kitab);

        if (!$kitab) {
            abort(404, 'Kitab tidak ditemukan');
        }

        $categories = CategoryKatalog::getActiveForSelect();
        $transcriptSegments = collect();
        $transcriptStats = [
            'count' => 0,
            'page_segments' => 0,
            'chapter_segments' => 0,
            'summary_segments' => 0,
            'toc_segments' => 0,
        ];

        if (Schema::hasTable('kitab_transcript_segments')) {
            $transcriptSegments = $kitab->transcriptSegments()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(10)
                ->get([
                    'id',
                    'transcript_type',
                    'title',
                    'page_start',
                    'page_end',
                    'content',
                    'content_translation',
                    'content_arabic',
                    'updated_at',
                ]);

            $transcriptStats = [
                'count' => (int) $kitab->transcriptSegments()->where('is_active', true)->count(),
                'page_segments' => (int) $kitab->transcriptSegments()->where('is_active', true)->where('transcript_type', 'page')->count(),
                'chapter_segments' => (int) $kitab->transcriptSegments()->where('is_active', true)->where('transcript_type', 'chapter')->count(),
                'summary_segments' => (int) $kitab->transcriptSegments()->where('is_active', true)->where('transcript_type', 'summary')->count(),
                'toc_segments' => (int) $kitab->transcriptSegments()->where('is_active', true)->where('transcript_type', 'toc')->count(),
            ];
        }

        return view('EditKitab', compact('kitab', 'categories', 'transcriptSegments', 'transcriptStats'));
    }

    public function updateKitab(Request $request, $id_kitab)
    {
        try {
            // Cari kitab berdasarkan ID
            $kitab = Kitab::findOrFail($id_kitab);

            // Validasi input
            $validated = $request->validate([
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:255',
                'kategori' => 'required|string|max:255',
                'bahasa' => 'required|string|max:100',
                'deskripsi' => 'required|string',
                'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'file_pdf' => 'nullable|mimes:pdf|max:20480', // 20MB
            ]);

            // Update file PDF jika ada
            if ($request->hasFile('file_pdf')) {
                $pdf = $request->file('file_pdf');
                $pdfName = time() . '_' . str_replace(' ', '_', $pdf->getClientOriginalName());
                $pdf->move(public_path('pdf'), $pdfName);
                $validated['file_pdf'] = $pdfName;
            }

            // Update file cover jika ada
            if ($request->hasFile('cover')) {
                $cover = $request->file('cover');
                $coverName = time() . '_' . str_replace(' ', '_', $cover->getClientOriginalName());
                $cover->move(public_path('cover'), $coverName);
                $validated['cover'] = $coverName;
            }

            // Update data kitab
            $kitab->update($validated);

            $shouldImportTranscript = $request->hasFile('file_pdf')
                || !$kitab->transcriptSegments()->where('is_active', true)->exists();

            $message = 'Kitab berhasil diperbarui!';
            if ($shouldImportTranscript) {
                try {
                    $importResult = $this->transcriptImportService->import($kitab->fresh(), true);
                    $message .= " Transcript diperbarui ({$importResult['page_segments']} halaman, {$importResult['chapter_segments']} bab).";
                } catch (\Throwable $error) {
                    Log::warning('Automatic transcript import failed after kitab update', [
                        'kitab_id' => $kitab->id_kitab,
                        'error' => $error->getMessage(),
                    ]);
                    $message .= ' Data kitab tersimpan, tetapi transcript otomatis belum berhasil dibuat.';
                }
            }

            return redirect('manejemenkitab')->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Validasi gagal. Silakan periksa kembali input Anda.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui kitab. Silakan coba lagi.');
        }
    }

    public function DeleteKitab($id_kitab)
    {
        try {
            $kitab = Kitab::findOrFail($id_kitab);
            $kitab->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Kitab berhasil dihapus!']);
            }

            return redirect('manejemenkitab')->with('success', 'Kitab berhasil dihapus!');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus kitab.'], 500);
            }
            return redirect('manejemenkitab')->with('error', 'Gagal menghapus kitab. Silakan coba lagi.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:kitab,id_kitab',
        ]);

        $count = Kitab::whereIn('id_kitab', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} kitab berhasil dihapus.",
        ]);
    }

    public function bulkExport(Request $request)
    {
        $ids = $request->query('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }

        $kitabs = !empty($ids)
            ? Kitab::whereIn('id_kitab', $ids)->orderBy('judul')->get()
            : Kitab::orderBy('judul')->get();

        $filename = 'kitab_export_' . date('Y-m-d_H-i') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($kitabs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Judul', 'Penulis', 'Kategori', 'Bahasa', 'Views', 'Downloads']);

            foreach ($kitabs as $k) {
                fputcsv($file, [
                    $k->id_kitab,
                    $k->judul,
                    $k->penulis,
                    $k->kategori,
                    $k->bahasa,
                    $k->views ?? 0,
                    $k->downloads ?? 0,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importTranscript(Request $request, $id_kitab)
    {
        $kitab = Kitab::findOrFail($id_kitab);
        $force = $request->boolean('force', true);

        try {
            $result = $this->transcriptImportService->import($kitab, $force);

            return response()->json([
                'success' => true,
                'message' => "Transcript untuk '{$kitab->judul}' berhasil diproses.",
                'data' => $result,
            ]);
        } catch (\Throwable $error) {
            Log::error('Failed to import transcript', [
                'kitab_id' => $kitab->id_kitab,
                'error' => $error->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $error->getMessage() ?: 'Gagal memproses transcript kitab.',
            ], 422);
        }
    }

    public function bulkImportTranscripts(Request $request)
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $validated = $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'integer|exists:kitab,id_kitab',
            'force' => 'nullable|boolean',
            'only_missing' => 'nullable|boolean',
        ]);

        $query = Kitab::query()->orderBy('id_kitab');
        $ids = collect($validated['ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values();

        if ($ids->isNotEmpty()) {
            $query->whereIn('id_kitab', $ids->all());
        }

        if (($validated['only_missing'] ?? false) === true) {
            $query->whereDoesntHave('transcriptSegments', function ($builder) {
                $builder->where('is_active', true);
            });
        }

        $kitabs = $query->get();
        $force = (bool) ($validated['force'] ?? true);

        $success = [];
        $failed = [];
        $skipped = [];

        foreach ($kitabs as $kitab) {
            try {
                $result = $this->transcriptImportService->import($kitab, $force);
                if (($result['status'] ?? '') === 'skipped') {
                    $skipped[] = [
                        'id_kitab' => $kitab->id_kitab,
                        'judul' => $kitab->judul,
                        'message' => $result['message'],
                    ];
                } else {
                    $success[] = [
                        'id_kitab' => $kitab->id_kitab,
                        'judul' => $kitab->judul,
                        'page_segments' => $result['page_segments'],
                        'chapter_segments' => $result['chapter_segments'],
                        'total_segments' => $result['total_segments'],
                    ];
                }
            } catch (\Throwable $error) {
                Log::error('Bulk transcript import failed', [
                    'kitab_id' => $kitab->id_kitab,
                    'error' => $error->getMessage(),
                ]);

                $failed[] = [
                    'id_kitab' => $kitab->id_kitab,
                    'judul' => $kitab->judul,
                    'message' => $error->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => count($failed) === 0,
            'message' => 'Import transcript selesai diproses.',
            'data' => [
                'processed' => $kitabs->count(),
                'success_count' => count($success),
                'failed_count' => count($failed),
                'skipped_count' => count($skipped),
                'successes' => $success,
                'failures' => $failed,
                'skipped' => $skipped,
            ],
        ], count($failed) > 0 ? 207 : 200);
    }
}
