<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kitab;
use App\Services\KitabTranscriptService;
use Illuminate\Http\JsonResponse;

class KitabTranscriptController extends Controller
{
    public function show(KitabTranscriptService $transcriptService, int $id_kitab): JsonResponse
    {
        $kitab = Kitab::published()->find($id_kitab);

        if (!$kitab) {
            return response()->json([
                'success' => false,
                'message' => 'Kitab tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transcript kitab ditemukan',
            'data' => $transcriptService->buildPayload($kitab),
        ]);
    }
}
