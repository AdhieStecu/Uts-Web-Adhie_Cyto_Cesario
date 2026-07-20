<?php
// File: inc/footer.php
?>
<!-- FOOTER -->
<footer class="main-footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">⚡ BoloTopup.ID</div>
                    <p>Marketplace game terpercaya #1 di Indonesia. Transaksi aman, cepat, dan terjamin.</p>
                    <div class="footer-badges">
                        <span>🛡️ Transaksi Aman</span>
                        <span>💰 Garansi Uang Kembali</span>
                        <span>🎧 CS 24/7</span>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Kategori Populer</h4>
                    <ul>
                        <li><a href="<?= APP_URL ?>/pages/category.php?slug=topup">💎 Top Up</a></li>
                        <li><a href="<?= APP_URL ?>/pages/category.php?slug=game-key">🎮 Game Key</a></li>
                        <li><a href="<?= APP_URL ?>/pages/category.php?slug=voucher">🎫 Voucher</a></li>
                        <li><a href="<?= APP_URL ?>/pages/category.php?slug=akun">👤 Akun</a></li>
                        <li><a href="<?= APP_URL ?>/pages/category.php?slug=roblox">🟥 Roblox</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Bantuan</h4>
                    <ul>
                        <li><a href="#">❓ FAQ</a></li>
                        <li><a href="#">📞 Hubungi Kami</a></li>
                        <li><a href="#">📋 Syarat & Ketentuan</a></li>
                        <li><a href="#">🔒 Kebijakan Privasi</a></li>
                        <li><a href="#">🏪 Cara Berjualan</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Pembayaran</h4>
                    <div class="payment-methods">
                        <span class="pay-badge">QRIS</span>
                        <span class="pay-badge">BCA</span>
                        <span class="pay-badge">BNI</span>
                        <span class="pay-badge">Mandiri</span>
                        <span class="pay-badge">OVO</span>
                        <span class="pay-badge">GoPay</span>
                        <span class="pay-badge">Dana</span>
                    </div>
                    <h4 style="margin-top:20px;">Ikuti Kami</h4>
                    <div class="social-links">
                        <a href="#">📸 Instagram</a>
                        <a href="#">🐦 Twitter</a>
                        <a href="#">💬 Discord</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved. Made with ❤️ in Indonesia.</p>
        </div>
    </div>
</footer>

<script src="<?= APP_URL ?>/assets/js/main.js?v=1.2.1"></script>
</body>
</html>