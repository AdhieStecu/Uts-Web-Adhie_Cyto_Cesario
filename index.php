<?php
// =============================================
// HOMEPAGE - index.php (Redesigned Itemku-Style)
// =============================================
$pageTitle = 'Marketplace Game Terpercaya #1 Indonesia';
require_once __DIR__ . '/inc/functions.php';

// Ambil produk featured
$featuredProducts = db()->fetchAll(
    "SELECT p.*, c.name as cat_name FROM products p 
     JOIN categories c ON p.category_id = c.id 
     WHERE p.is_featured = 1 AND p.status = 'active' 
     ORDER BY p.sold_count DESC LIMIT 8"
);

// Ambil produk Top Up Game (kategori 1 / slug topup)
$topupProducts = db()->fetchAll(
    "SELECT p.*, c.name as cat_name FROM products p 
     JOIN categories c ON p.category_id = c.id 
     WHERE c.slug = 'topup' AND p.status = 'active' 
     ORDER BY p.sold_count DESC LIMIT 5"
);

// Ambil produk Voucher (kategori 4 / slug voucher)
$voucherProducts = db()->fetchAll(
    "SELECT p.*, c.name as cat_name FROM products p 
     JOIN categories c ON p.category_id = c.id 
     WHERE c.slug = 'voucher' AND p.status = 'active' 
     ORDER BY p.sold_count DESC LIMIT 5"
);

// Ambil kategori untuk grid
$categories = db()->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");

// 4 Promo cards in hero banner
$heroGames = [
    [
        'title' => 'Grow a Garden 2', 
        'desc' => 'Tumbuhkan taman blocky impianmu sekarang!', 
        'badge' => 'New Release', 
        'badge_class' => 'new',
        'btn_text' => 'Cek Sekarang!', 
        'image' => 'grow_garden.png',
        'link' => APP_URL . '/pages/search.php?q=Growtopia'
    ],
    [
        'title' => 'Robux Giftcard', 
        'desc' => 'Dapatkan tambahan 25% lebih banyak Robux!', 
        'badge' => 'Giftcard', 
        'badge_class' => '',
        'btn_text' => 'Top Up', 
        'image' => 'roblox.png',
        'link' => APP_URL . '/pages/category.php?slug=roblox'
    ],
    [
        'title' => 'Mobile Legends', 
        'desc' => 'Beli Diamond & dapatkan skin event khusus!', 
        'badge' => 'Special Event', 
        'badge_class' => 'event',
        'btn_text' => 'Top Up', 
        'image' => 'mobile_legends.png',
        'link' => APP_URL . '/pages/category.php?slug=topup'
    ],
    [
        'title' => 'Steam Wallet Code', 
        'desc' => 'Nikmati diskon gila-gilaan di Steam Summer Sale!', 
        'badge' => 'Special Event', 
        'badge_class' => 'event',
        'btn_text' => 'Beli Steam Wallet', 
        'image' => 'steam_wallet.png',
        'link' => APP_URL . '/pages/category.php?slug=voucher'
    ],
];

require_once __DIR__ . '/inc/header.php';
?>

<!-- HERO BANNER -->
<section class="hero-banner">
    <div class="container">
        <div class="hero-grid">
            <?php foreach ($heroGames as $game): ?>
            <div class="hero-card">
                <img src="<?= APP_URL ?>/assets/img/<?= $game['image'] ?>" alt="<?= htmlspecialchars($game['title']) ?>" onerror="this.src='<?= APP_URL ?>/assets/img/no-image.png'">
                <div class="hero-overlay">
                    <div>
                        <span class="hero-badge <?= $game['badge_class'] ?>"><?= $game['badge'] ?></span>
                        <h3><?= htmlspecialchars($game['title']) ?></h3>
                        <p><?= htmlspecialchars($game['desc']) ?></p>
                    </div>
                    <a href="<?= $game['link'] ?>" class="hero-btn">
                        <?= $game['btn_text'] ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- TRUST BAR -->
        <div class="hero-trust-bar" style="display:flex; justify-content:center; gap:40px; margin-top:35px; color:rgba(255,255,255,0.9); font-size:13px; font-weight:700; border-top:1px solid rgba(255,255,255,0.15); padding-top:20px;">
            <span>🛡️ Transaksi Aman</span>
            <span>|</span>
            <span>💰 Garansi Uang Kembali</span>
            <span>|</span>
            <span>🎧 Bantuan Customer Care 24/7</span>
        </div>
    </div>
</section>

<?php if (!isLoggedIn()): ?>
<!-- AUTH CTA SECTION -->
<section class="auth-cta-section" style="padding-top:25px; padding-bottom:5px;">
    <div class="container">
        <div class="auth-cta-card" style="box-shadow: 0 10px 25px rgba(0,0,0,0.05); padding: 30px 40px;">
            <div class="auth-cta-content">
                <h2>🎮 Mulai Top Up & Belanja Game Sekarang!</h2>
                <p>Daftar sekarang untuk mendapatkan potongan harga khusus anggota, promo menarik, dan melacak riwayat transaksi Anda secara instan!</p>
            </div>
            <div class="auth-cta-actions">
                <a href="<?= APP_URL ?>/pages/login.php" class="btn btn-outline btn-lg" style="border-color: rgba(255, 255, 255, 0.2); color: white;">🔑 Masuk Akun</a>
                <a href="<?= APP_URL ?>/pages/register.php" class="btn btn-primary btn-lg">🚀 Daftar Gratis</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- KATEGORI ICONS GRID (10 Columns) -->
<section class="section" style="padding-bottom:0; padding-top:20px;">
    <div class="container">
        <div class="cat-icons">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= APP_URL ?>/pages/category.php?slug=<?= $cat['slug'] ?>" class="cat-icon-item">
                <div class="cat-icon-box">
                    <?= renderCategoryIcon($cat['icon'], '', 'width:36px;height:36px;') ?>
                </div>
                <span class="cat-icon-label"><?= htmlspecialchars($cat['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- TOP UP GAME SECTION -->
<section class="section" style="padding-top: 40px; padding-bottom: 20px;">
    <div class="container">
        <div class="section-header" style="border-bottom: 2px solid var(--border); padding-bottom: 10px; margin-bottom: 20px;">
            <h2 class="section-title" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                💎 Top Up Game
            </h2>
            <a href="<?= APP_URL ?>/pages/category.php?slug=topup" class="btn btn-outline btn-sm" style="border-color: var(--primary); color: var(--primary); font-weight: 700; border-radius: 20px; padding: 4px 16px;">Lihat Semua ›</a>
        </div>

        <?php if (empty($topupProducts)): ?>
            <div class="empty-state" style="padding: 30px;">Belum ada produk Top Up Game.</div>
        <?php else: ?>
            <div class="grid-5">
                <?php foreach ($topupProducts as $prod): ?>
                <div class="card product-card" style="box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                    <div class="card-img-wrap" style="height: 140px;">
                        <img src="<?= getImageUrl($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" onerror="this.style.display='none';this.parentElement.style.background='var(--primary)';this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:32px;\'>🎮</div>'">
                    </div>
                    <div class="card-body" style="padding: 12px;">
                        <p class="game-name" style="font-size: 10px; margin-bottom: 2px;"><?= htmlspecialchars($prod['game_name'] ?? $prod['cat_name']) ?></p>
                        <h3 class="prod-name" style="font-size: 13px; margin-bottom: 6px; height: 36px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?= htmlspecialchars($prod['name']) ?></h3>
                        <div style="font-family: var(--font-head); font-weight: 800; font-size: 15px; color: var(--primary); margin-bottom: 8px;"><?= rupiah($prod['price']) ?></div>
                        <a href="<?= APP_URL ?>/pages/product.php?id=<?= $prod['id'] ?>" class="btn btn-primary btn-sm btn-block" style="font-size: 12px; padding: 6px 12px;">Beli</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- VOUCHER SECTION -->
<section class="section" style="padding-top: 20px; padding-bottom: 40px;">
    <div class="container">
        <div class="section-header" style="border-bottom: 2px solid var(--border); padding-bottom: 10px; margin-bottom: 20px;">
            <h2 class="section-title" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                🎫 Voucher
            </h2>
            <a href="<?= APP_URL ?>/pages/category.php?slug=voucher" class="btn btn-outline btn-sm" style="border-color: var(--primary); color: var(--primary); font-weight: 700; border-radius: 20px; padding: 4px 16px;">Lihat Semua ›</a>
        </div>

        <?php if (empty($voucherProducts)): ?>
            <div class="empty-state" style="padding: 30px;">Belum ada produk Voucher.</div>
        <?php else: ?>
            <div class="grid-5">
                <?php foreach ($voucherProducts as $prod): ?>
                <div class="card product-card" style="box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                    <div class="card-img-wrap" style="height: 140px;">
                        <img src="<?= getImageUrl($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" onerror="this.style.display='none';this.parentElement.style.background='var(--primary)';this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:32px;\'>🎫</div>'">
                    </div>
                    <div class="card-body" style="padding: 12px;">
                        <p class="game-name" style="font-size: 10px; margin-bottom: 2px;"><?= htmlspecialchars($prod['game_name'] ?? $prod['cat_name']) ?></p>
                        <h3 class="prod-name" style="font-size: 13px; margin-bottom: 6px; height: 36px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?= htmlspecialchars($prod['name']) ?></h3>
                        <div style="font-family: var(--font-head); font-weight: 800; font-size: 15px; color: var(--primary); margin-bottom: 8px;"><?= rupiah($prod['price']) ?></div>
                        <a href="<?= APP_URL ?>/pages/product.php?id=<?= $prod['id'] ?>" class="btn btn-primary btn-sm btn-block" style="font-size: 12px; padding: 6px 12px;">Beli</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- PRODUK UNGGULAN -->
<section class="section" style="background:var(--bg-card); padding: 50px 0;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🔥 Produk Terpopuler</h2>
            <a href="<?= APP_URL ?>/pages/search.php" class="section-link">Lihat Semua →</a>
        </div>

        <?php if (empty($featuredProducts)): ?>
        <div class="empty-state">
            <span class="empty-icon">🎮</span>
            <p>Belum ada produk tersedia</p>
        </div>
        <?php else: ?>
        <div class="grid-4">
            <?php foreach ($featuredProducts as $prod): ?>
            <div class="card product-card">
                <div class="card-img-wrap">
                    <img src="<?= getImageUrl($prod['image']) ?>" 
                         alt="<?= htmlspecialchars($prod['name']) ?>"
                         onerror="this.style.display='none';this.parentElement.style.background='var(--primary)';this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:50px;\'>🎮</div>'">
                    <span class="badge-label hot">Terlaris 🔥</span>
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

<!-- Floating Customer Care Button -->
<div id="floatingChatBtn" style="position:fixed; bottom:30px; right:30px; width:56px; height:56px; background:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 6px 16px rgba(0,0,0,0.25); cursor:pointer; z-index:9999;">
    <span style="font-size:26px; color:white; line-height:0; display:inline-block; vertical-align:middle; transform:translateY(-1px);">🎧</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatBtn = document.getElementById('floatingChatBtn');
    if (chatBtn) {
        chatBtn.addEventListener('click', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hubungi Customer Service 🎧',
                    html: `
                        <div style="text-align:left; font-size:15px; line-height:1.8; color:var(--text-secondary); font-family:var(--font-body);">
                            <p>Halo! Butuh bantuan transaksi di <strong>BoloTopup.ID</strong>? Hubungi tim CS kami yang siap siaga melayani Anda.</p>
                            <div style="margin-top:20px; display:flex; flex-direction:column; gap:10px;">
                                <a href="<?= APP_URL ?>/pages/faq.php" class="btn btn-gold btn-block" style="color:black; font-weight:700; text-decoration:none; justify-content:center; font-family:var(--font-head);">
                                    ❓ Kunjungi Pusat Bantuan & FAQ
                                </a>
                                <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-success btn-block" style="color:black; font-weight:700; text-decoration:none; justify-content:center; font-family:var(--font-head);">
                                    💬 Chat WhatsApp (+62 812-3456-7890)
                                </a>
                                <a href="mailto:support@bolotopup.id" class="btn btn-primary btn-block" style="color:white; font-weight:700; text-decoration:none; justify-content:center; font-family:var(--font-head);">
                                    ✉️ Kirim Email (support@bolotopup.id)
                                </a>
                            </div>
                        </div>
                    `,
                    background: '#ffffff',
                    showConfirmButton: false,
                    showCloseButton: true,
                    customClass: {
                        popup: 'swal2-custom-popup',
                        title: 'swal2-custom-title'
                    }
                });
            } else {
                alert('Butuh bantuan? Hubungi CS kami via WhatsApp di +62 812-3456-7890');
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>