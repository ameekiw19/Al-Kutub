#!/bin/bash

echo "Setting up broadcasting for real-time notifications..."

# Add to .env file
cat >> .env << EOF

# Broadcasting Configuration
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_APP_CLUSTER=your_pusher_cluster
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http

# Queue Worker (for background jobs)
QUEUE_CONNECTION=database

EOF

echo "Installing required packages..."
composer require pusher/pusher-php-server

echo "Publishing broadcasting configuration..."
php artisan vendor:publish --provider="Illuminate\Broadcasting\BroadcastServiceProvider"

echo "Creating WebSocket server configuration..."
cat > broadcasting.php << 'EOF'
<?php

require __DIR__.'/../vendor/autoload.php';

use Pusher\Pusher;

$pusher = new Pusher(
    env('PUSHER_APP_KEY'),
    env('PUSHER_APP_SECRET'),
    env('PUSHER_APP_ID'),
    [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'host' => env('PUSHER_HOST'),
        'port' => env('PUSHER_PORT'),
        'scheme' => env('PUSHER_SCHEME'),
        'encrypted' => true,
        'useTLS' => true,
    ]
);

echo "WebSocket server started on ws://127.0.0.1:6001\n";

EOF

echo "Setup completed!"
echo "Next steps:"
echo "1. Get Pusher credentials from https://dashboard.pusher.com/"
echo "2. Update PUSHER_* variables in .env file"
echo "3. Run 'php artisan queue:work' for background processing"
echo "4. Test real-time notifications by adding a new kitab"
