<?php
// File: pages/forgot-password.php
$pageTitle = 'Lupa Password';
require_once __DIR__ . '/../includes/functions.php';

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
                // Delete previous tokens for this email
                db()->execute("DELETE FROM password_resets WHERE email = ?", 's', $email);

                // Generate secure random token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Save token to DB
                db()->insert(
                    "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)",
                    'sss', $email, $token, $expiresAt
                );

                // Send Reset Link Email
                $resetLink = APP_URL . "/pages/reset-password.php?token=" . $token;
                $subject = "Reset Kata Sandi Anda - " . APP_NAME . " ⚡";
                
                $emailContent = "
                    <p>Halo <strong>" . htmlspecialchars($user['full_name'] ?: $user['username']) . "</strong>,</p>
                    <p>Kami menerima permintaan untuk mereset kata sandi akun Anda di <strong>" . htmlspecialchars(APP_NAME) . "</strong>.</p>
                    <p>Silakan klik tombol di bawah ini untuk mereset kata sandi Anda. Tautan ini akan kedaluwarsa dalam 1 jam.</p>
                    <div style='text-align:center; margin: 30px 0;'>
                        <a href='" . htmlspecialchars($resetLink) . "' class='btn btn-accent' style='display:inline-block; padding:12px 24px; background-color:#f59e0b; color:#0f172a !important; text-decoration:none; font-weight:600; border-radius:6px;'>🔑 Reset Kata Sandi</a>
                    </div>
                    <div class='divider'></div>
                    <p style='font-size:12px; color:#64748b;'>Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser Anda:</p>
                    <p style='font-size:11px; color:#3b82f6; word-break:break-all;'><a href='" . htmlspecialchars($resetLink) . "' style='color:#3b82f6;'>" . htmlspecialchars($resetLink) . "</a></p>
                    <p style='font-size:12px; color:#64748b; margin-top:20px;'>Jika Anda tidak meminta pengaturan ulang ini, Anda dapat mengabaikan email ini dengan aman.</p>
                ";

                if (sendEmail($email, $subject, $emailContent)) {
                    $success = 'Instruksi reset password telah dikirim ke email Anda! Silakan periksa inbox atau spam. 📧';
                } else {
                    $error = 'Gagal mengirim email reset password. Pastikan konfigurasi SMTP di server Anda benar.';
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
        <p class="auth-sub">Masukkan email terdaftar Anda untuk menerima tautan pengaturan ulang kata sandi</p>

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
            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom:20px;">✉️ Kirim Tautan Reset</button>
        </form>

        <p style="text-align:center;font-size:14px;color:var(--text-muted);">
            Kembali ke 
            <a href="<?= APP_URL ?>/pages/login.php" style="color:var(--accent);font-weight:700;">Halaman Masuk</a>
        </p>
    </div>
</div>
</body>
</html>
