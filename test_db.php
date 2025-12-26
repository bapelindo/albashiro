<?php
// Test database connection and query
require_once 'config/config.php';
require_once 'core/Database.php';

echo "=== DATABASE CONNECTION TEST ===\n\n";

try {
    $db = Database::getInstance();
    echo "✅ Database instance created\n";

    $pdo = $db->getPdo();
    echo "✅ PDO connection obtained\n";

    // Test services query
    echo "\n--- Testing Services Query ---\n";
    $stmt = $pdo->query("SELECT name, description, price FROM services ORDER BY sort_order LIMIT 3");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Services found: " . count($services) . "\n";
    foreach ($services as $s) {
        $price = number_format($s['price'], 0, ',', '.');
        echo "  - {$s['name']}: Rp $price\n";
    }

    // Test therapists query
    echo "\n--- Testing Therapists Query ---\n";
    $stmt = $pdo->query("SELECT name, specialty FROM therapists WHERE is_active = 1 LIMIT 3");
    $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Therapists found: " . count($therapists) . "\n";
    foreach ($therapists as $t) {
        echo "  - {$t['name']} (Spesialisasi: {$t['specialty']})\n";
    }

    echo "\n✅ ALL TESTS PASSED!\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
