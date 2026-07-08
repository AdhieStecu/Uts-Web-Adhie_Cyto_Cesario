<?php
// File: pages/order-detail.php
$pageTitle = 'Detail Pesanan';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$order = db()->fetchOne(
    "SELECT o.*, p.name as product_name, p.description as product_desc, p.image as product_image, p.delivery_type,
            u.username as seller_name
     FROM orders o 
     JOIN products p ON o.product_id = p.id
     JOIN users u ON o.seller_id = u.id
     WHERE o.id = ? AND o.buyer_id = ?",
    'ii', $id, $_SESSION['user_id']
);

if (!$order) {
    setFlash('error', 'Pesanan tidak ditemukan.');
    redirect(APP_URL . '/pages/orders.php');
}

$payment = db()->fetchOne("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", 'i', $id);
$review = db()->fetchOne("SELECT * FROM reviews WHERE order_id = ?", 'i', $id);

// Handle review submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token tidak valid.');
    } elseif ($order['status'] !== 'completed') {
        setFlash('error', 'Hanya pesanan selesai yang bisa diulas.');
    } elseif ($review) {
        setFlash('error', 'Kamu sudah memberikan ulasan.');
    } else {
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $comment = sanitize($_POST['comment'] ?? '');
        db()->insert(
            "INSERT INTO reviews (order_id, product_id, buyer_id, rating, comment) VALUES (?, ?, ?, ?, ?)",
            'iiiis', $id, $order['product_id'], $_SESSION['user_id'], $rating, $comment
        );
        // Update product rating
        $avgRating = db()->fetchOne("SELECT AVG(rating) as avg FROM reviews WHERE product_id = ?", 'i', $order['product_id'])['avg'];
        db()->execute("UPDATE products SET rating = ? WHERE id = ?", 'di', $avgRating, $order['product_id']);
        setFlash('success', 'Ulasan berhasil dikirim! ⭐');
        redirect(currentUrl());
    }
}

// Handle selesaikan pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_order'])) {
    if ($order['status'] === 'processing') {
        db()->execute("UPDATE orders SET status = 'completed', completed_at = NOW() WHERE id = ?", 'i', $id);
        // Transfer dana ke seller
        $sellerAmount = $order['total_price'] - $order['platform_fee'];
        db()->execute("UPDATE users SET balance = balance + ? WHERE id = ?", 'di', $sellerAmount, $order['seller_id']);
        
        // Ambil data detail Pembeli dan Penjual
        $buyer = db()->fetchOne("SELECT email, username, full_name FROM users WHERE id = ?", 'i', $order['buyer_id']);
        $seller = db()->fetchOne("SELECT email, username, full_name FROM users WHERE id = ?", 'i', $order['seller_id']);

        if ($seller) {
            $sellerName = htmlspecialchars($seller['full_name'] ?: $seller['username']);
            $sellerSubject = "Pesanan Selesai & Dana Cair #" . $order['order_number'] . " - " . APP_NAME . " 💰";
            $sellerBody = "
                <h2 class='email-title'>Pesanan Selesai & Saldo Cair! 💰</h2>
                <p>Halo Penjual <strong>" . $sellerName . "</strong>,</p>
                <p>Pesanan dengan nomor <strong>#" . htmlspecialchars($order['order_number']) . "</strong> telah dikonfirmasi selesai oleh pembeli.</p>
                <p>Dana bersih sebesar <strong>" . rupiah($sellerAmount) . "</strong> telah berhasil ditambahkan ke saldo akun " . htmlspecialchars(APP_NAME) . " Anda.</p>
                <div class='divider'></div>
                <h3 style='color: #ffffff;'>Rincian Saldo Masuk:</h3>
                <table class='detail-table'>
                    <tr>
                        <th>No. Pesanan</th>
                        <td><code>" . htmlspecialchars($order['order_number']) . "</code></td>
                    </tr>
                    <tr>
                        <th>Produk</th>
                        <td>" . htmlspecialchars($order['product_name']) . " (x" . $order['quantity'] . ")</td>
                    </tr>
                    <tr style='font-weight: bold; color: #f59e0b;'>
                        <th>Dana Diterima</th>
                        <td><span class='text-highlight'>" . rupiah($sellerAmount) . "</span></td>
                    </tr>
                </table>
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='" . APP_URL . "/pages/dashboard.php' class='btn btn-accent'>💰 Lihat Saldo Akun</a>
                </div>
            ";
            sendEmail($seller['email'], $sellerSubject, $sellerBody);
        }

        if ($buyer) {
            $buyerName = htmlspecialchars($buyer['full_name'] ?: $buyer['username']);
            $buyerSubject = "Pesanan Anda Selesai #" . $order['order_number'] . " - Terima Kasih! 🎉";
            $buyerBody = "
                <h2 class='email-title'>Pesanan Anda Selesai! 🎉</h2>
                <p>Halo <strong>" . $buyerName . "</strong>,</p>
                <p>Terima kasih telah berbelanja di <strong>" . htmlspecialchars(APP_NAME) . "</strong>! Konfirmasi penerimaan pesanan <strong>#" . htmlspecialchars($order['order_number']) . "</strong> telah berhasil kami terima.</p>
                <p>Kami harap Anda puas dengan layanan kami. Jangan lupa untuk memberikan ulasan bintang 5 ya!</p>
                <div class='divider'></div>
                <h3 style='color: #ffffff;'>Detail Transaksi Selesai:</h3>
                <table class='detail-table'>
                    <tr>
                        <th>No. Pesanan</th>
                        <td><code>" . htmlspecialchars($order['order_number']) . "</code></td>
                    </tr>
                    <tr>
                        <th>Produk</th>
                        <td>" . htmlspecialchars($order['product_name']) . " (x" . $order['quantity'] . ")</td>
                    </tr>
                    <tr>
                        <th>Total Belanja</th>
                        <td>" . rupiah($order['total_price']) . "</td>
                    </tr>
                </table>
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='" . APP_URL . "/pages/order-detail.php?id=" . $id . "' class='btn btn-accent'>⭐ Tulis Ulasan Produk</a>
                </div>
            ";
            sendEmail($buyer['email'], $buyerSubject, $buyerBody);
        }

        sendNotification($order['seller_id'], 'Pesanan Selesai! 💰', "Pesanan #{$order['order_number']} telah selesai. Dana telah ditransfer.", 'success');
        setFlash('success', 'Pesanan telah diselesaikan! Dana seller telah ditransfer. 🎉');
        redirect(currentUrl());
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;max-width:800px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <h1 style="font-family:var(--font-head);font-size:26px;font-weight:800;">📋 Detail Pesanan</h1>
        <a href="<?= APP_URL ?>/pages/orders.php" class="btn btn-outline">← Kembali</a>
    </div>

    <!-- STATUS TIMELINE -->
    <?php
    $steps = ['pending' => 1, 'paid' => 2, 'processing' => 3, 'completed' => 4];
    $currentStep = $steps[$order['status']] ?? 1;
    ?>
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <div style="display:flex;justify-content:space-between;align-items:center;position:relative;">
                <div style="position:absolute;top:20px;left:10%;right:10%;height:2px;background:var(--border);z-index:0;"></div>
                <?php foreach (['Menunggu'=>'⏳','Dibayar'=>'💳','Diproses'=>'⚙️','Selesai'=>'✅'] as $label => $icon): ?>
                <?php $i = array_search($label, array_keys(['Menunggu'=>1,'Dibayar'=>2,'Diproses'=>3,'Selesai'=>4])) + 1; ?>
                <div style="text-align:center;position:relative;z-index:1;">
                    <div style="width:40px;height:40px;border-radius:50%;background:<?= $currentStep >= $i ? 'var(--primary)' : 'var(--bg-card2)' ?>;border:2px solid <?= $currentStep >= $i ? 'var(--primary)' : 'var(--border)' ?>;display:flex;align-items:center;justify-content:center;font-size:18px;margin:0 auto 8px;">
                        <?= $icon ?>
                    </div>
                    <div style="font-size:12px;font-weight:600;color:<?= $currentStep >= $i ? 'var(--accent)' : 'var(--text-muted)' ?>;"><?= $label ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ORDER INFO -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <h3 style="font-family:var(--font-head);font-size:16px;margin-bottom:16px;">📦 Info Pesanan</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:14px;">
                <div><span style="color:var(--text-muted);">No. Order</span><br><code style="color:var(--accent);"><?= $order['order_number'] ?></code></div>
                <div><span style="color:var(--text-muted);">Tanggal</span><br><?= date('d M Y H:i', strtotime($order['created_at'])) ?></div>
                <div><span style="color:var(--text-muted);">Produk</span><br><strong><?= htmlspecialchars($order['product_name']) ?></strong></div>
                <div><span style="color:var(--text-muted);">Seller</span><br><?= htmlspecialchars($order['seller_name']) ?></div>
                <div><span style="color:var(--text-muted);">Metode Bayar</span><br><?= $order['payment_method'] ?></div>
                <div><span style="color:var(--text-muted);">Status Bayar</span><br>
                    <span class="status-badge status-<?= $order['payment_status'] === 'paid' ? 'completed' : 'pending' ?>">
                        <?= $order['payment_status'] === 'paid' ? '✅ Lunas' : '⏳ Belum Bayar' ?>
                    </span>
                </div>
            </div>
            <hr style="border-color:var(--border);margin:16px 0;">
            <div style="display:flex;justify-content:space-between;font-family:var(--font-head);font-size:20px;font-weight:800;">
                <span>Total Pembayaran</span>
                <span style="color:var(--accent);"><?= rupiah($order['total_price']) ?></span>
            </div>
        </div>
    </div>

    <!-- AKSI -->
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <?php if ($order['payment_status'] === 'unpaid'): ?>
        <a href="<?= APP_URL ?>/pages/payment.php?order_id=<?= $order['id'] ?>&method=<?= $order['payment_method'] ?>" class="btn btn-primary">💳 Bayar Sekarang</a>
        <?php endif; ?>
        <?php if ($order['status'] === 'processing'): ?>
        <form method="POST" style="display:inline;">
            <?= csrfInput() ?>
            <button type="button" name="complete_order" value="1" class="btn btn-success" data-confirm="Konfirmasi pesanan sudah diterima?">
                ✅ Pesanan Diterima
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- ULASAN -->
    <?php if ($order['status'] === 'completed'): ?>
    <div class="card">
        <div class="card-body">
            <h3 style="font-family:var(--font-head);font-size:16px;margin-bottom:16px;">⭐ Ulasan Produk</h3>
            <?php if ($review): ?>
            <div style="background:var(--bg-card2);border-radius:10px;padding:16px;">
                <div style="font-size:20px;margin-bottom:8px;"><?= renderStars($review['rating']) ?></div>
                <p style="color:var(--text-secondary);"><?= htmlspecialchars($review['comment']) ?></p>
                <div style="font-size:12px;color:var(--text-muted);margin-top:8px;"><?= timeAgo($review['created_at']) ?></div>
            </div>
            <?php else: ?>
            <form method="POST">
                <?= csrfInput() ?>
                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label style="cursor:pointer;font-size:28px;">
                            <input type="radio" name="rating" value="<?= $i ?>" required style="display:none;">
                            <span class="star-label" data-val="<?= $i ?>">☆</span>
                        </label>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Komentar</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Ceritakan pengalaman belanja kamu..."></textarea>
                </div>
                <button type="submit" name="submit_review" value="1" class="btn btn-primary">⭐ Kirim Ulasan</button>
            </form>
            <script>
            document.querySelectorAll('.star-label').forEach((star, i, stars) => {
                star.addEventListener('click', () => {
                    stars.forEach((s, j) => s.textContent = j <= i ? '⭐' : '☆');
                    stars[i].previousElementSibling.checked = true;
                });
            });
            </script>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>