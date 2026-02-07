<?php
session_start();
require_once 'config/koneksi.php';
require_once 'lib.php';

// Cek kalo belum login, redirect ke login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/');
    exit();
}

// Pagination settings
$min_limit = 5;
$max_limit = 500;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$limit = max($min_limit, min($max_limit, $limit));
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page < 1 ? 1 : $page;
$offset = ($page - 1) * $limit;

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM users";
$count_result = $conn->query($count_query);
$total_data = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data siswa dengan pagination
$query = "SELECT id, nama, email, kelas, jurusan, angkatan, created_at 
          FROM users 
          ORDER BY created_at DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Data Siswa</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>

<body>
    <div class="dashboard-container">

        <!-- ── Navbar ── -->
        <nav class="navbar">
            <div class="nav-brand">
                <div class="nav-brand-icon">
                    <svg viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                    </svg>
                </div>
                <span class="nav-brand-text">Dashboard</span>
            </div>

            <div class="nav-right">
                <div class="nav-user">
                    <div class="nav-user-info">
                        <span class="nav-user-name"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                        <span class="nav-user-detail">
                            <?php
                            if (!empty($_SESSION['kelas']) && !empty($_SESSION['jurusan'])) {
                                echo htmlspecialchars($_SESSION['kelas']);
                            } else {
                                echo htmlspecialchars($_SESSION['email']);
                            }
                            ?>
                        </span>
                    </div>
                    <div class="nav-avatar"><?php echo getInitials($_SESSION['nama']); ?></div>
                </div>

                <a href="/profile" class="nav-pill">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="8" r="4" />
                    </svg>
                    <span>Profile</span>
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
                    <h1>Daftar Siswa</h1>
                    <p>Kelola dan pantau data seluruh siswa</p>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Siswa</span>
                        <div class="stat-card-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                    </div>
                    <span class="stat-card-value"><?php echo number_format($total_data); ?></span>
                    <span class="stat-card-sub">siswa terdaftar</span>
                </div>

                <div class="stat-card">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Halaman</span>
                        <div class="stat-card-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                <line x1="3" y1="9" x2="21" y2="9" />
                                <line x1="9" y1="21" x2="9" y2="9" />
                            </svg>
                        </div>
                    </div>
                    <span class="stat-card-value"><?php echo $page; ?><span style="font-size:var(--text-xl);color:var(--clr-text-tertiary);font-weight:var(--weight-medium);letter-spacing:0"> / <?php echo $total_pages ?: 1; ?></span></span>
                    <span class="stat-card-sub"><?php echo $limit; ?> data per halaman</span>
                </div>

                <form method="GET" action="" class="stat-card stat-card-editable" id="limitForm">
                    <input type="hidden" name="page" value="1">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Ditampilkan</span>
                        <div class="stat-card-icon">
                            <svg viewBox="0 0 24 24">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-card-value-wrapper">
                        <input type="number" name="limit" value="<?php echo $limit; ?>" min="<?php echo $min_limit; ?>" max="<?php echo $max_limit; ?>" class="stat-value-input" data-original="<?php echo $limit; ?>" onchange="handleLimitChange()">
                    </div>
                    <span class="stat-card-sub">dari <?php echo number_format($total_data); ?> siswa</span>
                    <button type="submit" class="stat-apply-btn" id="limitApplyBtn" disabled>
                        <svg viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="content-card">
                <div class="content-header">
                    <h2>Semua Siswa</h2>
                    <span class="content-meta"><?php echo number_format($total_data); ?> total</span>
                </div>

                <?php if ($result->num_rows > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Angkatan</th>
                                    <th>Daftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = $offset + 1;
                                while ($row = $result->fetch_assoc()):
                                    $jurusan      = strtolower($row['jurusan'] ?: '');
                                    $badge_class  = $jurusan !== '' ? 'badge-' . $jurusan : 'badge-default';
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td class="td-ellipsis td-name"><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td class="td-ellipsis td-email"><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="td-ellipsis"><?php echo htmlspecialchars($row['kelas'] ?: '—'); ?></td>
                                        <td class="td-ellipsis">
                                            <span class="badge <?php echo htmlspecialchars($badge_class); ?>">
                                                <?php echo htmlspecialchars($row['jurusan'] ?: '—'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['angkatan'] ?: '—'); ?></td>
                                        <td class="td-date"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <form method="GET" action="" id="paginationForm" class="pagination-form">
                            <div class="pagination">
                                <!-- Prev -->
                                <?php if ($page > 1): ?>
                                    <button type="button" onclick="changePage(<?php echo $page - 1; ?>)" class="pag-prev">← Prev</button>
                                <?php else: ?>
                                    <span class="disabled pag-prev">← Prev</span>
                                <?php endif; ?>

                                <!-- Pages -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page   = min($total_pages, $page + 2);

                                if ($page <= 3) {
                                    $end_page = min($total_pages, 5);
                                }
                                if ($page >= $total_pages - 2) {
                                    $start_page = max(1, $total_pages - 4);
                                }

                                if ($start_page > 1) {
                                    echo '<button type="button" onclick="changePage(1)" class="pag-num">1</button>';
                                    if ($start_page > 2) echo '<span class="disabled">…</span>';
                                }

                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    if ($i === $page) {
                                        echo '<div class="pag-input-wrapper active">';
                                        echo '<input type="number" name="page" value="' . $i . '" min="1" max="' . $total_pages . '" class="pag-input" data-original="' . $i . '" oninput="handlePageChange()">';
                                        echo '</div>';
                                    } else {
                                        echo '<button type="button" onclick="changePage(' . $i . ')" class="pag-num">' . $i . '</button>';
                                    }
                                }

                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) echo '<span class="disabled">…</span>';
                                    echo '<button type="button" onclick="changePage(' . $total_pages . ')" class="pag-num">' . $total_pages . '</button>';
                                }
                                ?>

                                <!-- Next -->
                                <?php if ($page < $total_pages): ?>
                                    <button type="button" onclick="changePage(<?php echo $page + 1; ?>)" class="pag-next">Next →</button>
                                <?php else: ?>
                                    <span class="disabled pag-next">Next →</span>
                                <?php endif; ?>
                            </div>

                            <button type="submit" id="applyBtn" class="pag-apply-btn" disabled>
                                <svg viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Apply
                            </button>
                        </form>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-data">
                        <div class="no-data-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                        </div>
                        <h3>Belum ada siswa</h3>
                        <p>Data siswa akan muncul di sini setelah ada yang daftar</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

    </div>

    <script>
        function changePage(pageNum) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', pageNum);
            window.location.href = '?' + urlParams.toString();
        }

        function handlePageChange() {
            const input = document.querySelector('.pag-input');
            const applyBtn = document.getElementById('applyBtn');
            const originalValue = parseInt(input.dataset.original);
            const currentValue = parseInt(input.value);

            if (currentValue !== originalValue && currentValue >= 1) {
                applyBtn.disabled = false;
            } else {
                applyBtn.disabled = true;
            }
        }

        function handleLimitChange() {
            const input = document.querySelector('.stat-value-input');
            const applyBtn = document.getElementById('limitApplyBtn');
            const originalValue = parseInt(input.dataset.original);
            const currentValue = parseInt(input.value);

            if (currentValue !== originalValue && currentValue >= <?php echo $min_limit ?> && currentValue <= <?php echo $max_limit ?>) {
                applyBtn.disabled = false;
            } else {
                applyBtn.disabled = true;
            }
        }

        // Show apply button on card hover if there's a change
        const card = document.querySelector('.content-card');
        const form = document.getElementById('paginationForm');
        const applyBtn = document.getElementById('applyBtn');

        if (card && form) {
            card.addEventListener('mouseenter', () => {
                form.classList.add('card-hover');
            });

            card.addEventListener('mouseleave', () => {
                form.classList.remove('card-hover');
            });
        }

        // Handle form submission
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const input = document.querySelector('.pag-input');
                if (!input.value || input.value < 1) {
                    return;
                }
                changePage(input.value);
            });
        }

        // Stat card hover effect & input listener
        const statCard = document.getElementById('limitForm');
        const limitInput = document.querySelector('.stat-value-input');

        if (statCard) {
            statCard.addEventListener('mouseenter', () => {
                statCard.classList.add('stat-hover');
            });

            statCard.addEventListener('mouseleave', () => {
                statCard.classList.remove('stat-hover');
            });
        }

        // Add input event listener for real-time checking
        if (limitInput) {
            limitInput.addEventListener('input', handleLimitChange);
        }
    </script>

</body>

</html>
<?php
$stmt->close();
$conn->close();
?>