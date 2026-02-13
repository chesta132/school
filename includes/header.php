<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$current_user = getCurrentUser();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>ChardyMart POS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/modal.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php if (isLoggedIn()): ?>
        <!-- Desktop Navbar -->
        <nav class="navbar">
            <div class="nav-container">
                <div style="display:flex;align-items:center;gap:12px;">
                    <!-- Hamburger (mobile only) -->
                    <button class="nav-hamburger" id="sidebarToggle" aria-label="Open menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="nav-brand">
                        <i class="fas fa-store"></i>
                        <span>ChardyMart</span>
                    </div>
                </div>
                <div class="nav-menu">
                    <a href="/" class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/products" class="nav-link <?php echo $current_page == 'products' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i>
                        <span>Produk</span>
                    </a>
                    <a href="/cashier" class="nav-link <?php echo $current_page == 'cashier' ? 'active' : ''; ?>">
                        <i class="fas fa-cash-register"></i>
                        <span>Kasir</span>
                    </a>
                    <a href="/transactions" class="nav-link <?php echo $current_page == 'transactions' ? 'active' : ''; ?>">
                        <i class="fas fa-receipt"></i>
                        <span>Transaksi</span>
                    </a>
                    <a href="/users" class="nav-link <?php echo $current_page == 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Pengguna Terdaftar</span>
                    </a>
                </div>
                <div class="nav-user">
                    <a href="/profile" class="nav-link <?php echo $current_page == 'profile' ? 'active' : ''; ?>" style="padding: 6px 12px;">
                        <i class="fas fa-user"></i>
                        <span class="user-name"><?php echo htmlspecialchars($current_user['username']); ?></span>
                    </a>
                    <button onclick="handleLogout()" class="btn-logout" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile Sidebar -->
        <aside class="mobile-sidebar" id="mobileSidebar">
            <div class="mobile-sidebar-header">
                <div class="mobile-sidebar-brand">
                    <i class="fas fa-store"></i>
                    <span>ChardyMart</span>
                </div>
                <button class="mobile-sidebar-close" id="sidebarClose" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mobile-sidebar-user">
                <div class="mobile-sidebar-user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div class="mobile-sidebar-user-name"><?php echo htmlspecialchars($current_user['username']); ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">Kasir</div>
                </div>
            </div>

            <nav class="mobile-sidebar-nav">
                <a href="/" class="mobile-nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/products" class="mobile-nav-link <?php echo $current_page == 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
                <a href="/cashier" class="mobile-nav-link <?php echo $current_page == 'cashier' ? 'active' : ''; ?>">
                    <i class="fas fa-cash-register"></i>
                    <span>Kasir</span>
                </a>
                <a href="/transactions" class="mobile-nav-link <?php echo $current_page == 'transactions' ? 'active' : ''; ?>">
                    <i class="fas fa-receipt"></i>
                    <span>Transaksi</span>
                </a>
                <a href="/profile" class="mobile-nav-link <?php echo $current_page == 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <a href="/users" class="mobile-nav-link <?php echo $current_page == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Pengguna Terdaftar</span>
                </a>
            </nav>

            <div class="mobile-sidebar-footer">
                <button onclick="handleLogout()" class="mobile-logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <script>
            (function() {
                var toggle = document.getElementById('sidebarToggle');
                var sidebar = document.getElementById('mobileSidebar');
                var closeBtn = document.getElementById('sidebarClose');

                document.addEventListener('click', (e) => {
                    if (!toggle.contains(e.target) && !sidebar.contains(e.target) && sidebar.classList.contains('open')) {
                        closeSidebar();
                    }
                })

                function openSidebar() {
                    sidebar.classList.add('open');
                    document.body.style.overflow = 'hidden';
                }

                function closeSidebar() {
                    sidebar.classList.remove('open');
                    document.body.style.overflow = '';
                }

                toggle.addEventListener('click', openSidebar);
                closeBtn.addEventListener('click', closeSidebar);
            })();
        </script>
    <?php endif; ?>

    <main class="main-content">