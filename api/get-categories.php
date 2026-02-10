<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'categories' => $categories]);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}
