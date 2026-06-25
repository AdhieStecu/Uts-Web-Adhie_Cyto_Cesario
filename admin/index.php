<?php
// File: admin/index.php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Stats
$totalUsers = db()->fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role = 'user'")['cnt'];
$totalProducts = db()->fetchOne("SELECT COUNT(*) as cnt FROM products")['cnt'];
$totalOrders = db()->fetchOne("SELECT COUNT(*) as cnt FROM orders")['cnt'];
$totalRevenue = db()->fetchOne("SELECT COALESCE(SUM(platform_fee),0) as total FROM orders WHERE payment_status = 'paid'")['total'];
$pendingOrders = db()->fetchOne("SELECT COUNT(*) as cnt FROM orders WHERE status = 'pending'")['cnt'];
$recentOrders = db()->fetchAll("SELECT o.*, u.username, p.name as product_name FROM orders o JOIN users u ON o.buyer_id = u.id JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js" defer></script>
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
        <a href="<?= APP_URL ?>/admin/index.php" class="active">📊 Dashboard</a>
        <a href="<?= APP_URL ?>/admin/products.php">🎮 Produk</a>
        <a href="<?= APP_URL ?>/admin/categories.php">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
    </aside>

    <!-- CONTENT -->
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">📊 Dashboard</h1>
            <span style="color:var(--text-muted);font-size:14px;">📅 <?= date('d F Y, H:i') ?> WIB</span>
        </div>

        <!-- STAT CARDS -->
        <div class="grid-4" style="margin-bottom:30px;">
            <div class="stat-card" style="border-color:rgba(0,102,255,0.3);">
                <span class="stat-icon">👥</span>
                <div>
                    <div class="stat-value"><?= number_format($totalUsers) ?></div>
                    <div class="stat-label">Total User</div>
                </div>
            </div>
            <div class="stat-card" style="border-color:rgba(0,229,255,0.3);">
                <span class="stat-icon">🎮</span>
                <div>
                    <div class="stat-value"><?= number_format($totalProducts) ?></div>
                    <div class="stat-label">Total Produk</div>
                </div>
            </div>
            <div class="stat-card" style="border-color:rgba(255,214,0,0.3);">
                <span class="stat-icon">📦</span>
                <div>
                    <div class="stat-value"><?= number_format($totalOrders) ?></div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
            </div>
            <div class="stat-card" style="border-color:rgba(0,230,118,0.3);">
                <span class="stat-icon">💰</span>
                <div>
                    <div class="stat-value" style="font-size:16px;"><?= rupiah($totalRevenue) ?></div>
                    <div class="stat-label">Revenue Platform</div>
                </div>
            </div>
        </div>

        <!-- PENDING ALERT -->
        <?php if ($pendingOrders > 0): ?>
        <div class="flash-message flash-warning" style="margin-bottom:24px;">
            ⚠️ Ada <strong><?= $pendingOrders ?></strong> pesanan pending yang perlu diproses.
            <a href="<?= APP_URL ?>/admin/orders.php?status=pending" style="color:var(--warning);font-weight:700;">Lihat sekarang →</a>
        </div>
        <?php endif; ?>

        <!-- QUICK ACTIONS -->
        <div style="display:flex;gap:12px;margin-bottom:30px;flex-wrap:wrap;">
            <a href="<?= APP_URL ?>/admin/products.php?action=add" class="btn btn-primary">+ Tambah Produk</a>
            <a href="<?= APP_URL ?>/admin/import_products.php" class="btn btn-outline">📥 Import Produk</a>
            <a href="<?= APP_URL ?>/admin/categories.php?action=add" class="btn btn-outline">+ Tambah Kategori</a>
            <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-outline">👥 Kelola User</a>
            <a href="<?= APP_URL ?>/admin/orders.php?status=pending" class="btn btn-secondary">⏳ Order Pending</a>
        </div>

        <!-- RECENT ORDERS -->
        <div class="card">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;">📋 Pesanan Terbaru</h3>
                    <a href="<?= APP_URL ?>/admin/orders.php" class="btn btn-outline btn-sm">Semua Pesanan</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Pembeli</th>
                                <th>Produk</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><code style="color:var(--accent);font-size:12px;"><?= $order['order_number'] ?></code></td>
                                <td><?= htmlspecialchars($order['username']) ?></td>
                                <td><?= htmlspecialchars(excerpt($order['product_name'], 25)) ?></td>
                                <td><strong><?= rupiah($order['total_price']) ?></strong></td>
                                <td><?= $order['payment_method'] ?></td>
                                <td>
                                    <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                                </td>
                                <td style="color:var(--text-muted);font-size:13px;"><?= timeAgo($order['created_at']) ?></td>
                                <td>
                                    <a href="<?= APP_URL ?>/admin/orders.php?action=view&id=<?= $order['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
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