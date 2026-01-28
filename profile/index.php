<?php
session_start();
require_once '../config/koneksi.php';

// Cek kalo belum login, redirect ke login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/');
    exit();
}

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];

// Handle form submission untuk update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validasi
    if (empty($nama) || empty($email)) {
        $error = 'Nama dan email harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek email udah dipake user lain apa belum
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email sudah digunakan user lain!';
        } else {
            // Update data user
            $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama, $email, $user_id);
            
            if ($stmt->execute()) {
                // Update session
                $_SESSION['nama'] = $nama;
                $_SESSION['email'] = $email;
                $success = 'Profile berhasil diupdate!';
            } else {
                $error = 'Terjadi kesalahan. Coba lagi nanti!';
            }
        }
        $stmt->close();
    }
}

// Handle form submission untuk update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua field password harus diisi!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password baru tidak cocok!';
    } else {
        // Cek password lama
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Password lama salah!';
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Password berhasil diupdate!';
            } else {
                $error = 'Terjadi kesalahan. Coba lagi nanti!';
            }
        }
        $stmt->close();
    }
}

// Ambil data user terbaru
$stmt = $conn->prepare("SELECT nama, email, kelas, jurusan, angkatan FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Cek apakah ada foto profil
$profile_pic_path = "../uploads/images/profile-picture/pfp-{$user_id}.jpg";
$has_profile_pic = file_exists($profile_pic_path);

// Generate initial dari nama (max 2 huruf)
function getInitials($name) {
    $words = explode(' ', $name);
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        return strtoupper(substr($name, 0, 2));
    }
}
$initials = getInitials($user['nama']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($user['nama']); ?></title>
    <link rel="stylesheet" href="/assets/css/profile.css">
</head>
<body>
    <div class="profile-container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>👤 My Profile</h1>
                <p>Kelola informasi profil kamu</p>
            </div>
            <a href="/" class="btn-back">← Kembali ke Dashboard</a>
        </div>

        <!-- Profile Card -->
        <div class="profile-card">
            <!-- Profile Header dengan Foto -->
            <div class="profile-header">
                <div class="profile-picture-section">
                    <?php if ($has_profile_pic): ?>
                        <img src="/<?php echo $profile_pic_path; ?>?v=<?php echo time(); ?>" alt="Profile Picture" class="profile-picture" id="profileImage">
                    <?php else: ?>
                        <div class="profile-initial" id="profileInitial"><?php echo $initials; ?></div>
                    <?php endif; ?>
                    
                    <div class="upload-btn-wrapper" onclick="document.getElementById('fileInput').click()">
                        <span class="upload-icon">📷</span>
                        <input type="file" id="fileInput" accept="image/jpeg,image/jpg,image/png" onchange="uploadProfilePicture(this)">
                    </div>
                </div>
                
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['nama']); ?></h2>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if (!empty($user['kelas']) && !empty($user['jurusan'])): ?>
                        <p><?php echo htmlspecialchars($user['kelas'] . ' - ' . $user['jurusan']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Form Update Profile -->
            <form method="POST" action="">
                <div class="form-section">
                    <h3>📝 Informasi Dasar</h3>
                    
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input 
                            type="text" 
                            id="nama" 
                            name="nama" 
                            value="<?php echo htmlspecialchars($user['nama']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-section">
                    <h3>🎓 Informasi Akademik</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="kelas">Kelas</label>
                            <input 
                                type="text" 
                                id="kelas" 
                                value="<?php echo htmlspecialchars($user['kelas'] ?: '-'); ?>"
                                disabled
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="angkatan">Angkatan</label>
                            <input 
                                type="text" 
                                id="angkatan" 
                                value="<?php echo htmlspecialchars($user['angkatan'] ?: '-'); ?>"
                                disabled
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="jurusan">Jurusan</label>
                        <input 
                            type="text" 
                            id="jurusan" 
                            value="<?php echo htmlspecialchars($user['jurusan'] ?: '-'); ?>"
                            disabled
                        >
                    </div>

                    <div class="info-box">
                        <p>ℹ️ Informasi akademik tidak dapat diubah sendiri.</p>
                        <p>Hubungi admin jika ada kesalahan data.</p>
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn-submit">
                    💾 Simpan Perubahan
                </button>
            </form>

            <!-- Form Update Password -->
            <form method="POST" action="" style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                <div class="form-section">
                    <h3>🔐 Ganti Password</h3>
                    
                    <div class="form-group">
                        <label for="current_password">Password Lama</label>
                        <div class="password-toggle">
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password" 
                                placeholder="Masukkan password lama"
                            >
                            <button type="button" class="toggle-btn" onclick="togglePasswordField('current_password')">👁️</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <div class="password-toggle">
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
                                placeholder="Minimal 6 karakter"
                            >
                            <button type="button" class="toggle-btn" onclick="togglePasswordField('new_password')">👁️</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <div class="password-toggle">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="Ketik ulang password baru"
                            >
                            <button type="button" class="toggle-btn" onclick="togglePasswordField('confirm_password')">👁️</button>
                        </div>
                    </div>

                    <div class="info-box">
                        <p>⚠️ Setelah ganti password, anda perlu login ulang dengan password baru.</p>
                    </div>
                </div>

                <button type="submit" name="update_password" class="btn-submit">
                    🔒 Update Password
                </button>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script>
        function uploadProfilePicture(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validasi tipe file
                if (!file.type.match('image/jpeg') && !file.type.match('image/jpg') && !file.type.match('image/png')) {
                    alert('Hanya file JPG, JPEG, atau PNG yang diperbolehkan!');
                    return;
                }
                
                // Validasi ukuran file (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file maksimal 5MB!');
                    return;
                }
                
                // Show loading
                document.getElementById('loadingOverlay').classList.add('active');
                
                // Upload via AJAX
                const formData = new FormData();
                formData.append('profile_picture', file);
                
                fetch('/upload-profile-picture.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading
                    document.getElementById('loadingOverlay').classList.remove('active');
                    
                    if (data.success) {
                        // Reload gambar atau ganti ke img element
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    document.getElementById('loadingOverlay').classList.remove('active');
                    alert('Terjadi kesalahan saat upload! Detail: ' + error.message);
                    console.error('Error:', error);
                });
            }
        }

        function togglePasswordField(fieldId) {
            const field = document.getElementById(fieldId);
            const btn = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                btn.textContent = '🙈';
            } else {
                field.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>