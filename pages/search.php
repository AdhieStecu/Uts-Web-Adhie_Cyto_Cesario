<?php
// File: pages/search.php
$pageTitle = 'Cari Produk';
require_once __DIR__ . '/../includes/functions.php';

$q       = sanitize($_GET['q'] ?? '');
$catSlug = sanitize($_GET['cat'] ?? '');
$sort    = sanitize($_GET['sort'] ?? 'newest');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;

// Build WHERE clause pakai string interpolasi aman (sudah di-escape)
$conditions = ["p.status = 'active'"];
$bindTypes  = '';
$bindParams = [];

if ($q) {
    $qEsc = db()->escape($q);
    $conditions[] = "(p.name LIKE '%{$qEsc}%' OR p.game_name LIKE '%{$qEsc}%' OR p.description LIKE '%{$qEsc}%')";
}
if ($catSlug) {
    $cat = db()->fetchOne("SELECT id FROM categories WHERE slug = ?", 's', $catSlug);
    if ($cat) {
        $conditions[] = "p.category_id = " . (int)$cat['id'];
    }
}

$where = 'WHERE ' . implode(' AND ', $conditions);

$orderBy = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'popular'    => 'p.sold_count DESC',
    'rating'     => 'p.rating DESC',
    default      => 'p.created_at DESC'
};

$total    = db()->fetchOne("SELECT COUNT(*) as cnt FROM products p $where")['cnt'] ?? 0;
$products = db()->fetchAll(
    "SELECT p.*, c.name as cat_name FROM products p
     JOIN categories c ON p.category_id = c.id
     $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset"
);

$categories = db()->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:30px;padding-bottom:60px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-family:var(--font-head);font-size:22px;font-weight:800;">
                <?= $q ? '&#128269; Hasil: "' . htmlspecialchars($q) . '"' : '&#127918; Semua Produk' ?>
            </h1>
            <p style="color:var(--text-muted);font-size:14px;"><?= $total ?> produk ditemukan</p>
        </div>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <?php if ($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
            <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($catSlug) ?>"><?php endif; ?>
            <select name="sort" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <option value="newest"     <?= $sort==='newest'    ?'selected':'' ?>>Terbaru</option>
                <option value="popular"    <?= $sort==='popular'   ?'selected':'' ?>>Terpopuler</option>
                <option value="price_asc"  <?= $sort==='price_asc' ?'selected':'' ?>>Harga Terendah</option>
                <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Harga Tertinggi</option>
                <option value="rating"     <?= $sort==='rating'    ?'selected':'' ?>>Rating Terbaik</option>
            </select>
        </form>
    </div>

    <!-- FILTER KATEGORI -->
    <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;">
        <a href="?q=<?= urlencode($q) ?>" class="btn <?= !$catSlug?'btn-primary':'btn-outline' ?> btn-sm">Semua</a>
        <?php foreach ($categories as $cat): ?>
        <a href="?q=<?= urlencode($q) ?>&cat=<?= $cat['slug'] ?>"
           class="btn <?= $catSlug===$cat['slug']?'btn-primary':'btn-outline' ?> btn-sm">
            <?= renderCategoryIcon($cat['icon']) ?> <?= htmlspecialchars($cat['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($products)): ?>
    <div class="empty-state">
        <span class="empty-icon">&#128269;</span>
        <h3>Produk tidak ditemukan</h3>
        <p>Coba kata kunci yang berbeda atau pilih kategori lain.</p>
        <a href="<?= APP_URL ?>/pages/search.php" class="btn btn-primary mt-20">Lihat Semua Produk</a>
    </div>
    <?php else: ?>
    <div class="grid-4">
        <?php foreach ($products as $prod): ?>
        <div class="card product-card">
            <div class="card-img-wrap">
                <div style="width:100%;height:180px;background:var(--bg-card2);display:flex;align-items:center;justify-content:center;font-size:50px;overflow:hidden;">
                    <?php if ($prod['image']): ?>
                        <img src="<?= getImageUrl($prod['image']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.outerHTML='&#127918;'">
                    <?php else: ?>&#127918;<?php endif; ?>
                </div>
                <?php if ($prod['is_featured']): ?>
                    <span class="badge-label hot">&#128293; Hot</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="game-name"><?= htmlspecialchars($prod['game_name'] ?? $prod['cat_name']) ?></p>
                <h3 class="prod-name"><?= htmlspecialchars($prod['name']) ?></h3>
                <div>
                    <?php if ($prod['original_price'] > $prod['price']): ?>
                        <span class="prod-price-original"><?= rupiah($prod['original_price']) ?></span>
                    <?php endif; ?>
                    <span class="prod-price"><?= rupiah($prod['price']) ?></span>
                </div>
                <div class="prod-meta">
                    <span>&#11088; <?= number_format($prod['rating'],1) ?></span>
                    <span>&#128230; Stok <?= $prod['stock'] ?></span>
                </div>
                <a href="<?= APP_URL ?>/pages/product.php?id=<?= $prod['id'] ?>" class="btn btn-primary btn-sm btn-buy">
                    Beli Sekarang
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?= paginate($total, $perPage, $page, APP_URL . '/pages/search.php?q=' . urlencode($q) . '&cat=' . $catSlug . '&sort=' . $sort) ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>