<?php
// COMPREHENSIVE TEST: Database → Logic → AI Context
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'app/services/GeminiService.php';

echo "=== COMPREHENSIVE SYSTEM TEST ===\n\n";

// 1. Test Database Connection
echo "1. DATABASE CONNECTION\n";
echo "   DB_HOST: " . DB_HOST . "\n";
echo "   DB_NAME: " . DB_NAME . "\n";
echo "   DB_USER: " . DB_USER . "\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    echo "   ✅ Connection successful\n\n";
} catch (Exception $e) {
    echo "   ❌ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test Services Query (exact same query as GeminiService)
echo "2. SERVICES QUERY TEST\n";
try {
    $stmt = $pdo->query("SELECT name, description, price FROM services ORDER BY sort_order");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Services found: " . count($services) . "\n";

    if (empty($services)) {
        echo "   ❌ NO SERVICES FOUND!\n";
    } else {
        echo "   ✅ Services loaded:\n";
        foreach ($services as $s) {
            $price = number_format($s['price'], 0, ',', '.');
            echo "      - {$s['name']}: Rp $price\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ QUERY FAILED: " . $e->getMessage() . "\n";
}

// 3. Test Therapists Query
echo "\n3. THERAPISTS QUERY TEST\n";
try {
    $stmt = $pdo->query("SELECT name, specialty FROM therapists WHERE is_active = 1");
    $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Therapists found: " . count($therapists) . "\n";

    if (empty($therapists)) {
        echo "   ❌ NO THERAPISTS FOUND!\n";
    } else {
        echo "   ✅ Therapists loaded:\n";
        foreach ($therapists as $t) {
            echo "      - {$t['name']} ({$t['specialty']})\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ QUERY FAILED: " . $e->getMessage() . "\n";
}

// 4. Test GeminiService Integration
echo "\n4. GEMINI SERVICE INTEGRATION TEST\n";
try {
    $gemini = new GeminiService();
    echo "   ✅ GeminiService instantiated\n";

    // Use reflection to call private method buildSystemContext
    $reflection = new ReflectionClass($gemini);
    $method = $reflection->getMethod('buildSystemContext');
    $method->setAccessible(true);

    $context = $method->invoke($gemini, 'berapa harga paket?');

    // Check if services data is in context
    if (strpos($context, 'Hipnoterapi Anak') !== false) {
        echo "   ✅ Services data found in AI context\n";
    } else {
        echo "   ❌ Services data NOT in AI context!\n";
    }

    // Check for hallucination warnings
    if (strpos($context, 'CRITICAL WARNING') !== false) {
        echo "   ✅ Anti-hallucination warnings present\n";
    } else {
        echo "   ❌ Anti-hallucination warnings MISSING!\n";
    }

    // Show relevant context snippet
    echo "\n   Context snippet (LAYANAN & HARGA section):\n";
    if (preg_match('/\[LAYANAN & HARGA RESMI\](.*?)\[TERAPIS\]/s', $context, $matches)) {
        $snippet = trim($matches[1]);
        $lines = explode("\n", $snippet);
        foreach (array_slice($lines, 0, 15) as $line) {
            echo "   " . $line . "\n";
        }
    }

} catch (Exception $e) {
    echo "   ❌ FAILED: " . $e->getMessage() . "\n";
}

// 5. Test API Keys
echo "\n5. API KEYS CHECK\n";
echo "   GOOGLE_API_KEY: " . (defined('GOOGLE_API_KEY') && !empty(GOOGLE_API_KEY) ? '✅ Set (' . strlen(GOOGLE_API_KEY) . ' chars)' : '❌ NOT SET') . "\n";
echo "   HUGGINGFACE_API_KEY: " . (defined('HUGGINGFACE_API_KEY') && !empty(HUGGINGFACE_API_KEY) ? '✅ Set (' . strlen(HUGGINGFACE_API_KEY) . ' chars)' : '❌ NOT SET') . "\n";

echo "\n=== TEST COMPLETE ===\n";
