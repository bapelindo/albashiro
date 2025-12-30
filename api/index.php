<?php
// Vercel Entry Point - Optimized for Streaming
// Prevent Vercel Timeout (10s limit) by forcing unbuffered output

// 1. Disable PHP Output Buffering
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
while (ob_get_level())
    ob_end_clean();

// 2. Set Streaming Settings (Headers handled by Controller)
$requestUrl = $_GET['url'] ?? $_SERVER['REQUEST_URI'];

// Only apply NGINX buffer disabling here, Content-Type is handled by Controller
if (stripos($requestUrl, 'chat') !== false) {
    header('X-Accel-Buffering: no');
}

// 3. Load Main App
require __DIR__ . '/../index.php';
