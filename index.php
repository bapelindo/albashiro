<?php
/**
 * Albashiro - Islamic Spiritual Hypnotherapy
 * Front Controller / Entry Point
 */

// Define constant to allow config access
define('ALBASHIRO', true);

// Load configuration
require_once __DIR__ . '/config/config.php';

// Load core classes
require_once SITE_ROOT . '/core/ServerTiming.php';
require_once SITE_ROOT . '/core/Database.php';
require_once SITE_ROOT . '/core/Controller.php';
require_once SITE_ROOT . '/core/App.php';

// Start output buffering to capture content allowing headers to be sent later
ob_start();

// Start App Timer
ServerTiming::start('app');

// Register shutdown function to send headers and flush output
register_shutdown_function(function () {
    ServerTiming::stop('app');
    ServerTiming::sendHeaders();
    ob_end_flush();
});

// Initialize application
$app = new App();
