<?php
// File: pages/faq.php
$pageTitle = 'Bantuan & FAQ';
require_once __DIR__ . '/../inc/functions.php';

$user = currentUser();
$error = '';
$success = false;

// List FAQ Data
$faqs = [
    [
        'id' => 1,
        'category' => 'umum',
        'question' => 'Apa itu BoloTopup.ID?',
        'answer' => '<strong>BoloTopup.ID</strong> adalah platform marketplace game terpercaya di Indonesia yang menyediakan layanan top up game voucher (seperti Diamond Mobile Legends, Free Fire, Robux) dan key game orisinal dengan pengiriman cepat, aman, dan harga kompetitif.'
    ],
    [
        'id' => 2,
        'category' => 'umum',
        'question' => 'Apakah bertransaksi di website ini aman?',
        'answer' => 'Tentu saja sangat aman. Kami menggunakan sistem keamanan SSL terenkripsi untuk melindungi data Anda. Transaksi dijamin aman melalui enkripsi data, simulasi pembayaran virtual account/QRIS yang andal, serta perlindungan saldo akun Anda.'
    ],
    [
        'id' => 3,
        'category' => 'akun',
        'question' => 'Bagaimana cara mendaftar akun baru?',
        'answer' => 'Anda dapat mendaftar dengan mengklik tombol <strong>Daftar</strong> di pojok kanan atas halaman. Isi formulir pendaftaran yang terdiri dari Nama Lengkap, Username, Email aktif, dan Sandi Anda secara lengkap.'
    ],
    [
        'id' => 4,
        'category' => 'akun',
        'question' => 'Apakah saya bisa login menggunakan akun Google?',
        'answer' => 'Ya! Kami mendukung fitur Single Sign-On (SSO) Google. Anda cukup menuju halaman Login dan mengklik tombol <strong>Masuk dengan Google</strong> untuk masuk secara cepat tanpa mengetik sandi.'
    ],
    [
        'id' => 5,
        'category' => 'transaksi',
        'question' => 'Metode pembayaran apa saja yang tersedia?',
        'answer' => 'Kami mendukung berbagai metode pembayaran terpopuler di Indonesia, meliputi scan <strong>QRIS</strong> (GoPay, OVO, Dana, ShopeePay) serta **Transfer Bank Virtual Account** (seperti Bank BCA).'
    ],
    [
        'id' => 6,
        'category' => 'transaksi',
        'question' => 'Bagaimana cara mengisi saldo akun (+ Isi Saldo)?',
        'answer' => 'Bagi pengguna yang telah masuk (login), Anda dapat mengklik tombol berwarna emas <strong>+ Isi Saldo</strong> di navigasi samping dashboard Anda. Tentukan nominal top up, pilih metode pembayaran (QRIS/Transfer), lalu lakukan simulasi bayar hingga saldo sukses ditambahkan.'
    ],
    [
        'id' => 7,
        'category' => 'pengiriman',
        'question' => 'Apa perbedaan antara tipe pengiriman Instan dan Manual?',
        'answer' => '<ul><li><strong>Instan (Automatic)</strong>: Produk game (seperti kode voucher atau diamond) akan langsung dikirim oleh sistem secara otomatis dalam hitungan detik setelah pembayaran lunas.</li><li><strong>Manual (Seller Process)</strong>: Produk memerlukan pemrosesan manual dari pihak penjual. Penjual akan mengirimkan pesanan Anda dalam estimasi waktu 10-30 menit.</li></ul>'
    ],
    [
        'id' => 8,
        'category' => 'pengiriman',
        'question' => 'Bagaimana jika pesanan saya belum dikirim dalam waktu lama?',
        'answer' => 'Apabila pesanan Anda dengan tipe kirim manual mengalami keterlambatan, silakan hubungi tim dukungan kami melalui formulir tiket bantuan di bawah ini dengan melampirkan nomor order transaksi Anda agar kami segera menindaklanjutinya ke penjual.'
    ]
];

// Handle Support Ticket Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_ticket'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        
        if (!$email) {
            $error = 'Alamat email Anda tidak valid.';
        } elseif (strlen($subject) < 5) {
            $error = 'Subjek keluhan minimal 5 karakter.';
        } elseif (strlen($message) < 15) {
            $error = 'Isi pesan penjelasan bantuan minimal 15 karakter.';
        } else {
            // Kirim Email Konfirmasi Penerimaan Tiket Bantuan ke Pengguna
            $emailSubject = "Tiket Bantuan Diterima #" . strtoupper(substr(uniqid(), -6)) . " - " . APP_NAME;
            $emailBody = "
                <h2 class='email-title'>Pertanyaan Anda Telah Diterima! ✉️</h2>
                <p>Halo <strong>" . htmlspecialchars($user ? $user['username'] : 'Pengguna Setia') . "</strong>,</p>
                <p>Terima kasih telah menghubungi layanan bantuan <strong>" . htmlspecialchars(APP_NAME) . "</strong>. Kami telah menerima tiket aduan/pertanyaan Anda dan tim admin kami akan segera merespon dalam waktu maksimal 24 jam.</p>
                <div class='divider'></div>
                <h3 style='color: #ffffff;'>Rincian Tiket Bantuan Anda:</h3>
                <table class='detail-table'>
                    <tr>
                        <th style='width: 30%;'>Email Pengirim</th>
                        <td>" . htmlspecialchars($email) . "</td>
                    </tr>
                    <tr>
                        <th>Subjek Keluhan</th>
                        <td>" . htmlspecialchars($subject) . "</td>
                    </tr>
                    <tr>
                        <th>Isi Pesan / Penjelasan</th>
                        <td>" . nl2br(htmlspecialchars($message)) . "</td>
                    </tr>
                    <tr>
                        <th>Status Tiket</th>
                        <td><span class='badge'>Menunggu Respon Admin</span></td>
                    </tr>
                </table>
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='" . APP_URL . "' class='btn btn-accent'>🌐 Kembali ke Website</a>
                </div>
            ";
            
            // Panggil sendEmail
            sendEmail($email, $emailSubject, $emailBody);
            
            // Catat notifikasi jika sedang login
            if (isLoggedIn()) {
                sendNotification($_SESSION['user_id'], 'Tiket Bantuan Dibuat ✉️', "Tiket aduan tentang '{$subject}' telah terkirim. Rincian telah dikirim ke email Anda.", 'info');
            }
            
            $success = true;
            setFlash('success', 'Tiket bantuan berhasil dikirim! Silakan periksa inbox/spam email Anda untuk detail salinan tiket aduan. 🎉');
        }
    }
}

require_once __DIR__ . '/../inc/header.php';
?>

<!-- STYLE KHUSUS HALAMAN FAQ -->
<style>
    .faq-container {
        max-width: 900px;
        margin: 40px auto 60px;
        padding: 0 15px;
    }
    .faq-title-section {
        text-align: center;
        margin-bottom: 40px;
    }
    .faq-title {
        font-family: var(--font-head);
        font-size: 32px;
        font-weight: 900;
        margin-bottom: 10px;
    }
    .faq-subtitle {
        color: var(--text-muted);
        font-size: 14px;
    }
    
    /* Search Bar */
    .faq-search-wrapper {
        position: relative;
        max-width: 550px;
        margin: 0 auto 30px;
    }
    .faq-search-input {
        width: 100%;
        padding: 14px 20px 14px 45px;
        border-radius: 50px;
        border: 2px solid var(--border);
        background: var(--bg-card);
        color: var(--text-primary);
        font-size: 14px;
        transition: all 0.3s;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .faq-search-input:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(0,102,255,0.15);
    }
    .faq-search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        color: var(--text-muted);
    }

    /* Category Tabs */
    .faq-tabs {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    .faq-tab-btn {
        padding: 10px 20px;
        border-radius: 30px;
        border: 1px solid var(--border);
        background: var(--bg-card2);
        color: var(--text-secondary);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 13px;
    }
    .faq-tab-btn:hover {
        background: var(--border);
        color: var(--text-primary);
    }
    .faq-tab-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Accordion items */
    .faq-list {
        margin-bottom: 40px;
    }
    .faq-item {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
        transition: all 0.3s;
    }
    .faq-item:hover {
        border-color: rgba(0,102,255,0.3);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .faq-question-btn {
        width: 100%;
        padding: 18px 24px;
        background: none;
        border: none;
        text-align: left;
        font-family: var(--font-head);
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }
    .faq-question-arrow {
        font-size: 14px;
        color: var(--text-muted);
        transition: transform 0.3s;
    }
    .faq-answer-wrapper {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0, 1, 0, 1);
        background: rgba(255,255,255,0.01);
    }
    .faq-answer-content {
        padding: 0 24px 20px;
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.6;
        border-top: 1px solid transparent;
    }
    
    /* Active accordion state */
    .faq-item.active {
        border-color: var(--primary);
    }
    .faq-item.active .faq-question-arrow {
        transform: rotate(180deg);
        color: var(--primary);
    }
    .faq-item.active .faq-answer-wrapper {
        max-height: 1000px; /* high limit to accommodate any content */
        transition: max-height 0.4s cubic-bezier(1, 0, 1, 0);
    }
    .faq-item.active .faq-answer-content {
        border-top: 1px solid var(--border);
        padding-top: 15px;
    }

    .no-results {
        display: none;
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
        background: var(--bg-card);
        border: 1px dashed var(--border);
        border-radius: 12px;
    }
</style>

<div class="faq-container">
    <!-- TITLE -->
    <div class="faq-title-section">
        <h1 class="faq-title">❓ Pusat Bantuan & FAQ</h1>
        <p class="faq-subtitle">Punya pertanyaan seputar BoloTopup.ID? Temukan jawaban Anda secara instan di bawah ini.</p>
    </div>

    <!-- SEARCH BAR -->
    <div class="faq-search-wrapper">
        <span class="faq-search-icon">🔍</span>
        <input type="text" id="faqSearchInput" class="faq-search-input" placeholder="Ketik kata kunci untuk mencari jawaban (cth: QRIS, Saldo, Voucher)...">
    </div>

    <!-- CATEGORY TABS -->
    <div class="faq-tabs">
        <button class="faq-tab-btn active" data-category="all">Semua Kategori</button>
        <button class="faq-tab-btn" data-category="umum">Umum</button>
        <button class="faq-tab-btn" data-category="akun">Akun</button>
        <button class="faq-tab-btn" data-category="transaksi">Transaksi</button>
        <button class="faq-tab-btn" data-category="pengiriman">Pengiriman</button>
    </div>

    <!-- ACCORDION FAQ LIST -->
    <div class="faq-list" id="faqList">
        <?php foreach ($faqs as $faq): ?>
            <div class="faq-item" data-category="<?= htmlspecialchars($faq['category']) ?>">
                <button class="faq-question-btn" type="button">
                    <span><?= htmlspecialchars($faq['question']) ?></span>
                    <span class="faq-question-arrow">▼</span>
                </button>
                <div class="faq-answer-wrapper">
                    <div class="faq-answer-content">
                        <?= $faq['answer'] ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Empty Search Fallback -->
        <div class="no-results" id="noResults">
            <span style="font-size: 32px;">🔍</span>
            <p style="margin-top:10px; font-weight: 600;">Maaf, pertanyaan tidak ditemukan.</p>
            <p style="font-size: 12px; margin-top:4px;">Silakan ketik kata kunci lain atau kirim tiket bantuan pada form di bawah.</p>
        </div>
    </div>

    <!-- SUPPORT TICKET FORM -->
    <div class="card" style="margin-top: 40px; border-color: rgba(0,102,255,0.25);">
        <div class="card-body">
            <h3 style="font-family:var(--font-head); font-size:18px; margin-bottom:12px; font-weight:700; display:flex; align-items:center; gap:8px;">
                ✉️ Tidak Menemukan Jawaban? Hubungi Kami
            </h3>
            <p style="color:var(--text-muted); font-size:13px; margin-bottom:20px; line-height:1.6;">
                Jika keluhan atau kendala transaksi Anda tidak terjawab melalui panduan di atas, kirimkan detail pertanyaan Anda melalui formulir tiket bantuan di bawah ini. Tim bantuan admin kami akan membalas secara langsung ke kotak masuk email Anda.
            </p>

            <?php if ($error): ?>
                <div class="flash-message flash-error" style="margin-bottom: 15px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="flash-message flash-success" style="margin-bottom: 15px;">
                    <strong>Tiket Berhasil Dikirim!</strong> Salinan aduan dikirim ke email Anda. Mohon tunggu respon admin dalam waktu 24 jam.
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= csrfInput() ?>
                
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email Anda *</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email aktif Anda untuk menerima respon" 
                           value="<?= htmlspecialchars($_POST['email'] ?? ($user['email'] ?? '')) ?>" 
                           <?= isLoggedIn() ? 'readonly style="background:rgba(255,255,255,0.03); color:var(--text-muted);"' : 'required' ?>>
                </div>

                <div class="form-group">
                    <label class="form-label" for="subject">Subjek Kendala / Masalah *</label>
                    <input type="text" name="subject" id="subject" class="form-control" placeholder="Contoh: Saldo QRIS tidak bertambah / Voucher tidak valid" 
                           value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required minlength="5">
                </div>

                <div class="form-group">
                    <label class="form-label" for="message">Pesan Penjelasan Detail Kendala *</label>
                    <textarea name="message" id="message" class="form-control" rows="5" placeholder="Tulis rincian kendala Anda (misal: nomor order GS..., tanggal transaksi, atau kronologi kendala Anda)" required minlength="15"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <button type="submit" name="send_ticket" class="btn btn-primary" style="margin-top:10px;">
                    🚀 Kirim Tiket Bantuan Pelanggan
                </button>
            </form>
        </div>
    </div>
</div>

<!-- CLIENT JAVASCRIPT FOR SEARCH & INTERACTION -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faqSearchInput');
    const tabButtons = document.querySelectorAll('.faq-tab-btn');
    const faqItems = document.querySelectorAll('.faq-item');
    const noResults = document.getElementById('noResults');

    // Accordion Toggle Click Logic
    faqItems.forEach(item => {
        const questionBtn = item.querySelector('.faq-question-btn');
        questionBtn.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Close other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });

            // Toggle current item
            if (isActive) {
                item.classList.remove('active');
            } else {
                item.classList.add('active');
            }
        });
    });

    // Filtering logic combining search input and active category tab
    function filterFAQs() {
        const searchText = searchInput.value.toLowerCase().trim();
        const activeTab = document.querySelector('.faq-tab-btn.active');
        const activeCategory = activeTab.getAttribute('data-category');
        
        let visibleCount = 0;

        faqItems.forEach(item => {
            const itemCategory = item.getAttribute('data-category');
            const questionText = item.querySelector('.faq-question-btn span').textContent.toLowerCase();
            const answerText = item.querySelector('.faq-answer-content').textContent.toLowerCase();
            
            const matchCategory = (activeCategory === 'all' || itemCategory === activeCategory);
            const matchSearch = (questionText.includes(searchText) || answerText.includes(searchText));

            if (matchCategory && matchSearch) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
                item.classList.remove('active'); // close accordion if hidden
            }
        });

        // Show/hide no results text
        if (visibleCount === 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }

    // Input Search Event
    searchInput.addEventListener('input', filterFAQs);

    // Tab Button Click Event
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterFAQs();
        });
    });
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
