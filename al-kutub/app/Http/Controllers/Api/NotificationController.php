<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Kitab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $limit = min((int) $request->get('limit', 20), 100);
            $notifications = $this->baseNotificationsQuery($user->id)
                ->orderBy('app_notifications.created_at', 'desc')
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notifications'
            ], 500);
        }
    }

    /**
     * Get latest notifications (for real-time updates)
     */
    public function latest(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $limit = min((int) $request->get('limit', 10), 50);
            $since = $request->get('since'); // Timestamp for incremental updates

            $query = $this->baseNotificationsQuery($user->id)
                ->orderBy('app_notifications.created_at', 'desc')
                ->limit($limit);

            if ($since) {
                $query->where('app_notifications.created_at', '>', $since);
            }

            $notifications = $query->get();

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting latest notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get latest notifications'
            ], 500);
        }
    }

    /**
     * Get new kitabs (for real-time updates)
     */
    public function newKitabs(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $limit = $request->get('limit', 10);
            $since = $request->get('since'); // Timestamp for incremental updates

            $query = Kitab::published()->orderBy('created_at', 'desc')->limit($limit);

            if ($since) {
                $query->where('created_at', '>', $since);
            }

            $kitabs = $query->get()->map(function ($kitab) {
                return [
                    'id_kitab' => $kitab->id_kitab,
                    'judul' => $kitab->judul,
                    'penulis' => $kitab->penulis,
                    'kategori' => $kitab->kategori,
                    'bahasa' => $kitab->bahasa,
                    'deskripsi' => $kitab->deskripsi,
                    'cover' => url('cover/' . $kitab->cover),
                    'file_pdf' => url('pdf/' . $kitab->file_pdf),
                    'views' => $kitab->views,
                    'downloads' => $kitab->downloads,
                    'created_at' => $kitab->created_at->toISOString(),
                    'updated_at' => $kitab->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $kitabs,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting new kitabs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get new kitabs'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = request()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $notification = AppNotification::find($id);
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $readAt = now();

            $this->markNotificationsReadForUser($user->id, [(int) $notification->id], $readAt);

            $unreadCount = $this->unreadCountForUser($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => [
                    'notification_id' => (int) $notification->id,
                    'read_at' => $readAt->toISOString(),
                    'unread_count' => (int) $unreadCount,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        try {
            $user = request()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $count = $this->baseNotificationsQuery($user->id)
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count'
            ], 500);
        }
    }

    /**
     * Mark all unread notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $readAt = now();
            $unreadIds = $this->unreadNotificationIdsForUser($user->id);
            $markedCount = $this->markNotificationsReadForUser($user->id, $unreadIds, $readAt);
            $unreadCount = $this->unreadCountForUser($user->id);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'data' => [
                    'marked_count' => (int) $markedCount,
                    'unread_count' => (int) $unreadCount,
                    'read_at' => $readAt->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }
}
