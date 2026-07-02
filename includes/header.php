<?php
// File: includes/header.php
require_once __DIR__ . '/functions.php';
$cartCount = isLoggedIn() ? cartCount() : 0;
$notifCount = isLoggedIn() ? unreadNotifCount($_SESSION['user_id']) : 0;
$categories = db()->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' : '' ?><?= APP_NAME ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? $pageDesc : 'Marketplace game terpercaya di Indonesia' ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.2.1">
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/favicon.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
    <div class="container">
        <div class="topbar-left">
            <span>🇮🇩 ID - IDR</span>
            <span>|</span>
            <span>📱 Download App</span>
        </div>
        <div class="topbar-right">
            <a href="<?= APP_URL ?>/pages/faq.php">❓ Bantuan</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= APP_URL ?>/pages/dashboard.php">👤 <?= htmlspecialchars($_SESSION['username']) ?></a>
                <?php if (isAdmin()): ?>
                    <a href="<?= APP_URL ?>/admin/index.php" class="admin-link">⚙️ Admin</a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/pages/logout.php">Keluar</a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/pages/login.php">Masuk</a>
                <a href="<?= APP_URL ?>/pages/register.php" class="btn-topbar">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- HEADER UTAMA -->
<header class="main-header">
    <div class="container">
        <a href="<?= APP_URL ?>" class="logo">
            <span class="logo-icon">⚡</span>
            <span class="logo-text">BoloTopup<span class="logo-dot">.ID</span></span>
        </a>

        <!-- SEARCH WRAPPER (Itemku Style) -->
        <div style="flex: 1; display: flex; flex-direction: column; max-width: 600px; margin: 0 20px;">
            <form class="search-bar" action="<?= APP_URL ?>/pages/search.php" method="GET" style="margin-bottom: 6px; width: 100%;">
                <input type="text" name="q" placeholder="Cari Game, Diamond, Hero..." 
                       value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button type="submit">Cari</button>
            </form>
            <div class="search-tags" style="display: flex; gap: 8px; font-size: 11px; flex-wrap: wrap;">
                <a href="<?= APP_URL ?>/pages/search.php?q=Robux" style="color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.12); padding: 2px 8px; border-radius: 4px; font-weight: 600; text-decoration: none;">Robux 5 Hari</a>
                <a href="<?= APP_URL ?>/pages/search.php?q=plants" style="color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.12); padding: 2px 8px; border-radius: 4px; font-weight: 600; text-decoration: none;">plants vs brainrots</a>
                <a href="<?= APP_URL ?>/pages/search.php?q=Blox+Fruit" style="color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.12); padding: 2px 8px; border-radius: 4px; font-weight: 600; text-decoration: none;">Akun Blox Fruit</a>
                <a href="<?= APP_URL ?>/pages/search.php?q=Mobile+Legends" style="color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.12); padding: 2px 8px; border-radius: 4px; font-weight: 600; text-decoration: none;">MLBB</a>
            </div>
        </div>

        <!-- HEADER ACTIONS -->
        <div class="header-actions">
            <!-- Theme Toggle Switch -->
            <button id="themeToggleBtn" class="action-btn" title="Ganti Tema" style="border-radius:50%; font-size: 16px; cursor: pointer;">
                🌙
            </button>
            <?php if (isLoggedIn()): ?>
                <a href="<?= APP_URL ?>/pages/notifications.php" class="action-btn" title="Notifikasi">
                    🔔
                    <?php if ($notifCount > 0): ?>
                        <span class="badge"><?= $notifCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= APP_URL ?>/pages/cart.php" class="action-btn" title="Keranjang">
                    🛒
                    <?php if ($cartCount > 0): ?>
                        <span class="badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <?php $headerUser = currentUser(); ?>
                <a href="<?= APP_URL ?>/pages/dashboard.php" class="user-avatar-btn">
                    <?php if ($headerUser && $headerUser['avatar'] && $headerUser['avatar'] !== 'default.png'): ?>
                        <img src="<?= getImageUrl($headerUser['avatar']) ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;vertical-align:middle;display:inline-block;">
                    <?php else: ?>
                        <span><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></span>
                    <?php endif; ?>
                    <?= htmlspecialchars($_SESSION['username']) ?>
                </a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/pages/login.php" class="btn btn-outline">Masuk</a>
                <a href="<?= APP_URL ?>/pages/register.php" class="btn btn-primary">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- NAVIGATION -->
<nav class="main-nav">
    <div class="container">
        <a href="<?= APP_URL ?>/pages/categories.php" class="nav-all-cat">☰ Semua Kategori</a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= APP_URL ?>/pages/category.php?slug=<?= $cat['slug'] ?>">
                <?= renderCategoryIcon($cat['icon']) ?> <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

<!-- FLASH MESSAGE -->
<div class="container">
    <?php showFlash(); ?>
</div>