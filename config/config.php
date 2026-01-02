<?php
// Load .env.local for localhost development
if (file_exists(__DIR__ . '/../.env.local')) {
    $envFile = file(__DIR__ . '/../.env.local', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0)
            continue; // Skip empty lines and comments
        if (strpos($line, '=') === false)
            continue; // Skip lines without =

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // Remove surrounding quotes
        $value = trim($value, '"\'');
        if (!empty($name)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

/**
 * Albashiro - Configuration File
 */

// Allow web access (via ALBASHIRO constant) or CLI execution (for cron jobs)
if (!defined('ALBASHIRO') && php_sapi_name() !== 'cli') {
    exit('Direct access not permitted');
}

// =====================================================
// DATABASE CONFIGURATION
// =====================================================
// Support for Environment Variables (Vercel/Cloud) with Local Fallback
define('DB_HOST', getenv('TIDB_HOST') ?: 'gateway01.ap-northeast-1.prod.aws.tidbcloud.com');
define('DB_NAME', getenv('TIDB_DATABASE') ?: 'albashiro');
define('DB_USER', getenv('TIDB_USER') ?: '4TnpUUxik5ZLHTT.root');
define('DB_PASS', getenv('TIDB_PASSWORD') ?: 'LuCuUyXhfEy3EJVI');
define('DB_PORT', getenv('TIDB_PORT') ?: '4000');
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// SITE CONFIGURATION
// =====================================================
define('SITE_NAME', 'Albashiro');
define('SITE_TAGLINE', 'Islamic Spiritual Hypnotherapy');
// Detect environment URL
$protocol = 'http://';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'https://';
} elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = 'https://';
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$root = $host === 'localhost' ? '/albashiro' : ''; // Vercel serves from root
define('SITE_URL', getenv('SITE_URL') ?: $protocol . $host . $root);
define('SITE_ROOT', dirname(__DIR__));

// =====================================================
// ADMIN CONFIGURATION
// =====================================================
define('ADMIN_WHATSAPP', '6282228967897');
define('ADMIN_EMAIL', 'info@albashiro.com');
define('FONNTE_API_TOKEN', 'baXPGAQDBSfTe3vQ84W8'); // Device Token
define('FONNTE_GROUP_ID', '120363422798942271@g.us'); // WhatsApp Group for notifications

// =====================================================
// AI CHATBOT CONFIGURATION (Ollama)
// =====================================================
// ⚠️ SINGLE SOURCE OF TRUTH - Edit model here only!
// Primary: Local Ollama (Standalone)
// Auto-detect environment: localhost vs production
$isLocalhost = (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));
$defaultOllamaUrl = $isLocalhost ? 'http://localhost:11434' : 'https://ollama.bapel.my.id';
define('OLLAMA_API_URL', getenv('OLLAMA_API_URL') ?: $defaultOllamaUrl);
define('OLLAMA_MODEL', 'albashiro');  // Fine-tuned Qwen3 4B with Islamic persona & Albashiro knowledgeIslamic persona & Albashiro knowledge
// Secondary: Embedding Model// Embedding Model: 'all-minilm' (Official, Light & Fast)

// Legacy/Cloud APIs (DISABLED)
// define('GOOGLE_API_KEY', getenv('GOOGLE_API_KEY') ?: '');
// define('GOOGLE_MODEL', 'gemini-2.0-flash');
// define('HUGGINGFACE_API_KEY', getenv('HUGGINGFACE_API_KEY') ?: '');
// define('HUGGINGFACE_API_KEY', getenv('HUGGINGFACE_API_KEY') ?: '');
// define('HUGGINGFACE_API_URL', 'https://router.huggingface.co/v1/chat/completions');

// Experimental: Semantic Intent Routing (Memory Intensive)
define('USE_SEMANTIC_ROUTING', true); // Set to true to enable experimental vector-based intent detection

// =====================================================
// THERAPIST WHATSAPP NUMBERS
// =====================================================
// Format: Therapist ID => WhatsApp Number
define('THERAPIST_WHATSAPP', [
    1 => '628155017069',  // Bunda Dewi
    2 => '62895335419945',  // Bu Muza
    3 => '6282131089039',  // Ustadzah Fatimah Zahra
]);

// =====================================================
// TIMEZONE
// =====================================================
date_default_timezone_set('Asia/Jakarta');

// Set to 0 in production
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);

// =====================================================
// SESSION CONFIGURATION
// =====================================================
if (session_status() === PHP_SESSION_NONE && php_sapi_name() !== 'cli') {
    // Vercel-compatible session configuration
    // Use /tmp directory (only writable location on Vercel)
    if (getenv('VERCEL')) {
        ini_set('session.save_path', '/tmp');
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        ini_set('session.gc_maxlifetime', 1440); // 24 minutes
    }

    // Set session cookie parameters for security
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Generate base URL
 */
function base_url($path = '')
{
    // Check if it's an asset (public folder) to avoid routing via index.php
    if (strpos($path, 'public/') === 0 || strpos($path, 'assets/') === 0) {
        return SITE_URL . '/' . ltrim($path, '/');
    }

    if ($path == '') {
        return SITE_URL . '/';
    }

    // Clean URL generation
    $path = ltrim($path, '/');
    return SITE_URL . '/' . $path;
}

/**
 * Redirect to URL
 */
function redirect($url)
{
    header("Location: " . base_url($url));
    exit;
}

/**
 * Escape output for XSS prevention
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token (Vercel-compatible)
 */
function csrf_token()
{
    $cookieName = 'albashiro_csrf';

    // Try to get from cookie first
    if (isset($_COOKIE[$cookieName])) {
        $token = $_COOKIE[$cookieName];
        $_SESSION['csrf_token'] = $token; // Sync to session
        return $token;
    }

    // Generate new token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $token = $_SESSION['csrf_token'];

    // Store in cookie for persistence across serverless instances
    $secure = isset($_SERVER['HTTPS']) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    setcookie(
        $cookieName,
        $token,
        [
            'expires' => time() + 3600, // 1 hour
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );

    return $token;
}

/**
 * Verify CSRF token (Vercel-compatible)
 */
function verify_csrf($token)
{
    $cookieName = 'albashiro_csrf';

    // Check cookie first (for Vercel)
    if (isset($_COOKIE[$cookieName])) {
        return hash_equals($_COOKIE[$cookieName], $token);
    }

    // Fallback to session (for local)
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format price to Indonesian Rupiah
 */
function format_rupiah($number)
{
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Generate booking code
 */
function generate_booking_code()
{
    return 'ALB-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

/**
 * Generate WhatsApp booking link
 */
function generate_wa_link($therapist_name, $service_name, $client_name, $date)
{
    $message = "Assalamu'alaikum,\n\n";
    $message .= "Saya ingin melakukan reservasi hipnoterapi:\n\n";
    $message .= "Nama: {$client_name}\n";
    $message .= "Terapis: {$therapist_name}\n";
    $message .= "Layanan: {$service_name}\n";
    $message .= "Tanggal: {$date}\n\n";
    $message .= "Mohon konfirmasi ketersediaan jadwal. Terima kasih.";

    return 'https://wa.me/' . ADMIN_WHATSAPP . '?text=' . urlencode($message);
}

/**
 * Format date to Indonesian format
 */
function format_date_id($date)
{
    $months = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    $timestamp = strtotime($date);
    $day_name = $days[date('w', $timestamp)];
    $day = date('j', $timestamp);
    $month = $months[(int) date('n', $timestamp)];
    $year = date('Y', $timestamp);

    return "{$day_name}, {$day} {$month} {$year}";
}
