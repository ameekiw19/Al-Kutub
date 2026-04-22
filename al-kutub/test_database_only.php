<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Database Insertion Only (No Files)...\n\n";

// Create request without files
$request = new \Illuminate\Http\Request([
    'judul' => 'Test Database Only Kitab ' . date('H:i:s'),
    'penulis' => 'Database Test Author',
    'deskripsi' => 'This is a database-only test without file uploads',
    'kategori' => 'sirah',
    'bahasa' => 'arab',
]);

echo "Request prepared:\n";
echo "- Judul: " . $request->get('judul') . "\n";
echo "- Penulis: " . $request->get('penulis') . "\n";
echo "- Kategori: " . $request->get('kategori') . "\n";
echo "- Bahasa: " . $request->get('bahasa') . "\n";

// Test controller directly
try {
    echo "\nCalling AdminController::AddKitab (database only)...\n";
    
    // Temporarily override file checks
    $controller = new class extends \App\Http\Controllers\AdminController {
        public function AddKitab(\Illuminate\Http\Request $request) {
            // Skip file checks for this test
            return $this->addKitabWithoutFiles($request);
        }
        
        private function addKitabWithoutFiles(\Illuminate\Http\Request $request) {
            try {
                \Log::info('AddKitab method called (database only)', [
                    'request_data' => $request->all()
                ]);

                // Validasi input only
                $validated = $request->validate([
                    'judul' => 'required|string|max:255',
                    'penulis' => 'required|string|max:255',
                    'deskripsi' => 'required',
                    'kategori' => 'required',
                    'bahasa' => 'required|string|max:100',
                ]);

                \Log::info('Validation passed', ['validated_data' => $validated]);

                // Simpan ke database dengan transaction
                \DB::beginTransaction();
                try {
                    \Log::info('Starting database transaction');
                    
                    $kitabData = [
                        'judul' => $validated['judul'],
                        'penulis' => $validated['penulis'],
                        'deskripsi' => $validated['deskripsi'],
                        'kategori' => $validated['kategori'],
                        'bahasa' => $validated['bahasa'],
                        'file_pdf' => 'placeholder.pdf', // Placeholder
                        'cover' => 'placeholder.jpg', // Placeholder
                        'views' => 0,
                        'downloads' => 0,
                        'viewed_by' => json_encode([]),
                    ];

                    \Log::info('Creating kitab with data', ['kitab_data' => $kitabData]);
                    $kitab = \App\Models\Kitab::create($kitabData);
                    \Log::info('Kitab created successfully', ['kitab_id' => $kitab->id_kitab]);
                    
                    // Verify kitab was actually saved
                    $savedKitab = \App\Models\Kitab::find($kitab->id_kitab);
                    if (!$savedKitab) {
                        throw new \Exception('Kitab was not saved to database');
                    }
                    \Log::info('Kitab verified in database', ['kitab_id' => $savedKitab->id_kitab]);
                    
                    \DB::commit();
                    \Log::info('Database transaction committed successfully');
                    
                    // Return success response
                    return redirect('manejemenkitab')->with('success', 'Kitab berhasil ditambahkan!');
                    
                } catch (\Exception $e) {
                    \DB::rollBack();
                    \Log::error('Database transaction failed: ' . $e->getMessage(), [
                        'kitab_data' => $kitabData ?? [],
                        'exception' => $e
                    ]);
                    throw $e;
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Validation error in AddKitab: ' . $e->getMessage(), [
                    'errors' => $e->errors()
                ]);
                throw $e;
            } catch (\Exception $e) {
                \Log::error('Error in AddKitab: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->all(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                throw $e;
            }
        }
    };
    
    $response = $controller->AddKitab($request);
    
    echo "Response received: " . get_class($response) . "\n";
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
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

// Final database check
echo "\nFinal database check:\n";
try {
    $latest = \App\Models\Kitab::latest()->first();
    if ($latest && strpos($latest->judul, 'Test Database Only Kitab') !== false) {
        echo "✅ SUCCESS: Kitab found in database!\n";
        echo "   ID: {$latest->id_kitab}\n";
        echo "   Judul: {$latest->judul}\n";
        echo "   Penulis: {$latest->penulis}\n";
        echo "   Kategori: {$latest->kategori}\n";
        echo "   Bahasa: {$latest->bahasa}\n";
        echo "   Deskripsi: " . substr($latest->deskripsi, 0, 50) . "...\n";
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
        
        echo "\n🎉 DATABASE INSERTION WORKING! 🎉\n";
        echo "   The issue is with file upload, not database.\n";
        
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
