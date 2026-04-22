<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApiFcmController extends Controller
{
    private $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Save FCM token from mobile app
     * POST /api/fcm/token
     */
    public function saveToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_token' => 'required|string',
                'device_type' => 'string|in:android,ios',
                'app_version' => 'string|nullable'
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak diizinkan'
                ], 401);
            }

            // Deactivate old tokens for this user (optional - keep multiple devices)
            // FcmToken::deactivateUserTokens($user->id);

            // Check if token already exists
            $existingToken = FcmToken::where('device_token', $validated['device_token'])->first();
            
            if ($existingToken) {
                // Update existing token
                $existingToken->update([
                    'user_id' => $user->id,
                    'device_type' => $validated['device_type'] ?? 'android',
                    'app_version' => $validated['app_version'],
                    'is_active' => true
                ]);
            } else {
                // Create new token
                FcmToken::create([
                    'user_id' => $user->id,
                    'device_token' => $validated['device_token'],
                    'device_type' => $validated['device_type'] ?? 'android',
                    'app_version' => $validated['app_version'],
                    'is_active' => true
                ]);
            }

            // Subscribe to general topic
            $this->fcmService->subscribeToTopic($validated['device_token'], 'all_users');

            Log::info('FCM Token saved', [
                'user_id' => $user->id,
                'device_token' => substr($validated['device_token'], 0, 20) . '...',
                'device_type' => $validated['device_type'] ?? 'android'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM berhasil disimpan'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to save FCM token', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan token FCM'
            ], 500);
        }
    }

    /**
     * Remove FCM token (logout)
     * DELETE /api/fcm/token
     */
    public function removeToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_token' => 'required|string'
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak diizinkan'
                ], 401);
            }

            // Deactivate the token
            FcmToken::where('user_id', $user->id)
                   ->where('device_token', $validated['device_token'])
                   ->update(['is_active' => false]);

            Log::info('FCM Token deactivated', [
                'user_id' => $user->id,
                'device_token' => substr($validated['device_token'], 0, 20) . '...'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to remove FCM token', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus token FCM'
            ], 500);
        }
    }

    /**
     * Test FCM notification
     * POST /api/fcm/test
     */
    public function testNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'message' => 'required|string',
                'device_token' => 'string|nullable'
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak diizinkan'
                ], 401);
            }

            // Send to specific token or all tokens
            if (!empty($validated['device_token'])) {
                $result = $this->fcmService->sendToDevice(
                    $validated['device_token'],
                    $validated['title'],
                    $validated['message'],
                    ['type' => 'test']
                );
            } else {
                // Send to all user's tokens
                $userTokens = FcmToken::getUserTokens($user->id);
                if (empty($userTokens)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada token FCM aktif untuk user ini'
                    ]);
                }

                $result = $this->fcmService->sendToDevices(
                    $userTokens,
                    $validated['title'],
                    $validated['message'],
                    ['type' => 'test']
                );
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Notifikasi tes berhasil dikirim' : 'Gagal mengirim notifikasi tes',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send test notification', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi tes'
            ], 500);
        }
    }

    /**
     * Get user's FCM tokens
     * GET /api/fcm/tokens
     */
    public function getUserTokens()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $tokens = FcmToken::where('user_id', $user->id)
                             ->where('is_active', true)
                             ->get()
                             ->map(function ($token) {
                                 return [
                                     'id' => $token->id,
                                     'device_type' => $token->device_type,
                                     'app_version' => $token->app_version,
                                     'created_at' => $token->created_at,
                                     // Don't return the actual token for security
                                 ];
                             });

            return response()->json([
                'success' => true,
                'data' => $tokens
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user FCM tokens', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil token FCM'
            ], 500);
        }
    }
}
