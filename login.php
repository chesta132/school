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
    <title>Login - ChardyMart POS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/modal.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-store"></i>
                <h1>ChardyMart POS</h1>
                <p>Silakan login untuk melanjutkan</p>
            </div>
            
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="username">Username atau Email</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="btn-text">Login</span>
                    <span class="btn-loader" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Belum punya akun? <a href="/register">Daftar di sini</a></p>
            </div>
        </div>
    </div>
    
    <script src="/assets/js/modal.js"></script>
    <script src="/assets/js/notification.js"></script>
    <script>
        const loginForm = document.getElementById('loginForm');
        
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = loginForm.querySelector('button[type="submit"]');
            const btnText = btn.querySelector('.btn-text');
            const btnLoader = btn.querySelector('.btn-loader');
            
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-block';
            
            const formData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value
            };
            
            try {
                const response = await fetch('/api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Notification.show({ message: data.message, type: 'success' });
                    setTimeout(() => window.location.href = '/', 1000);
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
