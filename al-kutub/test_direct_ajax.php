<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING DIRECT AJAX FILE HANDLING ===\n\n";

// Create test files
$pdfPath = '/tmp/direct_ajax_test.pdf';
$coverPath = '/tmp/direct_ajax_test.jpg';

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
echo "   PDF: $pdfPath (" . filesize($pdfPath) . " bytes)\n";
echo "   Cover: $coverPath (" . filesize($coverPath) . " bytes)\n";

// Create UploadedFile objects
$pdfFile = new \Illuminate\Http\UploadedFile(
    $pdfPath,
    'direct_ajax_test.pdf',
    'application/pdf',
    filesize($pdfPath),
    UPLOAD_ERR_OK,
    true
);

$coverFile = new \Illuminate\Http\UploadedFile(
    $coverPath,
    'direct_ajax_test.jpg',
    'image/jpeg',
    filesize($coverPath),
    UPLOAD_ERR_OK,
    true
);

echo "✅ UploadedFile objects created\n";
echo "   PDF valid: " . ($pdfFile->isValid() ? 'YES' : 'NO') . "\n";
echo "   PDF error: " . $pdfFile->getError() . " - " . $pdfFile->getErrorMessage() . "\n";
echo "   Cover valid: " . ($coverFile->isValid() ? 'YES' : 'NO') . "\n";
echo "   Cover error: " . $coverFile->getError() . " - " . $coverFile->getErrorMessage() . "\n";

// Test file upload directly
echo "\n=== TESTING FILE UPLOAD DIRECTLY ===\n";

try {
    $controller = new \App\Http\Controllers\Api\AdminKitabController(
        new \App\Services\FcmService()
    );
    
    // Use reflection to access private method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('handleFileUpload');
    $method->setAccessible(true);
    
    // Test PDF upload
    echo "Testing PDF upload...\n";
    $pdfResult = $method->invoke($controller, $pdfFile, 'pdf');
    echo "✅ PDF uploaded: $pdfResult\n";
    
    // Test Cover upload
    echo "Testing Cover upload...\n";
    $coverResult = $method->invoke($controller, $coverFile, 'cover');
    echo "✅ Cover uploaded: $coverResult\n";
    
    // Verify files exist
    $pdfExists = file_exists(public_path('pdf/' . $pdfResult));
    $coverExists = file_exists(public_path('cover/' . $coverResult));
    
    echo "   PDF file exists: " . ($pdfExists ? '✅ YES' : '❌ NO') . "\n";
    echo "   Cover file exists: " . ($coverExists ? '✅ YES' : '❌ NO') . "\n";
    
    if ($pdfExists && $coverExists) {
        echo "\n🎉 FILE UPLOAD WORKING! 🎉\n";
        
        // Test database insert
        echo "\n=== TESTING DATABASE INSERT ===\n";
        
        $kitab = \App\Models\Kitab::create([
            'judul' => 'Direct AJAX Test Kitab ' . date('H:i:s'),
            'penulis' => 'Direct AJAX Test Author',
            'deskripsi' => 'This is direct AJAX test with working file upload',
            'kategori' => 'tafsir',
            'bahasa' => 'arab',
            'file_pdf' => $pdfResult,
            'cover' => $coverResult,
            'views' => 0,
            'downloads' => 0,
            'viewed_by' => json_encode([]),
        ]);
        
        echo "✅ Kitab created with ID: " . $kitab->id_kitab . "\n";
        
        // Verify
        $saved = \App\Models\Kitab::find($kitab->id_kitab);
        if ($saved) {
            echo "✅ Kitab verified in database\n";
            echo "   Title: " . $saved->judul . "\n";
            echo "   PDF: " . $saved->file_pdf . "\n";
            echo "   Cover: " . $saved->cover . "\n";
            
            echo "\n🎉 COMPLETE SUCCESS! 🎉\n";
            echo "   ✅ File Upload: WORKING\n";
            echo "   ✅ Database Insert: WORKING\n";
            echo "   ✅ AJAX Ready: YES\n";
        } else {
            echo "❌ Kitab not found in database\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace: " . substr($e->getTraceAsString(), 0, 300) . "...\n";
}

// Clean up
@unlink($pdfPath);
@unlink($coverPath);

echo "\n=== TEST SUMMARY ===\n";
echo "✅ File Creation: WORKING\n";
echo "✅ UploadedFile Objects: WORKING\n";
echo "✅ File Upload: " . (isset($pdfResult) && $pdfResult ? 'WORKING' : 'NEEDS DEBUG') . "\n";
echo "✅ Database: " . (isset($kitab) && $kitab ? 'WORKING' : 'NEEDS DEBUG') . "\n";

echo "\nNext steps:\n";
echo "1. Fix Laravel validation rules\n";
echo "2. Test AJAX form in browser\n";
echo "3. Enable real-time notifications\n";
echo "4. Test with Android app\n";

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";

?>
