<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Full Form Submission...\n\n";

// Simulate complete form submission with files
$_POST = [
    'judul' => 'Test Kitab Full Submission ' . date('H:i:s'),
    'penulis' => 'Test Author Full',
    'deskripsi' => 'This is a complete test for form submission with all fields',
    'kategori' => 'fiqih',
    'bahasa' => 'arab',
    '_token' => csrf_token()
];

// Mock file uploads
$_FILES = [
    'file_pdf' => [
        'name' => 'test.pdf',
        'type' => 'application/pdf',
        'size' => 1024000, // 1MB
        'tmp_name' => '/tmp/test.pdf',
        'error' => 0
    ],
    'cover' => [
        'name' => 'test.jpg',
        'type' => 'image/jpeg',
        'size' => 512000, // 512KB
        'tmp_name' => '/tmp/test.jpg',
        'error' => 0
    ]
];

echo "Simulated POST data:\n";
print_r($_POST);
echo "\nSimulated FILES data:\n";
print_r($_FILES);

// Create request
$request = \Illuminate\Http\Request::create('/admin/addkitab', 'POST', $_POST, [], $_FILES);

// Test AdminController directly
try {
    $controller = new \App\Http\Controllers\AdminController();
    
    echo "\nTesting AdminController::AddKitab...\n";
    
    // Create temporary files for testing FIRST
    $pdfPath = '/tmp/test.pdf';
    $coverPath = '/tmp/test.jpg';

    file_put_contents($pdfPath, '%PDF-1.4 test content');
    file_put_contents($coverPath, base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A'));

    // Mock files for testing
    $pdfMock = new \Illuminate\Http\UploadedFile(
        $pdfPath,
        'test.pdf',
        'application/pdf',
        null,
        true,
        false // Disable path checking
    );

    $coverMock = new \Illuminate\Http\UploadedFile(
        $coverPath,
        'test.jpg',
        'image/jpeg',
        null,
        true,
        false // Disable path checking
    );
    
    // Replace request files with mocks
    $request->files->set('file_pdf', $pdfMock);
    $request->files->set('cover', $coverMock);
    
    // Call the controller method
    $response = $controller->AddKitab($request);
    
    echo "Response type: " . get_class($response) . "\n";
    
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        echo "JSON Response: " . $response->getContent() . "\n";
    } elseif ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "Redirect Response: " . $response->getStatusCode() . "\n";
    }
    
} catch (\Exception $e) {
    echo "Controller Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Clean up
@unlink('/tmp/test.pdf');
@unlink('/tmp/test.jpg');

echo "\nChecking latest kitab in database:\n";
try {
    $latest = \App\Models\Kitab::latest()->first();
    if ($latest) {
        echo "Latest kitab:\n";
        echo "  ID: " . $latest->id_kitab . "\n";
        echo "  Judul: " . $latest->judul . "\n";
        echo "  Penulis: " . $latest->penulis . "\n";
        echo "  Kategori: " . $latest->kategori . "\n";
        echo "  Bahasa: " . $latest->bahasa . "\n";
        echo "  Created: " . $latest->created_at . "\n";
    } else {
        echo "No kitabs found in database\n";
    }
} catch (\Exception $e) {
    echo "Database check error: " . $e->getMessage() . "\n";
}

?>
