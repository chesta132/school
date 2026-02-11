<?php
// api/get-users.php - Get all users (admin only)

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    $stmt = $pdo->query("
        SELECT id, username, email, created_at, updated_at 
        FROM users 
        ORDER BY created_at DESC
    ");
    $users = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'users' => $users
    ]);
    
} catch (PDOException $e) {
    error_log("Get users error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server'], 500);
}
