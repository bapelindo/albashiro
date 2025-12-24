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
require_once SITE_ROOT . '/core/Database.php';
require_once SITE_ROOT . '/core/Controller.php';
require_once SITE_ROOT . '/core/App.php';

// Initialize application
$app = new App();
