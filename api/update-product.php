<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
$price = floatval($data['price'] ?? 0);
$discount = floatval($data['discount'] ?? 0);
$stock = intval($data['stock'] ?? 0);

try {
    $stmt = $pdo->prepare("UPDATE products SET price = ?, discount = ?, stock = ? WHERE id = ?");
    $stmt->execute([$price, $discount, $stock, $id]);
    
    jsonResponse(['success' => true, 'message' => 'Produk berhasil diupdate']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}
