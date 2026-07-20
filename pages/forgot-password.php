<?php
// File: pages/forgot-password.php
$pageTitle = 'Lupa Password';
require_once __DIR__ . '/../inc/functions.php';

if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid. Coba lagi.';
    } else {
        $email = sanitize($_POST['email'] ?? '');

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } else {
            // Check if user exists
            $user = db()->fetchOne("SELECT * FROM users WHERE email = ? LIMIT 1", 's', $email);

            if ($user) {
                // Generate and send OTP
                $otp = generateOTP();
                storeOTP($email, $otp, 'forgot_password');
                
                if (sendOtpEmail($email, $otp, 'forgot_password')) {
                    setFlash('info', 'Kode OTP reset password telah dikirim ke email Anda! Silakan periksa inbox.');
                    redirect(APP_URL . '/pages/verify-otp.php?email=' . urlencode($email) . '&type=forgot_password');
                } else {
                    $error = 'Gagal mengirim email OTP reset password. Pastikan konfigurasi SMTP di server Anda benar.';
                }
            } else {
                $error = 'Alamat email tidak terdaftar di sistem kami.';
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
    <title>Lupa Password | <?= APP_NAME ?></title>
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
        <h1 class="auth-title">Lupa Password? 🔑</h1>
        <p class="auth-sub">Masukkan email terdaftar Anda untuk menerima kode OTP verifikasi pengaturan ulang kata sandi</p>

        <?php if ($error): ?>
            <div class="flash-message flash-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash-message flash-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfInput() ?>
            <div class="form-group" style="margin-bottom:24px;">
                <label class="form-label">Alamat Email</label>
                <input type="email" name="email" class="form-control" placeholder="contoh@email.com" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom:20px;">✉️ Kirim Kode OTP</button>
        </form>

        <p style="text-align:center;font-size:14px;color:var(--text-muted);">
            Kembali ke 
            <a href="<?= APP_URL ?>/pages/login.php" style="color:var(--accent);font-weight:700;">Halaman Masuk</a>
        </p>
    </div>
</div>
</body>
</html>
