<?php
// =============================================
// KONFIGURASI DATABASE & APLIKASI
// File: inc/config.php
// =============================================

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove surrounding quotes if present
            if (preg_match('/^"([^"]*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match('/^\'([^\']*)\'$/', $value, $matches)) {
                $value = $matches[1];
            }
            
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}
loadEnv(__DIR__ . '/../.env');

// Database Config (DISESUAIKAN UNTUK INFINITYFREE)
// Ganti teks di sebelah kanan sesuai dengan detail MySQL Databases di InfinityFree
define('DB_HOST', isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'sql210.infinityfree.com');
define('DB_PORT', isset($_ENV['DB_PORT']) ? (int)$_ENV['DB_PORT'] : 3306);
define('DB_USER', isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'if0_42451397'); 
define('DB_PASS', isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : 'Jumanji1324'); 
define('DB_NAME', isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'if0_42451397_gamestore_db');

// App Config
define('APP_NAME', isset($_ENV['APP_NAME']) ? $_ENV['APP_NAME'] : 'BoloTopup.ID');

// Dynamic APP_URL & Protocol Detection (HTTPS/HTTP & Reverse Proxy Compatible)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || ($_SERVER['SERVER_PORT'] ?? 80) == 443
           || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
           || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on');

$protocol = $isHttps ? "https://" : "http://";

if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($scriptName));
    if ($dir === '/') {
        $dir = '';
    }
    // Remove folders like pages, admin, inc, assets, etc. to get the root directory path
    $dir = preg_replace('/(\/(pages|admin|inc|vendor|assets))(\/.*)?$/i', '', $dir);
    $detectedUrl = rtrim($protocol . $host . $dir, '/');
} else {
    $detectedUrl = 'http://localhost/Uts-Web-Adhie_Cyto_Cesario';
}

$rawAppUrl = isset($_ENV['APP_URL']) && $_ENV['APP_URL'] !== '' ? $_ENV['APP_URL'] : $detectedUrl;

// Automatically match HTTPS protocol if page requested over HTTPS to prevent mixed content CSS/JS block
if ($isHttps && strpos($rawAppUrl, 'http://') === 0) {
    $rawAppUrl = 'https://' . substr($rawAppUrl, 7);
}

define('APP_URL', rtrim($rawAppUrl, '/'));
define('APP_VERSION', '1.0.0');

// Google SSO Config
define('GOOGLE_CLIENT_ID', isset($_ENV['GOOGLE_CLIENT_ID']) ? $_ENV['GOOGLE_CLIENT_ID'] : '');
define('GOOGLE_CLIENT_SECRET', isset($_ENV['GOOGLE_CLIENT_SECRET']) ? $_ENV['GOOGLE_CLIENT_SECRET'] : '');

$googleRedirectUri = isset($_ENV['GOOGLE_REDIRECT_URI']) && !empty($_ENV['GOOGLE_REDIRECT_URI'])
    ? $_ENV['GOOGLE_REDIRECT_URI']
    : (APP_URL . '/pages/google-callback.php');

// Dynamically sync protocol (HTTP vs HTTPS) with current request
if ($isHttps && strpos($googleRedirectUri, 'http://') === 0) {
    $googleRedirectUri = 'https://' . substr($googleRedirectUri, 7);
} elseif (!$isHttps && strpos($googleRedirectUri, 'https://') === 0) {
    $googleRedirectUri = 'http://' . substr($googleRedirectUri, 8);
}

define('GOOGLE_REDIRECT_URI', $googleRedirectUri);

// SMTP Config
define('SMTP_HOST', isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : 'smtp.gmail.com');
define('SMTP_PORT', isset($_ENV['SMTP_PORT']) ? (int)$_ENV['SMTP_PORT'] : 587);
define('SMTP_USER', isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : '');
define('SMTP_PASS', isset($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : '');
define('SMTP_SECURE', isset($_ENV['SMTP_SECURE']) ? $_ENV['SMTP_SECURE'] : 'tls');
define('SMTP_FROM_EMAIL', isset($_ENV['SMTP_FROM_EMAIL']) ? $_ENV['SMTP_FROM_EMAIL'] : '');
define('SMTP_FROM_NAME', isset($_ENV['SMTP_FROM_NAME']) ? $_ENV['SMTP_FROM_NAME'] : APP_NAME);

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