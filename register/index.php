<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/kelas_config.php';

// Kalo udah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $kelas_data = trim($_POST['kelas'] ?? '');
    $angkatan = trim($_POST['angkatan'] ?? '');
    
    // Parse kelas data (format: tingkat|nomor|jurusan)
    $kelas_parts = explode('|', $kelas_data);
    $tingkat = $kelas_parts[0] ?? '';
    $nomor_kelas = $kelas_parts[1] ?? '';
    $jurusan = $kelas_parts[2] ?? '';
    
    // Validasi
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Nama, email, dan password harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif (empty($kelas_data)) {
        $error = 'Kelas harus dipilih!';
    } else {
        // Cek email udah ada apa belum
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert ke database
            $stmt = $conn->prepare("INSERT INTO users (nama, email, password, kelas, nomor_kelas, jurusan, angkatan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nama, $email, $hashed_password, $tingkat, $nomor_kelas, $jurusan, $angkatan);
            
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan. Coba lagi nanti!';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Create Account</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✨ Create Account</h1>
            <p>Daftar sekarang dan mulai perjalananmu</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input 
                    type="text" 
                    id="nama" 
                    name="nama" 
                    placeholder="Masukkan nama lengkap" 
                    value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="nama@email.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="kelas">Kelas</label>
                <select id="kelas" name="kelas" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php
                    $all_kelas = getAllKelas();
                    foreach ($all_kelas as $k) {
                        $selected = (($_POST['kelas'] ?? '') === $k['value']) ? 'selected' : '';
                        echo "<option value=\"{$k['value']}\" {$selected}>{$k['label']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="angkatan">Angkatan</label>
                <input 
                    type="number" 
                    id="angkatan" 
                    name="angkatan" 
                    placeholder="2024"
                    min="2000"
                    max="2100"
                    value="<?php echo htmlspecialchars($_POST['angkatan'] ?? date('Y')); ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-toggle">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Minimal 6 karakter"
                        required
                        autocomplete="off"
                    >
                    <button type="button" class="toggle-btn" onclick="togglePassword('password')">👁️</button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <div class="password-toggle">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Ketik ulang password"
                        required
                    >
                    <button type="button" class="toggle-btn" onclick="togglePassword('confirm_password')">👁️</button>
                </div>
            </div>
            
            <button type="submit" class="btn">Daftar Sekarang</button>
        </form>
        
        <div class="divider">
            <span>atau</span>
        </div>
        
        <div class="login-link">
            Sudah punya akun? <a href="/login">Login di sini</a>
        </div>
    </div>
    
    <script>
        function togglePassword(fieldId) {
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
        
        // Validasi real-time
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password tidak cocok!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
        });
    </script>
</body>
</html>