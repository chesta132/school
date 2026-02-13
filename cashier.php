<?php
$page_title = 'Kasir';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/pagination_logic.php';
require_once __DIR__ . '/includes/pagination.php';

requireAuth();

// Get pagination params for product list
$pagination = getPaginationParams(10, 5, 50);

// Search filter for product list
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where_clause = "";
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE p.name LIKE ? OR p.sku LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Count total products
$count_query = "SELECT COUNT(*) as total FROM products p $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_data = $stmt->fetch()['total'];
$total_pages = calculateTotalPages($total_data, $pagination['limit']);

// Get products with pagination
$query = "
    SELECT p.*, c.name as category_name 
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

require_once __DIR__ . '/includes/header.php';
?>

<div class="cashier-container" style="display: grid; grid-template-columns: 1fr 380px; gap: 24px;">

    <!-- Left: Product Search & Cart -->
    <div style="display: flex; flex-direction: column; gap: 16px; min-height: 0;">
        <div class="page-header" style="margin-bottom: 0;">
            <div>
                <h1>Kasir</h1>
                <p>Ketik SKU produk untuk menambahkan ke keranjang</p>
            </div>
        </div>

        <!-- SKU Input -->
        <div class="card">
            <div class="card-body" style="padding: 16px;">
                <div class="search-box">
                    <i class="fas fa-barcode"></i>
                    <input type="text" id="skuInput" placeholder="Ketik SKU lalu tekan Enter..." autofocus autocomplete="off">
                </div>
            </div>
        </div>

        <!-- Product List -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Daftar Produk</h3>
                <form method="GET" action="" style="max-width: 300px; margin: 0;">
                    <input type="hidden" name="limit" value="<?php echo $pagination['limit']; ?>">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Cari produk..." autocomplete="off" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </form>
            </div>
            <div class="card-body" style="padding: 0;">
                <div style="max-height: 400px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="productList">
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada produk</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $p): ?>
                                        <tr data-product-id="<?php echo $p['id']; ?>"
                                            data-sku="<?php echo htmlspecialchars($p['sku']); ?>"
                                            data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                            data-price="<?php echo $p['price']; ?>"
                                            data-discount="<?php echo $p['discount']; ?>"
                                            data-stock="<?php echo $p['stock']; ?>">
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                                    <div style="font-size: 12px; color: var(--text-muted);">
                                                        SKU: <?php echo htmlspecialchars($p['sku']); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo formatCurrency($p['price']); ?></td>
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
                                            <td>
                                                <button onclick="addToCartFromList('<?php echo htmlspecialchars($p['sku']); ?>')"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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

        <!-- Cart Table -->
        <div class="card" style="flex: 1; min-height: 300px; display: flex; flex-direction: column;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Keranjang Belanja</h3>
                <button onclick="clearCart()" class="btn btn-secondary" style="padding: 6px 14px; font-size: 13px;">
                    <i class="fas fa-trash"></i> Kosongkan
                </button>
            </div>
            <div class="card-body" style="padding: 0; overflow-y: auto; flex: 1;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartItems">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="empty-cart">
                                        <svg viewBox="0 0 24 24">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                        <p>Keranjang kosong</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Summary & Payment -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
        <!-- Order Summary -->
        <div class="card">
            <div class="card-header">
                <h3>Ringkasan</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Total Item</span>
                        <span id="totalItems" style="font-weight: 600;">0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Subtotal</span>
                        <span id="totalAmount" style="font-weight: 600;">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Total Diskon</span>
                        <span id="totalDiscount" style="font-weight: 600; color: var(--error);">Rp 0</span>
                    </div>
                    <hr style="border: none; border-top: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 18px; font-weight: 700;">Grand Total</span>
                        <span id="grandTotal" style="font-size: 18px; font-weight: 700; color: var(--primary);">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment -->
        <div class="card">
            <div class="card-header">
                <h3>Pembayaran</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="form-group" style="margin: 0;">
                        <label for="paymentInput">Uang Diterima</label>
                        <input type="number" id="paymentInput" class="form-control" placeholder="0" min="0" style="font-size: 20px; font-weight: 700;">
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; background: var(--bg-secondary, #f5f5f5); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Kembalian</span>
                        <span id="changeAmount" style="font-weight: 700; font-size: 18px; color: var(--success);">Rp 0</span>
                    </div>

                    <!-- Quick cash buttons -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <button onclick="setPayment(10000)" class="btn btn-secondary">Rp 10.000</button>
                        <button onclick="setPayment(20000)" class="btn btn-secondary">Rp 20.000</button>
                        <button onclick="setPayment(50000)" class="btn btn-secondary">Rp 50.000</button>
                        <button onclick="setPayment(100000)" class="btn btn-secondary">Rp 100.000</button>
                    </div>

                    <button id="processBtn" onclick="processPayment()" class="btn btn-primary btn-block" disabled style="font-size: 16px; padding: 14px;">
                        <i class="fas fa-cash-register"></i> Proses Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo renderPaginationScript('.card'); ?>

<script>
    function setPayment(amount) {
        document.getElementById('paymentInput').value = amount;
        updateChange();
    }

    function formatCurrency(amount) {
        return 'Rp ' + parseFloat(amount || 0).toLocaleString('id-ID');
    }

    function addToCartFromList(sku) {
        document.getElementById('skuInput').value = sku;
        // Trigger the existing cashier.js SKU input handler
        const event = new KeyboardEvent('keypress', {
            key: 'Enter',
            keyCode: 13,
            which: 13
        });
        document.getElementById('skuInput').dispatchEvent(event);
    }
</script>

<?php
$additional_scripts = ['/assets/js/cashier.js'];
require_once __DIR__ . '/includes/footer.php';
?>