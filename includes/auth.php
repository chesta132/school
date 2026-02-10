<?php
// auth.php - Authentication Middleware

require_once __DIR__ . '/config.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email']
        ];
    }
    return null;
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

/**
 * Redirect if already logged in
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: /');
        exit;
    }
}

/**
 * Logout user
 */
function logout() {
    session_unset();
    session_destroy();
    session_start();
}
