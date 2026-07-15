<?php
// File: pages/payment.php
$pageTitle = 'Pembayaran';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user = currentUser();
$orderId = (int)($_GET['order_id'] ?? 0);
$method = sanitize($_GET['method'] ?? '');

$order = db()->fetchOne(
    "SELECT o.*, p.name as product_name, p.image as product_image 
     FROM orders o JOIN products p ON o.product_id = p.id 
     WHERE o.id = ? AND o.buyer_id = ?",
    'ii', $orderId, $_SESSION['user_id']
);

if (!$order) {
    setFlash('error', 'Order tidak ditemukan.');
    redirect(APP_URL . '/pages/dashboard.php');
}

$payment = db()->fetchOne("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", 'i', $orderId);

// Simulasi konfirmasi pembayaran atau pembayaran menggunakan saldo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['simulate_pay']) || isset($_POST['pay_with_saldo']))) {
    if (isset($_POST['pay_with_saldo'])) {
        $latestUser = currentUser();
        if ($latestUser['balance'] < $order['total_price']) {
            setFlash('error', 'Saldo Anda tidak mencukupi untuk melakukan transaksi ini.');
            redirect(currentUrl());
        }
        // Deduct balance
        db()->execute("UPDATE users SET balance = balance - ? WHERE id = ?", 'di', $order['total_price'], $_SESSION['user_id']);
    }

    db()->execute("UPDATE payments SET status = 'success', paid_at = NOW() WHERE order_id = ?", 'i', $orderId);
    db()->execute("UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = ?", 'i', $orderId);
    db()->execute("UPDATE products SET stock = stock - ?, sold_count = sold_count + ? WHERE id = ?", 'iii', $order['quantity'], $order['quantity'], $order['product_id']);
    
    // Ambil data detail Pembeli dan Penjual
    $buyer = db()->fetchOne("SELECT email, username, full_name FROM users WHERE id = ?", 'i', $order['buyer_id']);
    $seller = db()->fetchOne("SELECT email, username, full_name FROM users WHERE id = ?", 'i', $order['seller_id']);

    if ($buyer) {
        $buyerName = htmlspecialchars($buyer['full_name'] ?: $buyer['username']);
        $buyerSubject = "Invoice Pembayaran #" . $order['order_number'] . " - " . APP_NAME . " 💳";
        $buyerBody = "
            <h2 class='email-title'>Pembayaran Berhasil! 🎉</h2>
            <p>Halo <strong>" . $buyerName . "</strong>,</p>
            <p>Terima kasih telah melakukan pembayaran. Pesanan Anda saat ini sedang diproses oleh penjual.</p>
            <div class='divider'></div>
            <h3 style='color: #ffffff;'>Detail Transaksi:</h3>
            <table class='detail-table'>
                <tr>
                    <th>No. Transaksi</th>
                    <td><code>" . htmlspecialchars($order['order_number']) . "</code></td>
                </tr>
                <tr>
                    <th>Produk</th>
                    <td>" . htmlspecialchars($order['product_name']) . " (x" . $order['quantity'] . ")</td>
                </tr>
                <tr>
                    <th>Biaya Layanan</th>
                    <td>" . rupiah($order['platform_fee']) . "</td>
                </tr>
                <tr style='font-weight: bold; color: #f59e0b;'>
                    <th>Total Pembayaran</th>
                    <td><span class='text-highlight'>" . rupiah($order['total_price']) . "</span></td>
                </tr>
                <tr>
                    <th>Metode Pembayaran</th>
                    <td>" . htmlspecialchars($order['payment_method']) . "</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><span class='badge badge-success'>Lunas (Paid)</span></td>
                </tr>
            </table>
            <div style='text-align: center; margin-top: 30px;'>
                <a href='" . APP_URL . "/pages/order-detail.php?id=" . $orderId . "' class='btn btn-accent'>📋 Lihat Detail Pesanan</a>
            </div>
        ";
        sendEmail($buyer['email'], $buyerSubject, $buyerBody);
    }

    if ($seller) {
        $sellerName = htmlspecialchars($seller['full_name'] ?: $seller['username']);
        $sellerSubject = "Pesanan Baru Masuk #" . $order['order_number'] . " - " . APP_NAME . " 🎮";
        $sellerGain = $order['total_price'] - $order['platform_fee'];
        $sellerBody = "
            <h2 class='email-title'>Ada Pesanan Baru! 🎉</h2>
            <p>Halo Penjual <strong>" . $sellerName . "</strong>,</p>
            <p>Pembayaran untuk pesanan <strong>#" . htmlspecialchars($order['order_number']) . "</strong> telah dikonfirmasi dan lunas.</p>
            <p>Silakan segera proses dan kirimkan pesanan untuk pembeli.</p>
            <div class='divider'></div>
            <h3 style='color: #ffffff;'>Ringkasan Pesanan:</h3>
            <table class='detail-table'>
                <tr>
                    <th>Produk</th>
                    <td>" . htmlspecialchars($order['product_name']) . " (x" . $order['quantity'] . ")</td>
                </tr>
                <tr>
                    <th>Pembeli</th>
                    <td>" . htmlspecialchars($buyer['username'] ?? 'User') . "</td>
                </tr>
                <tr style='font-weight: bold; color: #f59e0b;'>
                    <th>Pendapatan Bersih Anda</th>
                    <td><span class='text-highlight'>" . rupiah($sellerGain) . "</span></td>
                </tr>
            </table>
            <div style='text-align: center; margin-top: 30px;'>
                <a href='" . APP_URL . "/pages/order-detail.php?id=" . $orderId . "' class='btn btn-accent'>⚙️ Proses Pesanan Sekarang</a>
            </div>
        ";
        sendEmail($seller['email'], $sellerSubject, $sellerBody);
    }

    sendNotification($_SESSION['user_id'], 'Pembayaran Berhasil! ✅', "Order #{$order['order_number']} telah dibayar. Seller sedang memproses.", 'success', APP_URL . '/pages/order-detail.php?id=' . $orderId);
    sendNotification($order['seller_id'], 'Ada Order Baru! 🎉', "Order #{$order['order_number']} telah dibayar. Segera proses!", 'info', APP_URL . '/pages/order-detail.php?id=' . $orderId);
    
    setFlash('success', 'Pembayaran berhasil! Order sedang diproses. 🎉');
    redirect(APP_URL . '/pages/order-detail.php?id=' . $orderId);
}

// Generate QRIS baru jika belum ada
$qrisData = null;
if ($method === 'qris' || $method === 'QRIS') {
    $qrisData = generateQRIS($order['total_price'], $order['order_number']);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;max-width:800px;">
    <div style="text-align:center;margin-bottom:30px;">
        <h1 style="font-family:var(--font-head);font-size:28px;font-weight:800;">💳 Pembayaran</h1>
        <p style="color:var(--text-muted);">Order #<?= htmlspecialchars($order['order_number']) ?></p>
    </div>

    <!-- STATUS BANNER -->
    <?php if ($order['payment_status'] === 'paid'): ?>
    <div class="flash-message flash-success" style="text-align:center;font-size:16px;">
        ✅ Pembayaran Sudah Dikonfirmasi!
    </div>
    <?php endif; ?>

    <!-- ORDER SUMMARY -->
    <div class="card" style="margin-bottom:24px;">
        <div class="card-body">
            <h3 style="font-family:var(--font-head);font-size:16px;margin-bottom:16px;">📦 Ringkasan Pesanan</h3>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="color:var(--text-muted);">Produk</span>
                <span style="font-weight:700;"><?= htmlspecialchars($order['product_name']) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="color:var(--text-muted);">Jumlah</span>
                <span><?= $order['quantity'] ?>x</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="color:var(--text-muted);">Biaya Platform</span>
                <span><?= rupiah($order['platform_fee']) ?></span>
            </div>
            <hr style="border-color:var(--border);margin:12px 0;">
            <div style="display:flex;justify-content:space-between;font-family:var(--font-head);font-size:22px;font-weight:800;">
                <span>Total Bayar</span>
                <span style="color:var(--accent);"><?= rupiah($order['total_price']) ?></span>
            </div>
        </div>
    </div>

    <?php if ($order['payment_status'] !== 'paid'): ?>
        <?php if (strtolower($method) === 'saldo'): ?>
        <!-- SALDO PAYMENT -->
        <div class="card" style="margin-bottom:24px;">
            <div class="card-body" style="text-align:center; padding: 35px 25px;">
                <h3 style="font-family:var(--font-head); font-size:22px; margin-bottom:14px; font-weight:800;">💰 Pembayaran dengan Saldo Utama</h3>
                <p style="color:var(--text-muted); font-size:14px; margin-bottom:24px;">
                    Saldo Anda saat ini: <strong style="color:var(--gold); font-size:18px; font-family:var(--font-head);"><?= rupiah($user['balance']) ?></strong>
                </p>
                
                <?php if ($user['balance'] >= $order['total_price']): ?>
                    <div style="background:rgba(0,230,118,0.06); border:1px solid rgba(0,230,118,0.15); border-radius:10px; padding:18px; margin-bottom:28px;">
                        <span style="color:var(--success); font-weight:600; font-size:14px;">✅ Saldo Anda mencukupi untuk melakukan pembayaran ini.</span>
                    </div>
                    
                    <form method="POST">
                        <?= csrfInput() ?>
                        <button type="submit" name="pay_with_saldo" value="1" class="btn btn-primary btn-block btn-lg" style="justify-content:center;">
                            💳 Konfirmasi & Bayar Sekarang
                        </button>
                    </form>
                <?php else: ?>
                    <div style="background:rgba(255,71,87,0.06); border:1px solid rgba(255,71,87,0.15); border-radius:10px; padding:18px; margin-bottom:28px;">
                        <span style="color:var(--error); font-weight:600; font-size:14px;">❌ Saldo Anda tidak mencukupi untuk pembayaran ini.</span>
                    </div>
                    <div style="display:flex; gap:12px;">
                        <a href="<?= APP_URL ?>/pages/topup-balance.php" class="btn btn-gold btn-block" style="justify-content:center; text-decoration:none;">+ Isi Saldo</a>
                        <a href="<?= APP_URL ?>/pages/dashboard.php" class="btn btn-outline btn-block" style="justify-content:center; text-decoration:none;">Batal</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php elseif ($qrisData): ?>
        <!-- QRIS PAYMENT -->
        <div class="card" style="margin-bottom:24px;">
            <div class="card-body">
                <h3 style="font-family:var(--font-head);font-size:18px;margin-bottom:20px;text-align:center;">📱 Scan QRIS untuk Membayar</h3>
                
                <!-- QRIS BOX -->
                <div class="qris-box" style="border:3px solid #0066ff;">
                    <div class="qris-title">BoloTopup.ID</div>
                    <div style="font-size:11px;color:#666;margin-bottom:10px;">NMID: <?= QRIS_MERCHANT_ID ?></div>
                    <img src="<?= $qrisData['qr_image_url'] ?>" alt="QRIS Code" id="qrisImg">
                    <div class="qris-amount"><?= rupiah($order['total_price']) ?></div>
                    <div class="qris-timer" id="qrisTimer">Berlaku 60 menit</div>
                    <div style="font-size:11px;color:#999;margin-top:8px;">Scan dengan GoPay, OVO, Dana, ShopeePay, atau mobile banking manapun</div>
                </div>

                <div style="text-align:center;margin-top:20px;">
                    <p style="color:var(--text-muted);font-size:14px;margin-bottom:16px;">
                        ⚠️ Jangan tutup halaman ini sebelum pembayaran terkonfirmasi
                    </p>
                    
                    <!-- TOMBOL SIMULASI -->
                    <div style="background:rgba(255,170,0,0.1);border:1px solid var(--warning);border-radius:10px;padding:16px;margin-bottom:16px;">
                        <p style="color:var(--warning);font-size:13px;margin-bottom:12px;">
                            ⚠️ <strong>Mode Simulasi</strong> - Di produksi, pembayaran dikonfirmasi otomatis via webhook
                        </p>
                        <form method="POST">
                            <?= csrfInput() ?>
                            <button type="submit" name="simulate_pay" value="1" class="btn btn-success">
                                ✅ Simulasi: Konfirmasi Pembayaran
                            </button>
                        </form>
                    </div>

                    <a href="<?= APP_URL ?>/pages/dashboard.php" class="btn btn-outline">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- NON-QRIS PAYMENT INSTRUCTIONS -->
        <div class="card" style="margin-bottom:24px;">
            <div class="card-body">
                <h3 style="font-family:var(--font-head);font-size:18px;margin-bottom:20px;">🏦 Instruksi Pembayaran <?= htmlspecialchars($order['payment_method']) ?></h3>
                
                <div style="background:var(--bg-card2);border-radius:10px;padding:20px;margin-bottom:20px;">
                    <p style="color:var(--text-muted);margin-bottom:16px;">Transfer ke rekening berikut:</p>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                        <span>Bank</span>
                        <strong><?= htmlspecialchars($order['payment_method']) ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                        <span>No. Rekening</span>
                        <strong>1234-5678-9012</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                        <span>Atas Nama</span>
                        <strong>PT BoloTopup Indonesia</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:800;color:var(--accent);margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
                        <span>Jumlah Transfer</span>
                        <span><?= rupiah($order['total_price']) ?></span>
                    </div>
                    <div style="font-size:12px;color:var(--warning);margin-top:8px;">
                        ⚠️ Transfer tepat sesuai nominal di atas (termasuk angka unik)
                    </div>
                </div>

                <div style="background:rgba(255,170,0,0.1);border:1px solid var(--warning);border-radius:10px;padding:16px;margin-bottom:16px;">
                    <p style="color:var(--warning);font-size:13px;margin-bottom:12px;">
                        ⚠️ <strong>Mode Simulasi</strong>
                    </p>
                    <form method="POST">
                        <?= csrfInput() ?>
                        <button type="submit" name="simulate_pay" value="1" class="btn btn-success">
                            ✅ Simulasi: Konfirmasi Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- INSTRUKSI QRIS -->
    <?php if ($qrisData && $order['payment_status'] !== 'paid'): ?>
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;">
        <h4 style="font-family:var(--font-head);margin-bottom:12px;">ℹ️ Cara Pembayaran QRIS</h4>
        <ol style="color:var(--text-secondary);font-size:14px;line-height:2;padding-left:20px;">
            <li>Buka aplikasi dompet digital (GoPay, OVO, Dana, ShopeePay, dll.)</li>
            <li>Pilih menu "Scan QR" atau "QRIS"</li>
            <li>Arahkan kamera ke QR Code di atas</li>
            <li>Pastikan jumlah sudah sesuai, lalu konfirmasi pembayaran</li>
            <li>Tunggu konfirmasi otomatis (biasanya dalam hitungan detik)</li>
        </ol>
    </div>
    <?php endif; ?>
</div>

<script>
// Countdown timer QRIS (60 menit)
let timeLeft = 3600;
const timerEl = document.getElementById('qrisTimer');
if (timerEl) {
    const countdown = setInterval(() => {
        timeLeft--;
        const m = Math.floor(timeLeft / 60);
        const s = timeLeft % 60;
        timerEl.textContent = `Berlaku: ${m}:${s.toString().padStart(2,'0')}`;
        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerEl.textContent = '❌ QRIS kedaluwarsa';
            timerEl.style.color = 'red';
        }
    }, 1000);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>