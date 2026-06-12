<?php
/**
 * Authentication helper
 */
require_once 'config.php';
require_once 'Database.php';
require_once 'User.php';

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function isAuthenticated() {
    return !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

function loginUser($user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    $_SESSION['logged_in'] = true;

    $db = new Database();
    $userModel = new User($db);
    $userModel->updateLastLogin($user['id']);
}

function logoutUser() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
    header('Location: login.php');
    exit;
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
