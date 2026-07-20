<?php
// File: inc/sidebar.php
if (!isLoggedIn()) return;
$user = currentUser();
$activePage = isset($activePage) ? $activePage : '';
?>
<div class="sidebar">
    <div class="sidebar-avatar" style="overflow:hidden;background:#ccc;display:flex;align-items:center;justify-content:center;">
        <?php if ($user['avatar'] && $user['avatar'] !== 'default.png'): ?>
            <img src="<?= getImageUrl($user['avatar']) ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php else: ?>
            <span style="color:white;font-weight:800;font-size:28px;"><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
        <?php endif; ?>
    </div>
    <div class="sidebar-name"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></div>
    <div class="sidebar-balance"><?= rupiah($user['balance']) ?></div>
    <div style="text-align:center;margin-bottom:20px;">
        <a href="<?= APP_URL ?>/pages/topup-balance.php" class="btn btn-gold btn-sm">+ Isi Saldo</a>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= APP_URL ?>/pages/dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">🏠 Dashboard</a>
        <a href="<?= APP_URL ?>/pages/orders.php" class="<?= $activePage === 'orders' ? 'active' : '' ?>">📦 Pesanan Saya</a>
        <a href="<?= APP_URL ?>/pages/cart.php" class="<?= $activePage === 'cart' ? 'active' : '' ?>">🛒 Keranjang</a>
        <a href="<?= APP_URL ?>/pages/notifications.php" class="<?= $activePage === 'notifications' ? 'active' : '' ?>">🔔 Notifikasi</a>
        <a href="<?= APP_URL ?>/pages/profile.php" class="<?= $activePage === 'profile' ? 'active' : '' ?>">⚙️ Edit Profil</a>
        <?php 
        $isSeller = ($user['role'] === 'seller') || (db()->fetchOne("SELECT COUNT(*) as cnt FROM products WHERE seller_id = ?", 'i', $user['id'])['cnt'] > 0);
        if ($isSeller): 
        ?>
            <a href="<?= APP_URL ?>/pages/seller-dashboard.php" class="<?= $activePage === 'seller-dashboard' ? 'active' : '' ?>">🏪 Dashboard Penjual</a>
        <?php endif; ?>
        <?php if (isAdmin()): ?>
            <a href="<?= APP_URL ?>/admin/index.php" style="color:var(--gold);">⚡ Panel Admin</a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/pages/logout.php" style="color:var(--error);">🚪 Keluar</a>
    </nav>
</div>
