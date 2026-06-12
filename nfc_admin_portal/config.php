<?php
/**
 * Global Configuration
 */

// Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nfc_patrol_admin');

// Application Settings
define('APP_NAME', 'iZi GP Admin');
define('APP_URL', 'http://localhost/nfc_admin_portal');

// API Keys
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>