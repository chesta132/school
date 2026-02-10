<?php
// api/get-product.php - Get product by SKU

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$sku = sanitize($_GET['sku'] ?? '');

if (empty($sku)) {
    jsonResponse(['success' => false, 'message' => 'SKU harus diisi']);
}

$product = getProductBySKU($pdo, $sku);

if ($product) {
    jsonResponse([
        'success' => true,
        'product' => $product
    ]);
} else {
    jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan']);
}
