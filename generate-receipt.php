<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAuth();

// Get transaction by ID or code
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$code = isset($_GET['code']) ? sanitize($_GET['code']) : '';

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
    $stmt->execute([$id]);
} elseif (!empty($code)) {
    $stmt = $pdo->prepare("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.transaction_code = ?");
    $stmt->execute([$code]);
} else {
    die('Invalid request');
}

$transaction = $stmt->fetch();

if (!$transaction) {
    die('Transaction not found');
}

// Get transaction items
$stmt = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
$stmt->execute([$transaction['id']]);
$items = $stmt->fetchAll();

// Set filename
$filename = 'receipt-' . $transaction['transaction_code'] . '.pdf';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $filename; ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: #0a0a0a;
            color: #ededed;
        }

        .receipt-wrapper {
            max-width: 300px;
            margin: 0 auto;
        }
        
        .receipt {
            background: #fff;
            color: #000;
            border: 1px solid #000;
            padding: 15px;
        }

        .receipt * { color: #000; }

        @media print {
            body { background: white; padding: 0; }
        }
        
        .center {
            text-align: center;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        
        .row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .item-row {
            margin: 5px 0;
        }
        
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            padding: 10px 20px;
            background: #ffffff;
            color: #000000;
            border: 1px solid #ffffff;
            border-radius: 6px;
            cursor: pointer;
            margin: 0 5px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
        }

        .btn:hover {
            background: #e5e5e5;
            border-color: #e5e5e5;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center;margin-bottom:20px;">
        <button class="btn" onclick="window.print()">Print / Save as PDF</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>

    <div class="receipt-wrapper">
    <div class="receipt">
        <div class="center bold" style="font-size: 16px;">CHARDYMART</div>
        <div class="center">Jl. Sudirman No. 123</div>
        <div class="center">Jakarta Pusat 10220</div>
        <div class="center">Telp: 021-5551234</div>
        
        <div class="divider"></div>
        
        <div class="row">
            <span>TRX:</span>
            <span class="bold"><?php echo htmlspecialchars($transaction['transaction_code']); ?></span>
        </div>
        <div class="row">
            <span>Date:</span>
            <span><?php echo formatDateTime($transaction['created_at']); ?></span>
        </div>
        <div class="row">
            <span>Kasir:</span>
            <span><?php echo htmlspecialchars($transaction['username']); ?></span>
        </div>
        
        <div class="divider"></div>
        
        <?php foreach ($items as $item): ?>
            <?php
                $itemTotal = $item['price'] * $item['qty'];
                $itemDiscount = $itemTotal * ($item['discount'] / 100);
                $subtotal = $itemTotal - $itemDiscount;
            ?>
            <div class="item-row">
                <div><?php echo htmlspecialchars($item['product_name']); ?></div>
                <div class="row">
                    <span><?php echo $item['qty']; ?> x <?php echo formatCurrency($item['price']); ?></span>
                    <span class="bold"><?php echo formatCurrency($itemTotal); ?></span>
                </div>
                <?php if ($item['discount'] > 0): ?>
                    <div class="row" style="font-size: 10px;">
                        <span>Disc <?php echo $item['discount']; ?>%</span>
                        <span>-<?php echo formatCurrency($itemDiscount); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="divider"></div>
        
        <div class="row">
            <span>Total Items:</span>
            <span class="bold"><?php 
                $totalQty = array_sum(array_column($items, 'qty'));
                echo $totalQty;
            ?></span>
        </div>
        <div class="row">
            <span>Total Amount:</span>
            <span><?php echo formatCurrency($transaction['total_amount']); ?></span>
        </div>
        <?php if ($transaction['total_discount'] > 0): ?>
            <div class="row">
                <span>Total Discount:</span>
                <span>-<?php echo formatCurrency($transaction['total_discount']); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="divider"></div>
        
        <div class="row bold" style="font-size: 14px;">
            <span>GRAND TOTAL:</span>
            <span><?php echo formatCurrency($transaction['grand_total']); ?></span>
        </div>
        
        <div class="divider" style="margin-top: 15px;"></div>
        
        <div class="row">
            <span>Payment:</span>
            <span><?php echo formatCurrency($transaction['payment_amount']); ?></span>
        </div>
        <div class="row">
            <span>Change:</span>
            <span><?php echo formatCurrency($transaction['change_amount']); ?></span>
        </div>
        
        <div class="divider"></div>
        
        <div class="center bold" style="margin-top: 10px;">Terima Kasih!</div>
        <div class="center">Belanja Kembali di</div>
        <div class="center bold">CHARDYMART</div>
    </div>
    </div>

    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
