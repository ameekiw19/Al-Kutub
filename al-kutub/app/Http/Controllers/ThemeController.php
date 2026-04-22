<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    /**
     * Get user's theme preference
     */
    public function getTheme(): JsonResponse
    {
        try {
            $user = Auth::user();
            $theme = $user->theme_preference ?? 'light';
            
            return response()->json([
                'success' => true,
                'message' => 'Theme preference retrieved successfully',
                'data' => [
                    'theme' => $theme,
                    'theme_mode' => 'system',
                    'custom_colors' => null,
                    'server_theme' => null,
                    'last_sync' => null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve theme preference: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user's theme preference
     */
    public function updateTheme(Request $request): JsonResponse
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'theme' => 'required|in:light,dark,auto,custom',
                'custom_colors' => 'nullable|array',
                'custom_colors.*' => 'required|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $theme = $request->theme;
            $customColors = $request->custom_colors ?? [];
            
            $user = Auth::user();
            $user->theme_preference = $theme;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Theme preference updated successfully',
                'data' => [
                    'theme' => $theme,
                    'theme_mode' => $this->getThemeMode($theme),
                    'custom_colors' => $customColors,
                    'server_theme' => null,
                    'last_sync' => now()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update theme preference: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get server theme configuration
     */
    public function getServerTheme(): JsonResponse
    {
        try {
            // In a real implementation, this would fetch from database or config
            $serverTheme = [
                'theme' => 'light',
                'theme_mode' => 'system',
                'custom_colors' => [
                    'primary' => '#44A194',
                    'secondary' => '#76D3C6',
                    'background' => '#E0F2F1',
                    'surface' => '#F8F9FA',
                    'error' => '#EF4444'
                ],
                'last_updated' => now(),
                'version' => '1.0.0'
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Server theme configuration retrieved successfully',
                'data' => $serverTheme
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve server theme: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync theme with server
     */
    public function syncTheme(Request $request): JsonResponse
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'server_theme' => 'required|array',
                'server_theme.*' => 'required|string',
                'force_sync' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = Auth::user();
            $serverTheme = $request->server_theme;
            $forceSync = $request->force_sync ?? false;
            
            // Update server theme in database (would be implemented)
            $lastSyncTime = now();
            
            return response()->json([
                'success' => true,
                'message' => 'Theme synced successfully',
                'data' => [
                    'server_theme' => $serverTheme,
                    'last_sync' => $lastSyncTime,
                    'version' => '1.0.0'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync theme: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get theme mode (light/dark/custom)
     */
    private function getThemeMode(string $theme): string
    {
        return match ($theme) {
            'light' => 'system',
            'dark' => 'system',
            'custom' => 'custom'
        };
    }
}
