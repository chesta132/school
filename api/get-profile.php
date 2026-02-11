<?php
// api/get-profile.php - Get current user profile

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("SELECT id, username, email, created_at, updated_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        jsonResponse([
            'success' => true,
            'user' => $user
        ]);
    } else {
        jsonResponse(['success' => false, 'message' => 'User tidak ditemukan'], 404);
    }
} catch (PDOException $e) {
    error_log("Get profile error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server'], 500);
}
