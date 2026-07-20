<?php
// File: admin/categories.php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$action = sanitize($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    db()->execute("DELETE FROM categories WHERE id = ?", 'i', $id);
    setFlash('success', 'Kategori berhasil dihapus.');
    redirect(APP_URL . '/admin/categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $slug = slugify($name);
    $icon = sanitize($_POST['icon'] ?? '🎮');
    
    // Upload image if provided
    if (!empty($_POST['icon_base64'])) {
        $uploaded = uploadImage($_POST['icon_base64'], 'category');
        if ($uploaded) {
            $icon = $uploaded;
        }
    }
    $desc = sanitize($_POST['description'] ?? '');
    $active = isset($_POST['is_active']) ? 1 : 0;
    $sort = (int)($_POST['sort_order'] ?? 0);

    if ($id) {
        db()->execute("UPDATE categories SET name=?,slug=?,icon=?,description=?,is_active=?,sort_order=? WHERE id=?", 'ssssiii', $name,$slug,$icon,$desc,$active,$sort,$id);
        setFlash('success', 'Kategori berhasil diupdate.');
    } else {
        db()->insert("INSERT INTO categories (name,slug,icon,description,is_active,sort_order) VALUES (?,?,?,?,?,?)", 'ssssii', $name,$slug,$icon,$desc,$active,$sort);
        setFlash('success', 'Kategori berhasil ditambahkan.');
    }
    redirect(APP_URL . '/admin/categories.php');
}

$editCat = null;
if (($action === 'edit') && $id) $editCat = db()->fetchOne("SELECT * FROM categories WHERE id = ?", 'i', $id);
$categories = db()->fetchAll("SELECT *, (SELECT COUNT(*) FROM products WHERE category_id = categories.id) as product_count FROM categories ORDER BY sort_order");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori | Admin</title>
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
        <a href="<?= APP_URL ?>/admin/categories.php" class="active">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/tinjau-seller.php">🔍 Tinjau Seller</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
        <a href="<?= APP_URL ?>/admin/backup.php">🗄️ Backup Database</a>
    </aside>
    <div class="admin-content">
        <?php showFlash(); ?>

        <?php if ($action === 'add' || ($action === 'edit' && $editCat)): ?>
        <div class="admin-header">
            <h1 class="admin-title"><?= $action==='edit'?'✏️ Edit':'➕ Tambah' ?> Kategori</h1>
            <a href="<?= APP_URL ?>/admin/categories.php" class="btn btn-outline">← Kembali</a>
        </div>
        <div class="card" style="max-width:600px;">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfInput() ?>
                    <div class="form-group">
                        <label class="form-label">Nama Kategori *</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editCat['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Icon (Emoji) *</label>
                        <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($editCat['icon'] ?? '🎮') ?>" placeholder="Contoh: 💎 🎮 👤">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gambar Kategori (Opsional, menggantikan Icon)</label>
                        <input type="file" id="category_image" class="form-control" accept="image/*">
                        <input type="hidden" name="icon_base64" id="icon_base64">
                        <div style="margin-top:10px;">
                            <img id="category_preview" src="<?= preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $editCat['icon'] ?? '') ? getImageUrl($editCat['icon']) : '' ?>" style="max-width:100px;max-height:100px;border-radius:8px;object-fit:cover;<?= preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $editCat['icon'] ?? '') ? '' : 'display:none;' ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editCat['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Urutan Tampil</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $editCat['sort_order'] ?? 0 ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                            <input type="checkbox" name="is_active" <?= ($editCat['is_active'] ?? 1) ? 'checked' : '' ?> style="width:18px;height:18px;">
                            <span>Aktif (tampil di menu)</span>
                        </label>
                    </div>
                    <div style="display:flex;gap:12px;">
                        <a href="<?= APP_URL ?>/admin/categories.php" class="btn btn-outline">Batal</a>
                        <button type="submit" class="btn btn-primary"><?= $action==='edit'?'💾 Simpan':'➕ Tambah' ?></button>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <div class="admin-header">
            <h1 class="admin-title">📂 Kelola Kategori</h1>
            <a href="?action=add" class="btn btn-primary">+ Tambah Kategori</a>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Icon</th><th>Nama</th><th>Slug</th><th>Produk</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td style="font-size:28px;"><?= renderCategoryIcon($cat['icon'], '', 'width:40px;height:40px;') ?></td>
                                <td><strong><?= htmlspecialchars($cat['name']) ?></strong><br><small style="color:var(--text-muted);"><?= htmlspecialchars($cat['description'] ?? '') ?></small></td>
                                <td><code style="color:var(--accent);"><?= $cat['slug'] ?></code></td>
                                <td><?= $cat['product_count'] ?> produk</td>
                                <td><?= $cat['sort_order'] ?></td>
                                <td><span class="status-badge <?= $cat['is_active']?'status-active':'status-cancelled' ?>"><?= $cat['is_active']?'✅ Aktif':'❌ Nonaktif' ?></span></td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <a href="?action=edit&id=<?= $cat['id'] ?>" class="btn btn-primary btn-sm">✏️ Edit</a>
                                        <a href="?action=delete&id=<?= $cat['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Hapus kategori ini?">🗑️</a>
                                    </div>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    initImageCropper('category_image', 'icon_base64', 'category_preview', 1);
});
</script>
</body>
</html>