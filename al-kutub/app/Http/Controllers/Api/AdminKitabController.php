<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kitab;
use App\Models\AppNotification;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminKitabController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Store a newly created kitab via AJAX
     */
    public function store(Request $request)
    {
        Log::info('AJAX Kitab submission started', [
            'request_data' => $request->all(),
            'has_files' => [
                'pdf' => $request->hasFile('file_pdf'),
                'cover' => $request->hasFile('cover')
            ]
        ]);

        try {
            // Validate request
            $request->validate([
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:255',
                'deskripsi' => 'required',
                'kategori' => 'required|string',
                'bahasa' => 'required|string|max:100',
                'file_pdf' => 'required|file|mimes:pdf|max:10240',
                'cover' => 'required|file|image|mimes:jpeg,png,jpg,webp|max:2048',
            ], [
                'file_pdf.required' => 'File PDF harus diupload',
                'file_pdf.mimes' => 'File PDF harus berformat PDF',
                'file_pdf.max' => 'File PDF maksimal 10MB',
                'cover.required' => 'Cover harus diupload',
                'cover.mimes' => 'Cover harus berformat gambar (JPG/PNG)',
                'cover.max' => 'Cover maksimal 2MB',
            ]);

            Log::info('Validation passed');

            // Start database transaction
            DB::beginTransaction();

            try {
                // Handle file uploads
                $pdfPath = $this->handleFileUpload($request->file('file_pdf'), 'pdf');
                $coverPath = $this->handleFileUpload($request->file('cover'), 'cover');

                Log::info('Files uploaded', ['pdf' => $pdfPath, 'cover' => $coverPath]);

                // Create kitab
                $kitab = Kitab::create([
                    'judul' => $request->judul,
                    'penulis' => $request->penulis,
                    'deskripsi' => $request->deskripsi,
                    'kategori' => $request->kategori,
                    'bahasa' => $request->bahasa,
                    'file_pdf' => $pdfPath,
                    'cover' => $coverPath,
                    'views' => 0,
                    'downloads' => 0,
                    'viewed_by' => json_encode([]),
                    'publication_status' => 'draft',
                ]);

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
     * Handle file upload with proper error handling
     */
    private function handleFileUpload($file, $type)
    {
        Log::info("Processing {$type} file upload", [
            'file_exists' => !!$file,
            'file_valid' => $file ? $file->isValid() : false,
            'file_error' => $file ? $file->getError() : 'no file',
            'file_size' => $file ? $file->getSize() : 0,
            'mime_type' => $file ? $file->getMimeType() : 'unknown'
        ]);

        if (!$file) {
            throw new \Exception("File {$type} tidak ditemukan");
        }

        if (!$file->isValid()) {
            $errorMessage = $file->getErrorMessage();
            $errorCode = $file->getError();
            Log::error("File {$type} validation failed", [
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'original_name' => $file->getClientOriginalName()
            ]);
            throw new \Exception("File {$type} tidak valid: {$errorMessage} (Error: {$errorCode})");
        }

        // Create directory if not exists
        $uploadPath = public_path($type);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
            Log::info("Created {$type} directory", ['path' => $uploadPath]);
        }

        // Generate unique filename
        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $filepath = $uploadPath . '/' . $filename;

        try {
            // Check if source file exists
            if (!file_exists($file->getPathname())) {
                Log::error("Source file does not exist", [
                    'source_path' => $file->getPathname(),
                    'original_name' => $file->getClientOriginalName()
                ]);
                throw new \Exception("File sumber tidak ditemukan");
            }

            // Move file using copy instead of move for better compatibility
            if (!copy($file->getPathname(), $filepath)) {
                throw new \Exception("Gagal menyalin file {$type}");
            }
            
            // Verify file was copied
            if (!file_exists($filepath)) {
                throw new \Exception("File {$type} tidak tersimpan setelah penyalinan");
            }
            
            Log::info("File {$type} uploaded successfully", [
                'filename' => $filename,
                'path' => $filepath,
                'size' => filesize($filepath),
                'original_name' => $file->getClientOriginalName()
            ]);

            return $filename;

        } catch (\Exception $e) {
            Log::error("Failed to upload {$type} file", [
                'error' => $e->getMessage(),
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'source_path' => $file->getPathname(),
                'destination_path' => $filepath
            ]);
            throw new \Exception("Gagal mengupload file {$type}: " . $e->getMessage());
        }
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
}
