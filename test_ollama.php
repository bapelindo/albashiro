<?php
// Bootstrap
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/app/services/OllamaService.php';

// Set header text/plain for CLI or Browser readability
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "=======================================================\n";
echo "       ALBASHIRO OLLAMA INTEGRATION TEST\n";
echo "=======================================================\n\n";

try {
    $ollama = new OllamaService();

    $questions = [
        "Apa itu hipnoterapi islami?",
        "Siapa saja terapis yang tersedia di Albashiro?",
        "Berapa biaya untuk terapi anak?",
        "Apakah ada jadwal kosong untuk besok sore?",
        "Di mana alamat lokasi prakteknya?",
        "Bagaimana cara melakukan reservasi?",
        "Apakah menyediakan layanan terapi online?",
        "Saya merasa sangat cemas dan takut mati setiap malam, tolong saya.",
        "Apa perbedaan hipnoterapi biasa dengan yang islami?",
        "Jam berapa klinik buka hari ini?"
    ];

    foreach ($questions as $i => $q) {
        $num = $i + 1;
        echo "-------------------------------------------------------\n";
        echo "PERTANYAAN #$num: $q\n";
        echo "-------------------------------------------------------\n";
        echo "JAWABAN:\n";

        $fullResponse = "";

        try {
            // Use chatStream with a callback to capture output
            $response = $ollama->chatStream(
                $q,
                [], // No history for independent tests
                function ($token) {
                    echo $token;
                    flush();
                },
                function ($status) {
                    // Ignore status updates like "Sedang memproses..."
                },
                true // Skip auto-learning logging
            );

            echo "\n\n";

            // Show metadata if available
            if (isset($response['metadata'])) {
                echo "[METADATA] Response Time: {$response['metadata']['response_time_ms']}ms | ";
                echo "Knowledge Matched: {$response['metadata']['knowledge_matched']} | ";
                echo "Keywords: {$response['metadata']['keywords_searched']}\n";
            }

        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }

        echo "\n";
        // Give a small pause between requests to let the model cool down/reset context if needed
        sleep(1);
    }

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}

echo "=======================================================\n";
echo "TEST SELESAI\n";
echo "=======================================================\n";
