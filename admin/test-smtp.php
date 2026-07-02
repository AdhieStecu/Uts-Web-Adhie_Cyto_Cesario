<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

echo "<h3>Pengujian Pengiriman Email SMTP</h3>";
echo "Menggunakan konfigurasi dari berkas .env:<br>";
echo "- Host: " . htmlspecialchars(SMTP_HOST) . "<br>";
echo "- Port: " . SMTP_PORT . "<br>";
echo "- Encryption: " . htmlspecialchars(SMTP_SECURE) . "<br>";
echo "- From User: " . htmlspecialchars(SMTP_USER) . "<br><br>";

if (empty(SMTP_USER) || empty(SMTP_PASS)) {
    echo "<strong style='color:red;'>GALAT: Kredensial SMTP belum dikonfigurasi di berkas .env!</strong>";
    exit;
}

// Coba kirim email ke email pengirim itu sendiri sebagai pengetesan
$to = SMTP_FROM_EMAIL ?: SMTP_USER;
$subject = "Uji Coba Sistem Email " . APP_NAME;
$body = "<h2>Halo Admin!</h2><p>Jika Anda menerima email ini, berarti sistem SMTP pada website <strong>" . htmlspecialchars(APP_NAME) . "</strong> telah berhasil terkonfigurasi dengan benar! 🎉</p>";

echo "Mencoba mengirim email uji coba ke: <strong>" . htmlspecialchars($to) . "</strong>...<br>";

if (sendEmail($to, $subject, $body)) {
    echo "<br><strong style='color: green;'>SUKSES: Email uji coba berhasil dikirim! Silakan periksa kotak masuk atau folder spam email Anda.</strong>";
} else {
    echo "<br><strong style='color: red;'>GAGAL: Email tidak dapat dikirim. Silakan periksa error log PHP untuk informasi detail.</strong>";
}