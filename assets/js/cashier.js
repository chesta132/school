// Cashier Page JavaScript

let cart = [];

// Load cart from localStorage
document.addEventListener('DOMContentLoaded', () => {
    loadCart();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    const skuInput = document.getElementById('skuInput');
    const paymentInput = document.getElementById('paymentInput');
    
    // SKU input handler
    skuInput.addEventListener('keypress', async (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const sku = skuInput.value.trim();
            if (sku) {
                await addProductBySKU(sku);
                skuInput.value = '';
            }
        }
    });
    
    // Payment input handler
    paymentInput.addEventListener('input', updateChange);
}

// Load cart from localStorage
function loadCart() {
    const saved = localStorage.getItem('pos_cart');
    if (saved) {
        cart = JSON.parse(saved);
        updateCartDisplay();
    }
}

// Save cart to localStorage
function saveCart() {
    localStorage.setItem('pos_cart', JSON.stringify(cart));
}

// Add product by SKU
async function addProductBySKU(sku) {
    try {
        const response = await fetch(`/api/get-product.php?sku=${encodeURIComponent(sku)}`);
        const data = await response.json();
        
        if (data.success) {
            addToCart(data.product);
        } else {
            Notification.show({ message: data.message, type: 'error' });
        }
    } catch (error) {
        Notification.show({ message: 'Gagal memuat produk', type: 'error' });
    }
}

// Add product to cart
function addToCart(product) {
    const existingIndex = cart.findIndex(item => item.id === product.id);
    
    if (existingIndex >= 0) {
        cart[existingIndex].qty += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            sku: product.sku,
            price: parseFloat(product.price),
            discount: parseFloat(product.discount),
            qty: 1,
            stock: parseInt(product.stock)
        });
    }
    
    saveCart();
    updateCartDisplay();
}

// Update cart display
function updateCartDisplay() {
    const tbody = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Keranjang kosong</td></tr>';
        updateSummary();
        return;
    }
    
    tbody.innerHTML = cart.map((item, index) => {
        const itemTotal = item.price * item.qty;
        const itemDiscount = itemTotal * (item.discount / 100);
        const subtotal = itemTotal - itemDiscount;
        
        return `
            <tr>
                <td>
                    <div>${item.name}</div>
                    <small style="color: var(--text-muted);">${item.sku}</small>
                </td>
                <td>${formatCurrency(item.price)}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button onclick="decreaseQty(${index})" class="action-btn" style="width: 28px; height: 28px;">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span style="min-width: 30px; text-align: center; font-weight: 600;">${item.qty}</span>
                        <button onclick="increaseQty(${index})" class="action-btn" style="width: 28px; height: 28px;" ${item.qty >= item.stock ? 'disabled' : ''}>
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <div>${formatCurrency(subtotal)}</div>
                    ${item.discount > 0 ? `<small style="color: var(--error);">-${formatCurrency(itemDiscount)} (${item.discount}%)</small>` : ''}
                </td>
                <td>
                    <button onclick="removeFromCart(${index})" class="action-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    updateSummary();
}

// Increase quantity
function increaseQty(index) {
    if (cart[index].qty < cart[index].stock) {
        cart[index].qty += 1;
        saveCart();
        updateCartDisplay();
    } else {
        Notification.show({ message: 'Stok tidak mencukupi', type: 'warning' });
    }
}

// Decrease quantity
function decreaseQty(index) {
    if (cart[index].qty > 1) {
        cart[index].qty -= 1;
        saveCart();
        updateCartDisplay();
    } else {
        removeFromCart(index);
    }
}

// Remove from cart
function removeFromCart(index) {
    cart.splice(index, 1);
    saveCart();
    updateCartDisplay();
}

// Clear cart
function clearCart() {
    if (cart.length === 0) {
        Notification.show({ message: 'Keranjang sudah kosong', type: 'info' });
        return;
    }
    
    Modal.confirm({
        title: 'Kosongkan Keranjang',
        message: 'Apakah Anda yakin ingin mengosongkan keranjang?',
        confirmText: 'Ya, Kosongkan',
        onConfirm: () => {
            cart = [];
            saveCart();
            updateCartDisplay();
            document.getElementById('paymentInput').value = '';
            updateChange();
            Notification.show({ message: 'Keranjang dikosongkan', type: 'success' });
        }
    });
}

// Update summary
function updateSummary() {
    let totalItems = 0;
    let totalAmount = 0;
    let totalDiscount = 0;
    
    cart.forEach(item => {
        totalItems += item.qty;
        const itemTotal = item.price * item.qty;
        const itemDiscount = itemTotal * (item.discount / 100);
        totalAmount += itemTotal;
        totalDiscount += itemDiscount;
    });
    
    const grandTotal = totalAmount - totalDiscount;
    
    document.getElementById('totalItems').textContent = totalItems;
    document.getElementById('totalAmount').textContent = formatCurrency(totalAmount);
    document.getElementById('totalDiscount').textContent = formatCurrency(totalDiscount);
    document.getElementById('grandTotal').textContent = formatCurrency(grandTotal);
    
    // Store grand total for payment validation
    window.currentGrandTotal = grandTotal;
    
    updateChange();
}

// Update change amount
function updateChange() {
    const payment = parseFloat(document.getElementById('paymentInput').value) || 0;
    const grandTotal = window.currentGrandTotal || 0;
    const change = payment - grandTotal;
    
    document.getElementById('changeAmount').textContent = formatCurrency(Math.max(0, change));
    
    // Enable/disable process button
    const processBtn = document.getElementById('processBtn');
    processBtn.disabled = cart.length === 0 || payment < grandTotal;
}

// Process payment
async function processPayment() {
    const payment = parseFloat(document.getElementById('paymentInput').value) || 0;
    const grandTotal = window.currentGrandTotal || 0;
    
    if (cart.length === 0) {
        Notification.show({ message: 'Keranjang kosong', type: 'error' });
        return;
    }
    
    if (payment < grandTotal) {
        Notification.show({ message: 'Pembayaran kurang', type: 'error' });
        return;
    }
    
    Modal.loading('Memproses transaksi...');
    
    try {
        const response = await fetch('/api/save-transaction.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                items: cart,
                payment_amount: payment
            })
        });
        
        const data = await response.json();
        
        Modal.closeLoading();
        
        if (data.success) {
            // Clear cart
            cart = [];
            saveCart();
            updateCartDisplay();
            document.getElementById('paymentInput').value = '';
            
            // Show success message
            Notification.show({ 
                message: `Transaksi berhasil! Kode: ${data.transaction.code}`, 
                type: 'success',
                duration: 5000
            });
            
            // Download receipt
            window.open(`/generate-receipt.php?code=${data.transaction.code}`, '_blank');
        } else {
            Notification.show({ message: data.message, type: 'error' });
        }
    } catch (error) {
        Modal.closeLoading();
        Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
    }
}
