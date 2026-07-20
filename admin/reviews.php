<?php
// File: admin/reviews.php
$pageTitle = 'Kelola Ulasan';
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

// Seed mock data if empty
$count = db()->fetchOne("SELECT COUNT(*) as cnt FROM reviews")['cnt'];
if ($count == 0) {
    db()->insert("INSERT INTO reviews (order_id, product_id, buyer_id, rating, comment) VALUES (2, 9, 5, 5, 'Sangat cepat dan instan! Mantap.')");
}

$id = (int)($_GET['id'] ?? 0);
$action = sanitize($_GET['action'] ?? 'list');

// Process Delete
if ($action === 'delete' && $id) {
    db()->execute("DELETE FROM reviews WHERE id = ?", 'i', $id);
    setFlash('success', 'Ulasan berhasil dihapus.');
    redirect(APP_URL . '/admin/reviews.php');
}

$reviews = db()->fetchAll(
    "SELECT r.*, u.username as buyer_name, p.name as product_name FROM reviews r 
     JOIN users u ON r.buyer_id = u.id 
     JOIN products p ON r.product_id = p.id 
     ORDER BY r.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ulasan Pembeli | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.2.1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js?v=1.2.1" defer></script>
</head>
<body>
<div class="admin-topbar">
    <div class="brand">⚡ BoloTopup Admin</div>
    <div class="top-actions">
        <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="<?= APP_URL ?>" class="btn btn-outline btn-sm">🌐 Lihat Website</a>
        <a href="<?= APP_URL ?>/pages/logout.php" class="btn btn-danger btn-sm">Keluar</a>
    </div>
</div>
<div class="admin-wrap">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="brand" style="padding:20px;">⚡ Admin Panel</div>
        <a href="<?= APP_URL ?>/admin/index.php">📊 Dashboard</a>
        <a href="<?= APP_URL ?>/admin/products.php">🎮 Produk</a>
        <a href="<?= APP_URL ?>/admin/categories.php">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/tinjau-seller.php">🔍 Tinjau Seller</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php" class="active">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
        <a href="<?= APP_URL ?>/admin/backup.php">🗄️ Backup Database</a>
    </aside>

    <div class="admin-content">
        <?php showFlash(); ?>
        
        <div class="admin-header">
            <h1 class="admin-title">⭐ Kelola Ulasan Pembeli</h1>
            <span style="color:var(--text-muted);font-size:14px;">Total Ulasan: <?= count($reviews) ?></span>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Produk</th>
                                <th>Pembeli</th>
                                <th>Rating</th>
                                <th>Komentar</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $rev): ?>
                            <tr>
                                <td>#<?= $rev['id'] ?></td>
                                <td><strong><?= htmlspecialchars($rev['product_name']) ?></strong></td>
                                <td><code><?= htmlspecialchars($rev['buyer_name']) ?></code></td>
                                <td style="color:var(--gold); font-size: 16px;">
                                    <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rev['rating'] ? '★' : '☆';
                                        }
                                    ?>
                                </td>
                                <td><em>"<?= htmlspecialchars($rev['comment'] ?: 'Tidak ada komentar') ?>"</em></td>
                                <td><?= timeAgo($rev['created_at']) ?></td>
                                <td>
                                    <a href="?action=delete&id=<?= $rev['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
