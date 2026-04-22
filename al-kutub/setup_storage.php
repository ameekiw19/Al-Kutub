<?php

/**
 * Setup storage directories for AlKutub application
 * Run this script to ensure all required directories exist with proper permissions
 */

echo "Setting up AlKutub storage directories...\n";

// Directories that need to exist
$directories = [
    'public/pdf',
    'public/cover',
    'storage/app/public/pdf',
    'storage/app/public/cover',
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        echo "Creating directory: $dir\n";
        mkdir($dir, 0755, true);
    } else {
        echo "Directory already exists: $dir\n";
    }
    
    // Set permissions
    chmod($dir, 0755);
}

// Create symbolic links if they don't exist
$symlinks = [
    'public/storage' => 'storage/app/public',
];

foreach ($symlinks as $link => $target) {
    if (!is_link($link)) {
        if (file_exists($link)) {
            unlink($link);
        }
        echo "Creating symbolic link: $link -> $target\n";
        symlink($target, $link);
    } else {
        echo "Symbolic link already exists: $link\n";
    }
}

// Clear Laravel cache
echo "Clearing Laravel cache...\n";
shell_exec('php artisan cache:clear');
shell_exec('php artisan config:clear');
shell_exec('php artisan route:clear');
shell_exec('php artisan view:clear');

echo "Setup completed!\n";
echo "Please ensure your web server can write to these directories.\n";
?>
