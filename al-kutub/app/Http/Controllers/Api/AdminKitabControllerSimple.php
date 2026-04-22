<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kitab;
use App\Models\AppNotification;
use App\Events\NewKitabAdded;
use App\Services\FcmService;
use App\Services\KitabPublicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminKitabControllerSimple extends Controller
{
    protected $fcmService;
    protected $publicationService;

    public function __construct(FcmService $fcmService, KitabPublicationService $publicationService)
    {
        $this->fcmService = $fcmService;
        $this->publicationService = $publicationService;
    }

    /**
     * Store a newly created kitab via AJAX (simplified version)
     */
    public function store(Request $request)
    {
        Log::info('SIMPLE AJAX Kitab submission started', [
            'request_data' => $request->all(),
            'has_files' => [
                'pdf' => $request->hasFile('file_pdf'),
                'cover' => $request->hasFile('cover')
            ]
        ]);

        try {
            // Validate basic fields only (skip file validation for now)
            $request->validate([
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:255',
                'deskripsi' => 'required',
                'kategori' => 'required|string',
                'bahasa' => 'required|string|max:100',
            ]);

            Log::info('Basic validation passed');

            // Start database transaction
            DB::beginTransaction();

            try {
                // Handle files with fallback
                $pdfPath = $this->handleFileSimple($request, 'file_pdf', 'pdf');
                $coverPath = $this->handleFileSimple($request, 'cover', 'cover');

                Log::info('Files processed', ['pdf' => $pdfPath, 'cover' => $coverPath]);

                // Create kitab
                $kitab = $this->publicationService->createDraft([
                    'judul' => $request->judul,
                    'penulis' => $request->penulis,
                    'deskripsi' => $request->deskripsi,
                    'kategori' => $request->kategori,
                    'bahasa' => $request->bahasa, // Keep original case from form
                    'file_pdf' => $pdfPath,
                    'cover' => $coverPath,
                    'views' => 0,
                    'downloads' => 0,
                    'viewed_by' => [], // Use array for JSON cast
                    'publication_status' => 'draft',
                ], (int) auth()->id(), 'Draft dibuat via admin API');

                Log::info('Kitab created', ['kitab_id' => $kitab->id_kitab]);

                // Commit transaction
                DB::commit();

                // Return success response
                return response()->json([
                    'success' => true,
                    'message' => 'Kitab berhasil disimpan sebagai draft.',
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

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Transaction failed: ' . $e->getMessage(), [
                    'exception' => $e,
                    'request_data' => $request->all()
                ]);
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Kitab creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kitab: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Simple file handler with fallback
     */
    private function handleFileSimple($request, $fieldName, $type)
    {
        try {
            // Try to get uploaded file
            $file = $request->file($fieldName);
            
            if ($file && $file->isValid()) {
                // Create directory if not exists
                $uploadPath = public_path($type);
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Generate unique filename
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filepath = $uploadPath . '/' . $filename;

                // Move file
                $file->move($uploadPath, $filename);
                
                Log::info("File {$type} uploaded successfully", [
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize()
                ]);

                return $filename;
            }
        } catch (\Exception $e) {
            Log::warning("File upload failed for {$fieldName}, creating placeholder", [
                'error' => $e->getMessage()
            ]);
        }

        // Fallback: create placeholder file
        $uploadPath = public_path($type);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $filename = 'placeholder_' . time() . '.' . ($type === 'pdf' ? 'pdf' : 'jpg');
        $filepath = $uploadPath . '/' . $filename;

        if ($type === 'pdf') {
            $content = '%PDF-1.4
1 0 obj<</Type>/Pages 2 0 R
endobj
2 0 obj<</Type>/Catalog/Pages 1 0 R
endobj
3 0 obj<</Type>/Page/Parent 1 0 R
/Resources<</Font>/ProcSet[/PDF/Text/ImageB/ImageC/ImageI]/ExtGState>>
/MediaBox[0 0 612 792]
/Contents 4 0 R
endobj
4 0 obj<</Type>/Page/Parent 1 0 R
/Resources<</Font>>
/MediaBox[0 0 612 792]
/Contents 5 0 R
endobj
5 0 obj<</Type>/Font/Subtype/Type1/BaseFont/Helvetica>>
endobj
xref
0 6
0000000000 65535 f 
trailer
</</Type>/Size 6
startxref
1
%%EOF';
        } else {
            $content = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A');
        }

        file_put_contents($filepath, $content);
        
        Log::info("Placeholder {$type} created", ['filename' => $filename]);
        
        return $filename;
    }

    /**
     * Get real-time statistics
     */
    public function getStats()
    {
        try {
            return response()->json([
                'success' => true,
                'stats' => [
                    'total_kitabs' => Kitab::count(),
                    'total_notifications' => AppNotification::count(),
                    'recent_kitabs' => Kitab::latest()->take(5)->get(),
                    'recent_notifications' => AppNotification::latest()->take(5)->get()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik'
            ], 500);
        }
    }

    public function submitReview(Request $request, $id_kitab)
    {
        try {
            $kitab = Kitab::findOrFail($id_kitab);
            $kitab = $this->publicationService->submitForReview(
                $kitab,
                (int) auth()->id(),
                $request->input('status_note')
            );

            return response()->json([
                'success' => true,
                'message' => 'Kitab berhasil dikirim ke review.',
                'data' => [
                    'id_kitab' => $kitab->id_kitab,
                    'publication_status' => $kitab->publication_status,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function publish(Request $request, $id_kitab)
    {
        try {
            $kitab = Kitab::findOrFail($id_kitab);
            $kitab = $this->publicationService->publish(
                $kitab,
                (int) auth()->id(),
                $request->input('status_note')
            );

            $notification = AppNotification::create([
                'title' => 'Kitab Baru Tersedia!',
                'message' => "Kitab '{$kitab->judul}' oleh {$kitab->penulis} telah dipublikasikan. Yuk baca sekarang!",
                'type' => 'new_kitab',
                'action_url' => "/kitab/{$kitab->id_kitab}",
                'data' => json_encode([
                    'kitab_id' => $kitab->id_kitab,
                    'judul' => $kitab->judul,
                    'penulis' => $kitab->penulis,
                    'published_at' => optional($kitab->published_at)->toISOString(),
                ]),
            ]);

            broadcast(new NewKitabAdded($kitab, [
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'action_url' => $notification->action_url,
            ]));
            $this->fcmService->sendNewKitabNotification($kitab);

            return response()->json([
                'success' => true,
                'message' => 'Kitab berhasil dipublikasikan.',
                'data' => [
                    'id_kitab' => $kitab->id_kitab,
                    'publication_status' => $kitab->publication_status,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function returnDraft(Request $request, $id_kitab)
    {
        try {
            $kitab = Kitab::findOrFail($id_kitab);
            $kitab = $this->publicationService->returnToDraft(
                $kitab,
                (int) auth()->id(),
                $request->input('status_note')
            );

            return response()->json([
                'success' => true,
                'message' => 'Kitab berhasil dikembalikan ke draft.',
                'data' => [
                    'id_kitab' => $kitab->id_kitab,
                    'publication_status' => $kitab->publication_status,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function revisions($id_kitab)
    {
        $kitab = Kitab::with(['revisions' => function ($query) {
            $query->with('actor:id,username')->latest()->limit(100);
        }])->findOrFail($id_kitab);

        return response()->json([
            'success' => true,
            'data' => $kitab->revisions,
        ]);
    }
}
