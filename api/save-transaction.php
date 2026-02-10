<?php
// api/save-transaction.php - Save transaction

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

$items = $data['items'] ?? [];
$payment_amount = floatval($data['payment_amount'] ?? 0);

if (empty($items)) {
    jsonResponse(['success' => false, 'message' => 'Keranjang kosong']);
}

try {
    $pdo->beginTransaction();
    
    $total_amount = 0;
    $total_discount = 0;
    
    // Calculate totals
    foreach ($items as $item) {
        $price = floatval($item['price']);
        $qty = intval($item['qty']);
        $discount_percent = floatval($item['discount']);
        
        $item_total = $price * $qty;
        $item_discount = calculateDiscount($item_total, $discount_percent);
        
        $total_amount += $item_total;
        $total_discount += $item_discount;
    }
    
    $grand_total = $total_amount - $total_discount;
    $change_amount = $payment_amount - $grand_total;
    
    if ($payment_amount < $grand_total) {
        jsonResponse(['success' => false, 'message' => 'Pembayaran kurang']);
    }
    
    // Generate transaction code
    $transaction_code = generateTransactionCode($pdo);
    
    // Insert transaction
    $stmt = $pdo->prepare("
        INSERT INTO transactions (transaction_code, user_id, total_amount, total_discount, grand_total, payment_amount, change_amount) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $transaction_code,
        $_SESSION['user_id'],
        $total_amount,
        $total_discount,
        $grand_total,
        $payment_amount,
        $change_amount
    ]);
    
    $transaction_id = $pdo->lastInsertId();
    
    // Insert transaction items and update stock
    foreach ($items as $item) {
        $product_id = intval($item['id']);
        $product_name = sanitize($item['name']);
        $price = floatval($item['price']);
        $qty = intval($item['qty']);
        $discount_percent = floatval($item['discount']);
        
        $item_total = $price * $qty;
        $item_discount = calculateDiscount($item_total, $discount_percent);
        $subtotal = $item_total - $item_discount;
        
        // Insert transaction item
        $stmt = $pdo->prepare("
            INSERT INTO transaction_items (transaction_id, product_id, product_name, qty, price, discount, subtotal) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $transaction_id,
            $product_id,
            $product_name,
            $qty,
            $price,
            $discount_percent,
            $subtotal
        ]);
        
        // Update product stock
        updateProductStock($pdo, $product_id, $qty);
    }
    
    $pdo->commit();
    
    jsonResponse([
        'success' => true,
        'message' => 'Transaksi berhasil',
        'transaction' => [
            'id' => $transaction_id,
            'code' => $transaction_code,
            'grand_total' => $grand_total,
            'payment' => $payment_amount,
            'change' => $change_amount
        ]
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Transaction error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan transaksi'], 500);
}
