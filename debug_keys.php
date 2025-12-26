<?php
// Debug script to check if API keys are loaded
require_once 'config/config.php';

echo "=== API KEY DEBUG ===\n\n";

echo "GOOGLE_API_KEY defined: " . (defined('GOOGLE_API_KEY') ? 'YES' : 'NO') . "\n";
echo "GOOGLE_API_KEY value: " . (defined('GOOGLE_API_KEY') ? (empty(GOOGLE_API_KEY) ? '[EMPTY]' : '[SET - ' . strlen(GOOGLE_API_KEY) . ' chars]') : '[NOT DEFINED]') . "\n\n";

echo "HUGGINGFACE_API_KEY defined: " . (defined('HUGGINGFACE_API_KEY') ? 'YES' : 'NO') . "\n";
echo "HUGGINGFACE_API_KEY value: " . (defined('HUGGINGFACE_API_KEY') ? (empty(HUGGINGFACE_API_KEY) ? '[EMPTY]' : '[SET - ' . strlen(HUGGINGFACE_API_KEY) . ' chars]') : '[NOT DEFINED]') . "\n\n";

echo "getenv('GOOGLE_API_KEY'): " . (getenv('GOOGLE_API_KEY') ? getenv('GOOGLE_API_KEY') : '[EMPTY]') . "\n";
echo "getenv('HUGGINGFACE_API_KEY'): " . (getenv('HUGGINGFACE_API_KEY') ? getenv('HUGGINGFACE_API_KEY') : '[EMPTY]') . "\n\n";

echo "\$_ENV['GOOGLE_API_KEY']: " . (isset($_ENV['GOOGLE_API_KEY']) ? $_ENV['GOOGLE_API_KEY'] : '[NOT SET]') . "\n";
echo "\$_ENV['HUGGINGFACE_API_KEY']: " . (isset($_ENV['HUGGINGFACE_API_KEY']) ? $_ENV['HUGGINGFACE_API_KEY'] : '[NOT SET]') . "\n";
