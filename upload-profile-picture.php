<?php
// Suppress all errors kecuali yg kita handle sendiri
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Set header JSON dulu sebelum apapun
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Cek apakah ada file yang diupload
if (!isset($_FILES['profile_picture'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['profile_picture'];

// Validasi error upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File terlalu besar (php.ini limit)',
        UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (form limit)',
        UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
        UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
    ];
    $message = isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'Unknown upload error';
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Validasi tipe file dari ekstensi
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png'];

if (!in_array($file_extension, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Hanya file JPG, JPEG, dan PNG yang diperbolehkan!']);
    exit();
}

// Validasi ukuran file (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 5MB!']);
    exit();
}

// Buat direktori kalo belum ada
$upload_dir = 'uploads/images/profile-picture/';
if (!is_dir($upload_dir)) {
    if (!@mkdir($upload_dir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat folder upload!']);
        exit();
    }
}

// Cek apakah folder writable
if (!is_writable($upload_dir)) {
    echo json_encode(['success' => false, 'message' => 'Folder upload tidak writable! Jalankan: chmod -R 777 uploads/']);
    exit();
}

// Nama file tujuan
$destination = $upload_dir . 'pfp-' . $user_id . '.jpg';

// Hapus foto lama kalo ada
if (file_exists($destination)) {
    @unlink($destination);
}

// Cek apakah GD library ada
if (!function_exists('imagecreatefromjpeg')) {
    echo json_encode(['success' => false, 'message' => 'GD library tidak terinstall! Install dengan: apt-get install php-gd']);
    exit();
}

// Proses gambar dan convert ke JPG
$image = null;

// Load image sesuai tipe
if ($file_extension === 'jpg' || $file_extension === 'jpeg') {
    $image = @imagecreatefromjpeg($file['tmp_name']);
} elseif ($file_extension === 'png') {
    $image = @imagecreatefrompng($file['tmp_name']);
    if ($image) {
        // Convert PNG transparency ke white background
        $width = imagesx($image);
        $height = imagesy($image);
        $new_image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($new_image, 255, 255, 255);
        imagefill($new_image, 0, 0, $white);
        imagecopy($new_image, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);
        $image = $new_image;
    }
}

if (!$image) {
    echo json_encode(['success' => false, 'message' => 'Gagal memproses gambar! File mungkin corrupt atau format tidak didukung.']);
    exit();
}

// Resize image ke 500x500 untuk optimasi
$width = imagesx($image);
$height = imagesy($image);
$size = min($width, $height);

// Crop ke square dari center
$x = ($width - $size) / 2;
$y = ($height - $size) / 2;

$square = imagecreatetruecolor(500, 500);
imagecopyresampled($square, $image, 0, 0, $x, $y, 500, 500, $size, $size);

// Save sebagai JPG
if (@imagejpeg($square, $destination, 90)) {
    imagedestroy($image);
    imagedestroy($square);
    echo json_encode(['success' => true, 'message' => 'Foto profil berhasil diupdate!', 'path' => $destination]);
} else {
    imagedestroy($image);
    imagedestroy($square);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan gambar! Cek permission folder: chmod -R 777 uploads/']);
}
exit();
?>