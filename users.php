<?php
$page_title = 'User Management';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/pagination_logic.php';
require_once __DIR__ . '/includes/pagination.php';

requireAuth();

// Get pagination params
$pagination = getPaginationParams(10, 5, 100);

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with search
$where_clause = "";
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE username LIKE ? OR email LIKE ? OR id LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Count total users
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_data = $stmt->fetch()['total'];
$total_pages = calculateTotalPages($total_data, $pagination['limit']);

// Get users with pagination
$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $pagination['limit'];
$params[] = $pagination['offset'];
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="users-container">
    <div class="page-header">
        <div>
            <h1>Manajemen User</h1>
            <p>Lihat kasir yang terdaftar</p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="GET" action="" class="search-filter-bar">
                <input type="hidden" name="limit" value="<?php echo $pagination['limit']; ?>">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Cari user..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
            
            <div class="table-responsive">
                <table class="table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Tanggal Daftar</th>
                            <th>Update Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada user</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><code><?php echo $user['id']; ?></code></td>
                                    <td style="white-space: nowrap;"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td style="white-space: nowrap;"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td style="white-space: nowrap;"><?php echo formatDateTime($user['created_at']); ?></td>
                                    <td style="white-space: nowrap;"><?php echo formatDateTime($user['updated_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
            // Render pagination
            $hidden_inputs = ['limit' => $pagination['limit']];
            if (!empty($search)) {
                $hidden_inputs['search'] = $search;
            }
            echo renderPagination($pagination['page'], $total_pages, $hidden_inputs);
            ?>
        </div>
    </div>
</div>

<?php echo renderPaginationScript(); ?>

<script>
function formatDateTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleString('id-ID', { 
        day: '2-digit', 
        month: 'short',
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit' 
    });
}
</script>

<?php
$additional_scripts = [];
require_once __DIR__ . '/includes/footer.php';
?>
