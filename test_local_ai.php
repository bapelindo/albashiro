<?php
// Test LocalAIService
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'app/services/LocalAIService.php';

echo "=== LOCAL AI SERVICE TEST ===\n\n";

$ai = new LocalAIService();

$testMessages = [
    "assalamualaikum",
    "berapa harga paket?",
    "apa saja layanan yang tersedia?",
    "siapa terapisnya?",
    "jadwal buka kapan?",
    "saya cemas dan susah tidur",
    "dimana lokasinya?",
    "bagaimana cara hubungi?",
];

foreach ($testMessages as $message) {
    echo "USER: $message\n";
    echo str_repeat("-", 50) . "\n";

    $result = $ai->chat($message);

    echo "INTENT: {$result['metadata']['intent']}\n";
    echo "TIME: {$result['metadata']['response_time_ms']}ms\n";
    echo "RESPONSE:\n{$result['response']}\n";
    echo "\n" . str_repeat("=", 70) . "\n\n";
}

echo "✅ ALL TESTS COMPLETED\n";
