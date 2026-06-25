<?php
// File: pages/notifications.php
$pageTitle = 'Notifikasi';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

// Mark all as read
if (isset($_GET['read_all'])) {
    db()->execute("UPDATE notifications SET is_read = 1 WHERE user_id = ?", 'i', $_SESSION['user_id']);
    redirect(APP_URL . '/pages/notifications.php');
}

$notifs = db()->fetchAll(
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
    'i', $_SESSION['user_id']
);
// Mark as read
db()->execute("UPDATE notifications SET is_read = 1 WHERE user_id = ?", 'i', $_SESSION['user_id']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;max-width:700px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <h1 style="font-family:var(--font-head);font-size:26px;font-weight:800;">🔔 Notifikasi</h1>
        <a href="?read_all=1" class="btn btn-outline btn-sm">✓ Tandai Semua Dibaca</a>
    </div>

    <?php if (empty($notifs)): ?>
    <div class="empty-state">
        <span class="empty-icon">🔔</span>
        <p>Belum ada notifikasi.</p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px;">
        <?php foreach ($notifs as $notif):
            $icons = ['success'=>'✅','error'=>'❌','warning'=>'⚠️','info'=>'ℹ️'];
            $icon = $icons[$notif['type']] ?? '🔔';
        ?>
        <div class="card" style="<?= !$notif['is_read'] ? 'border-color:var(--primary);' : '' ?>">
            <div class="card-body" style="display:flex;gap:14px;align-items:flex-start;">
                <span style="font-size:24px;flex-shrink:0;"><?= $icon ?></span>
                <div style="flex:1;">
                    <strong><?= htmlspecialchars($notif['title']) ?></strong>
                    <p style="color:var(--text-secondary);font-size:14px;margin-top:4px;"><?= htmlspecialchars($notif['message']) ?></p>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:6px;"><?= timeAgo($notif['created_at']) ?></div>
                </div>
                <?php if ($notif['link']): ?>
                <a href="<?= htmlspecialchars($notif['link']) ?>" class="btn btn-outline btn-sm">Lihat →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>