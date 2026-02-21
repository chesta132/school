// Products Page JavaScript
// Data produk dan kategori diambil dari PHP (server-side) dan di-embed ke halaman.
// JS ini hanya mengelola CRUD via modal + API.

let categories = [];

// Load categories untuk form modal
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
});

// Load categories
async function loadCategories() {
    try {
        const response = await fetch('/api/get-categories.php');
        const data = await response.json();
        if (data.success) {
            categories = data.categories;
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Show product modal (add/edit)
function showProductModal(productId = null) {
    const isEdit = productId !== null;
    const product = isEdit ? window.__products?.find(p => p.id === productId) : null;

    const categoryOptions = categories.map(cat =>
        `<option value="${cat.id}" ${product && product.category_id == cat.id ? 'selected' : ''}>${cat.name}</option>`
    ).join('');

    Modal.form({
        title: isEdit ? 'Edit Produk' : 'Tambah Produk',
        type: 'default',
        icon: isEdit ? 'edit' : 'plus-circle',
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
                    location.reload();
                } else {
                    Notification.show({ message: data.message, type: 'error' });
                }
            } catch (error) {
                Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
            }
        }
    });
}

// Edit product - ambil data dari row tabel yang di-render PHP
function editProduct(productId) {
    // Fetch product data fresh dari API
    fetch(`/api/get-product.php?id=${productId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (!window.__products) window.__products = [];
                const idx = window.__products.findIndex(p => p.id === productId);
                if (idx >= 0) window.__products[idx] = data.product;
                else window.__products.push(data.product);
                showProductModal(productId);
            } else {
                Notification.show({ message: 'Gagal memuat data produk', type: 'error' });
            }
        })
        .catch(() => Notification.show({ message: 'Terjadi kesalahan', type: 'error' }));
}

// Delete product
function deleteProduct(productId, productName) {
    Modal.confirm({
        title: 'Hapus Produk',
        message: `Apakah Anda yakin ingin menghapus produk "<strong>${productName}</strong>"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        icon: 'box-open',
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
                    location.reload();
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
        type: 'default',
        icon: 'folder-open',
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
        type: 'default',
        icon: 'folder-plus',
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
                    await loadCategories();
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
        message: 'Apakah Anda yakin? <strong>Semua produk dalam kategori ini juga akan terhapus.</strong> Tindakan ini tidak dapat dibatalkan.',
        type: 'danger',
        icon: 'folder',
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
                    await loadCategories();
                    location.reload();
                } else {
                    Notification.show({ message: data.message, type: 'error' });
                }
            } catch (error) {
                Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
            }
        }
    });
}
