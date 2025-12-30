<?php
/**
 * API Endpoint: Get Chat Context
 * Returns context data (RAG, system prompt, history) for Node.js proxy
 */

// Define constant to allow config access
define('ALBASHIRO', true);

// Load configuration
require_once __DIR__ . '/../../config/config.php';

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
    // Initialize OllamaService to use its context building
    $ollamaService = new OllamaService();

    // Build context using OllamaService's internal method
    // We'll use reflection to access the protected method
    $reflection = new ReflectionClass($ollamaService);
    $method = $reflection->getMethod('buildSystemContext');
    $method->setAccessible(true);

    $perfData = [];
    $hasHistory = !empty($history);

    $systemContext = $method->invoke($ollamaService, $message, $perfData, null, $hasHistory);

    // Format messages array
    $messages = [];

    // Add history
    foreach ($history as $msg) {
        $role = ($msg['role'] === 'ai') ? 'assistant' : 'user';
        $messages[] = [
            'role' => $role,
            'content' => $msg['message']
        ];
    }

    // Add current message with context
    $userMessageWithContext = "<context>\n$systemContext\n</context>\n\n<user_query>\n$message\n</user_query>";
    $messages[] = [
        'role' => 'user',
        'content' => $userMessageWithContext
    ];

    // Return context data
    header('Content-Type: application/json');
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
