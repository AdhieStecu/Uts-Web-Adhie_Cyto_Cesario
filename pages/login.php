<?php
// File: pages/login.php
$pageTitle = 'Masuk';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) redirect(APP_URL . '/pages/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid. Coba lagi.';
    } else {
        $emailOrUser = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = db()->fetchOne(
            "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1",
            'ss', $emailOrUser, $emailOrUser
        );

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 0) {
                $otp = generateOTP();
                storeOTP($user['email'], $otp, 'register');
                sendOtpEmail($user['email'], $otp, 'register');
                
                setFlash('info', 'Akun Anda belum terverifikasi. Kami telah mengirimkan kode OTP baru ke email Anda.');
                redirect(APP_URL . '/pages/verify-otp.php?email=' . urlencode($user['email']) . '&type=register');
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            setFlash('success', 'Selamat datang kembali, ' . $user['username'] . '! 👋');
            $redirect = $_GET['redirect'] ?? APP_URL . '/pages/dashboard.php';
            redirect($redirect);
        } else {
            $error = 'Email/username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | <?= APP_NAME ?></title>
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
        <h1 class="auth-title">Selamat Datang! 👋</h1>
        <p class="auth-sub">Masuk untuk melanjutkan transaksi game favorit kamu</p>

        <?php if ($error): ?>
            <div class="flash-message flash-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfInput() ?>
            <div class="form-group">
                <label class="form-label">Email atau Username</label>
                <input type="text" name="email" class="form-control" placeholder="Masukkan email atau username" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <div style="text-align:right;margin-bottom:20px;">
                <a href="<?= APP_URL ?>/pages/forgot-password.php" style="font-size:14px;color:var(--accent);">Lupa Password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">🚀 Masuk Sekarang</button>
        </form>

        <div class="auth-divider">atau</div>

        <a href="<?= APP_URL ?>/pages/google-login.php" class="btn btn-google btn-block btn-lg" style="margin-bottom:20px; border-radius:10px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="18px" height="18px" style="vertical-align:middle; margin-right:8px;">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.5 24c0-1.61-.15-3.16-.42-4.69H24v8.87h12.66c-.55 2.94-2.2 5.43-4.7 7.1l7.3 5.66C43.53 36.6 46.5 30.93 46.5 24z"/>
                <path fill="#FBBC05" d="M10.54 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.98-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.3-5.66c-2.03 1.36-4.63 2.17-8.59 2.17-6.26 0-11.57-4.22-13.46-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Masuk dengan Google
        </a>

        <p style="text-align:center;font-size:14px;color:var(--text-muted);">
            Belum punya akun? 
            <a href="<?= APP_URL ?>/pages/register.php" style="color:var(--accent);font-weight:700;">Daftar Gratis</a>
        </p>

        <div style="margin-top:24px;padding:14px;background:rgba(0,102,255,0.08);border-radius:10px;font-size:13px;color:var(--text-muted);text-align:center;">
            <strong>Demo Akun:</strong><br>
            Admin: admin / password<br>
            Seller: demo_seller / password<br>
            Buyer: demo_buyer / password
        </div>
    </div>
</div>
</body>
</html>