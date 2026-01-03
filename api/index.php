<?php
/**
 * SMART API BRIDGE
 * Handles routing for both:
 * 1. Vercel Production (Serverless PHP -> CodeIgniter)
 * 2. Local Cloudflare Tunnel (Apache -> Node.js Port 3000)
 */

// 1. Detect if running on Vercel
$isVercel = getenv('VERCEL') || (isset($_ENV['VERCEL']) && $_ENV['VERCEL'] === '1');

// 2. If Vercel, hand over to CodeIgniter (Legacy/Standard Mode)
if ($isVercel) {
    require __DIR__ . '/../index.php';
    exit;
}

// 3. If Local/Tunnel, PROXY to Node.js Service (Modern Mode)
// This fixes the "spinning" issue on albashiro.bapel.my.id

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Target Node.js Endpoint (Localhost IPv4 Explicit)
$targetUrl = 'http://127.0.0.1:3000/api/stream';

// Get JSON Input
$input = file_get_contents('php://input');

// Initialize cURL
$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Stream directly to output

// 1. Forward Response Headers (Critical for SSE)
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) {
    $len = strlen($header);
    $headerVal = trim($header);
    if (empty($headerVal))
        return $len;

    // SAFETY: Don't forward internal transport headers that might conflict with Apache/Cloudflare
    $lowerHeader = strtolower($headerVal);
    if (strpos($lowerHeader, 'transfer-encoding:') === 0)
        return $len;
    if (strpos($lowerHeader, 'content-length:') === 0)
        return $len;
    if (strpos($lowerHeader, 'connection:') === 0)
        return $len;

    header($headerVal);
    return $len;
});

// 2. Stream Body
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) {
    echo $chunk;
    if (ob_get_length())
        ob_flush();
    flush();
    return strlen($chunk);
});

curl_setopt($ch, CURLOPT_TIMEOUT, 120);

// Execute
$success = curl_exec($ch);

if (!$success) {
    $err = curl_error($ch);
    http_response_code(500);
    echo json_encode(['error' => 'Node.js Proxy Error: ' . $err]);
}

curl_close($ch);