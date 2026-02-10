<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$name = sanitize($data['name'] ?? '');
$category_id = intval($data['category_id'] ?? 0);
$weight = intval($data['weight'] ?? 0);
$price = floatval($data['price'] ?? 0);
$discount = floatval($data['discount'] ?? 0);
$stock = intval($data['stock'] ?? 0);

if (empty($name) || $category_id == 0 || $weight == 0 || $price == 0) {
    jsonResponse(['success' => false, 'message' => 'Data tidak lengkap']);
}

try {
    // Get category code
    $stmt = $pdo->prepare("SELECT code FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        jsonResponse(['success' => false, 'message' => 'Kategori tidak ditemukan']);
    }
    
    // Generate SKU
    $sku = generateSKU($category['code'], $name, $weight);
    
    // Check if SKU exists
    if (skuExists($pdo, $sku)) {
        jsonResponse(['success' => false, 'message' => 'SKU sudah ada, gunakan nama atau berat yang berbeda']);
    }
    
    // Insert product
    $stmt = $pdo->prepare("
        INSERT INTO products (name, category_id, weight, sku, price, discount, stock) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $category_id, $weight, $sku, $price, $discount, $stock]);
    
    jsonResponse(['success' => true, 'message' => 'Produk berhasil ditambahkan']);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}
