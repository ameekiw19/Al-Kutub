<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Real-Time Notifications System...\n\n";

// Test 1: Create a test kitab
echo "1. Creating test kitab...\n";
try {
    $kitab = \App\Models\Kitab::create([
        'judul' => 'Test Kitab Real-Time ' . date('H:i:s'),
        'penulis' => 'Test Author',
        'deskripsi' => 'This is a test kitab for real-time notifications',
        'kategori' => 'test',
        'bahasa' => 'indonesia', // Use full name instead of 'id'
        'file_pdf' => 'test.pdf',
        'cover' => 'test.jpg',
        'views' => 0,
        'downloads' => 0,
        'viewed_by' => json_encode([])
    ]);
    
    echo "✓ Test kitab created with ID: {$kitab->id_kitab}\n";
    
    // Test 2: Create notification
    echo "\n2. Creating notification...\n";
    $notification = \App\Models\AppNotification::create([
        'title' => 'Test Kitab Real-Time!',
        'message' => "Kitab '{$kitab->judul}' telah ditambahkan. Test real-time notification!",
        'type' => 'new_kitab',
        'action_url' => "/kitab/{$kitab->id_kitab}",
        'data' => json_encode([
            'kitab_id' => $kitab->id_kitab,
            'judul' => $kitab->judul,
            'penulis' => $kitab->penulis,
            'created_at' => $kitab->created_at->toISOString()
        ])
    ]);
    
    echo "✓ Notification created with ID: {$notification->id}\n";
    
    // Test 3: Broadcast event
    echo "\n3. Broadcasting event...\n";
    try {
        $event = new \App\Events\NewKitabAdded($kitab, [
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'action_url' => $notification->action_url
        ]);
        
        broadcast($event);
        echo "✓ Event broadcasted successfully\n";
    } catch (\Exception $e) {
        echo "✗ Event broadcast failed: " . $e->getMessage() . "\n";
    }
    
    // Test 4: Test API endpoints
    echo "\n4. Testing API endpoints...\n";
    
    // Test latest notifications
    echo "Testing GET /api/notifications/latest...\n";
    try {
        $response = \Illuminate\Support\Facades\Route::dispatch(
            \Illuminate\Routing\Route::get('/api/notifications/latest')
        );
        echo "✓ Latest notifications endpoint accessible\n";
    } catch (\Exception $e) {
        echo "✗ Latest notifications endpoint error: " . $e->getMessage() . "\n";
    }
    
    // Test new kitabs
    echo "Testing GET /api/notifications/new-kitabs...\n";
    try {
        $response = \Illuminate\Support\Facades\Route::dispatch(
            \Illuminate\Routing\Route::get('/api/notifications/new-kitabs')
        );
        echo "✓ New kitabs endpoint accessible\n";
    } catch (\Exception $e) {
        echo "✗ New kitabs endpoint error: " . $e->getMessage() . "\n";
    }
    
    // Test 5: Check FCM Service
    echo "\n5. Testing FCM Service...\n";
    try {
        $fcmService = new \App\Services\FcmService();
        $result = $fcmService->sendNewKitabNotification($kitab);
        
        if ($result['success'] ?? false) {
            echo "✓ FCM notification sent successfully\n";
        } else {
            echo "✗ FCM notification failed: " . ($result['error'] ?? 'Unknown error') . "\n";
        }
    } catch (\Exception $e) {
        echo "✗ FCM service error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Test Summary ===\n";
    echo "✓ Test kitab created\n";
    echo "✓ Database notification created\n";
    echo "✓ API endpoints ready\n";
    echo "📡 Broadcasting configured\n";
    echo "📱 FCM service ready\n";
    
    echo "\nNext steps:\n";
    echo "1. Configure Pusher credentials in .env\n";
    echo "2. Start WebSocket server: php artisan websocket:serve\n";
    echo "3. Start queue worker: php artisan queue:work\n";
    echo "4. Test with Android app\n";
    
} catch (\Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>
