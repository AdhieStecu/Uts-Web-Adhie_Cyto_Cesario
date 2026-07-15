<?php
// File: admin/test-smtp.php
$pageTitle = 'Uji Coba SMTP';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$error = '';
$success = null;
$debugOutput = '';
$test_to = SMTP_FROM_EMAIL ?: SMTP_USER ?: '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $test_to = filter_var($_POST['test_to'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$test_to) {
            $error = 'Alamat email tujuan tidak valid.';
        } elseif (empty(SMTP_USER) || empty(SMTP_PASS)) {
            $error = 'Kredensial SMTP belum dikonfigurasi di berkas .env!';
        } else {
            $subject = "Uji Coba Sistem Email " . APP_NAME;
            $body = "<h2>Halo Admin!</h2>
            <p>Jika Anda menerima email ini, berarti sistem SMTP pada website <strong>" . htmlspecialchars(APP_NAME) . "</strong> telah berhasil terkonfigurasi dengan benar! 🎉</p>
            <p>Silakan gunakan email ini untuk notifikasi otomatis di website Anda.</p>";
            
            // Tangkap output debug dari fungsi sendEmail
            ob_start();
            $success = sendEmail($test_to, $subject, $body, '', true);
            $debugOutput = ob_get_clean();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Coba SMTP | Admin</title>
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
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php" class="active">📧 Test SMTP</a>
        <a href="<?= APP_URL ?>/admin/backup.php">🗄️ Backup Database</a>
    </aside>

    <!-- CONTENT -->
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">📧 Pengujian SMTP Google</h1>
            <span style="color:var(--text-muted);font-size:14px;">Uji koneksi pengiriman email otomatis</span>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px;">
            <!-- CONFIG CARD -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head);font-size:18px;margin-bottom:16px;font-weight:700;">⚙️ Konfigurasi .env Saat Ini</h3>
                    <table style="width:100%; border-collapse: collapse; font-size: 14px;">
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px 0; color: var(--text-muted);">SMTP Host</td>
                            <td style="padding: 10px 0; text-align: right; font-weight: bold;"><?= htmlspecialchars(SMTP_HOST) ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px 0; color: var(--text-muted);">SMTP Port</td>
                            <td style="padding: 10px 0; text-align: right; font-weight: bold;"><?= SMTP_PORT ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px 0; color: var(--text-muted);">Encryption</td>
                            <td style="padding: 10px 0; text-align: right; font-weight: bold;"><span class="badge"><?= htmlspecialchars(SMTP_SECURE) ?></span></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px 0; color: var(--text-muted);">SMTP User (Gmail)</td>
                            <td style="padding: 10px 0; text-align: right; font-weight: bold;"><?= htmlspecialchars(SMTP_USER ?: '(Kosong)') ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px 0; color: var(--text-muted);">App Password</td>
                            <td style="padding: 10px 0; text-align: right; font-weight: bold;">
                                <?= SMTP_PASS ? '<span style="color:var(--success);">🔑 Terkonfigurasi</span>' : '<span style="color:var(--error);">❌ Belum Terisi</span>' ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; color: var(--text-muted);">Nama Pengirim</td>
                            <td style="padding: 10px 0; text-align: right; font-weight: bold;"><?= htmlspecialchars(SMTP_FROM_NAME) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- TEST FORM CARD -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head);font-size:18px;margin-bottom:16px;font-weight:700;">🚀 Kirim Email Uji Coba</h3>
                    
                    <?php if ($error): ?>
                        <div class="flash-message flash-error" style="margin-bottom: 15px;"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success === true): ?>
                        <div class="flash-message flash-success" style="margin-bottom: 15px;">
                            <strong>Berhasil!</strong> Email uji coba terkirim ke <strong><?= htmlspecialchars($test_to) ?></strong>. Silakan periksa inbox/spam.
                        </div>
                    <?php elseif ($success === false): ?>
                        <div class="flash-message flash-error" style="margin-bottom: 15px;">
                            <strong>Gagal!</strong> Email gagal dikirim. Silakan lihat log debug di bawah.
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <?= csrfInput() ?>
                        <div class="form-group">
                            <label class="form-label" for="test_to">Alamat Email Tujuan</label>
                            <input type="email" id="test_to" name="test_to" class="form-control" placeholder="contoh: email-saya@gmail.com" value="<?= htmlspecialchars($test_to) ?>" required>
                            <small style="display:block;margin-top:6px;color:var(--text-muted);">
                                Secara default menggunakan email pengirim atau akun SMTP.
                            </small>
                        </div>
                        <button type="submit" name="send_test" class="btn btn-primary btn-block" style="margin-top:20px;">
                            ⚡ Mulai Pengujian Email
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- DEBUG LOGS -->
        <?php if (!empty($debugOutput) || $success === false): ?>
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head);font-size:18px;margin-bottom:12px;font-weight:700;color:var(--accent);">📋 Log Percakapan SMTP Detail</h3>
                    <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">
                        Log di bawah ini dikirim langsung dari PHPMailer untuk membantu Anda melacak alasan kegagalan koneksi:
                    </p>
                    <div style="background: #090d1a; border: 1px solid var(--border); border-radius: 8px; padding: 15px; max-height: 400px; overflow-y: auto; font-family: monospace; white-space: pre-wrap;">
                        <?= $debugOutput ?: 'Tidak ada log output debug.' ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>