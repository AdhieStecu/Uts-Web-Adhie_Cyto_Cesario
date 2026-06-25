<?php
// File: pages/category.php
require_once __DIR__ . '/../includes/functions.php';

$slug     = sanitize($_GET['slug'] ?? '');
$category = db()->fetchOne("SELECT * FROM categories WHERE slug = ? AND is_active = 1", 's', $slug);

if (!$category) {
    setFlash('error', 'Kategori tidak ditemukan.');
    redirect(APP_URL . '/index.php');
}

$pageTitle = $category['name'];
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;
$sort    = sanitize($_GET['sort'] ?? 'newest');

// FIX: tidak pakai alias "p", langsung pakai nama tabel
$orderBy = match($sort) {
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'popular'    => 'sold_count DESC',
    default      => 'id DESC'
};

$catId = (int)$category['id'];

$total    = db()->fetchOne(
    "SELECT COUNT(*) as cnt FROM products WHERE category_id = $catId AND status = 'active'"
)['cnt'] ?? 0;

$products = db()->fetchAll(
    "SELECT * FROM products
     WHERE category_id = $catId AND status = 'active'
     ORDER BY $orderBy
     LIMIT $perPage OFFSET $offset"
);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:30px;padding-bottom:60px;">

    <!-- BREADCRUMB -->
    <nav style="font-size:14px;color:var(--text-muted);margin-bottom:24px;">
        <a href="<?= APP_URL ?>">&#127968; Home</a> &rarr;
        <a href="<?= APP_URL ?>/pages/categories.php">Semua Kategori</a> &rarr;
        <span style="color:var(--text-primary);"><?= htmlspecialchars($category['name']) ?></span>
    </nav>

    <!-- HEADER KATEGORI -->
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;
                padding:30px;margin-bottom:30px;display:flex;align-items:center;gap:20px;">
        <div style="font-size:60px;"><?= renderCategoryIcon($category['icon'], '', 'width:60px;height:60px;') ?></div>
        <div>
            <h1 style="font-family:var(--font-head);font-size:28px;font-weight:800;">
                <?= htmlspecialchars($category['name']) ?>
            </h1>
            <p style="color:var(--text-muted);">
                <?= htmlspecialchars($category['description'] ?? '') ?>
                &middot; <strong style="color:var(--accent);"><?= $total ?></strong> produk tersedia
            </p>
        </div>
    </div>

    <!-- SORT -->
    <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
        <form method="GET">
            <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
            <select name="sort" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="newest"     <?= $sort==='newest'    ?'selected':'' ?>>Terbaru</option>
                <option value="popular"    <?= $sort==='popular'   ?'selected':'' ?>>Terpopuler</option>
                <option value="price_asc"  <?= $sort==='price_asc' ?'selected':'' ?>>Harga Terendah</option>
                <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Harga Tertinggi</option>
            </select>
        </form>
    </div>

    <?php if (empty($products)): ?>
    <div class="empty-state">
        <span class="empty-icon"><?= renderCategoryIcon($category['icon'], '', 'width:80px;height:80px;') ?></span>
        <h3>Belum ada produk di kategori ini</h3>
        <p style="color:var(--text-muted);">Produk segera hadir, pantau terus!</p>
        <a href="<?= APP_URL ?>" class="btn btn-primary mt-20">Kembali ke Beranda</a>
    </div>
    <?php else: ?>
    <div class="grid-4">
        <?php foreach ($products as $prod): ?>
        <div class="card product-card">
            <div class="card-img-wrap">
                <div style="width:100%;height:180px;background:var(--bg-card2);
                            display:flex;align-items:center;justify-content:center;
                            font-size:50px;overflow:hidden;">
                    <?php if (!empty($prod['image'])): ?>
                        <img src="<?= getImageUrl($prod['image']) ?>"
                             style="width:100%;height:100%;object-fit:cover;"
                             onerror="this.style.display='none'">
                    <?php else: ?>
                        <?= renderCategoryIcon($category['icon']) ?>
                    <?php endif; ?>
                </div>
                <?php if ($prod['is_featured']): ?>
                    <span class="badge-label hot">&#128293; Hot</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="game-name"><?= htmlspecialchars($prod['game_name'] ?? $category['name']) ?></p>
                <h3 class="prod-name"><?= htmlspecialchars($prod['name']) ?></h3>
                <div>
                    <?php if (!empty($prod['original_price']) && $prod['original_price'] > $prod['price']): ?>
                        <span class="prod-price-original"><?= rupiah($prod['original_price']) ?></span>
                    <?php endif; ?>
                    <span class="prod-price"><?= rupiah($prod['price']) ?></span>
                </div>
                <div class="prod-meta">
                    <span>&#11088; <?= number_format($prod['rating'], 1) ?></span>
                    <span>&#128722; <?= $prod['sold_count'] ?> terjual</span>
                </div>
                <a href="<?= APP_URL ?>/pages/product.php?id=<?= $prod['id'] ?>"
                   class="btn btn-primary btn-sm btn-buy">Beli Sekarang</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?= paginate(
        $total, $perPage, $page,
        APP_URL . '/pages/category.php?slug=' . $slug . '&sort=' . $sort
    ) ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>