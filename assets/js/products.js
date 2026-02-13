// Products Page JavaScript

let products = [];
let categories = [];
let productsPagination;

// Load products and categories on page load
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadProducts();
    
    // Initialize pagination
    productsPagination = new Pagination({
        containerId: 'productsPagination',
        itemsPerPage: 10,
        perPageOptions: [10, 25, 50, 100],
        onPageChange: (items) => {
            displayProducts(items);
        }
    });
});

// Load categories
async function loadCategories() {
    try {
        const response = await fetch('/api/get-categories.php');
        const data = await response.json();
        
        if (data.success) {
            categories = data.categories;
            populateCategoryFilter();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Populate category filter
function populateCategoryFilter() {
    const select = document.getElementById('categoryFilter');
    categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.name;
        select.appendChild(option);
    });
}

// Load products
async function loadProducts() {
    try {
        const response = await fetch('/api/get-products.php');
        const data = await response.json();
        
        if (data.success) {
            products = data.products;
            productsPagination.setItems(products);
            displayProducts(productsPagination.getCurrentPageItems());
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// Display products in table
function displayProducts(productsToDisplay) {
    const tbody = document.querySelector('#productsTable tbody');
    
    if (productsToDisplay.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">Tidak ada produk</td></tr>';
        return;
    }
    
    tbody.innerHTML = productsToDisplay.map(p => `
        <tr>
            <td><code>${p.sku}</code></td>
            <td>${p.name}</td>
            <td><span class="badge badge-info">${p.category_name}</span></td>
            <td>${p.weight}g</td>
            <td>${formatCurrency(p.price)}</td>
            <td><span class="badge ${p.discount > 0 ? 'badge-warning' : 'badge-secondary'}">${p.discount}%</span></td>
            <td><span class="badge ${getStockBadgeClass(p.stock)}">${p.stock}</span></td>
            <td>${p.sold}</td>
            <td>
                <div class="actions">
                    <button onclick="editProduct(${p.id})" class="action-btn" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteProduct(${p.id})" class="action-btn" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Search products
function searchProducts() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const categoryId = document.getElementById('categoryFilter').value;
    
    let filtered = products;
    
    if (query) {
        filtered = filtered.filter(p => 
            p.name.toLowerCase().includes(query) || 
            p.sku.toLowerCase().includes(query)
        );
    }
    
    if (categoryId) {
        filtered = filtered.filter(p => p.category_id == categoryId);
    }
    
    productsPagination.setFilteredItems(filtered);
    displayProducts(productsPagination.getCurrentPageItems());
}

// Filter products by category
function filterProducts() {
    searchProducts();
}

// Show product modal (add/edit)
function showProductModal(productId = null) {
    const isEdit = productId !== null;
    const product = isEdit ? products.find(p => p.id === productId) : null;
    
    const categoryOptions = categories.map(cat => 
        `<option value="${cat.id}" ${product && product.category_id == cat.id ? 'selected' : ''}>${cat.name}</option>`
    ).join('');
    
    Modal.form({
        title: isEdit ? 'Edit Produk' : 'Tambah Produk',
        content: `
            <form id="productForm">
                <input type="hidden" name="id" value="${product ? product.id : ''}">
                
                <div class="form-group">
                    <label>Nama Produk *</label>
                    <input type="text" name="name" value="${product ? product.name : ''}" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="category_id" required>${categoryOptions}</select>
                </div>
                
                <div class="form-group">
                    <label>Berat (gram/ml) *</label>
                    <input type="number" name="weight" value="${product ? product.weight : ''}" min="1" required>
                </div>
                
                <div class="form-group">
                    <label>Harga *</label>
                    <input type="number" name="price" value="${product ? product.price : ''}" min="0" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Diskon (%)</label>
                    <input type="number" name="discount" value="${product ? product.discount : '0'}" min="0" max="100" step="0.01">
                </div>
                
                <div class="form-group">
                    <label>Stok *</label>
                    <input type="number" name="stock" value="${product ? product.stock : '0'}" min="0" required>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="button" onclick="Modal.close(this)" class="btn btn-secondary" style="flex: 1;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan</button>
                </div>
            </form>
        `,
        onSubmit: async (formData, modal) => {
            try {
                const endpoint = isEdit ? '/api/update-product.php' : '/api/create-product.php';
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Notification.show({ message: data.message, type: 'success' });
                    Modal.close(modal);
                    loadProducts();
                } else {
                    Notification.show({ message: data.message, type: 'error' });
                }
            } catch (error) {
                Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
            }
        }
    });
}

// Edit product
function editProduct(productId) {
    showProductModal(productId);
}

// Delete product
function deleteProduct(productId) {
    const product = products.find(p => p.id === productId);
    
    Modal.confirm({
        title: 'Hapus Produk',
        message: `Apakah Anda yakin ingin menghapus produk "${product.name}"?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        onConfirm: async () => {
            try {
                const response = await fetch('/api/delete-product.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: productId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Notification.show({ message: data.message, type: 'success' });
                    loadProducts();
                } else {
                    Notification.show({ message: data.message, type: 'error' });
                }
            } catch (error) {
                Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
            }
        }
    });
}

// Show category modal
function showCategoryModal() {
    Modal.form({
        title: 'Kelola Kategori',
        size: 'large',
        content: `
            <div style="margin-bottom: 24px;">
                <button onclick="addCategory()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kategori
                </button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    ${categories.map(cat => `
                        <tr>
                            <td><code>${cat.code}</code></td>
                            <td>${cat.name}</td>
                            <td>
                                <button onclick="deleteCategory(${cat.id})" class="action-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `
    });
}

// Add category
function addCategory() {
    Modal.form({
        title: 'Tambah Kategori',
        content: `
            <form id="categoryForm">
                <div class="form-group">
                    <label>Nama Kategori *</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Kode (3 huruf) *</label>
                    <input type="text" name="code" maxlength="3" pattern="[A-Z]{3}" required style="text-transform: uppercase;">
                    <small>Contoh: MIN, MAK, SNK</small>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="button" onclick="Modal.close(this)" class="btn btn-secondary" style="flex: 1;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan</button>
                </div>
            </form>
        `,
        onSubmit: async (formData, modal) => {
            try {
                const response = await fetch('/api/create-category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Notification.show({ message: data.message, type: 'success' });
                    Modal.close(modal);
                    loadCategories();
                    showCategoryModal();
                } else {
                    Notification.show({ message: data.message, type: 'error' });
                }
            } catch (error) {
                Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
            }
        }
    });
}

// Delete category
function deleteCategory(categoryId) {
    Modal.confirm({
        title: 'Hapus Kategori',
        message: 'Apakah Anda yakin? Produk dengan kategori ini juga akan terhapus.',
        confirmText: 'Hapus',
        onConfirm: async () => {
            try {
                const response = await fetch('/api/delete-category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: categoryId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Notification.show({ message: data.message, type: 'success' });
                    loadCategories();
                    loadProducts();
                    showCategoryModal();
                } else {
                    Notification.show({ message: data.message, type: 'error' });
                }
            } catch (error) {
                Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
            }
        }
    });
}
