<?php
// File: admin/withdrawals.php
$pageTitle = 'Kelola Penarikan Dana';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Seed mock data if empty
$count = db()->fetchOne("SELECT COUNT(*) as cnt FROM withdrawals")['cnt'];
if ($count == 0) {
    db()->insert("INSERT INTO withdrawals (user_id, amount, bank_name, account_number, account_name, status) VALUES (2, 50000, 'BCA', '1234567890', 'Demo Seller', 'pending')");
    db()->insert("INSERT INTO withdrawals (user_id, amount, bank_name, account_number, account_name, status) VALUES (2, 120000, 'Mandiri', '9876543210', 'Demo Seller', 'approved')");
}

$id = (int)($_GET['id'] ?? 0);

// Process Approve / Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token CSRF tidak valid.');
        redirect(APP_URL . '/admin/withdrawals.php');
    }
    
    $wd = db()->fetchOne("SELECT * FROM withdrawals WHERE id = ?", 'i', $id);
    if ($wd && $wd['status'] === 'pending') {
        if (isset($_POST['approve'])) {
            db()->execute("UPDATE withdrawals SET status = 'approved', processed_at = NOW() WHERE id = ?", 'i', $id);
            sendNotification($wd['user_id'], 'Penarikan Disetujui! 💸', "Penarikan dana sebesar " . rupiah($wd['amount']) . " telah disetujui dan ditransfer.", 'success');
            setFlash('success', 'Penarikan dana sebesar ' . rupiah($wd['amount']) . ' berhasil disetujui! ✅');
        } elseif (isset($_POST['reject'])) {
            $notes = sanitize($_POST['notes'] ?? 'Ditolak oleh administrator');
            // Reject: return funds back to user
            db()->execute("UPDATE withdrawals SET status = 'rejected', notes = ?, processed_at = NOW() WHERE id = ?", 'si', $notes, $id);
            db()->execute("UPDATE users SET balance = balance + ? WHERE id = ?", 'di', $wd['amount'], $wd['user_id']);
            sendNotification($wd['user_id'], 'Penarikan Ditolak! ❌', "Penarikan dana sebesar " . rupiah($wd['amount']) . " ditolak. Alasan: " . $notes, 'error');
            setFlash('warning', 'Penarikan dana ditolak. Saldo sebesar ' . rupiah($wd['amount']) . ' telah dikembalikan ke pengguna.');
        }
    }
    redirect(APP_URL . '/admin/withdrawals.php');
}

$withdrawals = db()->fetchAll(
    "SELECT w.*, u.username, u.full_name FROM withdrawals w 
     JOIN users u ON w.user_id = u.id 
     ORDER BY w.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penarikan Dana | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.2.1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js?v=1.2.1" defer></script>
</head>
<body>
<div class="admin-topbar">
    <div class="brand">⚡ BoloTopup Admin</div>
    <div class="top-actions">
        <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="<?= APP_URL ?>" class="btn btn-outline btn-sm">🌐 Lihat Website</a>
        <a href="<?= APP_URL ?>/pages/logout.php" class="btn btn-danger btn-sm">Keluar</a>
    </div>
</div>
<div class="admin-wrap">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="brand" style="padding:20px;">⚡ Admin Panel</div>
        <a href="<?= APP_URL ?>/admin/index.php">📊 Dashboard</a>
        <a href="<?= APP_URL ?>/admin/products.php">🎮 Produk</a>
        <a href="<?= APP_URL ?>/admin/categories.php">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/tinjau-seller.php">🔍 Tinjau Seller</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php" class="active">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
        <a href="<?= APP_URL ?>/admin/backup.php">🗄️ Backup Database</a>
    </aside>

    <div class="admin-content">
        <?php showFlash(); ?>
        
        <div class="admin-header">
            <h1 class="admin-title">💸 Kelola Penarikan Dana</h1>
            <span style="color:var(--text-muted);font-size:14px;">Total Permintaan: <?= count($withdrawals) ?></span>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Jumlah</th>
                                <th>Bank / No. Rekening</th>
                                <th>Atas Nama</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($withdrawals as $wd): ?>
                            <tr>
                                <td>#<?= $wd['id'] ?></td>
                                <td><strong><?= htmlspecialchars($wd['username']) ?></strong></td>
                                <td><strong style="color:var(--accent);"><?= rupiah($wd['amount']) ?></strong></td>
                                <td><?= htmlspecialchars($wd['bank_name']) ?> - <code><?= htmlspecialchars($wd['account_number']) ?></code></td>
                                <td><?= htmlspecialchars($wd['account_name']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $wd['status'] === 'approved' ? 'completed' : ($wd['status'] === 'rejected' ? 'cancelled' : 'pending') ?>">
                                        <?= ucfirst($wd['status']) ?>
                                    </span>
                                </td>
                                <td><?= timeAgo($wd['created_at']) ?></td>
                                <td>
                                    <?php if ($wd['status'] === 'pending'): ?>
                                        <div style="display:flex; gap:8px;">
                                            <form method="POST" action="?id=<?= $wd['id'] ?>" style="display:inline;" onsubmit="return confirm('Setujui penarikan ini?')">
                                                <?= csrfInput() ?>
                                                <button type="submit" name="approve" class="btn btn-primary btn-sm">Setujui</button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="showRejectModal(<?= $wd['id'] ?>)">Tolak</button>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted); font-size:12px;">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showRejectModal(id) {
    Swal.fire({
        title: 'Tolak Penarikan Dana',
        input: 'text',
        inputLabel: 'Alasan Penolakan:',
        inputPlaceholder: 'Tulis alasan penolakan di sini...',
        showCancelButton: true,
        confirmButtonText: 'Tolak Penarikan',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'swal2-custom-popup',
            title: 'swal2-custom-title',
            confirmButton: 'swal2-confirm swal2-custom-confirm-btn',
            cancelButton: 'swal2-cancel swal2-custom-cancel-btn'
        },
        inputValidator: (value) => {
            if (!value) {
                return 'Alasan penolakan wajib diisi!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?id=' + id;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?= $_SESSION['csrf_token'] ?>';
            
            const rejectInput = document.createElement('input');
            rejectInput.type = 'hidden';
            rejectInput.name = 'reject';
            rejectInput.value = '1';
            
            const notesInput = document.createElement('input');
            notesInput.type = 'hidden';
            notesInput.name = 'notes';
            notesInput.value = result.value;
            
            form.appendChild(csrfInput);
            form.appendChild(rejectInput);
            form.appendChild(notesInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
</body>
</html>
