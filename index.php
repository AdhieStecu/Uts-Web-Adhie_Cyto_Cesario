<?php
// =============================================
// HOMEPAGE - index.php
// =============================================
$pageTitle = 'Marketplace Game Terpercaya #1 Indonesia';
require_once __DIR__ . '/includes/header.php';

// Ambil produk featured
$featuredProducts = db()->fetchAll(
    "SELECT p.*, c.name as cat_name FROM products p 
     JOIN categories c ON p.category_id = c.id 
     WHERE p.is_featured = 1 AND p.status = 'active' 
     ORDER BY p.sold_count DESC LIMIT 8"
);

// Ambil produk terbaru
$newProducts = db()->fetchAll(
    "SELECT p.*, c.name as cat_name FROM products p 
     JOIN categories c ON p.category_id = c.id 
     WHERE p.status = 'active' 
     ORDER BY p.created_at DESC LIMIT 8"
);

// Ambil kategori
$categories = db()->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");

// Game cover images
$heroGames = [
    ['title' => 'Mobile Legends', 'badge' => 'Diamond Top Up', 'bg' => '#0d1530', 'image' => 'mobile_legends.png'],
    ['title' => 'Free Fire', 'badge' => 'Diamond Top Up', 'bg' => '#1a0505', 'image' => 'free_fire.png'],
    ['title' => 'Roblox', 'badge' => 'Robux Giftcard', 'bg' => '#001a05', 'image' => 'roblox.png'],
    ['title' => 'Steam Wallet', 'badge' => 'Voucher Game', 'bg' => '#000a1a', 'image' => 'steam_wallet.png'],
];
?>

<!-- HERO BANNER -->
<section class="hero-banner">
    <div class="container">
        <div class="hero-grid">
            <?php foreach ($heroGames as $i => $game): ?>
            <div class="hero-card" style="background: <?= $game['bg'] ?>;">
                <img src="<?= APP_URL ?>/assets/img/<?= $game['image'] ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                <div class="hero-overlay">
                    <span class="hero-badge <?= ($i === 2 || $i === 3) ? 'giftcard' : '' ?>">
                        <?= $game['badge'] ?>
                    </span>
                    <a href="<?= APP_URL ?>/pages/search.php?q=<?= urlencode($game['title']) ?>" class="hero-btn">
                        Top Up!
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!isLoggedIn()): ?>
<!-- AUTH CTA SECTION -->
<section class="auth-cta-section">
    <div class="container">
        <div class="auth-cta-card">
            <div class="auth-cta-content">
                <h2>🎮 Mulai Top Up & Belanja Game Sekarang!</h2>
                <p>Daftar sekarang untuk mendapatkan potongan harga khusus anggota, promo menarik, dan melacak riwayat transaksi Anda secara instan!</p>
            </div>
            <div class="auth-cta-actions">
                <a href="<?= APP_URL ?>/pages/login.php" class="btn btn-outline btn-lg">🔑 Masuk Akun</a>
                <a href="<?= APP_URL ?>/pages/register.php" class="btn btn-primary btn-lg">🚀 Daftar Gratis</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- TRUST BAR -->
<div class="trust-bar">
    <div class="container">
        <div class="trust-item"><span class="trust-icon">🛡️</span> Transaksi Aman</div>
        <div class="trust-item"><span class="trust-icon">💰</span> Garansi Uang Kembali</div>
        <div class="trust-item"><span class="trust-icon">🎧</span> Bantuan Customer Care 24/7</div>
        <div class="trust-item"><span class="trust-icon">⚡</span> Pengiriman Instan</div>
    </div>
</div>

<!-- KATEGORI ICONS -->
<section class="section" style="padding-bottom:0;">
    <div class="container">
        <div class="cat-icons">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= APP_URL ?>/pages/category.php?slug=<?= $cat['slug'] ?>" class="cat-icon-item">
                <div class="cat-icon-box"><?= renderCategoryIcon($cat['icon'], '', 'width:40px;height:40px;') ?></div>
                <span class="cat-icon-label"><?= htmlspecialchars($cat['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PRODUK UNGGULAN -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🔥 Produk Unggulan</h2>
            <a href="<?= APP_URL ?>/pages/search.php" class="section-link">Lihat Semua →</a>
        </div>

        <?php if (empty($featuredProducts)): ?>
        <div class="empty-state">
            <span class="empty-icon">🎮</span>
            <p>Belum ada produk tersedia</p>
            <?php if (isAdmin()): ?>
                <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-primary mt-20">+ Tambah Produk</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="grid-4">
            <?php foreach ($featuredProducts as $prod): ?>
            <div class="card product-card">
                <div class="card-img-wrap">
                    <img src="<?= getImageUrl($prod['image']) ?>" 
                         alt="<?= htmlspecialchars($prod['name']) ?>"
                         onerror="this.style.display='none';this.parentElement.style.background='#0d1530';this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:50px;\'>🎮</div>'">
                    <span class="badge-label hot">Hot 🔥</span>
                </div>
                <div class="card-body">
                    <p class="game-name"><?= htmlspecialchars($prod['game_name'] ?? $prod['cat_name']) ?></p>
                    <h3 class="prod-name"><?= htmlspecialchars($prod['name']) ?></h3>
                    <div>
                        <?php if ($prod['original_price'] && $prod['original_price'] > $prod['price']): ?>
                            <span class="prod-price-original"><?= rupiah($prod['original_price']) ?></span>
                        <?php endif; ?>
                        <span class="prod-price"><?= rupiah($prod['price']) ?></span>
                    </div>
                    <div class="prod-meta">
                        <span>⭐ <?= number_format($prod['rating'], 1) ?></span>
                        <span>🛒 <?= $prod['sold_count'] ?> terjual</span>
                    </div>
                    <a href="<?= APP_URL ?>/pages/product.php?id=<?= $prod['id'] ?>" class="btn btn-primary btn-sm btn-buy">
                        Beli Sekarang
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- BANNER PROMO -->
<div style="background:linear-gradient(135deg,#0044cc,#0066ff,#0099ff);padding:40px 0;margin:0;">
    <div class="container" style="text-align:center;">
        <h2 style="font-family:var(--font-head);font-size:28px;font-weight:900;margin-bottom:10px;">
            💎 QRIS Payment Tersedia!
        </h2>
        <p style="color:rgba(255,255,255,0.8);margin-bottom:20px;">Bayar dengan QRIS dari semua dompet digital favorit kamu</p>
        <a href="<?= APP_URL ?>/pages/search.php" class="btn btn-gold btn-lg">Belanja Sekarang</a>
    </div>
</div>

<!-- PRODUK TERBARU -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🆕 Produk Terbaru</h2>
            <a href="<?= APP_URL ?>/pages/search.php?sort=newest" class="section-link">Lihat Semua →</a>
        </div>
        <?php if (!empty($newProducts)): ?>
        <div class="grid-4">
            <?php foreach ($newProducts as $prod): ?>
            <div class="card product-card">
                <div class="card-img-wrap">
                    <img src="<?= getImageUrl($prod['image']) ?>" 
                         alt="<?= htmlspecialchars($prod['name']) ?>"
                         onerror="this.style.display='none';this.parentElement.style.background='#0d1530';this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:50px;\'>🎮</div>'">
                    <span class="badge-label new">Baru ✨</span>
                </div>
                <div class="card-body">
                    <p class="game-name"><?= htmlspecialchars($prod['game_name'] ?? $prod['cat_name']) ?></p>
                    <h3 class="prod-name"><?= htmlspecialchars($prod['name']) ?></h3>
                    <div>
                        <?php if ($prod['original_price'] && $prod['original_price'] > $prod['price']): ?>
                            <span class="prod-price-original"><?= rupiah($prod['original_price']) ?></span>
                        <?php endif; ?>
                        <span class="prod-price"><?= rupiah($prod['price']) ?></span>
                    </div>
                    <div class="prod-meta">
                        <span>📦 Stok: <?= $prod['stock'] ?></span>
                        <span>⚡ <?= ucfirst($prod['delivery_type']) ?></span>
                    </div>
                    <a href="<?= APP_URL ?>/pages/product.php?id=<?= $prod['id'] ?>" class="btn btn-outline btn-sm btn-buy">
                        Lihat Detail
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>