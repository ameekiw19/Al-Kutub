<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\UserNotificationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiNotificationSettingsController extends Controller
{
    /**
     * Get current user's notification settings.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $settings = UserNotificationSetting::firstOrCreate(
            ['user_id' => $user->id],
            $this->defaultSettings()
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification settings loaded successfully',
            'data' => $this->serializeSettings($settings),
        ]);
    }

    /**
     * Update current user's notification settings.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'enable_notifications' => 'sometimes|boolean',
            'new_book_notifications' => 'sometimes|boolean',
            'update_notifications' => 'sometimes|boolean',
            'reminder_notifications' => 'sometimes|boolean',
            'quiet_hours_enabled' => 'sometimes|boolean',
            'quiet_hours_start' => 'sometimes|date_format:H:i',
            'quiet_hours_end' => 'sometimes|date_format:H:i',
            'sound_enabled' => 'sometimes|boolean',
            'vibration_enabled' => 'sometimes|boolean',
            'led_enabled' => 'sometimes|boolean',
            'notification_style' => 'sometimes|in:BASIC,EXPANDED,SILENT',
            'categories' => 'sometimes|array',
            'categories.*' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings = UserNotificationSetting::firstOrCreate(
            ['user_id' => $user->id],
            $this->defaultSettings()
        );

        $before = $this->serializeSettings($settings);
        $payload = $validator->validated();

        $settings->fill($payload);
        $settings->save();

        AuditLog::logAuth('notification_settings_updated', $user->id, [
            'before' => $before,
            'after' => $this->serializeSettings($settings),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully',
            'data' => $this->serializeSettings($settings),
        ]);
    }

    private function defaultSettings(): array
    {
        return [
            'enable_notifications' => true,
            'new_book_notifications' => true,
            'update_notifications' => true,
            'reminder_notifications' => true,
            'quiet_hours_enabled' => false,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
            'sound_enabled' => true,
            'vibration_enabled' => true,
            'led_enabled' => true,
            'notification_style' => 'BASIC',
            'categories' => [
                'islamic' => true,
                'education' => true,
                'literature' => true,
                'history' => true,
                'science' => true,
            ],
        ];
    }

    private function serializeSettings(UserNotificationSetting $settings): array
    {
        return [
            'enable_notifications' => (bool) $settings->enable_notifications,
            'new_book_notifications' => (bool) $settings->new_book_notifications,
            'update_notifications' => (bool) $settings->update_notifications,
            'reminder_notifications' => (bool) $settings->reminder_notifications,
            'quiet_hours_enabled' => (bool) $settings->quiet_hours_enabled,
            'quiet_hours_start' => $settings->quiet_hours_start,
            'quiet_hours_end' => $settings->quiet_hours_end,
            'sound_enabled' => (bool) $settings->sound_enabled,
            'vibration_enabled' => (bool) $settings->vibration_enabled,
            'led_enabled' => (bool) $settings->led_enabled,
            'notification_style' => $settings->notification_style,
            'categories' => (array) $settings->categories,
        ];
    }
}
