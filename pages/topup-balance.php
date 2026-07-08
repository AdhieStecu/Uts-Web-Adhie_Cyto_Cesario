<?php
// File: pages/topup-balance.php
$pageTitle = 'Top Up Saldo';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user = currentUser();
$error = '';
$step = 1;
$amount = 0;
$method = '';

// Handle Step 1 -> Step 2
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_topup'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $amount = (float)($_POST['amount_custom'] ?: ($_POST['amount_select'] ?? 0));
        $method = sanitize($_POST['payment_method'] ?? '');
        
        if ($amount < 10000) {
            $error = 'Minimal pengisian saldo adalah Rp 10.000.';
        } elseif (empty($method)) {
            $error = 'Silakan pilih salah satu metode pembayaran.';
        } else {
            $step = 2; // Beralih ke halaman simulasi pembayaran
        }
    }
}

// Handle Step 2 -> Step 3 (Konfirmasi Simulasi Pembayaran Berhasil)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_topup'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $amount = (float)($_POST['amount'] ?? 0);
        $method = sanitize($_POST['payment_method'] ?? '');
        
        if ($amount < 10000) {
            $error = 'Nominal pengisian saldo tidak valid.';
        } else {
            // 1. Tambahkan saldo di database
            db()->execute("UPDATE users SET balance = balance + ? WHERE id = ?", 'di', $amount, $_SESSION['user_id']);
            
            // 2. Catat riwayat notifikasi lokal di web
            sendNotification($_SESSION['user_id'], 'Top Up Berhasil! 💰', "Saldo sebesar " . rupiah($amount) . " telah ditambahkan ke akun Anda.", 'success');
            
            // Ambil data user terbaru untuk mendapatkan saldo ter-update
            $freshUser = currentUser();
            
            // 3. Kirim Email Notifikasi Transaksi Sukses
            if ($freshUser && !empty($freshUser['email'])) {
                $emailSubject = "Top Up Saldo Berhasil - " . APP_NAME . " 💰";
                $emailBody = "
                    <h2 class='email-title'>Top Up Saldo Berhasil! 💰</h2>
                    <p>Halo <strong>" . htmlspecialchars($freshUser['full_name'] ?: $freshUser['username']) . "</strong>,</p>
                    <p>Kami telah berhasil mengonfirmasi pembayaran pengisian saldo Anda. Saldo telah didepositkan ke akun Anda.</p>
                    <div class='divider'></div>
                    <h3 style='color: #ffffff;'>Rincian Transaksi:</h3>
                    <table class='detail-table'>
                        <tr>
                            <th>Metode Pembayaran</th>
                            <td>" . htmlspecialchars($method) . "</td>
                        </tr>
                        <tr>
                            <th>Jumlah Pengisian</th>
                            <td><span class='text-highlight'>" . rupiah($amount) . "</span></td>
                        </tr>
                        <tr>
                            <th>Total Saldo Akun</th>
                            <td><strong>" . rupiah($freshUser['balance']) . "</strong></td>
                        </tr>
                        <tr>
                            <th>Status Transaksi</th>
                            <td><span class='badge badge-success'>Sukses / Berhasil</span></td>
                        </tr>
                    </table>
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='" . APP_URL . "/pages/dashboard.php' class='btn btn-accent'>🎮 Mulai Belanja Voucher Game</a>
                    </div>
                ";
                sendEmail($freshUser['email'], $emailSubject, $emailBody);
            }
            
            setFlash('success', 'Top up saldo sebesar ' . rupiah($amount) . ' berhasil ditambahkan! 🎉');
            redirect(APP_URL . '/pages/dashboard.php');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px; padding-bottom:60px;">
    <div class="dashboard-layout">
        <!-- SIDEBAR -->
        <?php
        $activePage = 'topup'; // Sesuai sidebar
        require_once __DIR__ . '/../includes/sidebar.php';
        ?>

        <!-- MAIN CONTENT -->
        <div style="flex: 1;">
            
            <?php if ($step === 1): ?>
                <!-- STEP 1: PILIH METODE & NOMINAL -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                    <h1 style="font-family:var(--font-head); font-size:26px; font-weight:800;">💰 Pengisian Saldo</h1>
                    <a href="<?= APP_URL ?>/pages/dashboard.php" class="btn btn-outline">← Dashboard</a>
                </div>

                <?php if ($error): ?>
                    <div class="flash-message flash-error" style="margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrfInput() ?>
                    
                    <div class="card" style="margin-bottom:24px;">
                        <div class="card-body">
                            <h3 style="font-family:var(--font-head); font-size:16px; margin-bottom:16px; font-weight:700;">1. Pilih Nominal Pengisian</h3>
                            
                            <!-- NOMINAL GRID BUTTONS -->
                            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap:12px; margin-bottom:20px;">
                                <?php foreach ([10000, 25000, 50000, 100000, 250000, 500000] as $value): ?>
                                    <label style="cursor:pointer;">
                                        <input type="radio" name="amount_select" value="<?= $value ?>" style="display:none;" class="nominal-radio">
                                        <div class="nominal-card" style="background:var(--bg-card2); border:1px solid var(--border); padding:16px; border-radius:10px; text-align:center; font-weight:700; transition:all 0.2s;">
                                            <?= rupiah($value) ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- CUSTOM INPUT -->
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" for="amount_custom">Atau Masukkan Nominal Kustom</label>
                                <input type="number" name="amount_custom" id="amount_custom" class="form-control" placeholder="Contoh: 75000 (Minimal 10000)" min="10000">
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom:24px;">
                        <div class="card-body">
                            <h3 style="font-family:var(--font-head); font-size:16px; margin-bottom:16px; font-weight:700;">2. Pilih Metode Pembayaran</h3>
                            
                            <!-- PAYMENT METHOD CHOICES -->
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                                <label style="cursor:pointer;">
                                    <input type="radio" name="payment_method" value="QRIS" style="display:none;" class="method-radio" checked>
                                    <div class="method-card" style="background:var(--bg-card2); border:2px solid var(--primary); padding:16px; border-radius:10px; transition:all 0.2s; display:flex; align-items:center; gap:12px;">
                                        <span style="font-size:24px;">📱</span>
                                        <div>
                                            <strong style="display:block;">QRIS</strong>
                                            <span style="font-size:11px; color:var(--text-muted);">Proses Otomatis Instan</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label style="cursor:pointer;">
                                    <input type="radio" name="payment_method" value="Transfer Bank BCA" style="display:none;" class="method-radio">
                                    <div class="method-card" style="background:var(--bg-card2); border:2px solid var(--border); padding:16px; border-radius:10px; transition:all 0.2s; display:flex; align-items:center; gap:12px;">
                                        <span style="font-size:24px;">🏦</span>
                                        <div>
                                            <strong style="display:block;">Transfer BCA</strong>
                                            <span style="font-size:11px; color:var(--text-muted);">Instruksi & Verifikasi</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="proses_topup" class="btn btn-primary btn-block btn-lg">
                        💳 Lanjutkan Pembayaran
                    </button>
                </form>

                <script>
                    // Menambahkan highlight kelas aktif untuk nominal yang dipilih
                    const nominalRadios = document.querySelectorAll('.nominal-radio');
                    const nominalCards = document.querySelectorAll('.nominal-card');
                    const customInput = document.getElementById('amount_custom');

                    nominalRadios.forEach((radio, index) => {
                        radio.addEventListener('change', () => {
                            nominalCards.forEach(c => {
                                c.style.borderColor = 'var(--border)';
                                c.style.backgroundColor = 'var(--bg-card2)';
                            });
                            nominalCards[index].style.borderColor = 'var(--primary)';
                            nominalCards[index].style.backgroundColor = 'rgba(0,102,255,0.05)';
                            customInput.value = ''; // Reset input kustom
                        });
                    });

                    customInput.addEventListener('input', () => {
                        nominalRadios.forEach(radio => radio.checked = false);
                        nominalCards.forEach(c => {
                            c.style.borderColor = 'var(--border)';
                            c.style.backgroundColor = 'var(--bg-card2)';
                        });
                    });

                    // Menambahkan highlight untuk metode pembayaran yang dipilih
                    const methodRadios = document.querySelectorAll('.method-radio');
                    const methodCards = document.querySelectorAll('.method-card');

                    methodRadios.forEach((radio, index) => {
                        radio.addEventListener('change', () => {
                            methodCards.forEach(c => {
                                c.style.borderColor = 'var(--border)';
                            });
                            methodCards[index].style.borderColor = 'var(--primary)';
                        });
                    });
                </script>

            <?php elseif ($step === 2): ?>
                <!-- STEP 2: SIMULASI PEMBAYARAN -->
                <div style="text-align:center; margin-bottom:30px;">
                    <h1 style="font-family:var(--font-head); font-size:28px; font-weight:800; color:var(--accent);">💳 Menyelesaikan Pembayaran</h1>
                    <p style="color:var(--text-muted);">Simulasi tagihan pengisian saldo akun Anda</p>
                </div>

                <div class="card" style="margin-bottom:24px; max-width:600px; margin-left:auto; margin-right:auto;">
                    <div class="card-body" style="text-align:center;">
                        <h3 style="font-family:var(--font-head); font-size:16px; margin-bottom:8px; font-weight:700;">Ringkasan Pengisian</h3>
                        <div style="font-size:24px; font-weight:800; color:var(--accent); margin-bottom:20px;">
                            <?= rupiah($amount) ?>
                        </div>
                        <div style="font-size:12px; color:var(--text-muted); margin-bottom:20px;">
                            Metode Pembayaran: <strong><?= htmlspecialchars($method) ?></strong>
                        </div>

                        <?php if ($method === 'QRIS'): ?>
                            <!-- QRIS BOX -->
                            <div class="qris-box" style="border:3px solid var(--primary); background: white; padding: 20px; border-radius: 12px; display: inline-block; margin-bottom: 20px; max-width: 320px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                <div class="qris-title" style="color: #0f172a; font-weight: 900; font-size: 18px; margin-bottom: 4px;">BoloTopup.ID</div>
                                <div style="font-size:10px; color:#64748b; margin-bottom:12px;">NMID: <?= QRIS_MERCHANT_ID ?></div>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?= urlencode("TOPUP|USER:" . $_SESSION['user_id'] . "|AMT:" . $amount . "|TS:" . time()) ?>&format=png" alt="QRIS Code" style="width:200px; height:200px; display:block; margin:0 auto 10px;">
                                <div class="qris-amount" style="color:#0f172a; font-weight:800; font-size:16px; margin-bottom:6px;"><?= rupiah($amount) ?></div>
                                <div class="qris-timer" style="font-size:11px; color:#b45309; font-weight:bold; background:#fef3c7; padding:4px 8px; border-radius:4px; display:inline-block;">Berlaku: 59:59</div>
                            </div>
                            <p style="font-size:12px; color:var(--text-muted); margin-bottom:24px;">
                                Scan QRIS di atas dengan aplikasi pembayaran Anda (Gopay, OVO, Dana, LinkAja, BCA Mobile, dll.)
                            </p>
                        <?php else: ?>
                            <!-- TRANSFER BANK BOX -->
                            <div style="background:var(--bg-card2); border:1px solid var(--border); border-radius:10px; padding:20px; text-align:left; margin-bottom:24px;">
                                <p style="color:var(--text-muted); font-size:13px; margin-bottom:12px;">Silakan transfer ke rekening berikut:</p>
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                    <span style="color:var(--text-muted);">Bank</span>
                                    <strong>BCA (Bank Central Asia)</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                    <span style="color:var(--text-muted);">Nomor Rekening Virtual</span>
                                    <strong style="color:var(--accent);">8830-1928-1102</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                    <span style="color:var(--text-muted);">Atas Nama</span>
                                    <strong>PT BoloTopup Indonesia</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-top:12px; padding-top:12px; border-top:1px solid var(--border); font-size:16px; font-weight:800; color:var(--accent);">
                                    <span>Nominal Transfer</span>
                                    <span><?= rupiah($amount) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- SIMULATION MODE BLOCK -->
                        <div style="background:rgba(234,179,8,0.1); border:1px solid var(--warning); border-radius:10px; padding:16px; margin-bottom:24px;">
                            <div style="color:var(--warning); font-size:13px; font-weight:700; margin-bottom:6px;">⚠️ MODE SIMULASI</div>
                            <p style="font-size:12px; color:var(--text-secondary); margin-bottom:12px;">
                                Klik tombol di bawah ini untuk mensimulasikan pembayaran sukses dan saldo akan langsung bertambah.
                            </p>
                            
                            <form method="POST">
                                <?= csrfInput() ?>
                                <input type="hidden" name="amount" value="<?= $amount ?>">
                                <input type="hidden" name="payment_method" value="<?= htmlspecialchars($method) ?>">
                                <button type="submit" name="confirm_topup" value="1" class="btn btn-success btn-block">
                                    ✅ Simulasikan Pembayaran Sukses
                                </button>
                            </form>
                        </div>

                        <a href="<?= APP_URL ?>/pages/topup-balance.php" class="btn btn-outline btn-block">Batal / Kembali</a>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
