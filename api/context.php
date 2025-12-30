<?php
/**
 * API Endpoint: Get Chat Context
 * Returns FULL context data (RAG, system prompt, history) for Node.js proxy
 * This replicates OllamaService.php's context building logic
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
require_once SITE_ROOT . '/app/services/OllamaService.php';

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
    // Initialize OllamaService and call chatStream to get prepared messages
    // We'll use output buffering to capture the context
    $ollamaService = new OllamaService();

    // Convert history format
    $conversationHistory = [];
    foreach ($history as $msg) {
        $conversationHistory[] = [
            'role' => $msg['role'],
            'message' => $msg['message']
        ];
    }

    // Call chatStream with a callback that does nothing
    // This will build the full context internally
    $capturedMessages = null;

    // We need to access the prepared messages
    // Since we can't easily extract them, we'll replicate the logic

    // Build messages array
    $messages = [];

    // Add history
    foreach ($conversationHistory as $msg) {
        $role = ($msg['role'] === 'ai') ? 'assistant' : 'user';
        $messages[] = [
            'role' => $role,
            'content' => $msg['message']
        ];
    }

    // Build system context (replicate buildSystemContext logic)
    $perfData = [];
    $hasHistory = !empty($messages);

    // Call the actual buildSystemContext method using reflection
    $reflection = new ReflectionClass($ollamaService);
    $method = $reflection->getMethod('buildSystemContext');
    $method->setAccessible(true);

    $systemContext = $method->invoke($ollamaService, $message, $perfData, null, $hasHistory);

    // Add user message with full context
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
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
