<?php
// File: pages/register.php
$pageTitle = 'Daftar';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        $fullName = sanitize($_POST['full_name'] ?? '');

        if (!$username || strlen($username) < 4) {
            $error = 'Username minimal 4 karakter.';
        } elseif (!$email) {
            $error = 'Email tidak valid.';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
        } elseif ($password !== $confirmPass) {
            $error = 'Password tidak cocok.';
        } else {
            // Cek duplikasi
            $existing = db()->fetchOne("SELECT id FROM users WHERE email = ? OR username = ?", 'ss', $email, $username);
            if ($existing) {
                $error = 'Email atau username sudah terdaftar.';
            } else {
                $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                $userId = db()->insert(
                    "INSERT INTO users (username, email, password, full_name, is_verified) VALUES (?, ?, ?, ?, 0)",
                    'ssss', $username, $email, $hashedPass, $fullName
                );
                if ($userId) {
                    // Generate and send OTP
                    $otp = generateOTP();
                    storeOTP($email, $otp, 'register');
                    sendOtpEmail($email, $otp, 'register');

                    setFlash('info', 'Registrasi sukses! Silakan masukkan kode OTP yang telah dikirimkan ke email Anda untuk mengaktifkan akun.');
                    redirect(APP_URL . '/pages/verify-otp.php?email=' . urlencode($email) . '&type=register');
                } else {
                    $error = 'Gagal membuat akun. Coba lagi.';
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
    <title>Daftar | <?= APP_NAME ?></title>
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
    <div class="auth-box" style="max-width:480px;">
        <div style="text-align:center;margin-bottom:24px;">
            <a href="<?= APP_URL ?>" style="font-family:var(--font-head);font-size:26px;font-weight:900;color:var(--text-primary);">
                ⚡ BoloTopup<span style="color:var(--accent)">.ID</span>
            </a>
        </div>
        <h1 class="auth-title">Buat Akun Baru 🚀</h1>
        <p class="auth-sub">Bergabung dan nikmati kemudahan belanja game</p>

        <?php if ($error): ?>
            <div class="flash-message flash-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfInput() ?>
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="full_name" class="form-control" placeholder="Nama lengkap kamu" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Username *</label>
                <input type="text" name="username" class="form-control" placeholder="Pilih username unik" required minlength="4" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" placeholder="Email aktif kamu" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password *</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
            </div>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:20px;">
                Dengan mendaftar, kamu menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a> kami.
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">✅ Daftar Sekarang</button>
        </form>

        <p style="text-align:center;font-size:14px;color:var(--text-muted);margin-top:20px;">
            Sudah punya akun? <a href="<?= APP_URL ?>/pages/login.php" style="color:var(--accent);font-weight:700;">Masuk</a>
        </p>
    </div>
</div>
</body>
</html>