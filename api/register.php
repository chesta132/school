<?php
// api/register.php - Handle registration requests

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$username = sanitize($data['username'] ?? '');
$email = sanitize($data['email'] ?? '');
$password = $data['password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';
$secret_key = $data['secret_key'] ?? '';

// Validation
if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    jsonResponse(['success' => false, 'message' => 'Semua field harus diisi']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Format email tidak valid']);
}

if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => 'Password minimal 6 karakter']);
}

if ($password !== $confirm_password) {
    jsonResponse(['success' => false, 'message' => 'Password dan konfirmasi password tidak sama']);
}

if (!validateSecretKey($secret_key)) {
    jsonResponse(['success' => false, 'message' => 'Secret key tidak valid']);
}

try {
    // Check if username exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()['count'] > 0) {
        jsonResponse(['success' => false, 'message' => 'Username sudah digunakan']);
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()['count'] > 0) {
        jsonResponse(['success' => false, 'message' => 'Email sudah digunakan']);
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashed_password]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Registrasi berhasil! Silakan login.'
    ]);
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server'], 500);
}
