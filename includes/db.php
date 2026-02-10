<?php
// db.php - Database Connection using PDO

require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
    
} catch (PDOException $e) {
    // Log error in production, display in development
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact administrator.");
}
