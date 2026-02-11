<?php
$page_title = 'Profile';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Profile Page Styles */
.profile-hero {
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 32px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 28px;
}

.profile-avatar {
    width: 72px;
    height: 72px;
    background: linear-gradient(135deg, #0070f3 0%, #7928ca 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
    flex-shrink: 0;
    border: 2px solid var(--border-color);
}

.profile-hero-info h2 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.profile-hero-info p {
    color: var(--text-secondary);
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.profile-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(0, 112, 243, 0.12);
    border: 1px solid rgba(0, 112, 243, 0.3);
    color: #0070f3;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 8px;
}

/* Tab Navigation */
.profile-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 28px;
}

.profile-tab-btn {
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--text-secondary);
    font-size: 14px;
    font-weight: 500;
    padding: 10px 20px;
    cursor: pointer;
    margin-bottom: -1px;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    font-family: inherit;
}

.profile-tab-btn:hover {
    color: var(--text-primary);
    background: var(--bg-hover);
}

.profile-tab-btn.active {
    color: var(--text-primary);
    border-bottom-color: #0070f3;
    background: var(--bg-tertiary);
}

/* Tab Panels */
.profile-tab-panel {
    display: none;
    animation: fadeInUp 200ms ease;
}

.profile-tab-panel.active {
    display: block;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Info Grid */
.profile-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

@media (max-width: 640px) {
    .profile-info-grid   { grid-template-columns: 1fr; }
    .profile-hero        { flex-direction: column; text-align: center; }
    .profile-hero-info p { justify-content: center; flex-wrap: wrap; }
    .profile-tabs        { overflow-x: auto; justify-content: center; }
}

.info-item {
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 16px 20px;
}

.info-item-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-muted);
    margin-bottom: 6px;
}

.info-item-value {
    font-size: 15px;
    font-weight: 500;
    color: var(--text-primary);
}

/* Security warning note */
.security-note {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: rgba(245, 166, 35, 0.06);
    border: 1px solid rgba(245, 166, 35, 0.2);
    border-radius: var(--radius-md);
    padding: 14px 16px;
    margin-bottom: 24px;
    font-size: 13px;
    color: var(--text-secondary);
}

.security-note i {
    color: var(--warning);
    margin-top: 2px;
    flex-shrink: 0;
}

.section-divider {
    border: none;
    border-top: 1px solid var(--border-color);
    margin: 20px 0;
}

/* Password toggle button */
.pw-toggle-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 4px 8px;
    line-height: 1;
}
.pw-toggle-btn:hover { color: var(--text-primary); }
</style>

<div class="dashboard-container">
    <div class="page-header">
        <div>
            <h1>Profile</h1>
            <p>Kelola informasi akun Anda</p>
        </div>
    </div>

    <!-- Hero Card -->
    <div class="profile-hero">
        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="profile-hero-info">
            <h2 id="heroUsername">—</h2>
            <p>
                <i class="fas fa-envelope" style="font-size:12px;"></i>
                <span id="heroEmail">—</span>
                <span class="profile-hero-badge"><i class="fas fa-id-badge"></i> Kasir</span>
            </p>
            <p style="margin-top:8px;">
                <i class="fas fa-calendar-alt" style="font-size:12px;"></i>
                <span>Bergabung: <span id="heroJoined">—</span></span>
            </p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="profile-tabs">
        <button class="profile-tab-btn active" onclick="switchTab('info')" id="tab-info">
            <i class="fas fa-info-circle"></i> Informasi
        </button>
        <button class="profile-tab-btn" onclick="switchTab('edit')" id="tab-edit">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
        <button class="profile-tab-btn" onclick="switchTab('security')" id="tab-security">
            <i class="fas fa-lock"></i> Keamanan
        </button>
    </div>

    <!-- Panel: Informasi (read-only) -->
    <div class="profile-tab-panel active" id="panel-info">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-id-card" style="margin-right:8px;color:var(--accent-blue);"></i>Detail Akun</h3>
            </div>
            <div class="card-body">
                <div class="profile-info-grid">
                    <div class="info-item">
                        <div class="info-item-label"><i class="fas fa-user" style="margin-right:5px;"></i>Username</div>
                        <div class="info-item-value" id="infoUsername">—</div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label"><i class="fas fa-envelope" style="margin-right:5px;"></i>Email</div>
                        <div class="info-item-value" id="infoEmail">—</div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label"><i class="fas fa-calendar" style="margin-right:5px;"></i>Terdaftar Sejak</div>
                        <div class="info-item-value" id="infoCreated">—</div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label"><i class="fas fa-sync-alt" style="margin-right:5px;"></i>Terakhir Diperbarui</div>
                        <div class="info-item-value" id="infoUpdated">—</div>
                    </div>
                </div>
                <div style="margin-top:20px;">
                    <button class="btn btn-primary" onclick="switchTab('edit')">
                        <i class="fas fa-edit" style="margin-right:6px;"></i>Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel: Edit Profile -->
    <div class="profile-tab-panel" id="panel-edit">
        <div class="card" style="max-width:520px;">
            <div class="card-header">
                <h3><i class="fas fa-edit" style="margin-right:8px;color:var(--accent-blue);"></i>Edit Informasi Profile</h3>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required autocomplete="email">
                    </div>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text"><i class="fas fa-save" style="margin-right:6px;"></i>Simpan Perubahan</span>
                            <span class="btn-loader" style="display:none;"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="switchTab('info')">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel: Keamanan -->
    <div class="profile-tab-panel" id="panel-security">
        <div class="card" style="max-width:520px;">
            <div class="card-header">
                <h3><i class="fas fa-lock" style="margin-right:8px;color:var(--accent-blue);"></i>Ubah Password</h3>
            </div>
            <div class="card-body">
                <div class="security-note">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Pastikan password baru Anda cukup kuat dan tidak mudah ditebak. Minimal 6 karakter.</span>
                </div>
                <form id="changePasswordForm">
                    <div class="form-group">
                        <label for="old_password">Password Saat Ini</label>
                        <div style="position:relative;">
                            <input type="password" id="old_password" name="old_password" class="form-control" required autocomplete="current-password">
                            <button type="button" onclick="togglePassword('old_password')" class="pw-toggle-btn">
                                <i class="fas fa-eye" id="old_password_icon"></i>
                            </button>
                        </div>
                    </div>

                    <hr class="section-divider">

                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <div style="position:relative;">
                            <input type="password" id="new_password" name="new_password" class="form-control" required autocomplete="new-password">
                            <button type="button" onclick="togglePassword('new_password')" class="pw-toggle-btn">
                                <i class="fas fa-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        <small>Minimal 6 karakter</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_new_password">Konfirmasi Password Baru</label>
                        <div style="position:relative;">
                            <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" required autocomplete="new-password">
                            <button type="button" onclick="togglePassword('confirm_new_password')" class="pw-toggle-btn">
                                <i class="fas fa-eye" id="confirm_new_password_icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top:8px;">
                        <span class="btn-text"><i class="fas fa-key" style="margin-right:6px;"></i>Ubah Password</span>
                        <span class="btn-loader" style="display:none;"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/modal.js"></script>
<script src="/assets/js/notification.js"></script>
<script>
    let profileData = null;

    /* ---- Tab switching ---- */
    function switchTab(name) {
        document.querySelectorAll('.profile-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.profile-tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        document.getElementById('panel-' + name).classList.add('active');
    }

    /* ---- Load profile ---- */
    async function loadProfile() {
        try {
            const response = await fetch('/api/get-profile.php');
            const data = await response.json();
            if (data.success) {
                profileData = data.user;
                populateProfile(profileData);
            }
        } catch (error) {
            console.error('Error loading profile:', error);
        }
    }

    function formatDateTime(datetime) {
        if (!datetime) return '—';
        const date = new Date(datetime);
        return date.toLocaleString('id-ID', {
            day: '2-digit', month: 'long', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function populateProfile(user) {
        // Hero section
        document.getElementById('heroUsername').textContent = user.username;
        document.getElementById('heroEmail').textContent    = user.email;
        document.getElementById('heroJoined').textContent   = formatDateTime(user.created_at);

        // Info panel
        document.getElementById('infoUsername').textContent = user.username;
        document.getElementById('infoEmail').textContent    = user.email;
        document.getElementById('infoCreated').textContent  = formatDateTime(user.created_at);
        document.getElementById('infoUpdated').textContent  = formatDateTime(user.updated_at);

        // Pre-fill edit form
        document.getElementById('username').value = user.username;
        document.getElementById('email').value    = user.email;
    }

    /* ---- Toggle password visibility ---- */
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon  = document.getElementById(fieldId + '_icon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    /* ---- Update profile ---- */
    document.getElementById('profileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn       = e.target.querySelector('button[type="submit"]');
        const btnText   = btn.querySelector('.btn-text');
        const btnLoader = btn.querySelector('.btn-loader');

        btn.disabled = true;
        btnText.style.display  = 'none';
        btnLoader.style.display = 'inline-block';

        const formData = {
            username: document.getElementById('username').value,
            email:    document.getElementById('email').value
        };

        try {
            const response = await fetch('/api/update-profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await response.json();
            if (data.success) {
                Notification.show({ message: data.message, type: 'success' });
                setTimeout(() => { window.location.reload(); }, 1200);
            } else {
                Notification.show({ message: data.message, type: 'error' });
                btn.disabled = false;
                btnText.style.display  = 'inline-block';
                btnLoader.style.display = 'none';
            }
        } catch (error) {
            Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
            btn.disabled = false;
            btnText.style.display  = 'inline-block';
            btnLoader.style.display = 'none';
        }
    });

    /* ---- Change password ---- */
    document.getElementById('changePasswordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn       = e.target.querySelector('button[type="submit"]');
        const btnText   = btn.querySelector('.btn-text');
        const btnLoader = btn.querySelector('.btn-loader');

        const newPassword     = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_new_password').value;

        if (newPassword !== confirmPassword) {
            Notification.show({ message: 'Password baru dan konfirmasi tidak sama', type: 'error' });
            return;
        }

        btn.disabled = true;
        btnText.style.display  = 'none';
        btnLoader.style.display = 'inline-block';

        const formData = {
            old_password:         document.getElementById('old_password').value,
            new_password:         newPassword,
            confirm_new_password: confirmPassword
        };

        try {
            const response = await fetch('/api/change-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await response.json();
            if (data.success) {
                Notification.show({ message: data.message, type: 'success' });
                e.target.reset();
            } else {
                Notification.show({ message: data.message, type: 'error' });
            }
        } catch (error) {
            Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
        } finally {
            btn.disabled = false;
            btnText.style.display  = 'inline-block';
            btnLoader.style.display = 'none';
        }
    });

    loadProfile();
</script>

<?php
$additional_scripts = [];
require_once __DIR__ . '/includes/footer.php';
?>
