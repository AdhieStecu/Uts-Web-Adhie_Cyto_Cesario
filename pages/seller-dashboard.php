<?php
// File: pages/seller-dashboard.php
$pageTitle = 'Dashboard Penjual';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user = currentUser();
$userId = $_SESSION['user_id'];

// Check if user has seller activity (has products or has orders as seller)
$productCount = db()->fetchOne("SELECT COUNT(*) as cnt FROM products WHERE seller_id = ?", 'i', $userId)['cnt'];
$ordersAsSeller = db()->fetchOne("SELECT COUNT(*) as cnt FROM orders WHERE seller_id = ?", 'i', $userId)['cnt'];

// Fetch Seller Stats
$totalEarnings = db()->fetchOne(
    "SELECT COALESCE(SUM(total_price - platform_fee), 0) as total FROM orders WHERE seller_id = ? AND status = 'completed'", 
    'i', $userId
)['total'];

$itemsSold = db()->fetchOne(
    "SELECT COALESCE(SUM(quantity), 0) as qty FROM orders WHERE seller_id = ? AND status = 'completed'", 
    'i', $userId
)['qty'];

$activeListings = db()->fetchOne(
    "SELECT COUNT(*) as cnt FROM products WHERE seller_id = ? AND status = 'active'", 
    'i', $userId
)['cnt'];

// Recent Sales (Orders received as seller)
$recentSales = db()->fetchAll(
    "SELECT o.*, u.username as buyer_name, p.name as product_name 
     FROM orders o 
     JOIN users u ON o.buyer_id = u.id 
     JOIN products p ON o.product_id = p.id 
     WHERE o.seller_id = ? 
     ORDER BY o.created_at DESC LIMIT 10",
    'i', $userId
);

// Prepare 30-day daily sales history data for Chart.js
$rawSales = db()->fetchAll(
    "SELECT DATE(created_at) as sale_date, SUM(total_price - platform_fee) as daily_revenue, COUNT(*) as daily_sales
     FROM orders
     WHERE seller_id = ? AND status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     GROUP BY DATE(created_at)
     ORDER BY sale_date ASC",
    'i', $userId
);

// Format sales data for Chart.js labels and values
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
    $formattedLabel = date('d M', strtotime($dateStr));
    $labels[] = $formattedLabel;
    
    if (isset($indexedRaw[$dateStr])) {
        $revenuePoints[] = $indexedRaw[$dateStr]['revenue'];
        $countPoints[] = $indexedRaw[$dateStr]['count'];
    } else {
        $revenuePoints[] = 0.0;
        $countPoints[] = 0;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px; padding-bottom:60px;">
    <div class="dashboard-layout">
        <!-- SIDEBAR -->
        <?php
        $activePage = 'seller-dashboard';
        require_once __DIR__ . '/../includes/sidebar.php';
        ?>

        <!-- MAIN CONTENT -->
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <h1 style="font-family:var(--font-head); font-size:26px; font-weight:800; margin:0;">
                    🏪 Dashboard Penjual
                </h1>
                <span style="font-size:14px; color:var(--text-muted);">Selamat Datang kembali di panel tokomu</span>
            </div>

            <!-- STAT CARDS -->
            <div class="grid-3" style="margin-bottom:30px;">
                <div class="stat-card" style="border-left: 4px solid var(--accent);">
                    <span class="stat-icon">💰</span>
                    <div>
                        <div class="stat-value" style="font-size:17px;"><?= rupiah($totalEarnings) ?></div>
                        <div class="stat-label">Pendapatan Bersih</div>
                    </div>
                </div>
                <div class="stat-card" style="border-left: 4px solid #06b6d4;">
                    <span class="stat-icon">⚔️</span>
                    <div>
                        <div class="stat-value"><?= $itemsSold ?> Item</div>
                        <div class="stat-label">Produk Terjual</div>
                    </div>
                </div>
                <div class="stat-card" style="border-left: 4px solid #10b981;">
                    <span class="stat-icon">🛍️</span>
                    <div>
                        <div class="stat-value"><?= $activeListings ?></div>
                        <div class="stat-label">Produk Aktif</div>
                    </div>
                </div>
            </div>

            <!-- GRAPH CHART -->
            <div class="card" style="margin-bottom:30px;">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head); font-size:18px; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                        📈 Grafik Penjualan (30 Hari Terakhir)
                    </h3>
                    <div style="position: relative; height: 320px; width: 100%;">
                        <canvas id="sellerSalesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- RECENT SALES ORDERS -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head); font-size:18px; font-weight:700; margin-bottom:20px;">
                        📋 Pesanan Masuk (Terbaru)
                    </h3>

                    <?php if (empty($recentSales)): ?>
                    <div class="empty-state">
                        <span class="empty-icon">🛒</span>
                        <p>Belum ada produk yang terjual.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Pembeli</th>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Pendapatan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSales as $sale): 
                                    $earnings = $sale['total_price'] - $sale['platform_fee'];
                                ?>
                                <tr>
                                    <td><code style="color:var(--accent);"><?= htmlspecialchars($sale['order_number']) ?></code></td>
                                    <td><?= htmlspecialchars($sale['buyer_name']) ?></td>
                                    <td><?= htmlspecialchars(excerpt($sale['product_name'], 30)) ?></td>
                                    <td style="text-align:center;"><?= $sale['quantity'] ?></td>
                                    <td><strong style="color:var(--accent);"><?= rupiah($earnings) ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?= $sale['status'] ?>">
                                            <?= ucfirst($sale['status']) ?>
                                        </span>
                                    </td>
                                    <td style="color:var(--text-muted); font-size:13px;"><?= timeAgo($sale['created_at']) ?></td>
                                    <td>
                                        <a href="<?= APP_URL ?>/pages/order-detail.php?id=<?= $sale['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
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

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('sellerSalesChart').getContext('2d');
    
    // Create soft gradients
    const revenueGradient = ctx.createLinearGradient(0, 0, 0, 300);
    revenueGradient.addColorStop(0, 'rgba(0, 102, 255, 0.3)');
    revenueGradient.addColorStop(1, 'rgba(0, 102, 255, 0.0)');

    const countGradient = ctx.createLinearGradient(0, 0, 0, 300);
    countGradient.addColorStop(0, 'rgba(6, 182, 212, 0.3)');
    countGradient.addColorStop(1, 'rgba(6, 182, 212, 0.0)');

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
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'yRevenue'
                },
                {
                    label: 'Jumlah Transaksi',
                    data: <?= json_encode($countPoints) ?>,
                    borderColor: '#06b6d4',
                    backgroundColor: countGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'yCount',
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim() || '#333'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                            } else {
                                label += context.parsed.y + ' Transaksi';
                            }
                            return label;
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
                        color: getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim() || '#888'
                    }
                },
                yRevenue: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(255, 255, 255, 0.06)'
                    },
                    ticks: {
                        color: '#0066ff',
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
                },
                yCount: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        color: '#06b6d4',
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
