<?php
// Vercel Entry Point - Optimized for Streaming
// Prevent Vercel Timeout (10s limit) by forcing unbuffered output

// 1. Disable PHP Output Buffering
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
while (ob_get_level())
    ob_end_clean();

// 2. Set Streaming Headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Critical for Nginx/Proxies

// 3. Load Main App
require __DIR__ . '/../index.php';
