<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$name = sanitize($data['name'] ?? '');
$code = strtoupper(sanitize($data['code'] ?? ''));

try {
    $stmt = $pdo->prepare("INSERT INTO categories (name, code) VALUES (?, ?)");
    $stmt->execute([$name, $code]);
    
    jsonResponse(['success' => true, 'message' => 'Kategori berhasil ditambahkan']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        jsonResponse(['success' => false, 'message' => 'Kode kategori sudah ada']);
    }
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}
