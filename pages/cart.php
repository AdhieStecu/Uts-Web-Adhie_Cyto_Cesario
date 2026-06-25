<?php
// File: pages/cart.php
$pageTitle = 'Keranjang Belanja';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

// Handle hapus dari cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token tidak valid.');
        redirect(APP_URL . '/pages/cart.php');
    }
    $action = $_POST['action'] ?? '';
    $cartId = (int)($_POST['cart_id'] ?? 0);

    if ($action === 'remove') {
        db()->execute("DELETE FROM cart WHERE id = ? AND user_id = ?", 'ii', $cartId, $_SESSION['user_id']);
        setFlash('success', 'Item dihapus dari keranjang.');
    } elseif ($action === 'update') {
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        db()->execute("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", 'iii', $qty, $cartId, $_SESSION['user_id']);
    }
    redirect(APP_URL . '/pages/cart.php');
}

$cartItems = db()->fetchAll(
    "SELECT c.*, p.name, p.price, p.image, p.stock, p.game_name, p.status 
     FROM cart c JOIN products p ON c.product_id = p.id 
     WHERE c.user_id = ?",
    'i', $_SESSION['user_id']
);

$total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;">
    <h1 style="font-family:var(--font-head);font-size:28px;font-weight:800;margin-bottom:30px;">🛒 Keranjang Belanja</h1>

    <?php if (empty($cartItems)): ?>
    <div class="empty-state">
        <span class="empty-icon">🛒</span>
        <h3>Keranjang Kosong</h3>
        <p>Belum ada produk di keranjang kamu.</p>
        <a href="<?= APP_URL ?>" class="btn btn-primary mt-20">🎮 Mulai Belanja</a>
    </div>
    <?php else: ?>
    <div class="grid-2" style="gap:30px;align-items:start;">
        <div>
            <?php foreach ($cartItems as $item): ?>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-body" style="display:flex;gap:16px;align-items:center;">
                    <div style="width:70px;height:70px;background:var(--bg-card2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;overflow:hidden;">
                        <?php if ($item['image']): ?>
                            <img src="<?= getImageUrl($item['image']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.outerHTML='🎮'">
                        <?php else: ?>🎮<?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <h4 style="font-weight:700;margin-bottom:4px;"><?= htmlspecialchars($item['name']) ?></h4>
                        <div style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($item['game_name'] ?? '') ?></div>
                        <div style="color:var(--accent);font-weight:800;font-family:var(--font-head);margin-top:4px;"><?= rupiah($item['price']) ?></div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px;">
                        <form method="POST" style="display:flex;align-items:center;gap:8px;">
                            <?= csrfInput() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>"
                                   style="width:60px;text-align:center;" class="form-control" onchange="this.form.submit()">
                        </form>
                        <form method="POST">
                            <?= csrfInput() ?>
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                            <button type="button" class="btn btn-danger btn-sm" data-confirm="Hapus item ini?">🗑️ Hapus</button>
                        </form>
                    </div>
                    <div style="text-align:right;flex-shrink:0;min-width:100px;">
                        <div style="font-size:12px;color:var(--text-muted);">Subtotal</div>
                        <div style="font-weight:800;color:var(--accent);font-family:var(--font-head);"><?= rupiah($item['price'] * $item['quantity']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- SUMMARY -->
        <div class="card" style="position:sticky;top:100px;">
            <div class="card-body">
                <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;margin-bottom:20px;">🧾 Ringkasan</h3>
                <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                    <span style="color:var(--text-muted);">Total Item</span>
                    <span><?= array_sum(array_column($cartItems, 'quantity')) ?> item</span>
                </div>
                <hr style="border-color:var(--border);margin:16px 0;">
                <div style="display:flex;justify-content:space-between;font-family:var(--font-head);font-size:22px;font-weight:800;margin-bottom:20px;">
                    <span>Total</span>
                    <span style="color:var(--accent);"><?= rupiah($total) ?></span>
                </div>
                <?php if (count($cartItems) === 1): ?>
                <a href="<?= APP_URL ?>/pages/checkout.php?product_id=<?= $cartItems[0]['product_id'] ?>" class="btn btn-primary btn-block btn-lg">
                    ⚡ Checkout Sekarang
                </a>
                <?php else: ?>
                <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px;text-align:center;">
                    Checkout item satu per satu
                </div>
                <?php foreach ($cartItems as $item): ?>
                <a href="<?= APP_URL ?>/pages/checkout.php?product_id=<?= $item['product_id'] ?>" class="btn btn-outline btn-block" style="margin-bottom:8px;">
                    ⚡ Beli: <?= htmlspecialchars(excerpt($item['name'], 25)) ?>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
                <a href="<?= APP_URL ?>" class="btn btn-outline btn-block" style="margin-top:10px;">← Lanjut Belanja</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>