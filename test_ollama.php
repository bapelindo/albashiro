<?php
/**
 * Test Ollama Model - Albashiro
 * File untuk test apakah model membaca context dengan benar
 */

// Configuration
$ollamaUrl = 'http://localhost:11434';
$model = 'albashiro';

// Test 1: Context Reading Test
echo "=== TEST 1: Context Reading Test ===\n\n";

$testContext = <<<CONTEXT
<context>
LAYANAN:
- Hipnoterapi Anak: Rp 555.000 (CONTEXT TEST)
- Terapis: Dr. Test Specialist (TEST DATA)
- Durasi: 45 menit (TEST DURATION)
</context>

<user_query>
Berapa harga hipnoterapi anak dan siapa terapisnya?
</user_query>
CONTEXT;

$response1 = testOllama($ollamaUrl, $model, $testContext);
echo "Input:\n$testContext\n\n";
echo "Output:\n$response1\n\n";
echo "Expected: Should mention 'Rp 555.000', 'Dr. Test Specialist', '45 menit'\n";
echo "Result: " . (
    strpos($response1, '555.000') !== false &&
    strpos($response1, 'Test Specialist') !== false
    ? "✅ PASS - Model membaca context dengan benar!"
    : "❌ FAIL - Model tidak membaca context!"
) . "\n\n";

echo str_repeat("=", 70) . "\n\n";

// Test 2: No Context Test (Should use base knowledge)
echo "=== TEST 2: No Context Test (Base Knowledge) ===\n\n";

$testNoContext = "Halo, apa itu hipnoterapi Islami?";

$response2 = testOllama($ollamaUrl, $model, $testNoContext);
echo "Input:\n$testNoContext\n\n";
echo "Output:\n$response2\n\n";
echo "Expected: Should give Islamic hypnotherapy explanation with Islamic phrases\n";
echo "Result: " . (
    (strpos($response2, 'Alhamdulillah') !== false ||
        strpos($response2, 'Insya Allah') !== false ||
        strpos($response2, 'Islam') !== false)
    ? "✅ PASS - Model memberikan respons Islami!"
    : "❌ FAIL - Model tidak Islami!"
) . "\n\n";

echo str_repeat("=", 70) . "\n\n";

// Test 3: Hallucination Test
echo "=== TEST 3: Hallucination Test ===\n\n";

$testHallucination = "Berapa harga terapi untuk anak?";

$response3 = testOllama($ollamaUrl, $model, $testHallucination);
echo "Input:\n$testHallucination\n\n";
echo "Output:\n$response3\n\n";
echo "Expected: Should mention 'Rp 500.000' or 'Rp500rb' (from SYSTEM prompt facts)\n";
echo "Result: " . (
    (strpos($response3, '500') !== false || strpos($response3, '500rb') !== false)
    ? "✅ PASS - Model menggunakan fakta yang benar!"
    : "⚠️ WARNING - Cek apakah harga yang disebutkan benar!"
) . "\n\n";

echo str_repeat("=", 70) . "\n\n";

echo "=== SUMMARY ===\n";
echo "Test selesai! Cek hasil di atas untuk memastikan model bekerja dengan baik.\n";

/**
 * Function to test Ollama API
 */
function testOllama($baseUrl, $model, $message)
{
    $url = $baseUrl . '/api/generate';

    $data = [
        'model' => $model,
        'prompt' => $message,
        'stream' => false,
        'options' => [
            'temperature' => 0.7,
            'top_p' => 0.8,
            'top_k' => 20,
            'num_predict' => 512
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return "ERROR: $error";
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['response'])) {
        return $result['response'];
    }

    return "ERROR: " . json_encode($result);
}
