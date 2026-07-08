<?php
// File: admin/products.php
// CRUD LENGKAP untuk produk
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$action = sanitize($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);
$categories = db()->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
$message = '';

// --- DELETE ---
if ($action === 'delete' && $id) {
    db()->execute("DELETE FROM products WHERE id = ?", 'i', $id);
    setFlash('success', 'Produk berhasil dihapus.');
    redirect(APP_URL . '/admin/products.php');
}

// --- HANDLE FORM SUBMIT (ADD/EDIT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $origPrice = (float)($_POST['original_price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 1);
    $gameName = sanitize($_POST['game_name'] ?? '');
    $platform = sanitize($_POST['platform'] ?? '');
    $delivery = sanitize($_POST['delivery_type'] ?? 'instant');
    $status = sanitize($_POST['status'] ?? 'active');
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $slug = slugify($name) . '-' . time();

    // Upload gambar
    $image = null;
    if (!empty($_POST['image_base64'])) {
        $image = uploadImage($_POST['image_base64'], 'product');
    } elseif (!empty($_FILES['image']['tmp_name'])) {
        $image = uploadImage($_FILES['image'], 'product');
    }

    if ($id) {
        // UPDATE
        if ($image) {
            db()->execute(
                "UPDATE products SET name=?, category_id=?, description=?, price=?, original_price=?, stock=?, game_name=?, platform=?, delivery_type=?, status=?, is_featured=?, image=? WHERE id=?",
                'sisddissssssi', $name, $catId, $description, $price, $origPrice, $stock, $gameName, $platform, $delivery, $status, $featured, $image, $id
            );
        } else {
            db()->execute(
                "UPDATE products SET name=?, category_id=?, description=?, price=?, original_price=?, stock=?, game_name=?, platform=?, delivery_type=?, status=?, is_featured=? WHERE id=?",
                'sisddisssssi', $name, $catId, $description, $price, $origPrice, $stock, $gameName, $platform, $delivery, $status, $featured, $id
            );
        }
        setFlash('success', 'Produk berhasil diupdate! ✅');
    } else {
        // INSERT
        db()->insert(
            "INSERT INTO products (name, slug, category_id, seller_id, description, price, original_price, stock, game_name, platform, delivery_type, status, is_featured, image) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'ssiisddissssis',
            $name, $slug, $catId, $_SESSION['user_id'], $description, $price, $origPrice, $stock, $gameName, $platform, $delivery, $status, $featured, $image
        );
        setFlash('success', 'Produk berhasil ditambahkan! 🎉');
    }
    redirect(APP_URL . '/admin/products.php');
}

// Ambil data produk untuk edit
$editProduct = null;
if ($action === 'edit' && $id) {
    $editProduct = db()->fetchOne("SELECT * FROM products WHERE id = ?", 'i', $id);
}

// List produk dengan pagination & search
$search = sanitize($_GET['s'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$whereClause = $search ? "WHERE p.name LIKE '%" . db()->escape($search) . "%' OR p.game_name LIKE '%" . db()->escape($search) . "%'" : '';
$total = db()->fetchOne("SELECT COUNT(*) as cnt FROM products p $whereClause")['cnt'];
$products = db()->fetchAll(
    "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id $whereClause ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.2.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js?v=1.2.1" defer></script>
</head>
<body>
<div class="admin-topbar">
    <div class="brand">⚡ BoloTopup Admin</div>
    <div class="top-actions">
        <a href="<?= APP_URL ?>" class="btn btn-outline btn-sm">🌐 Website</a>
        <a href="<?= APP_URL ?>/pages/logout.php" class="btn btn-danger btn-sm">Keluar</a>
    </div>
</div>
<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="brand" style="padding:20px;">⚡ Admin Panel</div>
        <a href="<?= APP_URL ?>/admin/index.php">📊 Dashboard</a>
        <a href="<?= APP_URL ?>/admin/products.php" class="active">🎮 Produk</a>
        <a href="<?= APP_URL ?>/admin/categories.php">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
    </aside>

    <div class="admin-content">
        <?php showFlash(); ?>

        <?php if ($action === 'add' || ($action === 'edit' && $editProduct)): ?>
        <!-- FORM ADD/EDIT -->
        <div class="admin-header">
            <h1 class="admin-title"><?= $action === 'edit' ? '✏️ Edit Produk' : '➕ Tambah Produk' ?></h1>
            <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline">← Kembali</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfInput() ?>
                    <div class="grid-2" style="gap:24px;">
                        <div>
                            <div class="form-group">
                                <label class="form-label">Nama Produk *</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kategori *</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                        <?= getCategoryIconEmoji($cat['icon']) ?> <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Game</label>
                                <input type="text" name="game_name" class="form-control" value="<?= htmlspecialchars($editProduct['game_name'] ?? '') ?>" placeholder="Contoh: Mobile Legends">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Platform</label>
                                <input type="text" name="platform" class="form-control" value="<?= htmlspecialchars($editProduct['platform'] ?? '') ?>" placeholder="Android/iOS, PC, dll.">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Harga Jual (Rp) *</label>
                                <input type="number" name="price" class="form-control" required value="<?= $editProduct['price'] ?? '' ?>" placeholder="15000">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Harga Asli (coret)</label>
                                <input type="number" name="original_price" class="form-control" value="<?= $editProduct['original_price'] ?? '' ?>" placeholder="18000">
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label class="form-label">Stok *</label>
                                <input type="number" name="stock" class="form-control" required value="<?= $editProduct['stock'] ?? 1 ?>" min="0">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tipe Pengiriman</label>
                                <select name="delivery_type" class="form-control">
                                    <option value="instant" <?= ($editProduct['delivery_type'] ?? '') === 'instant' ? 'selected' : '' ?>>⚡ Instan</option>
                                    <option value="manual" <?= ($editProduct['delivery_type'] ?? '') === 'manual' ? 'selected' : '' ?>>📋 Manual</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="active" <?= ($editProduct['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>✅ Aktif</option>
                                    <option value="inactive" <?= ($editProduct['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>❌ Nonaktif</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Gambar Produk</label>
                                <input type="file" id="product_image" class="form-control" accept="image/*">
                                <input type="hidden" name="image_base64" id="image_base64">
                                <div style="margin-top:10px;">
                                    <img id="product_preview" src="<?= !empty($editProduct['image']) ? getImageUrl($editProduct['image']) : '' ?>" style="max-width:150px;max-height:150px;border-radius:8px;object-fit:cover;<?= !empty($editProduct['image']) ? '' : 'display:none;' ?>">
                                </div>
                                <?php if (!empty($editProduct['image'])): ?>
                                <div style="margin-top:8px;font-size:13px;color:var(--text-muted);">
                                    Gambar saat ini: <?= htmlspecialchars($editProduct['image']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                                    <input type="checkbox" name="is_featured" <?= ($editProduct['is_featured'] ?? 0) ? 'checked' : '' ?> style="width:20px;height:20px;">
                                    <span class="form-label" style="margin:0;">🔥 Produk Unggulan (tampil di homepage)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi Produk</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Jelaskan produk secara detail..."><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
                    </div>
                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline">Batal</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <?= $action === 'edit' ? '💾 Simpan Perubahan' : '➕ Tambah Produk' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- PRODUCT LIST -->
        <div class="admin-header">
            <h1 class="admin-title">🎮 Kelola Produk</h1>
            <div style="display:flex;gap:12px;">
                <a href="<?= APP_URL ?>/admin/import_products.php" class="btn btn-outline">📥 Import Produk</a>
                <a href="<?= APP_URL ?>/admin/products.php?action=add" class="btn btn-primary">+ Tambah Produk</a>
            </div>
        </div>

        <!-- SEARCH -->
        <form method="GET" style="margin-bottom:20px;display:flex;gap:12px;">
            <input type="text" name="s" class="form-control" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>" style="max-width:300px;">
            <button type="submit" class="btn btn-primary">🔍 Cari</button>
            <?php if ($search): ?><a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline">✕ Reset</a><?php endif; ?>
        </form>

        <div class="card">
            <div class="card-body">
                <p style="color:var(--text-muted);margin-bottom:16px;font-size:14px;">
                    Total: <?= $total ?> produk <?= $search ? "(filter: \"$search\")" : '' ?>
                </p>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Terjual</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $prod): ?>
                            <tr>
                                <td>
                                    <div style="width:50px;height:50px;background:var(--bg-card2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;overflow:hidden;">
                                        <?php if ($prod['image']): ?>
                                            <img src="<?= getImageUrl($prod['image']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.outerHTML='🎮'">
                                        <?php else: ?>🎮<?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(excerpt($prod['name'], 35)) ?></strong>
                                    <?php if ($prod['is_featured']): ?><span style="font-size:11px;color:var(--gold);"> 🔥 Unggulan</span><?php endif; ?>
                                    <div style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($prod['game_name'] ?? '') ?></div>
                                </td>
                                <td><?= htmlspecialchars($prod['cat_name']) ?></td>
                                <td>
                                    <strong style="color:var(--accent);"><?= rupiah($prod['price']) ?></strong>
                                    <?php if ($prod['original_price'] > $prod['price']): ?>
                                        <div style="font-size:12px;color:var(--text-muted);text-decoration:line-through;"><?= rupiah($prod['original_price']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= $prod['stock'] ?></td>
                                <td><?= $prod['sold_count'] ?></td>
                                <td><span class="status-badge status-<?= $prod['status'] ?>"><?= ucfirst($prod['status']) ?></span></td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <a href="<?= APP_URL ?>/pages/product.php?id=<?= $prod['id'] ?>" class="btn btn-outline btn-sm" target="_blank">👁️</a>
                                        <a href="?action=edit&id=<?= $prod['id'] ?>" class="btn btn-primary btn-sm">✏️</a>
                                        <a href="?action=delete&id=<?= $prod['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Yakin hapus produk ini?">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?= paginate($total, $perPage, $page, APP_URL . '/admin/products.php') ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initImageCropper('product_image', 'image_base64', 'product_preview', 1);
});
</script>
</body>
</html>