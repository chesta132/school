<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($current_user['username']); ?>!</p>
    </div>
    
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #0070f3;">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Penjualan Hari Ini</p>
                <h2 class="stat-value" id="totalSalesToday">Rp 0</h2>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #7928ca;">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Transaksi Hari Ini</p>
                <h2 class="stat-value" id="totalTransactionsToday">0</h2>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #ff0080;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Item Terjual Hari Ini</p>
                <h2 class="stat-value" id="totalItemsSoldToday">0</h2>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f5a623;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Stok Menipis</p>
                <h2 class="stat-value" id="lowStockCount">0</h2>
            </div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                <h3>Penjualan 7 Hari Terakhir</h3>
            </div>
            <div class="card-body">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Transaksi Terakhir</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="recentTransactionsTable">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Total</th>
                                <th>Kasir</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>Produk Stok Menipis</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="lowStockTable">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let salesChart;
    
    async function loadDashboardData() {
        try {
            const response = await fetch('/api/get-stats.php');
            const data = await response.json();
            
            if (data.success) {
                // Update stats
                document.getElementById('totalSalesToday').textContent = formatCurrency(data.stats.total_sales_today);
                document.getElementById('totalTransactionsToday').textContent = data.stats.total_transactions_today;
                document.getElementById('totalItemsSoldToday').textContent = data.stats.total_items_sold_today;
                document.getElementById('lowStockCount').textContent = data.stats.low_stock_count;
                
                // Update chart
                updateSalesChart(data.sales_chart);
                
                // Update recent transactions
                updateRecentTransactions(data.recent_transactions);
                
                // Update low stock table
                updateLowStockTable(data.low_stock_products);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }
    
    function updateSalesChart(chartData) {
        const labels = chartData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
        });
        const values = chartData.map(item => parseFloat(item.total));
        
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        if (salesChart) {
            salesChart.destroy();
        }
        
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan',
                    data: values,
                    borderColor: '#0070f3',
                    backgroundColor: 'rgba(0, 112, 243, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }
    
    function updateRecentTransactions(transactions) {
        const tbody = document.querySelector('#recentTransactionsTable tbody');
        
        if (transactions.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    <p>Belum ada transaksi</p>
                </div>
            </td></tr>`;
            return;
        }
        
        tbody.innerHTML = transactions.map(t => `
            <tr>
                <td><code>${t.transaction_code}</code></td>
                <td>${formatCurrency(t.grand_total)}</td>
                <td>${t.username}</td>
                <td>${formatDateTime(t.created_at)}</td>
            </tr>
        `).join('');
    }
    
    function updateLowStockTable(products) {
        const tbody = document.querySelector('#lowStockTable tbody');
        
        if (products.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <p>Semua produk stok aman</p>
                </div>
            </td></tr>`;
            return;
        }
        
        tbody.innerHTML = products.map(p => `
            <tr>
                <td><code>${p.sku}</code></td>
                <td>${p.name}</td>
                <td><span class="badge badge-info">${p.category_name}</span></td>
                <td><span class="badge-stock ${getStockBadgeClass(p.stock)}">${p.stock}</span></td>
            </tr>
        `).join('');
    }
    
    function formatCurrency(amount) {
        return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
    }
    
    function formatDateTime(datetime) {
        const date = new Date(datetime);
        return date.toLocaleString('id-ID', { 
            day: '2-digit', 
            month: 'short', 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    
    function getStockBadgeClass(stock) {
        if (stock <= 0) return 'stock-out';
        if (stock < 10) return 'stock-low';
        return 'stock-normal';
    }
    
    // Load dashboard data on page load
    loadDashboardData();
</script>

<?php
$additional_scripts = [];
require_once __DIR__ . '/includes/footer.php';
?>
