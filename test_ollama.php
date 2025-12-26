<?php
// Test OllamaService locally
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'app/services/OllamaService.php';

echo "=== OLLAMA SERVICE TEST ===\n\n";

// Set Ollama URL (use localhost for testing before Cloud Run deployment)
putenv('OLLAMA_API_URL=http://localhost:11434');

$ollama = new OllamaService();

$testMessages = [
    "assalamualaikum",
    "berapa harga paket?",
    "saya cemas dan susah tidur",
];

foreach ($testMessages as $message) {
    echo "USER: $message\n";
    echo str_repeat("-", 70) . "\n";

    try {
        $result = $ollama->chat($message);

        echo "PROVIDER: {$result['metadata']['provider']}\n";
        echo "MODEL: {$result['metadata']['model']}\n";
        echo "TIME: {$result['metadata']['response_time_ms']}ms\n";

        if (isset($result['metadata']['fallback'])) {
            echo "⚠️  FALLBACK USED: {$result['metadata']['ollama_error']}\n";
        }

        echo "\nRESPONSE:\n{$result['response']}\n";

    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }

    echo "\n" . str_repeat("=", 70) . "\n\n";
}

echo "✅ TEST COMPLETED\n";
echo "\nNOTE: If Ollama is not running locally, it will fallback to LocalAI.\n";
echo "To test with Cloud Run, set OLLAMA_API_URL to your Cloud Run URL.\n";
