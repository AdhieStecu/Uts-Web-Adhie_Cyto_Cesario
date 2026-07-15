<?php
// File: pages/reset-password.php
$pageTitle = 'Reset Password';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$error = '';
$success = '';
$token = sanitize($_GET['token'] ?? '');

if (empty($token)) {
    $error = 'Token reset password tidak valid atau tidak ditemukan.';
} else {
    // Check if token exists and is not expired
    $now = date('Y-m-d H:i:s');
    $resetReq = db()->fetchOne(
        "SELECT * FROM password_resets WHERE token = ? AND expires_at > ? LIMIT 1",
        'ss', $token, $now
    );

    if (!$resetReq) {
        $error = 'Tautan reset password ini tidak valid, salah, atau telah kedaluwarsa. Silakan ajukan ulang kembali.';
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
                $error = 'Token tidak valid. Coba lagi.';
            } else {
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (strlen($password) < 6) {
                    $error = 'Kata sandi baru minimal harus terdiri dari 6 karakter.';
                } elseif ($password !== $confirmPassword) {
                    $error = 'Konfirmasi kata sandi baru tidak cocok.';
                } else {
                    // Update user password in DB
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $email = $resetReq['email'];

                    $updateStatus = db()->execute(
                        "UPDATE users SET password = ? WHERE email = ?",
                        'ss', $hashedPassword, $email
                    );

                    if ($updateStatus !== false) {
                        // Delete token from database
                        db()->execute("DELETE FROM password_resets WHERE email = ?", 's', $email);
                        
                        setFlash('success', 'Kata sandi Anda berhasil diperbarui! Silakan masuk kembali. 🎉');
                        redirect(APP_URL . '/pages/login.php');
                    } else {
                        $error = 'Gagal memperbarui kata sandi. Silakan coba kembali beberapa saat lagi.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.2.1">
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js?v=1.2.1" defer></script>
</head>
<body>
<div class="auth-page">
    <div class="auth-box">
        <div style="text-align:center;margin-bottom:30px;">
            <a href="<?= APP_URL ?>" style="font-family:var(--font-head);font-size:26px;font-weight:900;color:var(--text-primary);">
                ⚡ BoloTopup<span style="color:var(--accent)">.ID</span>
            </a>
        </div>
        <h1 class="auth-title">Reset Password 🔐</h1>
        <p class="auth-sub">Masukkan kata sandi baru untuk akun Anda</p>

        <?php if ($error && !$resetReq): ?>
            <div class="flash-message flash-error" style="margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
            <a href="<?= APP_URL ?>/pages/forgot-password.php" class="btn btn-primary btn-block btn-lg" style="margin-bottom:20px; text-decoration:none; text-align:center;">
                🔑 Kirim Ulang Tautan Reset
            </a>
        <?php else: ?>
            
            <?php if ($error): ?>
                <div class="flash-message flash-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrfInput() ?>
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Kata sandi baru (min 6 karakter)" required>
                </div>
                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi kata sandi baru" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom:20px;">🔒 Simpan Kata Sandi</button>
            </form>
        <?php endif; ?>

        <p style="text-align:center;font-size:14px;color:var(--text-muted);">
            Kembali ke 
            <a href="<?= APP_URL ?>/pages/login.php" style="color:var(--accent);font-weight:700;">Halaman Masuk</a>
        </p>
    </div>
</div>
</body>
</html>
