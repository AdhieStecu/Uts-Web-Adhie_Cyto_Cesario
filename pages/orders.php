<?php
// File: pages/orders.php
$pageTitle = 'Pesanan Saya';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$status = sanitize($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = "WHERE o.buyer_id = ?";
$params = [$_SESSION['user_id']];
$types = 'i';
if ($status) {
    $where .= " AND o.status = ?";
    $params[] = $status;
    $types .= 's';
}

$total = db()->fetchOne("SELECT COUNT(*) as cnt FROM orders o $where", $types, ...$params)['cnt'];
$orders = db()->fetchAll(
    "SELECT o.*, p.name as product_name, p.image as product_image 
     FROM orders o JOIN products p ON o.product_id = p.id 
     $where ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset",
    $types, ...$params
);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;">
    <div class="dashboard-layout">
        <!-- SIDEBAR -->
        <?php
        $activePage = 'orders';
        require_once __DIR__ . '/../includes/sidebar.php';
        ?>

        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                <h1 style="font-family:var(--font-head);font-size:26px;font-weight:800;">📦 Pesanan Saya</h1>
            </div>

            <!-- FILTER STATUS -->
            <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;">
                <?php
                $statuses = [''=>'Semua', 'pending'=>'Pending', 'processing'=>'Diproses', 'completed'=>'Selesai', 'cancelled'=>'Dibatalkan'];
                foreach ($statuses as $s => $label):
                    $active = $status === $s ? 'btn-primary' : 'btn-outline';
                ?>
                <a href="?status=<?= $s ?>" class="btn <?= $active ?> btn-sm"><?= $label ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($orders)): ?>
            <div class="empty-state">
                <span class="empty-icon">📦</span>
                <p>Tidak ada pesanan<?= $status ? " dengan status \"$status\"" : '' ?>.</p>
                <a href="<?= APP_URL ?>" class="btn btn-primary mt-20">🎮 Mulai Belanja</a>
            </div>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <?php foreach ($orders as $order): ?>
                <div class="card">
                    <div class="card-body">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
                            <div>
                                <code style="color:var(--accent);font-size:13px;"><?= $order['order_number'] ?></code>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;"><?= timeAgo($order['created_at']) ?></div>
                            </div>
                            <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                        </div>
                        <div style="display:flex;gap:14px;align-items:center;">
                            <div style="width:60px;height:60px;background:var(--bg-card2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;">
                                🎮
                            </div>
                            <div style="flex:1;">
                                <div style="font-weight:700;"><?= htmlspecialchars($order['product_name']) ?></div>
                                <div style="font-size:13px;color:var(--text-muted);">Qty: <?= $order['quantity'] ?> · <?= htmlspecialchars($order['payment_method']) ?></div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-family:var(--font-head);font-size:18px;font-weight:800;color:var(--accent);"><?= rupiah($order['total_price']) ?></div>
                                <div style="display:flex;gap:8px;margin-top:8px;justify-content:flex-end;">
                                    <a href="<?= APP_URL ?>/pages/order-detail.php?id=<?= $order['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
                                    <?php if ($order['payment_status'] === 'unpaid'): ?>
                                    <a href="<?= APP_URL ?>/pages/payment.php?order_id=<?= $order['id'] ?>&method=<?= $order['payment_method'] ?>" class="btn btn-primary btn-sm">💳 Bayar</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?= paginate($total, $perPage, $page, APP_URL . '/pages/orders.php?status=' . $status) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>