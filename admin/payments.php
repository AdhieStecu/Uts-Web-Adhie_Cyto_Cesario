<?php
// File: admin/payments.php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;
$status = sanitize($_GET['status'] ?? '');

$where = $status ? "WHERE pay.status = ?" : '';
$params = $status ? [$status] : [];
$types = $status ? 's' : '';

$total = db()->fetchOne("SELECT COUNT(*) as cnt FROM payments pay $where", $types ?: null, ...$params)['cnt'];
$payments = db()->fetchAll(
    "SELECT pay.*, o.order_number, u.username 
     FROM payments pay 
     JOIN orders o ON pay.order_id = o.id 
     JOIN users u ON o.buyer_id = u.id 
     $where ORDER BY pay.created_at DESC LIMIT $perPage OFFSET $offset",
    $types ?: null, ...$params
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pembayaran | Admin</title>
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
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/tinjau-seller.php">🔍 Tinjau Seller</a>
        <a href="<?= APP_URL ?>/admin/payments.php" class="active">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
        <a href="<?= APP_URL ?>/admin/backup.php">🗄️ Backup Database</a>
    </aside>
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">💳 Kelola Pembayaran</h1>
        </div>

        <!-- FILTER -->
        <div style="display:flex;gap:8px;margin-bottom:20px;">
            <?php foreach ([''=>'Semua','pending'=>'Pending','success'=>'Sukses','failed'=>'Gagal','expired'=>'Expired'] as $s => $label): ?>
            <a href="?status=<?= $s ?>" class="btn <?= $status===$s?'btn-primary':'btn-outline' ?> btn-sm"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Order #</th><th>User</th><th>Metode</th><th>Jumlah</th><th>Status</th><th>QRIS</th><th>Waktu</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pay): ?>
                            <tr>
                                <td>#<?= $pay['id'] ?></td>
                                <td><code style="color:var(--accent);font-size:12px;"><?= $pay['order_number'] ?></code></td>
                                <td><?= htmlspecialchars($pay['username']) ?></td>
                                <td><?= $pay['payment_method'] ?></td>
                                <td><strong style="color:var(--accent);"><?= rupiah($pay['amount']) ?></strong></td>
                                <td>
                                    <?php $sc = ['success'=>'status-completed','pending'=>'status-pending','failed'=>'status-cancelled','expired'=>'status-cancelled']; ?>
                                    <span class="status-badge <?= $sc[$pay['status']] ?? '' ?>"><?= ucfirst($pay['status']) ?></span>
                                </td>
                                <td>
                                    <?php if ($pay['qris_image_url']): ?>
                                    <a href="<?= htmlspecialchars($pay['qris_image_url']) ?>" target="_blank" class="btn btn-outline btn-sm">📱 QR</a>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td style="color:var(--text-muted);font-size:13px;"><?= timeAgo($pay['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?= paginate($total, $perPage, $page, APP_URL . '/admin/payments.php?status=' . $status) ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>