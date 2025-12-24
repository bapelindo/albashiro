<?php
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
define('DB_HOST', getenv('DB_HOST') ?: 'gateway01.ap-northeast-1.prod.aws.tidbcloud.com');
define('DB_NAME', getenv('DB_NAME') ?: 'albashiro');
define('DB_USER', getenv('DB_USER') ?: '4TnpUUxik5ZLHTT.root');
define('DB_PASS', getenv('DB_PASS') ?: 'xYwYMe4gp4c7IkgI');
define('DB_PORT', getenv('DB_PORT') ?: '4000');
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
define('FONNTE_GROUP_ID', '120363422821859147@g.us'); // WhatsApp Group for notifications

// =====================================================
// THERAPIST WHATSAPP NUMBERS
// =====================================================
// Format: Therapist ID => WhatsApp Number
define('THERAPIST_WHATSAPP', [
    1 => '6282228967897',  // Ustadz Ahmad Fadhil
    2 => '6282228967897',  // Dr. Siti Aminah
    3 => '6282228967897',  // Ustadzah Fatimah Zahra
]);

// =====================================================
// TIMEZONE
// =====================================================
date_default_timezone_set('Asia/Jakarta');

// =====================================================
// ERROR REPORTING (Set to 0 in production)
// =====================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =====================================================
// SESSION CONFIGURATION
// =====================================================
if (session_status() === PHP_SESSION_NONE && php_sapi_name() !== 'cli') {
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
 * Generate CSRF token
 */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf($token)
{
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
    $message .= "👤 Nama: {$client_name}\n";
    $message .= "👳 Terapis: {$therapist_name}\n";
    $message .= "✨ Layanan: {$service_name}\n";
    $message .= "📅 Tanggal: {$date}\n\n";
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
