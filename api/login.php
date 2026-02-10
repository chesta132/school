<?php
// api/login.php - Handle login requests

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$username = sanitize($data['username'] ?? '');
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Username dan password harus diisi']);
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        jsonResponse([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => [
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Username atau password salah']);
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server'], 500);
}
