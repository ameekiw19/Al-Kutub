<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Direct Controller Call...\n\n";

// Create test files
$pdfPath = '/tmp/test_direct.pdf';
$coverPath = '/tmp/test_direct.jpg';

file_put_contents($pdfPath, '%PDF-1.4 test content for direct controller');
file_put_contents($coverPath, base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A'));

// Create proper UploadedFile objects
$pdfFile = new \Illuminate\Http\UploadedFile(
    $pdfPath,
    'test_direct.pdf',
    'application/pdf',
    1234,
    UPLOAD_ERR_OK,
    true
);

$coverFile = new \Illuminate\Http\UploadedFile(
    $coverPath,
    'test_direct.jpg',
    'image/jpeg',
    567,
    UPLOAD_ERR_OK,
    true
);

// Create request
$request = new \Illuminate\Http\Request([
    'judul' => 'Test Direct Kitab ' . date('H:i:s'),
    'penulis' => 'Direct Test Author',
    'deskripsi' => 'This is a direct controller test',
    'kategori' => 'tafsir',
    'bahasa' => 'inggris',
]);

// Add files to request
$request->files->set('file_pdf', $pdfFile);
$request->files->set('cover', $coverFile);

echo "Request prepared:\n";
echo "- Judul: " . $request->get('judul') . "\n";
echo "- Penulis: " . $request->get('penulis') . "\n";
echo "- Kategori: " . $request->get('kategori') . "\n";
echo "- Bahasa: " . $request->get('bahasa') . "\n";
echo "- Has PDF: " . ($request->hasFile('file_pdf') ? 'YES' : 'NO') . "\n";
echo "- Has Cover: " . ($request->hasFile('cover') ? 'YES' : 'NO') . "\n";

// Test controller directly
try {
    echo "\nCalling AdminController::AddKitab...\n";
    
    $controller = new \App\Http\Controllers\AdminController();
    $response = $controller->AddKitab($request);
    
    echo "Response received: " . get_class($response) . "\n";
    
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $content = $response->getContent();
        echo "JSON Response: " . substr($content, 0, 200) . "...\n";
        
        $data = json_decode($content, true);
        if ($data['success'] ?? false) {
            echo "✅ SUCCESS: Kitab created successfully!\n";
            
            if (isset($data['kitab'])) {
                echo "   Kitab ID: " . $data['kitab']['id_kitab'] . "\n";
                echo "   Kitab Title: " . $data['kitab']['judul'] . "\n";
            }
        } else {
            echo "❌ FAILED: " . ($data['message'] ?? 'Unknown error') . "\n";
            if (isset($data['errors'])) {
                echo "   Errors: " . json_encode($data['errors']) . "\n";
            }
        }
    } elseif ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "✅ SUCCESS: Redirect response (status: " . $response->getStatusCode() . ")\n";
        
        // Check if it has success message
        $session = app('session');
        if ($session->has('success')) {
            echo "   Success message: " . $session->get('success') . "\n";
        }
    } else {
        echo "❌ Unexpected response type\n";
    }
    
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "❌ VALIDATION ERROR:\n";
    foreach ($e->errors()->all() as $error) {
        echo "   - " . $error . "\n";
    }
} catch (\Exception $e) {
    echo "❌ GENERAL ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Clean up
@unlink($pdfPath);
@unlink($coverPath);

// Final database check
echo "\nFinal database check:\n";
try {
    $latest = \App\Models\Kitab::latest()->first();
    if ($latest && strpos($latest->judul, 'Test Direct Kitab') !== false) {
        echo "✅ Latest kitab found in database!\n";
        echo "   ID: {$latest->id_kitab}\n";
        echo "   Judul: {$latest->judul}\n";
        echo "   Penulis: {$latest->penulis}\n";
        echo "   Kategori: {$latest->kategori}\n";
        echo "   Bahasa: {$latest->bahasa}\n";
        echo "   File PDF: {$latest->file_pdf}\n";
        echo "   Cover: {$latest->cover}\n";
        echo "   Created: {$latest->created_at}\n";
        
        // Verify all fields are populated
        $allFieldsFilled = !empty($latest->judul) && 
                          !empty($latest->penulis) && 
                          !empty($latest->deskripsi) && 
                          !empty($latest->kategori) && 
                          !empty($latest->bahasa);
        
        echo "   All fields filled: " . ($allFieldsFilled ? '✅ YES' : '❌ NO') . "\n";
    } else {
        echo "❌ Test kitab not found in database\n";
        
        // Show last few kitabs for debugging
        $recent = \App\Models\Kitab::latest()->take(3)->get();
        echo "Recent kitabs in database:\n";
        foreach ($recent as $kitab) {
            echo "   - [{$kitab->id_kitab}] {$kitab->judul}\n";
        }
    }
} catch (\Exception $e) {
    echo "Database check error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";

?>
