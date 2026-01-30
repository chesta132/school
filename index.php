<?php
session_start();
require_once 'config/koneksi.php';
require_once 'config/kelas_config.php';

// Cek kalo belum login, redirect ke login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/');
    exit();
}

// Pagination settings
$limit = 20; // Data per halaman
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
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>📊 Dashboard Siswa</h1>
                <p>Kelola dan lihat data siswa</p>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama']); ?></div>
                    <div class="user-detail">
                        <?php 
                        if (!empty($_SESSION['kelas']) && !empty($_SESSION['jurusan'])) {
                            echo htmlspecialchars($_SESSION['kelas']);
                        } else {
                            echo htmlspecialchars($_SESSION['email']);
                        }
                        ?>
                    </div>
                </div>
                <a href="/profile" class="btn-logout" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">Profile</a>
                <a href="/logout" class="btn-logout">Logout</a>
            </div>
        </div>

        <!-- Content -->
        <div class="content-card">
            <div class="content-header">
                <h2>Daftar Siswa</h2>
                <div class="total-count">
                    Total: <?php echo number_format($total_data); ?> Siswa
                </div>
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
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1;
                            while ($row = $result->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['kelas'] ?: '-'); ?></td>
                                    <td>
                                        <?php 
                                        $jurusan = strtolower($row['jurusan'] ?: '');
                                        $badge_class = 'badge-default';
                                        if ($jurusan != '') {
                                            $badge_class = 'badge-'.$jurusan;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($row['jurusan'] ?: '-'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['angkatan'] ?: '-'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <!-- Previous Button -->
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>">← Prev</a>
                        <?php else: ?>
                            <span class="disabled">← Prev</span>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);

                        // Kalo di awal, tampilin lebih banyak ke kanan
                        if ($page <= 3) {
                            $end_page = min($total_pages, 5);
                        }

                        // Kalo di akhir, tampilin lebih banyak ke kiri
                        if ($page >= $total_pages - 2) {
                            $start_page = max(1, $total_pages - 4);
                        }

                        // First page
                        if ($start_page > 1) {
                            echo '<a href="?page=1">1</a>';
                            if ($start_page > 2) {
                                echo '<span class="disabled">...</span>';
                            }
                        }

                        // Page numbers
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            if ($i == $page) {
                                echo '<span class="active">' . $i . '</span>';
                            } else {
                                echo '<a href="?page=' . $i . '">' . $i . '</a>';
                            }
                        }

                        // Last page
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<span class="disabled">...</span>';
                            }
                            echo '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>';
                        }
                        ?>

                        <!-- Next Button -->
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>">Next →</a>
                        <?php else: ?>
                            <span class="disabled">Next →</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-data">
                    <p>😔 Belum ada data siswa</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>