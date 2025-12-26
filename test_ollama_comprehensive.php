<?php
/**
 * Comprehensive Ollama Test Suite
 * Tests all aspects of gemma3:4b integration
 */

require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'app/services/OllamaService.php';

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         OLLAMA GEMMA3:4B COMPREHENSIVE TEST SUITE           ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Set Ollama URL
putenv('OLLAMA_API_URL=http://localhost:11434');

$ollama = new OllamaService();
$testResults = [];

// Test scenarios
$tests = [
    [
        'name' => 'Greeting Test',
        'message' => 'assalamualaikum',
        'expected' => 'Islamic greeting response',
        'check' => function ($response) {
            return stripos($response, 'warahmatullahi') !== false;
        }
    ],
    [
        'name' => 'Factual Price Test',
        'message' => 'berapa harga hipnoterapi anak?',
        'expected' => 'Exact price: Rp 500.000',
        'check' => function ($response) {
            return stripos($response, '500.000') !== false || stripos($response, '500,000') !== false;
        }
    ],
    [
        'name' => 'Empathy Test',
        'message' => 'saya cemas dan susah tidur, takut keluar rumah',
        'expected' => 'Empathy + recommendation',
        'check' => function ($response) {
            return (stripos($response, 'memahami') !== false || stripos($response, 'empati') !== false) &&
                (stripos($response, 'stres') !== false || stripos($response, 'kecemasan') !== false);
        }
    ],
    [
        'name' => 'Anti-Hallucination Test',
        'message' => 'ada paket premium atau paket keluarga?',
        'expected' => 'No such packages, show real services only',
        'check' => function ($response) {
            // Should NOT mention "Paket Premium" or "Paket Keluarga" as if they exist
            return stripos($response, 'tidak ada') !== false ||
                stripos($response, 'hubungi admin') !== false ||
                stripos($response, 'layanan yang tersedia') !== false;
        }
    ],
    [
        'name' => 'Complex Question Test',
        'message' => 'saya punya trauma masa kecil dan sekarang susah percaya orang, terapi apa yang cocok?',
        'expected' => 'Recommend "Trauma & Luka Batin"',
        'check' => function ($response) {
            return stripos($response, 'trauma') !== false &&
                (stripos($response, '600.000') !== false || stripos($response, '600,000') !== false);
        }
    ],
    [
        'name' => 'Therapist Info Test',
        'message' => 'siapa saja terapisnya?',
        'expected' => 'List of therapists from database',
        'check' => function ($response) {
            return stripos($response, 'Dewi') !== false || stripos($response, 'terapis') !== false;
        }
    ],
];

// Run tests
foreach ($tests as $index => $test) {
    $testNum = $index + 1;
    echo "┌─────────────────────────────────────────────────────────────┐\n";
    echo "│ TEST {$testNum}: {$test['name']}\n";
    echo "└─────────────────────────────────────────────────────────────┘\n";
    echo "USER: {$test['message']}\n";
    echo str_repeat("─", 65) . "\n";

    try {
        $startTime = microtime(true);
        $result = $ollama->chat($test['message']);
        $responseTime = round((microtime(true) - $startTime) * 1000);

        $provider = $result['metadata']['provider'];
        $model = $result['metadata']['model'];
        $response = $result['response'];

        // Check if test passed
        $passed = $test['check']($response);

        echo "PROVIDER: {$provider}\n";
        echo "MODEL: {$model}\n";
        echo "TIME: {$responseTime}ms\n";
        echo "EXPECTED: {$test['expected']}\n";
        echo "STATUS: " . ($passed ? "✅ PASS" : "❌ FAIL") . "\n\n";
        echo "RESPONSE:\n{$response}\n";

        $testResults[] = [
            'name' => $test['name'],
            'passed' => $passed,
            'time' => $responseTime,
            'provider' => $provider
        ];

    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $testResults[] = [
            'name' => $test['name'],
            'passed' => false,
            'time' => 0,
            'provider' => 'error'
        ];
    }

    echo "\n" . str_repeat("═", 65) . "\n\n";
}

// Summary
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                        TEST SUMMARY                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$totalTests = count($testResults);
$passedTests = count(array_filter($testResults, fn($r) => $r['passed']));
$failedTests = $totalTests - $passedTests;
$avgTime = array_sum(array_column($testResults, 'time')) / $totalTests;

echo "Total Tests: {$totalTests}\n";
echo "Passed: {$passedTests} ✅\n";
echo "Failed: {$failedTests} ❌\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n";
echo "Avg Response Time: " . round($avgTime) . "ms\n\n";

foreach ($testResults as $result) {
    $status = $result['passed'] ? '✅' : '❌';
    echo "{$status} {$result['name']} ({$result['time']}ms, {$result['provider']})\n";
}

echo "\n";
if ($passedTests === $totalTests) {
    echo "🎉 ALL TESTS PASSED! Ollama gemma3:4b is working perfectly!\n";
} else {
    echo "⚠️  Some tests failed. Check responses above for details.\n";
}

echo "\n";
