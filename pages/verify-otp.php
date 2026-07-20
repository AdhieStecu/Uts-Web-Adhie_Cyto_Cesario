<?php
// File: pages/verify-otp.php
$pageTitle = 'Verifikasi OTP';
require_once __DIR__ . '/../inc/functions.php';

if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$email = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL);
$type = sanitize($_GET['type'] ?? 'register'); // 'register' or 'forgot_password'

if (!$email || !in_array($type, ['register', 'forgot_password'])) {
    setFlash('error', 'Parameter verifikasi tidak valid.');
    redirect(APP_URL . '/pages/login.php');
}

$error = '';
$success = '';

// Handle OTP submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } elseif (isset($_POST['verify_otp'])) {
        $otpCode = sanitize($_POST['otp_code'] ?? '');
        
        if (strlen($otpCode) !== 6) {
            $error = 'Kode OTP harus berupa 6 digit angka.';
        } elseif (verifyOTPCode($email, $otpCode, $type)) {
            if ($type === 'register') {
                // Verify user
                db()->execute("UPDATE users SET is_verified = 1 WHERE email = ?", 's', $email);
                
                // Fetch verified user details to auto-login
                $user = db()->fetchOne("SELECT * FROM users WHERE email = ? LIMIT 1", 's', $email);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Welcome Email
                    $emailSubject = "Selamat Datang di " . APP_NAME . "! 🎮";
                    $emailBody = "
                        <h2 class='email-title'>Selamat Datang, " . htmlspecialchars($user['full_name'] ?: $user['username']) . "! 👋</h2>
                        <p>Terima kasih telah bergabung di <strong>" . htmlspecialchars(APP_NAME) . "</strong>, marketplace voucher game terpercaya di Indonesia.</p>
                        <p>Akun Anda telah berhasil diverifikasi dan aktif.</p>
                        <div style='text-align: center; margin-top: 30px;'>
                            <a href='" . APP_URL . "' class='btn btn-accent'>🎮 Mulai Belanja Sekarang</a>
                        </div>
                    ";
                    sendEmail($email, $emailSubject, $emailBody);

                    sendNotification($user['id'], 'Akun Terverifikasi! 🎉', 'Akun kamu berhasil diverifikasi. Selamat berbelanja!', 'success');
                    setFlash('success', 'Akun Anda berhasil diverifikasi! Selamat berbelanja. 🎉');
                    redirect(APP_URL . '/pages/dashboard.php');
                } else {
                    $error = 'Terjadi kesalahan sistem saat masuk.';
                }
            } else { // forgot_password
                // Set temporary session approval
                $_SESSION['otp_verified_email'] = $email;
                setFlash('success', 'Kode OTP berhasil diverifikasi. Silakan tentukan password baru Anda.');
                redirect(APP_URL . '/pages/reset-password.php');
            }
        } else {
            $error = 'Kode OTP salah atau telah kadaluwarsa.';
        }
    } elseif (isset($_POST['resend_otp'])) {
        // Resend OTP
        $otp = generateOTP();
        storeOTP($email, $otp, $type);
        sendOtpEmail($email, $otp, $type);
        $success = 'Kode OTP baru telah berhasil dikirim ke email Anda.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP | <?= APP_NAME ?></title>
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
    <div class="auth-box" style="max-width:460px;">
        <div style="text-align:center;margin-bottom:24px;">
            <a href="<?= APP_URL ?>" style="font-family:var(--font-head);font-size:26px;font-weight:900;color:var(--text-primary);">
                ⚡ BoloTopup<span style="color:var(--accent)">.ID</span>
            </a>
        </div>
        
        <h1 class="auth-title">Verifikasi Kode OTP 🔑</h1>
        <p class="auth-sub">Masukkan 6 digit kode keamanan yang kami kirimkan ke email:</p>
        <div style="text-align:center; font-weight:700; color:var(--accent); font-family:var(--font-head); margin-bottom:20px; font-size:15px; word-break:break-all;">
            <?= htmlspecialchars($email) ?>
        </div>

        <?php showFlash(); ?>

        <?php if ($error): ?>
            <div class="flash-message flash-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash-message flash-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfInput() ?>
            <div class="form-group" style="text-align:center;">
                <input type="text" name="otp_code" class="form-control" placeholder="123456" required maxlength="6" pattern="\d{6}" style="font-size:26px; text-align:center; letter-spacing:8px; font-family:monospace; height:54px; max-width:240px; margin:0 auto; background:var(--bg-card2); border-color:var(--border);">
            </div>
            
            <button type="submit" name="verify_otp" value="1" class="btn btn-primary btn-block btn-lg" style="justify-content:center; margin-top:20px;">
                🔑 Verifikasi Kode
            </button>
        </form>

        <form method="POST" style="text-align:center; margin-top:24px;">
            <?= csrfInput() ?>
            <span style="font-size:14px; color:var(--text-muted);">Tidak menerima kode? </span>
            <button type="submit" name="resend_otp" value="1" class="btn-link" style="background:none; border:none; color:var(--accent); font-weight:700; cursor:pointer; font-size:14px; text-decoration:underline; display:inline; padding:0;">
                Kirim Ulang OTP
            </button>
        </form>

        <p style="text-align:center; font-size:14px; color:var(--text-muted); margin-top:24px; border-top:1px solid var(--border); padding-top:16px; margin-bottom:0;">
            <a href="<?= APP_URL ?>/pages/login.php" style="color:var(--text-muted); text-decoration:none;">← Kembali ke Halaman Masuk</a>
        </p>
    </div>
</div>
</body>
</html>
