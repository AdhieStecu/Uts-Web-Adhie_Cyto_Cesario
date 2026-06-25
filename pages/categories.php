<?php
// File: pages/categories.phpppp
$pageTitle = 'Semua Kategori';
require_once __DIR__ . '/../includes/functions.php';

// FIX: query tanpa alias yang ambigu
$categories = db()->fetchAll(
    "SELECT c.*,
            COUNT(p.id)  as product_count,
            MIN(p.price) as min_price
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
     WHERE c.is_active = 1
     GROUP BY c.id
     ORDER BY c.sort_order ASC"
);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;">

    <!-- BREADCRUMB -->
    <nav style="font-size:14px;color:var(--text-muted);margin-bottom:24px;">
        <a href="<?= APP_URL ?>">&#127968; Home</a> &rarr;
        <span style="color:var(--text-primary);">Semua Kategori</span>
    </nav>

    <h1 style="font-family:var(--font-head);font-size:28px;font-weight:800;margin-bottom:8px;">
        &#128193; Semua Kategori
    </h1>
    <p style="color:var(--text-muted);margin-bottom:32px;">
        Temukan produk game favoritmu dari <strong><?= count($categories) ?></strong> kategori tersedia
    </p>

    <?php if (empty($categories)): ?>
    <div class="empty-state">
        <span class="empty-icon">&#128193;</span>
        <p>Belum ada kategori tersedia.</p>
    </div>
    <?php else: ?>
    <div class="grid-4">
        <?php foreach ($categories as $cat): ?>
        <a href="<?= APP_URL ?>/pages/category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"
           style="text-decoration:none;">
            <div class="card" style="text-align:center;padding:0;overflow:hidden;cursor:pointer;">

                <!-- ATAS: icon + nama -->
                <div style="background:linear-gradient(135deg,var(--bg-card2),var(--bg-nav));
                            padding:32px 20px 24px;">
                    <div style="font-size:52px;margin-bottom:12px;
                                filter:drop-shadow(0 4px 12px rgba(0,102,255,0.3));">
                        <?= renderCategoryIcon($cat['icon'], '', 'width:52px;height:52px;') ?>
                    </div>
                    <h3 style="font-family:var(--font-head);font-size:18px;font-weight:800;
                               color:var(--text-primary);margin-bottom:6px;">
                        <?= htmlspecialchars($cat['name']) ?>
                    </h3>
                    <p style="font-size:13px;color:var(--text-muted);line-height:1.4;">
                        <?= htmlspecialchars($cat['description'] ?? '') ?>
                    </p>
                </div>

                <!-- BAWAH: jumlah produk + harga -->
                <div style="padding:14px 20px;border-top:1px solid var(--border);
                            display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:var(--text-muted);">
                        &#127918; <?= (int)$cat['product_count'] ?> produk
                    </span>
                    <?php if ($cat['min_price']): ?>
                    <span style="font-size:13px;color:var(--accent);font-weight:700;">
                        ab <?= rupiah($cat['min_price']) ?>
                    </span>
                    <?php else: ?>
                    <span style="font-size:12px;color:var(--text-muted);">Segera Hadir</span>
                    <?php endif; ?>
                </div>

            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- BANNER CARI -->
    <div style="margin-top:50px;background:linear-gradient(135deg,#0044cc,#0066ff);
                border-radius:16px;padding:40px;text-align:center;">
        <h2 style="font-family:var(--font-head);font-size:22px;font-weight:900;margin-bottom:8px;">
            &#128269; Tidak menemukan yang dicari?
        </h2>
        <p style="color:rgba(255,255,255,0.8);margin-bottom:20px;">
            Gunakan fitur pencarian untuk menemukan produk spesifik
        </p>
        <form action="<?= APP_URL ?>/pages/search.php" method="GET"
              style="display:flex;gap:0;max-width:480px;margin:0 auto;">
            <input type="text" name="q" class="form-control"
                   placeholder="Cari game, diamond, item..."
                   style="border-radius:var(--radius) 0 0 var(--radius);border-right:none;">
            <button type="submit" class="btn btn-gold"
                    style="border-radius:0 var(--radius) var(--radius) 0;white-space:nowrap;">
                &#128269; Cari
            </button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>