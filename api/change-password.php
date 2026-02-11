<?php
// api/change-password.php - Change user password

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

$old_password = $data['old_password'] ?? '';
$new_password = $data['new_password'] ?? '';
$confirm_new_password = $data['confirm_new_password'] ?? '';
$user_id = $_SESSION['user_id'];

// Validation
if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
    jsonResponse(['success' => false, 'message' => 'Semua field harus diisi']);
}

if (strlen($new_password) < 6) {
    jsonResponse(['success' => false, 'message' => 'Password baru minimal 6 karakter']);
}

if ($new_password !== $confirm_new_password) {
    jsonResponse(['success' => false, 'message' => 'Password baru dan konfirmasi tidak sama']);
}

try {
    // Get current user password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'User tidak ditemukan'], 404);
    }
    
    // Verify old password
    if (!password_verify($old_password, $user['password'])) {
        jsonResponse(['success' => false, 'message' => 'Password lama salah']);
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $user_id]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Password berhasil diubah'
    ]);
    
} catch (PDOException $e) {
    error_log("Change password error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server'], 500);
}
