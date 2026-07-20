<?php
// File: pages/checkout.php
$pageTitle = 'Checkout';
require_once __DIR__ . '/../inc/functions.php';
requireLogin();

$productId = (int)($_GET['product_id'] ?? 0);
$product = db()->fetchOne("SELECT * FROM products WHERE id = ? AND status = 'active'", 'i', $productId);

if (!$product) {
    setFlash('error', 'Produk tidak ditemukan.');
    redirect(APP_URL . '/index.php');
}

$user = currentUser();
$fee = platformFee($product['price']);
$total = $product['price'] + $fee;

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token tidak valid.');
        redirect(currentUrl());
    }

    $paymentMethod = sanitize($_POST['payment_method'] ?? '');
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $total = ($product['price'] * $quantity) + $fee;

    if (!$paymentMethod) {
        setFlash('error', 'Pilih metode pembayaran terlebih dahulu.');
        redirect(currentUrl());
    }

    if ($paymentMethod === 'Saldo') {
        if ($user['balance'] < $total) {
            setFlash('error', 'Saldo Anda tidak mencukupi untuk melakukan transaksi ini.');
            redirect(currentUrl());
        }
    }

    // Buat order
    $orderNumber = generateOrderNumber();
    $orderId = db()->insert(
        "INSERT INTO orders (order_number, buyer_id, seller_id, product_id, quantity, price, total_price, platform_fee, payment_method, status, payment_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid')",
        'siiiiidds',
        $orderNumber, $_SESSION['user_id'], $product['seller_id'], $productId,
        $quantity, $product['price'], $total, $fee, $paymentMethod
    );

    if (!$orderId) {
        setFlash('error', 'Gagal membuat order. Coba lagi.');
        redirect(currentUrl());
    }

    // Generate QRIS jika metode QRIS
    if ($paymentMethod === 'QRIS') {
        $qris = generateQRIS($total, $orderNumber);
        db()->execute(
            "INSERT INTO payments (order_id, payment_method, amount, status, qris_string, qris_image_url, expired_at) VALUES (?, 'QRIS', ?, 'pending', ?, ?, ?)",
            'idsss', $orderId, $total, $qris['qr_string'], $qris['qr_image_url'], $qris['expired_at']
        );
        db()->execute("UPDATE orders SET qris_code = ?, qris_expired_at = ? WHERE id = ?", 'ssi', $qris['qr_string'], $qris['expired_at'], $orderId);
        
        // Notifikasi
        sendNotification($_SESSION['user_id'], 'Order Dibuat! 🎉', "Order #{$orderNumber} berhasil dibuat. Silakan scan QRIS untuk membayar.", 'info', APP_URL . '/pages/order-detail.php?id=' . $orderId);
        
        redirect(APP_URL . '/pages/payment.php?order_id=' . $orderId . '&method=qris');
    } else {
        db()->execute(
            "INSERT INTO payments (order_id, payment_method, amount, status, expired_at) VALUES (?, ?, ?, 'pending', ?)",
            'isds', $orderId, $paymentMethod, $total, date('Y-m-d H:i:s', strtotime('+1 day'))
        );
        sendNotification($_SESSION['user_id'], 'Order Dibuat! 🎉', "Order #{$orderNumber} berhasil dibuat. Silakan lakukan pembayaran.", 'info', APP_URL . '/pages/order-detail.php?id=' . $orderId);
        redirect(APP_URL . '/pages/payment.php?order_id=' . $orderId . '&method=' . urlencode($paymentMethod));
    }
}

require_once __DIR__ . '/../inc/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;">
    <h1 style="font-family:var(--font-head);font-size:28px;font-weight:800;margin-bottom:30px;">🛒 Checkout</h1>

    <div class="grid-2" style="gap:30px;align-items:start;">
        <!-- FORM CHECKOUT -->
        <div>
            <form method="POST" id="checkoutForm">
                <?= csrfInput() ?>

                <!-- INFO PRODUK -->
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-body">
                        <h3 style="font-family:var(--font-head);font-size:16px;margin-bottom:16px;">📦 Detail Produk</h3>
                        <div style="display:flex;gap:16px;align-items:center;">
                            <div style="width:70px;height:70px;background:var(--bg-card2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:30px;flex-shrink:0;">🎮</div>
                            <div>
                                <h4 style="font-weight:700;"><?= htmlspecialchars($product['name']) ?></h4>
                                <div style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($product['game_name'] ?? '') ?> · <?= htmlspecialchars($product['platform'] ?? '') ?></div>
                                <div style="color:var(--accent);font-weight:700;margin-top:4px;"><?= rupiah($product['price']) ?></div>
                            </div>
                        </div>
                        <div style="margin-top:16px;">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?= $product['stock'] ?>" style="width:100px;" id="qtyInput">
                        </div>
                    </div>
                </div>

                <!-- CATATAN -->
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-body">
                        <h3 style="font-family:var(--font-head);font-size:16px;margin-bottom:16px;">📝 Catatan (Opsional)</h3>
                        <textarea name="notes" class="form-control" placeholder="Contoh: ID game kamu, server, dll." rows="3"></textarea>
                    </div>
                </div>

                <!-- METODE PEMBAYARAN -->
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-body">
                        <h3 style="font-family:var(--font-head);font-size:16px;margin-bottom:16px;">💳 Metode Pembayaran</h3>
                        <div class="payment-methods-select">
                            <?php
                            $methods = [
                                ['id' => 'Saldo', 'icon' => '💰', 'name' => 'Saldo Utama (' . rupiah($user['balance']) . ')'],
                                ['id' => 'QRIS', 'icon' => '📱', 'name' => 'QRIS'],
                                ['id' => 'BCA', 'icon' => '🏦', 'name' => 'BCA'],
                                ['id' => 'Mandiri', 'icon' => '🏦', 'name' => 'Mandiri'],
                                ['id' => 'BNI', 'icon' => '🏦', 'name' => 'BNI'],
                                ['id' => 'OVO', 'icon' => '💜', 'name' => 'OVO'],
                                ['id' => 'GoPay', 'icon' => '💚', 'name' => 'GoPay'],
                            ];
                            foreach ($methods as $m):
                            ?>
                            <div class="pay-method-btn" onclick="selectPayment('<?= $m['id'] ?>')" id="pay-<?= $m['id'] ?>">
                                <span class="pay-icon"><?= $m['icon'] ?></span>
                                <span class="pay-name"><?= $m['name'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="payment_method" id="paymentMethod" required>
                        <div id="paymentError" style="color:var(--error);font-size:13px;margin-top:10px;display:none;">
                            ⚠️ Pilih metode pembayaran
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" onclick="return validateCheckout()">
                    ⚡ Lanjutkan Pembayaran
                </button>
            </form>
        </div>

        <!-- RINGKASAN ORDER -->
        <div>
            <div class="card" style="position:sticky;top:100px;">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;margin-bottom:20px;">🧾 Ringkasan Order</h3>

                    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;">
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--text-muted);">Harga Produk</span>
                            <span id="subTotal"><?= rupiah($product['price']) ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--text-muted);">Biaya Platform (<?= PLATFORM_FEE_PERCENT ?>%)</span>
                            <span><?= rupiah($fee) ?></span>
                        </div>
                        <hr style="border-color:var(--border);">
                        <div style="display:flex;justify-content:space-between;font-family:var(--font-head);font-size:20px;font-weight:800;">
                            <span>Total</span>
                            <span style="color:var(--accent);" id="totalPrice"><?= rupiah($total) ?></span>
                        </div>
                    </div>

                    <!-- SALDO USER -->
                    <div style="background:var(--bg-card2);border-radius:10px;padding:14px;font-size:14px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                            <span style="color:var(--text-muted);">Saldo Kamu</span>
                            <span style="color:var(--gold);font-weight:700;"><?= rupiah($user['balance']) ?></span>
                        </div>
                        <div style="color:var(--text-muted);font-size:12px;">
                            <?php if ($user['balance'] >= $total): ?>
                                ✅ Saldo mencukupi untuk transaksi ini
                            <?php else: ?>
                                ⚠️ Saldo tidak mencukupi, pilih metode pembayaran lain
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-top:16px;padding:14px;background:rgba(0,230,118,0.08);border:1px solid rgba(0,230,118,0.2);border-radius:10px;font-size:13px;color:var(--success);">
                        🛡️ Transaksi ini dilindungi oleh sistem escrow BoloTopup.ID
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectPayment(method) {
    document.querySelectorAll('.pay-method-btn').forEach(el => el.classList.remove('selected'));
    document.getElementById('pay-' + method).classList.add('selected');
    document.getElementById('paymentMethod').value = method;
    document.getElementById('paymentError').style.display = 'none';
}
function validateCheckout() {
    if (!document.getElementById('paymentMethod').value) {
        document.getElementById('paymentError').style.display = 'block';
        return false;
    }
    return true;
}
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>