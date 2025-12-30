<?php
/**
 * API Endpoint: Get Chat Context
 * Returns context data (RAG, system prompt, history) for Node.js proxy
 */

// Disable error display, return JSON errors instead
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON header early
header('Content-Type: application/json');

// Define constant to allow config access
define('ALBASHIRO', true);

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Load core classes
require_once SITE_ROOT . '/core/Database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$history = $input['history'] ?? [];

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

try {
    // Build messages manually
    $messages = [];

    // Add history
    foreach ($history as $msg) {
        $role = ($msg['role'] === 'ai') ? 'assistant' : 'user';
        $messages[] = [
            'role' => $role,
            'content' => $msg['message']
        ];
    }

    // Build basic context
    date_default_timezone_set('Asia/Jakarta');
    $currentDate = date('l, d F Y');
    $currentTime = date('H:i');

    $systemContext = "Tanggal: $currentDate\nWaktu: $currentTime WIB\n\n";
    $systemContext .= "Kamu adalah Asisten Albashiro, chatbot untuk Albashiro Islamic Spiritual Hypnotherapy.\n";

    // Add user message with context
    $userMessageWithContext = "<context>\n$systemContext\n</context>\n\n<user_query>\n$message\n</user_query>";
    $messages[] = [
        'role' => 'user',
        'content' => $userMessageWithContext
    ];

    // Return context data
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'metadata' => [
            'context_length' => strlen($systemContext),
            'history_count' => count($history)
        ]
    ]);

} catch (Exception $e) {
    error_log("Context API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
