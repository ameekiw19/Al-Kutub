<?php

namespace App\Http\Controllers;

use App\Models\ReadingNote;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ApiReadingNoteController extends Controller
{
    /**
     * Display a listing of the user's reading notes.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $kitabId = $request->query('kitab_id');
            
            $query = ReadingNote::forUser($user->id)
                ->with(['kitab:id_kitab,judul'])
                ->orderBy('created_at', 'desc');
            
            if ($kitabId) {
                $query->forKitab($kitabId);
            }
            
            $notes = $query->paginate(20);
            
            return response()->json([
                'success' => true,
                'message' => 'Reading notes retrieved successfully',
                'data' => $notes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reading notes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created reading note.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'kitab_id' => 'required|exists:kitab,id_kitab',
                'note_content' => 'required|string|max:2000',
                'page_number' => 'nullable|integer|min:1',
                'highlighted_text' => 'nullable|string|max:500',
                'note_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'is_private' => 'nullable|boolean',
                'client_request_id' => 'nullable|string|max:64',
                'client_updated_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $clientRequestId = trim((string) $request->input('client_request_id', ''));
            if ($clientRequestId === '') {
                $clientRequestId = null;
            }

            if ($clientRequestId !== null) {
                $existing = ReadingNote::query()
                    ->where('user_id', $user->id)
                    ->where('client_request_id', $clientRequestId)
                    ->first();

                if ($existing) {
                    $existing->load(['kitab:id_kitab,judul']);
                    return response()->json([
                        'success' => true,
                        'message' => 'Catatan sudah tersimpan sebelumnya',
                        'data' => $existing
                    ], 200);
                }
            }

            $clientUpdatedAt = $request->filled('client_updated_at')
                ? Carbon::parse($request->input('client_updated_at'))
                : null;

            $note = new ReadingNote([
                'user_id' => $user->id,
                'kitab_id' => $request->kitab_id,
                'note_content' => $request->note_content,
                'page_number' => $request->page_number,
                'highlighted_text' => $request->highlighted_text,
                'note_color' => $request->note_color ?? '#FFFF00',
                'is_private' => $request->is_private ?? true,
                'client_request_id' => $clientRequestId,
            ]);
            if ($clientUpdatedAt !== null) {
                $note->created_at = $clientUpdatedAt;
                $note->updated_at = $clientUpdatedAt;
            }
            $note->save();

            $note->load(['kitab:id_kitab,judul']);

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil dibuat',
                'data' => $note
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reading note: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified reading note.
     */
    public function show(ReadingNote $readingNote): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns the note or it's public
            if ($readingNote->user_id !== $user->id && $readingNote->is_private) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this note'
                ], 403);
            }

            $readingNote->load(['user:id,username', 'kitab:id_kitab,judul']);

            return response()->json([
                'success' => true,
                'message' => 'Reading note retrieved successfully',
                'data' => $readingNote
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reading note: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified reading note.
     */
    public function update(Request $request, ReadingNote $readingNote): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns the note
            if ($readingNote->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this note'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'note_content' => 'required|string|max:2000',
                'page_number' => 'nullable|integer|min:1',
                'highlighted_text' => 'nullable|string|max:500',
                'note_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'is_private' => 'nullable|boolean',
                'client_updated_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $clientUpdatedAt = $request->filled('client_updated_at')
                ? Carbon::parse($request->input('client_updated_at'))
                : null;
            $serverUpdatedAt = $readingNote->updated_at ? Carbon::parse($readingNote->updated_at) : null;
            $isStaleSnapshot = $clientUpdatedAt !== null &&
                $serverUpdatedAt !== null &&
                $clientUpdatedAt->lt($serverUpdatedAt);

            if ($isStaleSnapshot) {
                $readingNote->load(['kitab:id_kitab,judul']);
                return response()->json([
                    'success' => true,
                    'message' => 'Snapshot catatan lama diabaikan, data terbaru dipertahankan',
                    'data' => $readingNote
                ], 200);
            }

            $readingNote->update([
                'note_content' => $request->note_content,
                'page_number' => $request->page_number,
                'highlighted_text' => $request->highlighted_text,
                'note_color' => $request->note_color ?? $readingNote->note_color,
                'is_private' => $request->is_private ?? $readingNote->is_private,
            ]);

            if ($clientUpdatedAt !== null && $readingNote->updated_at && $clientUpdatedAt->greaterThan($readingNote->updated_at)) {
                $readingNote->forceFill(['updated_at' => $clientUpdatedAt])->save();
            }

            $readingNote->load(['kitab:id_kitab,judul']);

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil diperbarui',
                'data' => $readingNote
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reading note: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified reading note.
     */
    public function destroy(ReadingNote $readingNote): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns the note
            if ($readingNote->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this note'
                ], 403);
            }

            $readingNote->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reading note deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reading note: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reading notes statistics for the user.
     */
    public function stats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $totalNotes = ReadingNote::forUser($user->id)->count();
            $publicNotes = ReadingNote::forUser($user->id)->public()->count();
            $privateNotes = ReadingNote::forUser($user->id)->private()->count();
            
            // Notes per kitab
            $notesPerKitab = ReadingNote::forUser($user->id)
                ->join('kitab', 'reading_notes.kitab_id', '=', 'kitab.id_kitab')
                ->selectRaw('kitab.judul, COUNT(*) as note_count')
                ->groupBy('kitab.id_kitab', 'kitab.judul')
                ->orderBy('note_count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Reading notes statistics retrieved successfully',
                'data' => [
                    'total_notes' => $totalNotes,
                    'public_notes' => $publicNotes,
                    'private_notes' => $privateNotes,
                    'notes_per_kitab' => $notesPerKitab
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reading notes statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
