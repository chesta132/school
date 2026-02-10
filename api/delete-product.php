<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    
    jsonResponse(['success' => true, 'message' => 'Produk berhasil dihapus']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}
