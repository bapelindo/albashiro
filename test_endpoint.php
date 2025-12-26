<?php
// Simple test endpoint
define('ALBASHIRO', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/services/GeminiService.php';

header('Content-Type: application/json');

try {
    $gemini = new GeminiService();
    $result = $gemini->chat("test", []);

    echo json_encode([
        'success' => true,
        'response' => $result['response'],
        'test' => 'OpenRouter working!'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
