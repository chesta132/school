<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
    $stmt->execute([$id]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        jsonResponse(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
    }
    
    $stmt = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'transaction' => $transaction, 'items' => $items]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}
