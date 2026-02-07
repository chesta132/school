<?php
session_start();
require_once '../config/koneksi.php';
require_once '../lib.php';

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

// Handle delete account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $confirm_password = $_POST['confirm_password_delete'] ?? '';

    // Validasi password
    if (empty($confirm_password)) {
        $error = 'Password harus diisi untuk menghapus akun!';
    } else {
        // Cek password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!password_verify($confirm_password, $user['password'])) {
            $error = 'Password salah! Akun tidak dapat dihapus.';
        } else {
            // Hapus foto profil kalo ada
            $profile_pic = "../uploads/images/profile-picture/pfp-{$user_id}.jpg";
            if (file_exists($profile_pic)) {
                unlink($profile_pic);
            }

            // Hapus user dari database
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                // Destroy session
                session_destroy();
                // Redirect ke halaman login
                header('Location: /login');
                exit();
            } else {
                $error = 'Terjadi kesalahan saat menghapus akun. Coba lagi nanti!';
            }
        }
        $stmt->close();
    }
}
// Ambil data user terbaru
$stmt = $conn->prepare("SELECT nama, email, kelas, jurusan, angkatan, nomor_kelas FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Cek apakah ada foto profil
$profile_pic_path = "../uploads/images/profile-picture/pfp-{$user_id}.jpg";
$has_profile_pic = file_exists($profile_pic_path);

// Generate initial dari nama (max 2 huruf)
$initials = getInitials($user['nama']);
$kelas_full = buildKelas($user['kelas'], $user['jurusan'], $user['nomor_kelas']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($user['nama']); ?></title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
</head>

<body>
    <div class="profile-container">

        <!-- ── Navbar ── -->
        <nav class="navbar">
            <a href="/" class="nav-brand">
                <div class="nav-brand-icon">
                    <svg viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                    </svg>
                </div>
                <span class="nav-brand-text">Dashboard</span>
            </a>

            <div class="nav-right">
                <div class="nav-user">
                    <div class="nav-user-info">
                        <span class="nav-user-name"><?php echo htmlspecialchars($user['nama']); ?></span>
                        <span class="nav-user-detail">
                            <?php echo !empty($kelas_full) ? htmlspecialchars($kelas_full) : htmlspecialchars($user['email']); ?>
                        </span>
                    </div>
                    <div class="nav-avatar">
                        <?php if ($has_profile_pic): ?>
                            <img src="/<?php echo $profile_pic_path; ?>?v=<?php echo time(); ?>" alt="">
                        <?php else: ?>
                            <?php echo $initials; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="/" class="nav-pill">
                    <svg viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="/logout" class="nav-pill nav-pill--danger">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </nav>

        <!-- ── Main ── -->
        <main class="main-content">

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-left">
                    <h1>Profile</h1>
                    <p>Kelola informasi dan keamanan akun</p>
                </div>
                <a href="/" class="btn-back">
                    <svg viewBox="0 0 24 24">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    Dashboard
                </a>
            </div>

            <!-- Profile Hero -->
            <div class="profile-hero">
                <div class="avatar-wrap" onclick="document.getElementById('fileInput').click()">
                    <input type="file" id="fileInput" accept="image/jpeg,image/jpg,image/png" onchange="uploadProfilePicture(this)">
                    <div class="avatar-circle">
                        <?php if ($has_profile_pic): ?>
                            <img src="/<?php echo $profile_pic_path; ?>?v=<?php echo time(); ?>" alt="" id="profileImage">
                        <?php else: ?>
                            <span class="avatar-initials" id="profileInitial"><?php echo $initials; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="avatar-overlay">
                        <svg viewBox="0 0 24 24">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                            <circle cx="12" cy="13" r="4" />
                        </svg>
                    </div>
                </div>

                <div class="hero-info">
                    <h2><?php echo htmlspecialchars($user['nama']); ?></h2>
                    <p class="hero-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="hero-meta">
                        <?php if (!empty($kelas_full)): ?>
                            <span class="hero-tag">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                    <line x1="3" y1="9" x2="21" y2="9" />
                                    <line x1="9" y1="21" x2="9" y2="9" />
                                </svg>
                                <?php echo htmlspecialchars($kelas_full); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($user['jurusan'])): ?>
                            <span class="hero-tag">
                                <svg viewBox="0 0 24 24">
                                    <path d="M2 3l20 9-20 9V3z" />
                                    <path d="M22 12v8" />
                                </svg>
                                <?php echo htmlspecialchars(jurusanAliasToLong($user['jurusan'])); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($user['angkatan'])): ?>
                            <span class="hero-tag">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                <?php echo htmlspecialchars($user['angkatan']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div id="alert-container">
                <?php if ($error): ?>
                    <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
            </div>

            <!-- Section: Informasi Dasar -->
            <form method="POST" action="">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-header-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="8" r="4" />
                            </svg>
                        </div>
                        <div>
                            <h3>Informasi Dasar</h3>
                            <p>Nama dan email akun kamu</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <button type="submit" name="update_profile" class="btn-submit">Simpan Perubahan</button>
                </div>
            </form>

            <!-- Section: Informasi Akademik -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-header-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M2 3l20 9-20 9V3z" />
                            <path d="M22 12v8" />
                        </svg>
                    </div>
                    <div>
                        <h3>Informasi Akademik</h3>
                        <p>Data kelas dan jurusan</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Kelas</label>
                        <input type="text" value="<?php echo htmlspecialchars($kelas_full ?: '—'); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Angkatan</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['angkatan'] ?: '—'); ?>" disabled>
                    </div>
                </div>

                <div class="form-group">
                    <label>Jurusan</label>
                    <input type="text" value="<?php echo htmlspecialchars(jurusanAliasToLong($user['jurusan'] ?: '—')); ?>" disabled>
                </div>

                <div class="info-box">
                    <div class="info-box-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="16" x2="12" y2="12" />
                            <line x1="12" y1="8" x2="12.01" y2="8" />
                        </svg>
                    </div>
                    <p>Informasi akademik tidak dapat diubah sendiri. Hubungi admin jika ada kesalahan data.</p>
                </div>
            </div>

            <!-- Section: Ganti Password -->
            <form method="POST" action="">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-header-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </div>
                        <div>
                            <h3>Keamanan</h3>
                            <p>Ubah password akun</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="current_password">Password Lama</label>
                        <div class="password-toggle">
                            <input type="password" id="current_password" name="current_password" placeholder="Masukkan password lama">
                            <button type="button" class="toggle-btn" onclick="togglePasswordField('current_password')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                </svg></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <div class="password-toggle">
                            <input type="password" id="new_password" name="new_password" placeholder="Minimal 6 karakter">
                            <button type="button" class="toggle-btn" onclick="togglePasswordField('new_password')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                </svg></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <div class="password-toggle">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Ketik ulang password baru">
                            <button type="button" class="toggle-btn" onclick="togglePasswordField('confirm_password')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                </svg></button>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                <line x1="12" y1="9" x2="12" y2="13" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                        </div>
                        <p>Setelah ganti password, kamu perlu login ulang dengan password baru.</p>
                    </div>

                    <button type="submit" name="update_password" class="btn-submit">Update Password</button>
                </div>
            </form>

            <!-- Section: Delete Account -->
            <div class="section-card section-card--danger">
                <div class="section-header">
                    <div class="section-header-icon">
                        <svg viewBox="0 0 24 24">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            <line x1="10" y1="11" x2="10" y2="17" />
                            <line x1="14" y1="11" x2="14" y2="17" />
                        </svg>
                    </div>
                    <div>
                        <h3>Hapus Akun</h3>
                        <p>Hapus akun secara permanen</p>
                    </div>
                </div>

                <div class="info-box info-box--danger">
                    <div class="info-box-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    </div>
                    <p>Setelah akun dihapus, tidak ada cara untuk mengembalikannya. Semua data kamu akan hilang permanen dari sistem.</p>
                </div>

                <button type="button" class="btn-submit btn-submit--danger" onclick="openDeleteModal()">Hapus Akun Saya</button>
            </div>

        </main>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Konfirmasi Hapus Akun</h3>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">
                    <svg viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div class="modal-warning">
                    <svg viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    <div>
                        <p class="modal-warning-title">Peringatan: Tindakan Permanen</p>
                        <p class="modal-warning-text">Akun kamu akan dihapus selamanya dan tidak bisa dikembalikan. Semua data akan hilang.</p>
                    </div>
                </div>

                <form method="POST" action="" id="deleteAccountForm">
                    <div class="form-group">
                        <label for="modal_password">Masukkan password kamu untuk konfirmasi</label>
                        <div class="password-toggle">
                            <input type="password" id="modal_password" name="confirm_password_delete" placeholder="Password" required autofocus>
                            <button type="button" class="toggle-btn" onclick="togglePasswordField('modal_password')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-modal btn-modal--secondary" onclick="closeDeleteModal()">Batal</button>
                        <button type="submit" name="delete_account" class="btn-modal btn-modal--danger">Hapus Akun Saya</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../lib.js"></script>
    <script>
        function uploadProfilePicture(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                if (!file.type.match('image/jpeg') && !file.type.match('image/jpg') && !file.type.match('image/png')) {
                    showMessage('Hanya file JPG, JPEG, atau PNG yang diperbolehkan!', false);
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    showMessage('Ukuran file maksimal 5MB!', false);
                    return;
                }

                document.getElementById('loadingOverlay').classList.add('active');

                const formData = new FormData();
                formData.append('profile_picture', file);

                fetch('/upload-profile-picture.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('loadingOverlay').classList.remove('active');
                        if (data.success) {
                            showMessage(data.message);
                            // Virtual refresh, image saja
                            const timestamp = Date.now();
                            const imagePath = "/<?php echo $profile_pic_path; ?>";

                            // Update bagian avatar (lingkaran besar)
                            document.querySelector('.avatar-circle').innerHTML = `<img src="${imagePath}?v=${timestamp}" alt="" id="profileImage">`;

                            // Update juga bagian avatar kecil di Navbar agar sinkron
                            const navAvatar = document.querySelector('.nav-avatar');
                            if (navAvatar) {
                                navAvatar.innerHTML = `<img src="${imagePath}?v=${timestamp}" alt="">`;
                            }
                        } else {
                            showMessage('Error: ' + data.message, false);
                        }
                    })
                    .catch(error => {
                        document.getElementById('loadingOverlay').classList.remove('active');
                        showMessage('Terjadi kesalahan saat upload! Detail: ' + error.message, false);
                        console.error('Error:', error);
                    });
            }
        }

        function togglePasswordField(fieldId) {
            const field = document.getElementById(fieldId);
            const btn = field.nextElementSibling;

            if (field.type === 'password') {
                field.type = 'text';
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16"> <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/> <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/> <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/> </svg>';
            } else {
                field.type = 'password';
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"> <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/> <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/> </svg>';
            }
        }

        function openDeleteModal() {
            document.getElementById('deleteModal').classList.add('active');
            setTimeout(() => {
                document.getElementById('modal_password').focus();
            }, 100);
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            document.getElementById('deleteAccountForm').reset();
        }

        // Close modal on outside click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>

</html>