<?php
// api/update-profile.php - Update user profile

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);

$username = sanitize($data['username'] ?? '');
$email = sanitize($data['email'] ?? '');
$user_id = $_SESSION['user_id'];

// Validation
if (empty($username) || empty($email)) {
    jsonResponse(['success' => false, 'message' => 'Username dan email harus diisi']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Format email tidak valid']);
}

try {
    // Check if username exists (excluding current user)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()['count'] > 0) {
        jsonResponse(['success' => false, 'message' => 'Username sudah digunakan']);
    }
    
    // Check if email exists (excluding current user)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()['count'] > 0) {
        jsonResponse(['success' => false, 'message' => 'Email sudah digunakan']);
    }
    
    // Update user
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->execute([$username, $email, $user_id]);
    
    // Update session
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    
    jsonResponse([
        'success' => true,
        'message' => 'Profile berhasil diupdate'
    ]);
    
} catch (PDOException $e) {
    error_log("Update profile error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server'], 500);
}
