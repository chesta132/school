<?php
// Config.php - Configuration file for ChardyMart POS

// Database Configuration from Environment Variables
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'chardymart_pos');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'secret123');

// Secret Key for Registration
define('SECRET_KEY', getenv('SECRET_KEY') ?: 'chardymart_secret_2025_xyz');

// App Configuration
define('APP_NAME', 'ChardyMart POS');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Jakarta');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
