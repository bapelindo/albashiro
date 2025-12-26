<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('SITE_ROOT', __DIR__);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/services/GeminiService.php';

echo "Testing GeminiService...\n\n";

try {
    $gemini = new GeminiService();

    // Test with simple message
    $result = $gemini->chat("jadwal hari ini", []);

    echo "Success!\n";
    echo "Response: " . substr($result['response'], 0, 200) . "...\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}
