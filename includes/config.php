<?php
/**
 * NFC Security Patrol Admin System
 * Database Configuration
 */

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nfc_patrol_admin');

// Application settings
define('APP_NAME', 'NFC Security Patrol Admin');
define('APP_URL', 'http://localhost/nfc-admin');
define('APP_TIMEZONE', 'UTC');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
session_start();

// CSRF Token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
