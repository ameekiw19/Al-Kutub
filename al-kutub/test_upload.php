<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing upload functionality...\n";

// Test 1: Check if directories are writable
$directories = ['public/pdf', 'public/cover'];
foreach ($directories as $dir) {
    if (is_writable($dir)) {
        echo "✓ $dir is writable\n";
    } else {
        echo "✗ $dir is NOT writable\n";
    }
}

// Test 2: Check PHP upload settings
echo "\nPHP Upload Settings:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

// Test 3: Check Laravel configuration
echo "\nLaravel Configuration:\n";
echo "APP_DEBUG: " . env('APP_DEBUG', 'false') . "\n";
echo "APP_ENV: " . env('APP_ENV', 'production') . "\n";

// Test 4: Try to create a test file
echo "\nTesting file creation...\n";
$testFile = 'public/test_' . time() . '.txt';
if (file_put_contents($testFile, 'test content')) {
    echo "✓ Test file created successfully\n";
    unlink($testFile);
    echo "✓ Test file deleted successfully\n";
} else {
    echo "✗ Failed to create test file\n";
}

echo "\nTest completed!\n";
?>
