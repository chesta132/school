<?php
// functions.php - Helper Functions

/**
 * Generate SKU format: {CATEGORY_CODE}-{3_HURUF_NAMA}-{WEIGHT}
 */
function generateSKU($category_code, $product_name, $weight) {
    // Get first 3 letters of product name (uppercase, alphanumeric only)
    $name_part = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $product_name), 0, 3));
    
    // Ensure we have at least 3 characters
    if (strlen($name_part) < 3) {
        $name_part = str_pad($name_part, 3, 'X');
    }
    
    return strtoupper($category_code) . '-' . $name_part . '-' . $weight;
}

/**
 * Generate Transaction Code format: TRX-YYYYMMDD-XXX
 */
function generateTransactionCode($pdo) {
    $date = date('Ymd');
    $prefix = 'TRX-' . $date . '-';
    
    // Get count of transactions today
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM transactions WHERE transaction_code LIKE ?");
    $stmt->execute([$prefix . '%']);
    $result = $stmt->fetch();
    
    $sequence = str_pad($result['count'] + 1, 3, '0', STR_PAD_LEFT);
    
    return $prefix . $sequence;
}

/**
 * Format currency to Indonesian Rupiah
 */
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Calculate discount amount
 */
function calculateDiscount($price, $discount_percent) {
    return $price * ($discount_percent / 100);
}

/**
 * Calculate final price after discount
 */
function calculateFinalPrice($price, $discount_percent) {
    return $price - calculateDiscount($price, $discount_percent);
}

/**
 * Update product stock and sold count
 */
function updateProductStock($pdo, $product_id, $qty) {
    try {
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ?, sold = sold + ? WHERE id = ?");
        return $stmt->execute([$qty, $qty, $product_id]);
    } catch (PDOException $e) {
        error_log("Error updating stock: " . $e->getMessage());
        return false;
    }
}

/**
 * JSON Response helper
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Validate secret key
 */
function validateSecretKey($input_key) {
    return $input_key === SECRET_KEY;
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Get stock status badge class
 */
function getStockBadgeClass($stock) {
    if ($stock < 10) return 'badge-danger';
    if ($stock < 30) return 'badge-warning';
    return 'badge-success';
}

/**
 * Get stock status text
 */
function getStockStatusText($stock) {
    if ($stock < 10) return 'Low';
    if ($stock < 30) return 'Medium';
    return 'Good';
}

/**
 * Format date to Indonesian format
 */
function formatDate($date, $format = 'd M Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Format datetime to Indonesian format (alias used in views & receipts)
 */
function formatDateTime($datetime, $format = 'd M Y H:i') {
    return date($format, strtotime($datetime));
}

/**
 * Get product by SKU
 */
function getProductBySKU($pdo, $sku) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, c.code as category_code 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.sku = ?
        ");
        $stmt->execute([$sku]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting product: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if SKU exists
 */
function skuExists($pdo, $sku, $exclude_id = null) {
    try {
        if ($exclude_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE sku = ? AND id != ?");
            $stmt->execute([$sku, $exclude_id]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE sku = ?");
            $stmt->execute([$sku]);
        }
        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}
