<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== FORCE UPDATE DASHBOARD ===\n\n";

// Clear all caches
echo "1. Clearing caches...\n";
Artisan::call('cache:clear');
Artisan::call('view:clear');
Artisan::call('config:clear');
Artisan::call('route:clear');
echo "✅ All caches cleared\n\n";

// Test data flow
echo "2. Testing data flow...\n";
try {
    $controller = new App\Http\Controllers\AdminController();
    $view = $controller->HomeAdmin();
    
    echo "✅ Controller method executed\n";
    echo "✅ View: " . $view->name() . "\n";
    echo "✅ Data items: " . count($view->getData()) . "\n";
    
    $data = $view->getData();
    echo "✅ Total Kitab: " . ($data['total_kitab'] ?? 'NULL') . "\n";
    echo "✅ Total Users: " . ($data['total_user'] ?? 'NULL') . "\n";
    echo "✅ Total Views: " . ($data['total_views'] ?? 'NULL') . "\n";
    echo "✅ Reading Stats: " . (isset($data['readingStats']) ? 'SET' : 'NULL') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n3. Checking view file...\n";
$viewFile = __DIR__ . '/resources/views/AdminHome.blade.php';
if (file_exists($viewFile)) {
    $content = file_get_contents($viewFile);
    
    if (strpos($content, 'Dashboard Analytics') !== false) {
        echo "✅ View contains new header\n";
    } else {
        echo "❌ View missing new header\n";
    }
    
    if (strpos($content, 'linear-gradient') !== false) {
        echo "✅ View contains gradient styles\n";
    } else {
        echo "❌ View missing gradient styles\n";
    }
    
    if (strpos($content, 'DEBUG INFO') !== false) {
        echo "✅ View contains debug section\n";
    } else {
        echo "❌ View missing debug section\n";
    }
    
    echo "✅ View file exists and readable\n";
} else {
    echo "❌ View file not found\n";
}

echo "\n=== UPDATE COMPLETE ===\n";
echo "Now access: http://localhost:8000/admin/home\n";
echo "Login with: mimin / password\n";
