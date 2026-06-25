<?php
// =============================================
// KONFIGURASI DATABASE & APLIKASI
// File: includes/config.php
// =============================================

// Database Config
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Ganti sesuai user MySQL kamu
define('DB_PASS', '');           // Ganti sesuai password MySQL kamu
define('DB_NAME', 'gamestore_db');

// App Config
define('APP_NAME', 'BoloTopup.ID');
define('APP_URL', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario');
define('APP_VERSION', '1.0.0');

// Upload Config
define('UPLOAD_DIR', __DIR__ . '/../assets/img/uploads/');
define('UPLOAD_URL', APP_URL . '/assets/img/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Platform Fee (%)
define('PLATFORM_FEE_PERCENT', 2.5);

// QRIS Config (simulasi - bisa diganti dengan API Midtrans/Xendit)
define('QRIS_MERCHANT_ID', 'BOLOTOPUP001');
define('QRIS_MERCHANT_NAME', 'BOLOTOPUP.ID');
define('QRIS_CITY', 'Jakarta');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (matikan di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);