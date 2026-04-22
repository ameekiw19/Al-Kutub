<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Route Submission...\n\n";

// Create test files first
$pdfPath = '/tmp/test_route.pdf';
$coverPath = '/tmp/test_route.jpg';

file_put_contents($pdfPath, '%PDF-1.4 test content for route');
file_put_contents($coverPath, base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A'));

// Create proper UploadedFile objects
$pdfFile = new \Illuminate\Http\UploadedFile(
    $pdfPath,
    'test_route.pdf',
    'application/pdf',
    1234,
    UPLOAD_ERR_OK,
    true
);

$coverFile = new \Illuminate\Http\UploadedFile(
    $coverPath,
    'test_route.jpg',
    'image/jpeg',
    567,
    UPLOAD_ERR_OK,
    true
);

// Create request with files
$request = \Illuminate\Http\Request::create(
    '/admin/addkitab',
    'POST',
    [
        'judul' => 'Test Route Kitab ' . date('H:i:s'),
        'penulis' => 'Route Test Author',
        'deskripsi' => 'This is a test via route submission',
        'kategori' => 'hadis',
        'bahasa' => 'indonesia',
        '_token' => 'test-token'
    ],
    [],
    [
        'file_pdf' => $pdfFile,
        'cover' => $coverFile
    ]
);

echo "Request created successfully\n";
echo "Files attached: PDF={$pdfFile->getClientOriginalName()}, Cover={$coverFile->getClientOriginalName()}\n";

// Test through route
try {
    echo "\nTesting through Laravel route...\n";
    
    // Get the route
    $route = \Illuminate\Support\Facades\Route::getRoutes()->matchByMethod('POST', '/admin/addkitab');
    if (!$route) {
        echo "Route not found!\n";
        exit;
    }
    
    echo "Route found: " . $route->getActionName() . "\n";
    
    // Call the controller
    $controller = new \App\Http\Controllers\AdminController();
    $response = $controller->AddKitab($request);
    
    echo "Response received!\n";
    
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $content = $response->getContent();
        echo "JSON Response: " . $content . "\n";
        
        $data = json_decode($content, true);
        if ($data['success'] ?? false) {
            echo "✅ SUCCESS: Kitab created!\n";
        } else {
            echo "❌ FAILED: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "Response type: " . get_class($response) . "\n";
        echo "Status: " . $response->getStatusCode() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Clean up
@unlink($pdfPath);
@unlink($coverPath);

echo "\nChecking database...\n";
try {
    $latest = \App\Models\Kitab::latest()->first();
    if ($latest && strpos($latest->judul, 'Test Route Kitab') !== false) {
        echo "✅ Kitab found in database!\n";
        echo "   ID: {$latest->id_kitab}\n";
        echo "   Judul: {$latest->judul}\n";
        echo "   Kategori: {$latest->kategori}\n";
        echo "   Bahasa: {$latest->bahasa}\n";
    } else {
        echo "❌ Kitab not found in database\n";
    }
} catch (\Exception $e) {
    echo "Database check error: " . $e->getMessage() . "\n";
}

?>
