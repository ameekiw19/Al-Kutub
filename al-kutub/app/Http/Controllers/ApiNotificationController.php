<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppNotification;

class ApiNotificationController extends Controller
{
    /**
     * Get recent notifications
     * GET /api/notifications
     */
    public function index()
    {
        try {
            $notifications = AppNotification::latest()
                ->take(20) // Batasi 20 notifikasi terakhir
                ->get()
                ->map(function ($notif) {
                    return [
                        'id' => $notif->id,
                        'title' => $notif->title,
                        'message' => $notif->message,
                        'type' => $notif->type,
                        'action_url' => $notif->action_url,
                        'date' => $notif->created_at->diffForHumans(), // Format waktu: "2 hours ago"
                        'timestamp' => $notif->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get LATEST notification for polling
     * GET /api/notifications/latest
     */
    public function latest()
    {
        $notification = AppNotification::latest()->first();

        if (!$notification) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'action_url' => $notification->action_url,
                'timestamp' => $notification->created_at->toIso8601String() 
            ]
        ]);
    }
}
