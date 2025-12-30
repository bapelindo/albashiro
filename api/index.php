<?php
// Vercel Entry Point - Optimized for Streaming
// Prevent Vercel from buffering output
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
while (ob_get_level())
    ob_end_clean();

require __DIR__ . '/../index.php';