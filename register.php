<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ChardyMart POS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/modal.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-user-plus"></i>
                <h1>Daftar Akun</h1>
                <p>Buat akun baru untuk menggunakan ChardyMart POS</p>
            </div>
            
            <form id="registerForm" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Minimal 6 karakter</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label for="secret_key">Secret Key</label>
                    <input type="text" id="secret_key" name="secret_key" required>
                    <small>Hubungi administrator untuk mendapatkan secret key</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="btn-text">Daftar</span>
                    <span class="btn-loader" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Sudah punya akun? <a href="/login">Login di sini</a></p>
            </div>
        </div>
    </div>
    
    <script src="/assets/js/modal.js"></script>
    <script src="/assets/js/notification.js"></script>
    <script>
        const registerForm = document.getElementById('registerForm');
        
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = registerForm.querySelector('button[type="submit"]');
            const btnText = btn.querySelector('.btn-text');
            const btnLoader = btn.querySelector('.btn-loader');
            
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-block';
            
            const formData = {
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                confirm_password: document.getElementById('confirm_password').value,
                secret_key: document.getElementById('secret_key').value
            };
            
            try {
                const response = await fetch('/api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Notification.show({ message: data.message, type: 'success' });
                    setTimeout(() => window.location.href = '/login', 1500);
                } else {
                    Notification.show({ message: data.message, type: 'error' });
                    btn.disabled = false;
                    btnText.style.display = 'inline-block';
                    btnLoader.style.display = 'none';
                }
            } catch (error) {
                Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
                btn.disabled = false;
                btnText.style.display = 'inline-block';
                btnLoader.style.display = 'none';
            }
        });
    </script>
</body>
</html>
