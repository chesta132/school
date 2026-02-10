<?php
$page_title = 'Kasir';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

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
                                <td colspan="5" class="text-center">Keranjang kosong</td>
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

<script>
function setPayment(amount) {
    document.getElementById('paymentInput').value = amount;
    updateChange();
}

function formatCurrency(amount) {
    return 'Rp ' + parseFloat(amount || 0).toLocaleString('id-ID');
}
</script>

<?php
$additional_scripts = ['/assets/js/cashier.js'];
require_once __DIR__ . '/includes/footer.php';
?>
