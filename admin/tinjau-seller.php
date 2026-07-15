<?php
// File: admin/tinjau-seller.php
$pageTitle = 'Tinjau Seller';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$sellerId = (int)($_GET['seller_id'] ?? 0);

if ($sellerId) {
    // Fetch Selected Seller
    $seller = db()->fetchOne("SELECT * FROM users WHERE id = ?", 'i', $sellerId);
    if (!$seller) {
        setFlash('error', 'Seller tidak ditemukan.');
        redirect(APP_URL . '/admin/tinjau-seller.php');
    }

    // Stats
    // 1. Released Earnings (Dana Cair)
    $releasedEarnings = db()->fetchOne(
        "SELECT COALESCE(SUM(total_price - platform_fee), 0) as total FROM orders WHERE seller_id = ? AND status = 'completed' AND escrow_status = 'released'",
        'i', $sellerId
    )['total'];

    // 2. Pending Escrow (Pemasukan Pending di Admin)
    $pendingEscrow = db()->fetchOne(
        "SELECT COALESCE(SUM(escrow_amount), 0) as total FROM orders WHERE seller_id = ? AND escrow_status = 'held'",
        'i', $sellerId
    )['total'];

    // 3. Sales Rating
    $ratingStats = db()->fetchOne(
        "SELECT COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as cnt
         FROM reviews r
         JOIN products p ON r.product_id = p.id
         WHERE p.seller_id = ?",
        'i', $sellerId
    );

    // 4. Products List
    $products = db()->fetchAll("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC", 'i', $sellerId);

    // 5. Chart Sales Data (last 30 days)
    $rawSales = db()->fetchAll(
        "SELECT DATE(created_at) as sale_date, SUM(total_price - platform_fee) as daily_revenue, COUNT(*) as daily_sales
         FROM orders
         WHERE seller_id = ? AND status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY DATE(created_at)
         ORDER BY sale_date ASC",
        'i', $sellerId
    );

    $indexedRaw = [];
    foreach ($rawSales as $row) {
        $indexedRaw[$row['sale_date']] = [
            'revenue' => (float)$row['daily_revenue'],
            'count' => (int)$row['daily_sales']
        ];
    }

    $labels = [];
    $revenuePoints = [];
    $countPoints = [];

    for ($i = 29; $i >= 0; $i--) {
        $dateStr = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d M', strtotime($dateStr));
        if (isset($indexedRaw[$dateStr])) {
            $revenuePoints[] = $indexedRaw[$dateStr]['revenue'];
            $countPoints[] = $indexedRaw[$dateStr]['count'];
        } else {
            $revenuePoints[] = 0.0;
            $countPoints[] = 0;
        }
    }
} else {
    // List all sellers (users with role = 'seller' OR who have products)
    $sellers = db()->fetchAll(
        "SELECT DISTINCT u.id, u.username, u.email, u.role, u.balance,
                (SELECT COUNT(*) FROM products WHERE seller_id = u.id) as product_count,
                (SELECT COALESCE(AVG(rating), 0) FROM products WHERE seller_id = u.id) as avg_rating
         FROM users u
         LEFT JOIN products p ON p.seller_id = u.id
         WHERE u.role = 'seller' OR p.id IS NOT NULL
         ORDER BY u.username ASC"
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Admin</title>
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
        <a href="<?= APP_URL ?>/admin/tinjau-seller.php" class="active">🔍 Tinjau Seller</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
        <a href="<?= APP_URL ?>/admin/backup.php">🗄️ Backup Database</a>
    </aside>
    
    <div class="admin-content">
        <?php showFlash(); ?>
        
        <?php if ($sellerId && isset($seller)): ?>
            <!-- DETAIL SELLER POV -->
            <div class="admin-header">
                <h1 class="admin-title">🔍 Tinjauan Seller: <?= htmlspecialchars($seller['username']) ?></h1>
                <a href="<?= APP_URL ?>/admin/tinjau-seller.php" class="btn btn-outline">← Kembali</a>
            </div>

            <!-- STATS INFO -->
            <div class="grid-4" style="margin-bottom:30px;">
                <div class="stat-card" style="border-left: 4px solid var(--accent);">
                    <div>
                        <div class="stat-value" style="font-size:16px;"><?= rupiah($releasedEarnings) ?></div>
                        <div class="stat-label">Dana Sudah Cair</div>
                    </div>
                </div>
                <div class="stat-card" style="border-left: 4px solid var(--warning);">
                    <div>
                        <div class="stat-value" style="font-size:16px; color:var(--warning);"><?= rupiah($pendingEscrow) ?></div>
                        <div class="stat-label">Pending Escrow (Admin)</div>
                    </div>
                </div>
                <div class="stat-card" style="border-left: 4px solid #10b981;">
                    <div>
                        <div class="stat-value">⭐ <?= number_format($ratingStats['avg_rating'], 1) ?></div>
                        <div class="stat-label">Rating (<?= $ratingStats['cnt'] ?> Ulasan)</div>
                    </div>
                </div>
                <div class="stat-card" style="border-left: 4px solid #06b6d4;">
                    <div>
                        <div class="stat-value"><?= count($products) ?></div>
                        <div class="stat-label">Jumlah Produk</div>
                    </div>
                </div>
            </div>

            <!-- CHART & PRODUCT LIST -->
            <div class="grid-2" style="gap:24px; margin-bottom:30px; align-items:start;">
                <!-- CHART -->
                <div class="card">
                    <div class="card-body">
                        <h3 style="font-family:var(--font-head); font-size:16px; font-weight:700; margin-bottom:16px;">📈 Grafik Penjualan (30 Hari Terakhir)</h3>
                        <div style="position: relative; height: 260px; width: 100%;">
                            <canvas id="sellerSalesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- PROFILE SHORT -->
                <div class="card">
                    <div class="card-body">
                        <h3 style="font-family:var(--font-head); font-size:16px; font-weight:700; margin-bottom:16px;">👤 Detail Akun Penjual</h3>
                        <table style="width:100%; font-size:14px; border-collapse:collapse;">
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-muted);">Email</td>
                                <td style="padding:10px 0; font-weight:600;"><?= htmlspecialchars($seller['email']) ?></td>
                            </tr>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-muted);">Saldo Utama</td>
                                <td style="padding:10px 0; font-weight:600; color:var(--gold);"><?= rupiah($seller['balance']) ?></td>
                            </tr>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 0; color:var(--text-muted);">Role Akun</td>
                                <td style="padding:10px 0; font-weight:600;"><?= ucfirst($seller['role']) ?></td>
                            </tr>
                            <tr>
                                <td style="padding:10px 0; color:var(--text-muted);">Tanggal Bergabung</td>
                                <td style="padding:10px 0; font-weight:600;"><?= date('d M Y H:i', strtotime($seller['created_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PRODUCT LIST -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head); font-size:18px; font-weight:700; margin-bottom:20px;">🎮 Daftar Produk Seller</h3>
                    <?php if (empty($products)): ?>
                        <p style="color:var(--text-muted); font-style:italic; margin:0;">Seller ini belum mendaftarkan produk.</p>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nama Produk</th>
                                        <th>Game</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Terjual</th>
                                        <th>Rating</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($p['game_name'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($p['platform'] ?? '-') ?></td>
                                        <td><strong style="color:var(--accent);"><?= rupiah($p['price']) ?></strong></td>
                                        <td><?= $p['stock'] ?></td>
                                        <td><?= $p['sold_count'] ?></td>
                                        <td>⭐ <?= number_format($p['rating'], 1) ?></td>
                                        <td>
                                            <span class="status-badge <?= $p['status']==='active'?'status-completed':'status-cancelled' ?>">
                                                <?= ucfirst($p['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- LIST SELLERS -->
            <div class="admin-header">
                <h1 class="admin-title">🔍 Tinjau Aktivitas Seller</h1>
                <span style="color:var(--text-muted);">Memantau kinerja & pendapatan para seller</span>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Saldo Aktif</th>
                                    <th>Jumlah Produk</th>
                                    <th>Rating Rata-rata</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sellers as $s): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($s['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($s['email']) ?></td>
                                    <td>
                                        <span class="status-badge <?= $s['role']==='admin'?'status-completed':'status-active' ?>" style="<?= $s['role']==='seller'?'background:#06b6d4; color:#fff;':'' ?>">
                                            <?= ucfirst($s['role']) ?>
                                        </span>
                                    </td>
                                    <td><strong style="color:var(--gold);"><?= rupiah($s['balance']) ?></strong></td>
                                    <td><?= $s['product_count'] ?> produk</td>
                                    <td>⭐ <?= number_format($s['avg_rating'], 1) ?></td>
                                    <td>
                                        <a href="?seller_id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">🔍 Tinjau Detail</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($sellerId && isset($seller)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('sellerSalesChart').getContext('2d');
    
    const revenueGradient = ctx.createLinearGradient(0, 0, 0, 240);
    revenueGradient.addColorStop(0, 'rgba(0, 102, 255, 0.3)');
    revenueGradient.addColorStop(1, 'rgba(0, 102, 255, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Pendapatan Bersih (Rupiah)',
                    data: <?= json_encode($revenuePoints) ?>,
                    borderColor: '#0066ff',
                    backgroundColor: revenueGradient,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Pendapatan: ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.6)'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.06)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.6)',
                        callback: function(value) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                            if (value >= 1000) {
                                return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            }
                            return 'Rp ' + value;
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

</body>
</html>
