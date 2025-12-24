<?php
// Vercel Debug Tool
define('ALBASHIRO', true);
header('Content-Type: text/plain');

echo "=== VERCEL DEBUG ===\n\n";

echo "1. CURRENT DIRECTORY:\n";
echo getcwd() . "\n\n";

echo "2. PUBLIC/IMAGES LISTING:\n";
$imagesDir = __DIR__ . '/../public/images';
if (is_dir($imagesDir)) {
    echo "Directory exists: YES\n";
    $files = scandir($imagesDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file\n";
        }
    }
} else {
    echo "Directory exists: NO (Path: $imagesDir)\n";
    echo "Trying alternative path...\n";
    // Try explicit path
    $alt = '/var/task/user/public/images';
    if (is_dir($alt))
        echo "Found at $alt\n";
}

echo "\n3. CONFIG DUMP:\n";
require_once __DIR__ . '/../config/config.php';
echo "SITE_URL: " . SITE_URL . "\n";
echo "SITE_ROOT: " . SITE_ROOT . "\n";
echo "DB_HOST: " . DB_HOST . "\n";

echo "\n4. SERVER VARIABLES:\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'N/A') . "\n";
echo "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

echo "\n5. TEST IMAGE URL:\n";
echo base_url('public/images/hero-hypnotherapy.jpg') . "\n";
