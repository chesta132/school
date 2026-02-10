<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name, c.code as category_code 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        ORDER BY p.name ASC
    ");
    $products = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'products' => $products]);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}
