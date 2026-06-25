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
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js" defer></script>
</head>
<body>
<div class="auth-page">
    <div class="auth-box">
        <div style="text-align:center;margin-bottom:30px;">
            <a href="<?= APP_URL ?>" style="font-family:var(--font-head);font-size:26px;font-weight:900;color:white;">
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
                <a href="#" style="font-size:14px;color:var(--accent);">Lupa Password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">🚀 Masuk Sekarang</button>
        </form>

        <div class="auth-divider">atau</div>

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