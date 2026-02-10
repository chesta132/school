<?php
// Config.php - Configuration file for ChardyMart POS

// Database Configuration from Environment Variables
define('DB_HOST', getenv('DB_HOST') ?: '10.10.6.1');
define('DB_NAME', getenv('DB_NAME') ?: 'chardymart_pos');
define('DB_USER', getenv('DB_USER') ?: 'admin');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'admin123');

// Secret Key for Registration
define('SECRET_KEY', getenv('SECRET_KEY') ?: 'secret');

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
