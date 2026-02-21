// Cashier Page JavaScript

let cart = [];

// Load cart from localStorage on page load
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
    const stock = parseInt(product.stock);

    if (stock <= 0) {
        Notification.show({ message: 'Produk habis', type: 'error' });
        return;
    }

    const existingIndex = cart.findIndex(item => item.id === product.id);

    if (existingIndex >= 0) {
        if (cart[existingIndex].qty >= stock) {
            Notification.show({ message: 'Stok tidak mencukupi', type: 'warning' });
            return;
        }
        cart[existingIndex].qty += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            sku: product.sku,
            price: parseFloat(product.price),
            discount: parseFloat(product.discount),
            qty: 1,
            stock: stock
        });
    }

    saveCart();
    updateCartDisplay();
}

// Update cart display
function updateCartDisplay() {
    const tbody = document.getElementById('cartItems');

    if (cart.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <p>Keranjang kosong</p>
            </div>
        </td></tr>`;
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
                        <input type="number" 
                               value="${item.qty}" 
                               onchange="updateQtyFromInput(${index}, this.value)"
                               min="1" 
                               max="${item.stock}"
                               class="inputQty"
                               style="width: 60px; text-align: center; padding: 4px 8px; border: 1px solid var(--border-color); border-radius: 4px; font-weight: 600; background: transparent; color: #fff;">
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

    window.currentGrandTotal = grandTotal;

    updateChange();
}

// Update change amount
function updateChange() {
    const payment = parseFloat(document.getElementById('paymentInput').value) || 0;
    const grandTotal = window.currentGrandTotal || 0;
    const change = payment - grandTotal;

    document.getElementById('changeAmount').textContent = formatCurrency(Math.max(0, change));

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
            cart = [];
            saveCart();
            updateCartDisplay();
            document.getElementById('paymentInput').value = '';

            Notification.show({
                message: `Transaksi berhasil! Kode: ${data.transaction.code}`,
                type: 'success',
                duration: 5000
            });

            window.open(`/generate-receipt.php?code=${data.transaction.code}`, '_blank');
        } else {
            Notification.show({ message: data.message, type: 'error' });
        }
    } catch (error) {
        Modal.closeLoading();
        Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
    }
}

// Update qty from input field
function updateQtyFromInput(index, value) {
    const qty = parseInt(value);

    if (isNaN(qty) || qty < 1) {
        Notification.show({ message: 'Jumlah tidak valid', type: 'error' });
        updateCartDisplay();
        return;
    }

    if (qty > cart[index].stock) {
        Notification.show({ message: 'Stok tidak mencukupi', type: 'warning' });
        updateCartDisplay();
        return;
    }

    cart[index].qty = qty;
    saveCart();
    updateCartDisplay();
}
