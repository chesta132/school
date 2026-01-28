<?php
// Konfigurasi Database
// Kalo pake Docker, host nya 'db', kalo lokal pake 'localhost'
define('DB_HOST', getenv('MYSQL_HOST') ?: 'localhost');
define('DB_USER', 'root');
// Pake root123 buat docker, kosong buat local
define('DB_PASS', getenv('MYSQL_HOST') ? 'root123' : '');
define('DB_NAME', 'db_sekolah');

// Bikin koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset ke utf8mb4 biar support emoji dll
$conn->set_charset("utf8mb4");

?>