<?php
// File: pages/profile.php
$pageTitle = 'Edit Profil';
require_once __DIR__ . '/../inc/functions.php';
requireLogin();

$user = currentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid. Coba lagi.';
    } else {
        $fullName = sanitize($_POST['full_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        
        $password = $_POST['password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        
        $avatarData = $_POST['avatar_cropped'] ?? ''; // base64 dari cropper

        if (!$email) {
            $error = 'Email tidak valid.';
        } else {
            // Cek duplikasi email (selain user saat ini)
            $existing = db()->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", 'si', $email, $user['id']);
            if ($existing) {
                $error = 'Email sudah digunakan oleh akun lain.';
            } else {
                // Upload avatar baru jika ada
                $avatarFilename = $user['avatar'];
                if (!empty($avatarData)) {
                    $uploaded = uploadImage($avatarData, 'avatars');
                    if ($uploaded) {
                        // Hapus avatar lama jika bukan default
                        if ($user['avatar'] && $user['avatar'] !== 'default.png' && !filter_var($user['avatar'], FILTER_VALIDATE_URL)) {
                            @unlink(UPLOAD_DIR . $user['avatar']);
                        }
                        $avatarFilename = $uploaded;
                    }
                }

                // Cek password baru jika diisi
                $hashedPass = $user['password'];
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error = 'Password baru minimal 6 karakter.';
                    } elseif ($password !== $confirmPass) {
                        $error = 'Konfirmasi password baru tidak cocok.';
                    } else {
                        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                    }
                }

                if (empty($error)) {
                    // Update data pengguna
                    db()->execute(
                        "UPDATE users SET email = ?, full_name = ?, phone = ?, avatar = ?, password = ? WHERE id = ?",
                        'sssssi', $email, $fullName, $phone, $avatarFilename, $hashedPass, $user['id']
                    );
                    
                    setFlash('success', 'Profil Anda berhasil diperbarui! 🎉');
                    redirect(APP_URL . '/pages/profile.php');
                }
            }
        }
    }
}

require_once __DIR__ . '/../inc/header.php';
?>

<!-- Cropper.js Dependencies -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<div class="container" style="padding-top:40px;padding-bottom:60px;">
    <div class="dashboard-layout">
        <!-- SIDEBAR -->
        <?php
        $activePage = 'profile';
        require_once __DIR__ . '/../inc/sidebar.php';
        ?>

        <!-- MAIN CONTENT -->
        <div>
            <h1 style="font-family:var(--font-head);font-size:26px;font-weight:800;margin-bottom:24px;">
                ⚙️ Pengaturan Profil
            </h1>

            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="flash-message flash-error" style="margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" id="profileForm">
                        <?= csrfInput() ?>
                        
                        <!-- AVATAR UPLOAD SECTION -->
                        <div style="display:flex;align-items:center;gap:20px;margin-bottom:30px;flex-wrap:wrap;">
                            <div style="width:100px;height:100px;border-radius:50%;overflow:hidden;background:#eee;border:2px solid var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                                <img id="avatarPreview" src="<?= getImageUrl($user['avatar']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
                            </div>
                            <div>
                                <h4 style="font-family:var(--font-head);margin-bottom:6px;">Foto Profil</h4>
                                <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">Format JPEG, PNG, GIF, atau WebP (Maks. 5MB). Rasio 1:1 direkomendasikan.</p>
                                <input type="file" id="avatarInput" accept="image/*" style="display:none;">
                                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('avatarInput').click()">📁 Pilih Foto Baru</button>
                                <input type="hidden" name="avatar_cropped" id="avatarCropped">
                            </div>
                        </div>

                        <!-- PROFILE FIELDS -->
                        <div class="grid-2" style="margin-bottom:20px;">
                            <div class="form-group">
                                <label class="form-label">Username (Tidak dapat diubah)</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled style="background:var(--bg-card2);cursor:not-allowed;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Alamat Email *</label>
                                <input type="email" name="email" class="form-control" placeholder="Email aktif Anda" required value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">
                            </div>
                        </div>

                        <div class="grid-2" style="margin-bottom:20px;">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" placeholder="Nama lengkap Anda" value="<?= htmlspecialchars($_POST['full_name'] ?? $user['full_name']) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="phone" class="form-control" placeholder="Contoh: 08123456789" value="<?= htmlspecialchars($_POST['phone'] ?? $user['phone']) ?>">
                            </div>
                        </div>

                        <hr style="border-color:var(--border);margin:24px 0;">
                        <h3 style="font-family:var(--font-head);font-size:16px;margin-bottom:16px;color:var(--text-secondary);">🔒 Ubah Password (Kosongkan jika tidak ingin diubah)</h3>

                        <div class="grid-2" style="margin-bottom:20px;">
                            <div class="form-group">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru">
                            </div>
                        </div>

                        <div style="margin-top:30px;display:flex;gap:12px;justify-content:flex-end;">
                            <a href="<?= APP_URL ?>/pages/dashboard.php" class="btn btn-outline">Batal</a>
                            <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Cropper helper
    if (typeof initImageCropper === 'function') {
        initImageCropper('avatarInput', 'avatarCropped', 'avatarPreview', 1);
    } else {
        // Fallback in case main.js is loaded slightly later
        window.addEventListener('load', function() {
            initImageCropper('avatarInput', 'avatarCropped', 'avatarPreview', 1);
        });
    }
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
