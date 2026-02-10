<?php
$page_title = 'Produk';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

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
            <div class="search-filter-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari produk..." onkeyup="searchProducts()">
                </div>
                <select id="categoryFilter" onchange="filterProducts()" class="form-control" style="width: 200px;">
                    <option value="">Semua Kategori</option>
                </select>
            </div>
            
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
                        <tr>
                            <td colspan="9" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$additional_scripts = ['/assets/js/products.js'];
require_once __DIR__ . '/includes/footer.php';
?>
