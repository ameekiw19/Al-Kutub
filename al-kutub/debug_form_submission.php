<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging Form Submission...\n\n";

// Simulate form data
$formData = [
    'judul' => 'Test Kitab Debug ' . date('H:i:s'),
    'penulis' => 'Debug Author',
    'deskripsi' => 'This is a debug test for form submission',
    'kategori' => 'aqidah', // Radio button value
    'bahasa' => 'indonesia', // Select value
];

// Create mock request
$request = new \Illuminate\Http\Request();
$request->merge($formData);

echo "Form Data:\n";
print_r($formData);
echo "\n";

// Test validation
try {
    $validator = \Illuminate\Support\Facades\Validator::make($formData, [
        'judul' => 'required|string|max:255',
        'penulis' => 'required|string|max:255',
        'deskripsi' => 'required',
        'kategori' => 'required',
        'bahasa' => 'required|string|max:100',
    ]);

    if ($validator->fails()) {
        echo "Validation Errors:\n";
        print_r($validator->errors()->all());
    } else {
        echo "✓ Validation passed\n";
        
        // Test database insert
        try {
            \DB::beginTransaction();
            
            $kitab = \App\Models\Kitab::create([
                'judul' => $formData['judul'],
                'penulis' => $formData['penulis'],
                'deskripsi' => $formData['deskripsi'],
                'kategori' => $formData['kategori'],
                'bahasa' => $formData['bahasa'],
                'file_pdf' => 'debug.pdf',
                'cover' => 'debug.jpg',
                'views' => 0,
                'downloads' => 0,
                'viewed_by' => json_encode([]),
            ]);
            
            echo "✓ Kitab created with ID: " . $kitab->id_kitab . "\n";
            
            // Verify
            $saved = \App\Models\Kitab::find($kitab->id_kitab);
            if ($saved) {
                echo "✓ Kitab verified in database\n";
                echo "  - Judul: " . $saved->judul . "\n";
                echo "  - Kategori: " . $saved->kategori . "\n";
                echo "  - Bahasa: " . $saved->bahasa . "\n";
            } else {
                echo "✗ Kitab not found in database after creation\n";
            }
            
            \DB::rollBack(); // Rollback for debug
            echo "✓ Transaction rolled back (debug mode)\n";
            
        } catch (\Exception $e) {
            \DB::rollBack();
            echo "✗ Database error: " . $e->getMessage() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "✗ Validation error: " . $e->getMessage() . "\n";
}

echo "\nCheck database connection:\n";
try {
    $count = \App\Models\Kitab::count();
    echo "✓ Database connected. Total kitabs: " . $count . "\n";
} catch (\Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\nCheck table structure:\n";
try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('kitab');
    echo "Kitab table columns: " . implode(', ', $columns) . "\n";
} catch (\Exception $e) {
    echo "✗ Cannot get table structure: " . $e->getMessage() . "\n";
}

?>
