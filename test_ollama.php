<?php
// Test Ollama with gemma3:4b
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'app/services/OllamaService.php';

echo "=== OLLAMA GEMMA3:4B TEST ===\n\n";

// Set Ollama URL
putenv('OLLAMA_API_URL=http://localhost:11434');

$ollama = new OllamaService();

$testMessages = [
    "assalamualaikum",
    "berapa harga paket hipnoterapi?",
    "saya cemas dan susah tidur, apa yang harus saya lakukan?",
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
            echo "⚠️  FALLBACK: {$result['metadata']['ollama_error']}\n";
        }

        echo "\nRESPONSE:\n{$result['response']}\n";

    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }

    echo "\n" . str_repeat("=", 70) . "\n\n";
}

echo "✅ TEST COMPLETED\n";
echo "\nNOTE:\n";
echo "- If using Ollama: Response time ~1-3 seconds\n";
echo "- If fallback to LocalAI: Response time ~0-2ms\n";
echo "- gemma3:4b provides better quality than gemma2:2b\n";
