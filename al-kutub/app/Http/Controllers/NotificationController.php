<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private function baseNotificationsQuery(int $userId)
    {
        return AppNotification::query()
            ->leftJoin('notification_user_reads as nur', function ($join) use ($userId) {
                $join->on('nur.notification_id', '=', 'app_notifications.id')
                    ->where('nur.user_id', '=', $userId);
            })
            ->select('app_notifications.*', 'nur.read_at');
    }

    private function unreadNotificationIdsForUser(int $userId): array
    {
        return $this->baseNotificationsQuery($userId)
            ->whereNull('read_at')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
    }

    private function unreadCountForUser(int $userId): int
    {
        return $this->baseNotificationsQuery($userId)
            ->whereNull('read_at')
            ->count();
    }

    private function markNotificationsReadForUser(int $userId, array $notificationIds, $readAt): int
    {
        if (empty($notificationIds)) {
            return 0;
        }

        $rows = collect($notificationIds)
            ->unique()
            ->map(function ($notificationId) use ($userId, $readAt) {
                return [
                    'user_id' => $userId,
                    'notification_id' => (int) $notificationId,
                    'read_at' => $readAt,
                    'created_at' => $readAt,
                    'updated_at' => $readAt,
                ];
            })
            ->values()
            ->all();

        DB::table('notification_user_reads')->upsert(
            $rows,
            ['user_id', 'notification_id'],
            ['read_at', 'updated_at']
        );

        return count($rows);
    }

    public function index()
    {
        $user = Auth::user();
        $notifications = $this->baseNotificationsQuery($user->id)
            ->orderBy('app_notifications.created_at', 'desc')
            ->take(50)
            ->get();
        $unreadCount = $this->unreadCountForUser($user->id);

        return view('NotificationView', compact('notifications', 'unreadCount'));
    }

    public function unreadCount()
    {
        $user = Auth::user();
        $count = $this->unreadCountForUser($user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();

        $notification = AppNotification::find($id);
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $readAt = now();
        $this->markNotificationsReadForUser($user->id, [(int) $notification->id], $readAt);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'notification_id' => (int) $notification->id,
                'read_at' => $readAt->toISOString(),
                'unread_count' => (int) $this->unreadCountForUser($user->id),
            ],
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $readAt = now();

        $unreadIds = $this->unreadNotificationIdsForUser($user->id);
        $markedCount = $this->markNotificationsReadForUser($user->id, $unreadIds, $readAt);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'data' => [
                'marked_count' => (int) $markedCount,
                'unread_count' => (int) $this->unreadCountForUser($user->id),
                'read_at' => $readAt->toISOString(),
            ],
        ]);
    }
}
