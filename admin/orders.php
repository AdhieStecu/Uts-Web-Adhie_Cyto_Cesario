<?php
// File: admin/orders.php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$action = sanitize($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);
$status = sanitize($_GET['status'] ?? '');

// Update status order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = sanitize($_POST['status'] ?? '');
    $orderId = (int)($_POST['order_id'] ?? 0);
    db()->execute("UPDATE orders SET status = ? WHERE id = ?", 'si', $newStatus, $orderId);
    if ($newStatus === 'completed') {
        $order = db()->fetchOne("SELECT * FROM orders WHERE id = ?", 'i', $orderId);
        if ($order) {
            $sellerAmount = $order['total_price'] - $order['platform_fee'];
            db()->execute("UPDATE users SET balance = balance + ? WHERE id = ?", 'di', $sellerAmount, $order['seller_id']);
            sendNotification($order['buyer_id'], 'Pesanan Selesai ✅', "Pesanan #{$order['order_number']} telah selesai.", 'success');
            sendNotification($order['seller_id'], 'Dana Diterima 💰', "Dana " . rupiah($sellerAmount) . " telah masuk ke saldo kamu.", 'success');
        }
    }
    setFlash('success', 'Status order berhasil diupdate.');
    redirect(APP_URL . '/admin/orders.php');
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
$types = '';
if ($status) { $where = "WHERE o.status = ?"; $params[] = $status; $types = 's'; }

$total = db()->fetchOne("SELECT COUNT(*) as cnt FROM orders o $where", $types ?: null, ...$params)['cnt'];
$orders = db()->fetchAll(
    "SELECT o.*, u.username as buyer_name, p.name as product_name 
     FROM orders o JOIN users u ON o.buyer_id = u.id JOIN products p ON o.product_id = p.id 
     $where ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset",
    $types ?: null, ...$params
);

$viewOrder = null;
if ($action === 'view' && $id) {
    $viewOrder = db()->fetchOne(
        "SELECT o.*, u.username as buyer_name, u.email as buyer_email, p.name as product_name, s.username as seller_name 
         FROM orders o JOIN users u ON o.buyer_id = u.id JOIN products p ON o.product_id = p.id JOIN users s ON o.seller_id = s.id
         WHERE o.id = ?", 'i', $id
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.2.1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js?v=1.2.1" defer></script>
</head>
<body>
<div class="admin-topbar">
    <div class="brand">⚡ BoloTopup Admin</div>
    <div style="display:flex;gap:12px;">
        <a href="<?= APP_URL ?>" class="btn btn-outline btn-sm">🌐 Website</a>
        <a href="<?= APP_URL ?>/pages/logout.php" class="btn btn-danger btn-sm">Keluar</a>
    </div>
</div>
<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="brand" style="padding:20px;">⚡ Admin Panel</div>
        <a href="<?= APP_URL ?>/admin/index.php">📊 Dashboard</a>
        <a href="<?= APP_URL ?>/admin/products.php">🎮 Produk</a>
        <a href="<?= APP_URL ?>/admin/categories.php">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php" class="active">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/tinjau-seller.php">🔍 Tinjau Seller</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
        <a href="<?= APP_URL ?>/admin/backup.php">🗄️ Backup Database</a>
    </aside>
    <div class="admin-content">
        <?php showFlash(); ?>

        <?php if ($viewOrder): ?>
        <!-- DETAIL ORDER -->
        <div class="admin-header">
            <h1 class="admin-title">📋 Detail Order</h1>
            <a href="<?= APP_URL ?>/admin/orders.php" class="btn btn-outline">← Kembali</a>
        </div>
        <div class="grid-2" style="gap:24px;">
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head);margin-bottom:16px;">Info Order</h3>
                    <table style="width:100%;font-size:14px;border-collapse:collapse;">
                        <?php foreach ([
                            'No. Order' => $viewOrder['order_number'],
                            'Pembeli' => $viewOrder['buyer_name'] . ' (' . $viewOrder['buyer_email'] . ')',
                            'Seller' => $viewOrder['seller_name'],
                            'Produk' => $viewOrder['product_name'],
                            'Jumlah' => $viewOrder['quantity'],
                            'Total' => rupiah($viewOrder['total_price']),
                            'Fee Platform' => rupiah($viewOrder['platform_fee']),
                            'Metode Bayar' => $viewOrder['payment_method'],
                            'Status Bayar' => $viewOrder['payment_status'],
                            'Tanggal' => date('d M Y H:i', strtotime($viewOrder['created_at'])),
                        ] as $label => $val): ?>
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:10px 0;color:var(--text-muted);width:40%;"><?= $label ?></td>
                            <td style="padding:10px 0;font-weight:600;"><?= htmlspecialchars($val) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head);margin-bottom:16px;">Update Status</h3>
                    <div style="margin-bottom:16px;">
                        Status saat ini: <span class="status-badge status-<?= $viewOrder['status'] ?>"><?= ucfirst($viewOrder['status']) ?></span>
                    </div>
                    <form method="POST">
                        <?= csrfInput() ?>
                        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                        <div class="form-group">
                            <label class="form-label">Status Baru</label>
                            <select name="status" class="form-control">
                                <option value="pending" <?= $viewOrder['status']==='pending'?'selected':'' ?>>⏳ Pending</option>
                                <option value="processing" <?= $viewOrder['status']==='processing'?'selected':'' ?>>⚙️ Diproses</option>
                                <option value="shipped" <?= $viewOrder['status']==='shipped'?'selected':'' ?>>🚚 Dikirim</option>
                                <option value="completed" <?= $viewOrder['status']==='completed'?'selected':'' ?>>✅ Selesai</option>
                                <option value="cancelled" <?= $viewOrder['status']==='cancelled'?'selected':'' ?>>❌ Dibatalkan</option>
                                <option value="refunded" <?= $viewOrder['status']==='refunded'?'selected':'' ?>>💸 Refund</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" value="1" class="btn btn-primary btn-block">💾 Update Status</button>
                    </form>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- LIST ORDERS -->
        <div class="admin-header">
            <h1 class="admin-title">📦 Kelola Pesanan</h1>
            <span style="color:var(--text-muted);font-size:14px;">Total: <?= $total ?> pesanan</span>
        </div>

        <!-- FILTER STATUS & EXPORT -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php foreach ([''=>'Semua','pending'=>'Pending','processing'=>'Diproses','shipped'=>'Dikirim','completed'=>'Selesai','cancelled'=>'Dibatalkan'] as $s => $label): ?>
                <a href="?status=<?= $s ?>" class="btn <?= $status===$s?'btn-primary':'btn-outline' ?> btn-sm"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="export_orders.php?format=excel&status=<?= $status ?>" class="btn btn-success btn-sm" target="_blank">📊 Export Excel</a>
                <a href="export_orders.php?format=word&status=<?= $status ?>" class="btn btn-info btn-sm" target="_blank">📝 Export Word</a>
                <a href="export_orders.php?format=pdf&status=<?= $status ?>" class="btn btn-danger btn-sm" target="_blank">📕 Export PDF</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Order #</th><th>Pembeli</th><th>Produk</th><th>Total</th><th>Metode</th><th>Status</th><th>Bayar</th><th>Waktu</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><code style="color:var(--accent);font-size:12px;"><?= $order['order_number'] ?></code></td>
                                <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                                <td><?= htmlspecialchars(excerpt($order['product_name'], 25)) ?></td>
                                <td><strong><?= rupiah($order['total_price']) ?></strong></td>
                                <td><?= $order['payment_method'] ?></td>
                                <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                <td><span class="status-badge <?= $order['payment_status']==='paid'?'status-completed':'status-pending' ?>"><?= $order['payment_status'] ?></span></td>
                                <td style="color:var(--text-muted);font-size:13px;"><?= timeAgo($order['created_at']) ?></td>
                                <td><a href="?action=view&id=<?= $order['id'] ?>" class="btn btn-outline btn-sm">Detail</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?= paginate($total, $perPage, $page, APP_URL . '/admin/orders.php?status=' . $status) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>