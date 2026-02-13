<?php
$page_title = 'Transaksi';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/pagination_logic.php';
require_once __DIR__ . '/includes/pagination.php';

requireAuth();

// Get date range from query params
$date_from = $_GET['from'] ?? date('Y-m-d');
$date_to = $_GET['to'] ?? date('Y-m-d');

// Validate: to date should not be before from date
if (strtotime($date_to) < strtotime($date_from)) {
    $date_to = $date_from;
}

// Get pagination params
$pagination = getPaginationParams(10, 5, 100);

// Count total transactions for the date range
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM transactions t
    WHERE DATE(t.created_at) BETWEEN ? AND ?
");
$count_stmt->execute([$date_from, $date_to]);
$total_data = $count_stmt->fetch()['total'];
$total_pages = calculateTotalPages($total_data, $pagination['limit']);

// Get transactions with pagination
$stmt = $pdo->prepare("
    SELECT t.*, u.username 
    FROM transactions t 
    JOIN users u ON t.user_id = u.id 
    WHERE DATE(t.created_at) BETWEEN ? AND ?
    ORDER BY t.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$date_from, $date_to, $pagination['limit'], $pagination['offset']]);
$transactions = $stmt->fetchAll();

// Get summary
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_count,
        COALESCE(SUM(grand_total), 0) as total_sales,
        COALESCE(AVG(grand_total), 0) as avg_sale
    FROM transactions 
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$summary = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="transactions-container">
    <div class="page-header">
        <h1>Riwayat Transaksi</h1>
        <p>Lihat dan unduh riwayat transaksi</p>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="GET" class="search-filter-bar" id="dateFilterForm">
                <input type="hidden" name="limit" value="<?php echo $pagination['limit']; ?>">
                <div class="form-group" style="margin: 0;">
                    <input type="date" name="from" id="dateFrom" value="<?php echo $date_from; ?>" class="form-control">
                </div>
                <div class="form-group" style="margin: 0;">
                    <input type="date" name="to" id="dateTo" value="<?php echo $date_to; ?>" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>
    
    <div class="stats-grid transactions-stats">
        <div class="stat-card">
            <div class="stat-icon" style="background: #7928ca;">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Transaksi</p>
                <h2 class="stat-value"><?php echo number_format($summary['total_count']); ?></h2>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #0070f3;">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Penjualan</p>
                <h2 class="stat-value"><?php echo formatCurrency($summary['total_sales']); ?></h2>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #ff0080;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Rata-rata</p>
                <h2 class="stat-value"><?php echo formatCurrency($summary['avg_sale']); ?></h2>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Grand Total</th>
                            <th>Kasir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada transaksi</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($t['transaction_code']); ?></code></td>
                                    <td><?php echo formatDateTime($t['created_at']); ?></td>
                                    <td><?php echo formatCurrency($t['grand_total']); ?></td>
                                    <td><?php echo htmlspecialchars($t['username']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button onclick="viewDetails(<?php echo $t['id']; ?>)" class="action-btn" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="/generate-receipt.php?id=<?php echo $t['id']; ?>" class="action-btn" title="Download Receipt" target="_blank">
                                                <i class="fas fa-download"></i>
                                            </a>
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
            $hidden_inputs = [
                'limit' => $pagination['limit'],
                'from' => $date_from,
                'to' => $date_to
            ];
            echo renderPagination($pagination['page'], $total_pages, $hidden_inputs);
            ?>
        </div>
    </div>
</div>

<?php echo renderPaginationScript('.card:last-child'); ?>

<script>
// Date validation
document.addEventListener('DOMContentLoaded', () => {
    const dateForm = document.getElementById('dateFilterForm');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    
    if (dateForm && dateFrom && dateTo) {
        dateForm.addEventListener('submit', (e) => {
            if (dateTo.value && dateFrom.value && dateTo.value < dateFrom.value) {
                e.preventDefault();
                Notification.show({ 
                    message: 'Tanggal akhir tidak boleh lebih awal dari tanggal awal', 
                    type: 'error' 
                });
                dateTo.value = dateFrom.value;
            }
        });
        
        // Auto-adjust on change
        dateFrom.addEventListener('change', () => {
            if (dateTo.value && dateTo.value < dateFrom.value) {
                dateTo.value = dateFrom.value;
            }
        });
        
        dateTo.addEventListener('change', () => {
            if (dateFrom.value && dateTo.value < dateFrom.value) {
                dateTo.value = dateFrom.value;
                Notification.show({ 
                    message: 'Tanggal akhir disesuaikan ke tanggal awal', 
                    type: 'warning' 
                });
            }
        });
    }
});

async function viewDetails(transactionId) {
    try {
        const response = await fetch(`/api/get-transaction-details.php?id=${transactionId}`);
        const data = await response.json();
        
        if (data.success) {
            const items = data.items.map(item => `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.qty}</td>
                    <td>${formatCurrency(item.price)}</td>
                    <td>${item.discount}%</td>
                    <td>${formatCurrency(item.subtotal)}</td>
                </tr>
            `).join('');
            
            Modal.form({
                title: `Detail Transaksi - ${data.transaction.transaction_code}`,
                size: 'large',
                content: `
                    <div style="margin-bottom: 16px;">
                        <p><strong>Tanggal:</strong> ${formatDateTime(data.transaction.created_at)}</p>
                        <p><strong>Kasir:</strong> ${data.transaction.username}</p>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Diskon</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${items}</tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right;"><strong>Total:&nbsp;</strong></td>
                                <td><strong>${formatCurrency(data.transaction.total_amount)}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right;"><strong>Diskon:&nbsp;</strong></td>
                                <td style="color: var(--error);"><strong>${formatCurrency(data.transaction.total_discount)}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right;"><strong>Grand Total:&nbsp;</strong></td>
                                <td style="color: var(--success);"><strong>${formatCurrency(data.transaction.grand_total)}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                `
            });
        }
    } catch (error) {
        Notification.show({ message: 'Gagal memuat detail', type: 'error' });
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

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
