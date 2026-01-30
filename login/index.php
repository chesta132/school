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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validasi
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        // Cari user di database
        $stmt = $conn->prepare("SELECT id, nama, email, password, kelas, nomor_kelas, jurusan, angkatan FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Login berhasil
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['kelas'] = buildKelas($user['kelas'], $user['jurusan'], $user['nomor_kelas']);
                $_SESSION['jurusan'] = $user['jurusan'];
                $_SESSION['nomor_kelas'] = $user['nomor_kelas'];
                $_SESSION['angkatan'] = $user['angkatan'];
                
                // Redirect ke dashboard
                header('Location: /');
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Email tidak terdaftar!';
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
    <title>Login - Welcome Back</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome Back</h1>
            <p>Login ke akun kamu dan lanjutkan perjalanan</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="nama@email.com" 
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                    autofocus
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-toggle">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Masukkan password"
                        required
                        autocomplete="off"
                    >
                    <button type="button" class="toggle-btn" onclick="togglePassword()">👁️</button>
                </div>
            </div>
            
            <button type="submit" class="btn">Login Sekarang</button>
        </form>
        
        <div class="divider">
            <span>atau</span>
        </div>
        
        <div class="register-link">
            Belum punya akun? <a href="/register">Daftar di sini</a>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const field = document.getElementById('password');
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