<?php
// api/get-stats.php - Get dashboard statistics

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth();

header('Content-Type: application/json');

try {
    $today = date('Y-m-d');
    
    // Total penjualan hari ini
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(grand_total), 0) as total FROM transactions WHERE DATE(created_at) = ?");
    $stmt->execute([$today]);
    $total_sales_today = $stmt->fetch()['total'];
    
    // Jumlah transaksi hari ini
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = ?");
    $stmt->execute([$today]);
    $total_transactions_today = $stmt->fetch()['count'];
    
    // Total item terjual hari ini
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(ti.qty), 0) as total 
        FROM transaction_items ti 
        JOIN transactions t ON ti.transaction_id = t.id 
        WHERE DATE(t.created_at) = ?
    ");
    $stmt->execute([$today]);
    $total_items_sold_today = $stmt->fetch()['total'];
    
    // Produk stok menipis (< 10)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock < 10");
    $low_stock_count = $stmt->fetch()['count'];
    
    // Penjualan 7 hari terakhir
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, SUM(grand_total) as total 
        FROM transactions 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $sales_chart = $stmt->fetchAll();
    
    // 5 transaksi terakhir
    $stmt = $pdo->query("
        SELECT t.*, u.username 
        FROM transactions t 
        JOIN users u ON t.user_id = u.id 
        ORDER BY t.created_at DESC 
        LIMIT 5
    ");
    $recent_transactions = $stmt->fetchAll();
    
    // Produk stok < 10
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.stock < 10 
        ORDER BY p.stock ASC
    ");
    $low_stock_products = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'stats' => [
            'total_sales_today' => $total_sales_today,
            'total_transactions_today' => $total_transactions_today,
            'total_items_sold_today' => $total_items_sold_today,
            'low_stock_count' => $low_stock_count
        ],
        'sales_chart' => $sales_chart,
        'recent_transactions' => $recent_transactions,
        'low_stock_products' => $low_stock_products
    ]);
    
} catch (PDOException $e) {
    error_log("Stats error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server'], 500);
}
