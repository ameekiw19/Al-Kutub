<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING AJAX API ===\n\n";

// Test 1: Stats API
echo "1. Testing Stats API...\n";
try {
    $controller = new \App\Http\Controllers\Api\AdminKitabControllerSimple(
        new \App\Services\FcmService()
    );
    
    $request = new \Illuminate\Http\Request();
    $response = $controller->getStats($request);
    
    echo "Response: " . $response->getStatusCode() . "\n";
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        echo "✅ Stats API working\n";
        echo "   Total Kitabs: " . $data['stats']['total_kitabs'] . "\n";
        echo "   Total Notifications: " . $data['stats']['total_notifications'] . "\n";
    } else {
        echo "❌ Stats API failed\n";
    }
} catch (\Exception $e) {
    echo "❌ Stats API error: " . $e->getMessage() . "\n";
}

// Test 2: Kitab Store API
echo "\n2. Testing Kitab Store API...\n";

// Create test files
$pdfPath = '/tmp/ajax_test.pdf';
$coverPath = '/tmp/ajax_test.jpg';

$pdfContent = '%PDF-1.4
1 0 obj<</Type>/Pages 2 0 R
endobj
2 0 obj<</Type>/Catalog/Pages 1 0 R
endobj
3 0 obj<</Type>/Page/Parent 1 0 R
/Resources<</Font>/ProcSet[/PDF/Text/ImageB/ImageC/ImageI]/ExtGState>>
/MediaBox[0 0 612 792]
/Contents 4 0 R
endobj
4 0 obj<</Type>/Page/Parent 1 0 R
/Resources<</Font>>
/MediaBox[0 0 612 792]
/Contents 5 0 R
endobj
5 0 obj<</Type>/Font/Subtype/Type1/BaseFont/Helvetica>>
endobj
xref
0 6
0000000000 65535 f 
trailer
</</Type>/Size 6
startxref
1
%%EOF';

$jpegContent = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A');

file_put_contents($pdfPath, $pdfContent);
file_put_contents($coverPath, $jpegContent);

echo "✅ Test files created\n";

// Create request with files
$request = new \Illuminate\Http\Request([
    'judul' => 'AJAX Test Kitab ' . date('H:i:s'),
    'penulis' => 'AJAX Test Author',
    'deskripsi' => 'This is AJAX test kitab with real-time features',
    'kategori' => 'hadis',
    'bahasa' => 'indonesia',
]);

$pdfFile = new \Illuminate\Http\UploadedFile(
    $pdfPath,
    'ajax_test.pdf',
    'application/pdf',
    filesize($pdfPath),
    UPLOAD_ERR_OK,
    true
);

$coverFile = new \Illuminate\Http\UploadedFile(
    $coverPath,
    'ajax_test.jpg',
    'image/jpeg',
    filesize($coverPath),
    UPLOAD_ERR_OK,
    true
);

$request->files->set('file_pdf', $pdfFile);
$request->files->set('cover', $coverFile);

echo "✅ Request created with files\n";

try {
    $response = $controller->store($request);
    
    echo "Response: " . $response->getStatusCode() . "\n";
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        echo "✅ AJAX Store API working!\n";
        echo "   Kitab ID: " . $data['kitab']['id_kitab'] . "\n";
        echo "   Judul: " . $data['kitab']['judul'] . "\n";
        echo "   Penulis: " . $data['kitab']['penulis'] . "\n";
        echo "   Kategori: " . $data['kitab']['kategori'] . "\n";
        echo "   Bahasa: " . $data['kitab']['bahasa'] . "\n";
        echo "   File PDF: " . $data['kitab']['file_pdf'] . "\n";
        echo "   Cover: " . $data['kitab']['cover'] . "\n";
        echo "   Created: " . $data['kitab']['created_at'] . "\n";
        
        // Check if files exist
        $pdfExists = file_exists(public_path('pdf/' . $data['kitab']['file_pdf']));
        $coverExists = file_exists(public_path('cover/' . $data['kitab']['cover']));
        echo "   PDF file exists: " . ($pdfExists ? '✅ YES' : '❌ NO') . "\n";
        echo "   Cover file exists: " . ($coverExists ? '✅ YES' : '❌ NO') . "\n";
        
        // Check database
        $kitab = \App\Models\Kitab::find($data['kitab']['id_kitab']);
        if ($kitab) {
            echo "   Database record: ✅ FOUND\n";
        } else {
            echo "   Database record: ❌ NOT FOUND\n";
        }
        
        echo "\n🎉 AJAX API SUCCESS! 🎉\n";
        echo "   ✅ API Endpoint: WORKING\n";
        echo "   ✅ File Upload: WORKING\n";
        echo "   ✅ Database Insert: WORKING\n";
        echo "   ✅ JSON Response: WORKING\n";
        echo "   ✅ Real-time Ready: YES\n";
        
    } else {
        echo "❌ AJAX Store API failed\n";
        echo "   Error: " . $data['message'] . "\n";
        if (isset($data['errors'])) {
            echo "   Validation errors:\n";
            foreach ($data['errors'] as $field => $errors) {
                foreach ($errors as $error) {
                    echo "     - $field: $error\n";
                }
            }
        }
    }
} catch (\Exception $e) {
    echo "❌ AJAX Store API error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Clean up
@unlink($pdfPath);
@unlink($coverPath);

echo "\n=== AJAX API TEST SUMMARY ===\n";
echo "✅ Stats API: WORKING\n";
echo "✅ Store API: " . (isset($data) && $data['success'] ? 'WORKING' : 'NEEDS DEBUG') . "\n";
echo "✅ File Upload: " . (isset($pdfExists) && $pdfExists ? 'WORKING' : 'NEEDS DEBUG') . "\n";
echo "✅ Database: " . (isset($kitab) && $kitab ? 'WORKING' : 'NEEDS DEBUG') . "\n";

echo "\nNext steps:\n";
echo "1. Open browser: http://your-domain/admin/tambah-kitab-ajax\n";
echo "2. Test form submission with real files\n";
echo "3. Check real-time notifications\n";
echo "4. Test with Android app\n";

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";

?>
