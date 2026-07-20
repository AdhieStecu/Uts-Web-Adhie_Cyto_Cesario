<?php
// File: pages/dashboard.php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../inc/functions.php';
requireLogin();

$user = currentUser();
$orders = db()->fetchAll(
    "SELECT o.*, p.name as product_name FROM orders o JOIN products p ON o.product_id = p.id 
     WHERE o.buyer_id = ? ORDER BY o.created_at DESC LIMIT 10",
    'i', $_SESSION['user_id']
);

$totalOrders = db()->fetchOne("SELECT COUNT(*) as cnt FROM orders WHERE buyer_id = ?", 'i', $_SESSION['user_id'])['cnt'];
$completedOrders = db()->fetchOne("SELECT COUNT(*) as cnt FROM orders WHERE buyer_id = ? AND status = 'completed'", 'i', $_SESSION['user_id'])['cnt'];
$totalSpend = db()->fetchOne("SELECT COALESCE(SUM(total_price),0) as total FROM orders WHERE buyer_id = ? AND payment_status = 'paid'", 'i', $_SESSION['user_id'])['total'];

require_once __DIR__ . '/../inc/header.php';
?>

<div class="container">
    <div class="dashboard-layout">
        <!-- SIDEBAR -->
        <?php
        $activePage = 'dashboard';
        require_once __DIR__ . '/../inc/sidebar.php';
        ?>

        <!-- MAIN CONTENT -->
        <div>
            <h1 style="font-family:var(--font-head);font-size:26px;font-weight:800;margin-bottom:24px;">
                👋 Halo, <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>!
            </h1>

            <!-- STAT CARDS -->
            <div class="grid-3" style="margin-bottom:30px;">
                <div class="stat-card">
                    <span class="stat-icon">📦</span>
                    <div>
                        <div class="stat-value"><?= $totalOrders ?></div>
                        <div class="stat-label">Total Pesanan</div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">✅</span>
                    <div>
                        <div class="stat-value"><?= $completedOrders ?></div>
                        <div class="stat-label">Pesanan Selesai</div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">💰</span>
                    <div>
                        <div class="stat-value" style="font-size:16px;"><?= rupiah($totalSpend) ?></div>
                        <div class="stat-label">Total Belanja</div>
                    </div>
                </div>
            </div>

            <!-- RECENT ORDERS -->
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                        <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;">📋 Pesanan Terbaru</h3>
                        <a href="<?= APP_URL ?>/pages/orders.php" class="btn btn-outline btn-sm">Lihat Semua</a>
                    </div>

                    <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <span class="empty-icon">🛒</span>
                        <p>Belum ada pesanan</p>
                        <a href="<?= APP_URL ?>" class="btn btn-primary mt-20">Mulai Belanja</a>
                    </div>
                    <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Produk</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><code style="color:var(--accent);"><?= htmlspecialchars($order['order_number']) ?></code></td>
                                    <td><?= htmlspecialchars(excerpt($order['product_name'], 30)) ?></td>
                                    <td><strong><?= rupiah($order['total_price']) ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?= $order['status'] ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td style="color:var(--text-muted);"><?= timeAgo($order['created_at']) ?></td>
                                    <td>
                                        <a href="<?= APP_URL ?>/pages/order-detail.php?id=<?= $order['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
                                        <?php if ($order['payment_status'] === 'unpaid'): ?>
                                        <a href="<?= APP_URL ?>/pages/payment.php?order_id=<?= $order['id'] ?>&method=<?= $order['payment_method'] ?>" class="btn btn-primary btn-sm">Bayar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>