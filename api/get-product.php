<?php
// api/get-product.php - Get product by SKU or ID

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$product = null;

if (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
} elseif (!empty($_GET['sku'])) {
    $sku = sanitize($_GET['sku']);
    $product = getProductBySKU($pdo, $sku);
} else {
    jsonResponse(['success' => false, 'message' => 'Parameter SKU atau ID harus diisi']);
}

if ($product) {
    jsonResponse([
        'success' => true,
        'product' => $product
    ]);
} else {
    jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan']);
}
