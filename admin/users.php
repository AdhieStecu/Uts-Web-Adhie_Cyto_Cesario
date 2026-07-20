<?php
// File: admin/users.php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

// Toggle role/status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $action = sanitize($_POST['action'] ?? '');
    if ($userId && $userId !== $_SESSION['user_id']) {
        if ($action === 'change_role') {
            $newRole = sanitize($_POST['role'] ?? 'user');
            if (in_array($newRole, ['user', 'seller', 'admin'])) {
                db()->execute("UPDATE users SET role = ? WHERE id = ?", 'si', $newRole, $userId);
                setFlash('success', 'Role user berhasil diubah.');
            }
        } elseif ($action === 'delete') {
            db()->execute("DELETE FROM users WHERE id = ? AND role != 'admin'", 'i', $userId);
            setFlash('success', 'User berhasil dihapus.');
        } elseif ($action === 'add_balance') {
            $amount = (float)($_POST['amount'] ?? 0);
            if ($amount > 0) {
                db()->execute("UPDATE users SET balance = balance + ? WHERE id = ?", 'di', $amount, $userId);
                sendNotification($userId, 'Saldo Ditambah 💰', 'Admin menambahkan saldo ' . rupiah($amount) . ' ke akun kamu.', 'success');
                setFlash('success', 'Saldo berhasil ditambahkan.');
            }
        }
    }
    redirect(APP_URL . '/admin/users.php');
}

$search = sanitize($_GET['s'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = $search ? "WHERE username LIKE '%" . db()->escape($search) . "%' OR email LIKE '%" . db()->escape($search) . "%'" : '';
$total = db()->fetchOne("SELECT COUNT(*) as cnt FROM users $where")['cnt'];
$users = db()->fetchAll("SELECT * FROM users $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.2.1">
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
        <a href="<?= APP_URL ?>/admin/categories.php">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php" class="active">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/tinjau-seller.php">🔍 Tinjau Seller</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
        <a href="<?= APP_URL ?>/admin/backup.php">🗄️ Backup Database</a>
    </aside>
    <div class="admin-content">
        <?php showFlash(); ?>
        <div class="admin-header">
            <h1 class="admin-title">👥 Kelola Pengguna</h1>
            <span style="color:var(--text-muted);">Total: <?= $total ?> user</span>
        </div>

        <form method="GET" style="margin-bottom:20px;display:flex;gap:12px;">
            <input type="text" name="s" class="form-control" placeholder="Cari username/email..." value="<?= htmlspecialchars($search) ?>" style="max-width:300px;">
            <button type="submit" class="btn btn-primary">🔍 Cari</button>
            <?php if ($search): ?><a href="<?= APP_URL ?>/admin/users.php" class="btn btn-outline">✕ Reset</a><?php endif; ?>
        </form>

        <div class="card">
            <div class="card-body">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Saldo</th><th>Bergabung</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td style="color:var(--text-muted);">#<?= $user['id'] ?></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:32px;height:32px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:white;font-size:13px;">
                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                        </div>
                                        <strong><?= htmlspecialchars($user['username']) ?></strong>
                                    </div>
                                </td>
                                <td style="color:var(--text-muted);"><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="status-badge status-completed">👑 Admin</span>
                                    <?php elseif ($user['role'] === 'seller'): ?>
                                        <span class="status-badge status-active" style="background:#06b6d4; color:#fff; font-weight:700;">🏪 Seller</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">👤 User</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color:var(--gold);font-weight:700;"><?= rupiah($user['balance']) ?></td>
                                <td style="color:var(--text-muted);font-size:13px;"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['id'] !== (int)$_SESSION['user_id']): ?>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                        <form method="POST" style="display:inline-block;">
                                            <?= csrfInput() ?>
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="action" value="change_role">
                                            <select name="role" class="form-control" style="font-size:12px; padding:4px 8px; width:auto; height:32px; background:var(--bg-card2); color:var(--text-primary); border:1px solid var(--border); border-radius:6px; cursor:pointer;" onchange="this.form.submit()">
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>👤 User</option>
                                                <option value="seller" <?= $user['role'] === 'seller' ? 'selected' : '' ?>>🏪 Seller</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>👑 Admin</option>
                                            </select>
                                        </form>
                                        <!-- Tambah Saldo Modal -->
                                        <button class="btn btn-gold btn-sm" onclick="addBalance(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">💰 Saldo</button>
                                        <form method="POST" style="display:inline;">
                                            <?= csrfInput() ?>
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="button" class="btn btn-danger btn-sm" data-confirm="Hapus user <?= htmlspecialchars($user['username']) ?>?">🗑️</button>
                                        </form>
                                    </div>
                                    <?php else: ?>
                                    <span style="color:var(--text-muted);font-size:13px;">Akun ini</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?= paginate($total, $perPage, $page, APP_URL . '/admin/users.php') ?>
            </div>
        </div>
    </div>
</div>

<script>
function addBalance(id, name) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '💰 Tambah Saldo',
            html: `
                <div style="text-align: left; margin-bottom: 12px;">
                    <span style="color: var(--text-secondary); font-size: 14px;">Username: </span>
                    <strong style="color: var(--accent);">${name}</strong>
                </div>
                <input type="number" id="swalBalanceAmount" class="swal2-input form-control" style="width: 100%; margin: 0; background: var(--bg-card2); color: var(--text-primary); border-color: var(--border);" placeholder="Nominal saldo (Rp)" min="1000" required>
            `,
            background: '#0d1530',
            color: '#e8f0fe',
            showCancelButton: true,
            confirmButtonText: '💰 Tambahkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0066ff',
            cancelButtonColor: '#445577',
            customClass: {
                popup: 'swal2-custom-popup',
                title: 'swal2-custom-title',
                confirmButton: 'swal2-custom-confirm-btn',
                cancelButton: 'swal2-custom-cancel-btn'
            },
            preConfirm: () => {
                const amount = Swal.getPopup().querySelector('#swalBalanceAmount').value;
                if (!amount || amount < 1000) {
                    Swal.showValidationMessage(`Nominal minimal Rp 1.000`);
                }
                return { amount: amount };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                const csrfTokenEl = document.querySelector('input[name="csrf_token"]');
                const csrfVal = csrfTokenEl ? csrfTokenEl.value : '';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="${csrfVal}">
                    <input type="hidden" name="action" value="add_balance">
                    <input type="hidden" name="user_id" value="${id}">
                    <input type="hidden" name="amount" value="${result.value.amount}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    } else {
        // Fallback prompt
        const amount = prompt('Masukkan nominal tambah saldo untuk ' + name + ' (Min Rp 1.000):');
        if (amount && amount >= 1000) {
            const form = document.createElement('form');
            form.method = 'POST';
            const csrfTokenEl = document.querySelector('input[name="csrf_token"]');
            const csrfVal = csrfTokenEl ? csrfTokenEl.value : '';
            form.innerHTML = `
                <input type="hidden" name="csrf_token" value="${csrfVal}">
                <input type="hidden" name="action" value="add_balance">
                <input type="hidden" name="user_id" value="${id}">
                <input type="hidden" name="amount" value="${amount}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
</body>
</html>