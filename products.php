<?php
$page_title = 'Produk';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/pagination_logic.php';
require_once __DIR__ . '/includes/pagination.php';

requireAuth();

// Get pagination params
$pagination = getPaginationParams(10, 5, 100);

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build query with filters
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Count total
$count_query = "SELECT COUNT(*) as total FROM products p $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_data = $stmt->fetch()['total'];
$total_pages = calculateTotalPages($total_data, $pagination['limit']);

// Get products with pagination
$query = "
    SELECT p.*, c.name as category_name, c.code as category_code 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    $where_clause
    ORDER BY p.name ASC 
    LIMIT ? OFFSET ?
";
$params[] = $pagination['limit'];
$params[] = $pagination['offset'];
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="products-container">
    <div class="page-header">
        <div>
            <h1>Manajemen Produk</h1>
            <p>Kelola produk dan kategori</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <button onclick="showCategoryModal()" class="btn btn-secondary">
                <i class="fas fa-folder"></i> Kelola Kategori
            </button>
            <button onclick="showProductModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Produk
            </button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="GET" action="" class="search-filter-bar">
                <input type="hidden" name="limit" value="<?php echo $pagination['limit']; ?>">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-category">
                    <select name="category" class="form-control" style="width: 200px;">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table" id="productsTable">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Berat</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Stok</th>
                            <th>Terjual</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada produk</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($p['sku']); ?></code></td>
                                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                                    <td>
                                        <span class="badge" style="background: #1a1a1a;">
                                            <?php echo htmlspecialchars($p['category_name']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $p['weight']; ?> g</td>
                                    <td><?php echo formatCurrency($p['price']); ?></td>
                                    <td><?php echo $p['discount']; ?>%</td>
                                    <td>
                                        <span class="badge-stock <?php 
                                            if ($p['stock'] <= 0) {
                                                echo 'stock-out';
                                            } elseif ($p['stock'] < 10) {
                                                echo 'stock-low';
                                            } else {
                                                echo 'stock-normal';
                                            }
                                        ?>">
                                            <?php echo $p['stock']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $p['sold']; ?></td>
                                    <td>
                                        <div class="actions">
                                            <button onclick="editProduct(<?php echo $p['id']; ?>)" class="action-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteProduct(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['name'])); ?>')" class="action-btn" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
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
            if ($category_filter > 0) {
                $hidden_inputs['category'] = $category_filter;
            }
            echo renderPagination($pagination['page'], $total_pages, $hidden_inputs);
            ?>
        </div>
    </div>
</div>

<?php echo renderPaginationScript(); ?>

<?php
$additional_scripts = ['/assets/js/products.js'];
require_once __DIR__ . '/includes/footer.php';
?>
