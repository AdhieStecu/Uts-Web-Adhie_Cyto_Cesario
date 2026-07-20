<?php
// File: pages/product.php
require_once __DIR__ . '/../inc/functions.php';

$id = (int)($_GET['id'] ?? 0);
$product = db()->fetchOne(
    "SELECT p.*, c.name as cat_name, c.slug as cat_slug, u.username as seller_name 
     FROM products p 
     JOIN categories c ON p.category_id = c.id 
     JOIN users u ON p.seller_id = u.id 
     WHERE p.id = ? AND p.status = 'active'",
    'i', $id
);

if (!$product) {
    setFlash('error', 'Produk tidak ditemukan.');
    redirect(APP_URL . '/index.php');
}

// Update view count
db()->execute("UPDATE products SET views = views + 1 WHERE id = ?", 'i', $id);

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireLogin();
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token tidak valid.');
    } elseif ($_POST['action'] === 'add_cart') {
        $existing = db()->fetchOne("SELECT id FROM cart WHERE user_id = ? AND product_id = ?", 'ii', $_SESSION['user_id'], $id);
        if ($existing) {
            db()->execute("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?", 'ii', $_SESSION['user_id'], $id);
        } else {
            db()->insert("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)", 'ii', $_SESSION['user_id'], $id);
        }
        setFlash('success', 'Produk ditambahkan ke keranjang! 🛒');
        redirect(APP_URL . '/pages/cart.php');
    } elseif ($_POST['action'] === 'buy_now') {
        redirect(APP_URL . '/pages/checkout.php?product_id=' . $id);
    }
}

// Reviews
$reviews = db()->fetchAll(
    "SELECT r.*, u.username FROM reviews r JOIN users u ON r.buyer_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC LIMIT 10",
    'i', $id
);

$pageTitle = $product['name'];
require_once __DIR__ . '/../inc/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;">
    <!-- BREADCRUMB -->
    <nav style="font-size:14px;color:var(--text-muted);margin-bottom:24px;">
        <a href="<?= APP_URL ?>">🏠 Home</a> → 
        <a href="<?= APP_URL ?>/pages/category.php?slug=<?= $product['cat_slug'] ?>"><?= htmlspecialchars($product['cat_name']) ?></a> → 
        <span style="color:var(--text-primary);"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <div class="grid-2" style="gap:40px;align-items:start;">
        <!-- IMAGE -->
        <div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden;height:380px;display:flex;align-items:center;justify-content:center;font-size:80px;">
                <?php if ($product['image']): ?>
                    <img src="<?= getImageUrl($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">
                <?php else: ?>
                    🎮
                <?php endif; ?>
            </div>
        </div>

        <!-- DETAIL -->
        <div>
            <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">
                <?= htmlspecialchars($product['cat_name']) ?> · <?= htmlspecialchars($product['game_name'] ?? '') ?>
            </div>
            <h1 style="font-family:var(--font-head);font-size:26px;font-weight:800;margin-bottom:16px;line-height:1.3;">
                <?= htmlspecialchars($product['name']) ?>
            </h1>

            <!-- RATING & META -->
            <div style="display:flex;gap:20px;margin-bottom:20px;font-size:14px;color:var(--text-secondary);">
                <span>⭐ <?= number_format($product['rating'], 1) ?> (<?= count($reviews) ?> ulasan)</span>
                <span>🛒 <?= $product['sold_count'] ?> terjual</span>
                <span>👁️ <?= $product['views'] ?> dilihat</span>
            </div>

            <!-- HARGA -->
            <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:24px;">
                <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                    <div style="font-size:14px;color:var(--text-muted);text-decoration:line-through;"><?= rupiah($product['original_price']) ?></div>
                    <?php $disc = round((1 - $product['price']/$product['original_price']) * 100); ?>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span style="font-family:var(--font-head);font-size:32px;font-weight:900;color:var(--accent);"><?= rupiah($product['price']) ?></span>
                        <span style="background:#ff4444;color:white;padding:4px 10px;border-radius:20px;font-size:13px;font-weight:700;">-<?= $disc ?>%</span>
                    </div>
                <?php else: ?>
                    <div style="font-family:var(--font-head);font-size:32px;font-weight:900;color:var(--accent);"><?= rupiah($product['price']) ?></div>
                <?php endif; ?>
                
                <div style="display:flex;gap:20px;margin-top:12px;font-size:13px;color:var(--text-secondary);">
                    <span>📦 Stok: <strong style="color:var(--success);"><?= $product['stock'] ?></strong></span>
                    <span>⚡ Pengiriman: <strong><?= $product['delivery_type'] === 'instant' ? 'Instan' : 'Manual' ?></strong></span>
                    <span>🖥️ Platform: <strong><?= htmlspecialchars($product['platform'] ?? '-') ?></strong></span>
                </div>
            </div>

            <!-- SELLER INFO -->
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;padding:14px;background:var(--bg-card);border:1px solid var(--border);border-radius:10px;">
                <div style="width:40px;height:40px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:white;">
                    <?= strtoupper(substr($product['seller_name'], 0, 1)) ?>
                </div>
                <div>
                    <div style="font-weight:700;"><?= htmlspecialchars($product['seller_name']) ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">✅ Seller Terverifikasi</div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <?php if ($product['stock'] > 0): ?>
            <form method="POST">
                <?= csrfInput() ?>
                <div style="display:flex;gap:12px;">
                    <button type="submit" name="action" value="add_cart" class="btn btn-outline btn-lg" style="flex:1;">
                        🛒 Keranjang
                    </button>
                    <button type="submit" name="action" value="buy_now" class="btn btn-primary btn-lg" style="flex:2;">
                        ⚡ Beli Sekarang
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div class="flash-message flash-error">❌ Stok habis</div>
            <?php endif; ?>

            <!-- TRUST INFO -->
            <div style="display:flex;gap:16px;margin-top:16px;font-size:13px;color:var(--text-muted);">
                <span>🛡️ Transaksi Aman</span>
                <span>💰 Garansi Uang Kembali</span>
                <span>🎧 CS 24/7</span>
            </div>
        </div>
    </div>

    <!-- DESKRIPSI -->
    <div style="margin-top:50px;">
        <h2 style="font-family:var(--font-head);font-size:20px;font-weight:800;margin-bottom:16px;">📋 Deskripsi Produk</h2>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px;color:var(--text-secondary);line-height:1.8;">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>
    </div>

    <!-- ULASAN -->
    <div style="margin-top:50px;">
        <h2 style="font-family:var(--font-head);font-size:20px;font-weight:800;margin-bottom:16px;">
            💬 Ulasan Pembeli (<?= count($reviews) ?>)
        </h2>
        <?php if (empty($reviews)): ?>
        <div class="empty-state">
            <span class="empty-icon">💬</span>
            <p>Belum ada ulasan untuk produk ini.</p>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:16px;">
            <?php foreach ($reviews as $review): ?>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:white;font-size:14px;">
                            <?= strtoupper(substr($review['username'], 0, 1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($review['username']) ?></strong>
                    </div>
                    <div style="font-size:18px;"><?= renderStars($review['rating']) ?></div>
                </div>
                <p style="color:var(--text-secondary);"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                <div style="font-size:12px;color:var(--text-muted);margin-top:8px;"><?= timeAgo($review['created_at']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>