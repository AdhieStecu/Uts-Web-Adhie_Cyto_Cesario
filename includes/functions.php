<?php
// =============================================
// HELPER FUNCTIONS
// File: includes/functions.php
// =============================================

// Polyfill for ZipArchive if php-zip extension is not installed/enabled
if (!class_exists('ZipArchive')) {
    class ZipArchive {
        public const CREATE = 1;
        public const OVERWRITE = 8;
        public const RDONLY = 16;
        public const FL_NOCASE = 2;
        public const NO_ERROR = 0;
        
        private $pclzip = null;
        private $filename = '';
        public $numFiles = 0;
        
        public function open($filename, $flags = 0) {
            $this->filename = $filename;
            $pclZipPath = __DIR__ . '/../vendor/phpoffice/phpword/src/PhpWord/Shared/PCLZip/pclzip.lib.php';
            if (file_exists($pclZipPath)) {
                require_once $pclZipPath;
            } else {
                return false;
            }
            $this->pclzip = new PclZip($filename);
            $list = $this->pclzip->listContent();
            $this->numFiles = is_array($list) ? count($list) : 0;
            return true;
        }
        
        public function getFromName($name, $len = 0, $flags = 0) {
            if (!$this->pclzip) return false;
            $list = $this->pclzip->extract(PCLZIP_OPT_BY_NAME, $name, PCLZIP_OPT_EXTRACT_AS_STRING);
            if (is_array($list) && isset($list[0]['content'])) {
                return $list[0]['content'];
            }
            $altName = str_replace('\\', '/', $name);
            if ($altName !== $name) {
                $list = $this->pclzip->extract(PCLZIP_OPT_BY_NAME, $altName, PCLZIP_OPT_EXTRACT_AS_STRING);
                if (is_array($list) && isset($list[0]['content'])) {
                    return $list[0]['content'];
                }
            }
            $altName = str_replace('/', '\\', $name);
            if ($altName !== $name) {
                $list = $this->pclzip->extract(PCLZIP_OPT_BY_NAME, $altName, PCLZIP_OPT_EXTRACT_AS_STRING);
                if (is_array($list) && isset($list[0]['content'])) {
                    return $list[0]['content'];
                }
            }
            return false;
        }
        
        public function locateName($name, $flags = 0) {
            if (!$this->pclzip) return false;
            $list = $this->pclzip->listContent();
            if (is_array($list)) {
                foreach ($list as $idx => $entry) {
                    if (strcasecmp($entry['filename'], $name) === 0 || 
                        strcasecmp(str_replace('\\', '/', $entry['filename']), str_replace('\\', '/', $name)) === 0) {
                        return $idx;
                    }
                }
            }
            return false;
        }
        
        public function getNameIndex($index) {
            if (!$this->pclzip) return false;
            $list = $this->pclzip->listContent();
            if (is_array($list) && isset($list[$index])) {
                return $list[$index]['filename'];
            }
            return false;
        }
        
        public function extractTo($destination, $entries = null) {
            if (!$this->pclzip) return false;
            if ($entries === null) {
                $res = $this->pclzip->extract(PCLZIP_OPT_PATH, $destination);
            } else {
                if (is_string($entries)) {
                    $entries = [$entries];
                }
                $res = $this->pclzip->extract(PCLZIP_OPT_PATH, $destination, PCLZIP_OPT_BY_NAME, $entries);
            }
            return $res !== 0;
        }
        
        public function addFile($filename, $localname = null) {
            if (!$this->pclzip) return false;
            $remName = basename($filename);
            $tempDir = sys_get_temp_dir() . '/zip_temp_' . uniqid();
            mkdir($tempDir, 0777, true);
            $targetPath = $tempDir . '/' . ($localname ? $localname : $remName);
            if (!is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0777, true);
            }
            copy($filename, $targetPath);
            
            $res = $this->pclzip->add($targetPath, PCLZIP_OPT_REMOVE_PATH, $tempDir);
            
            unlink($targetPath);
            @rmdir(dirname($targetPath));
            @rmdir($tempDir);
            
            $list = $this->pclzip->listContent();
            $this->numFiles = is_array($list) ? count($list) : 0;
            return $res !== 0;
        }
        
        public function addFromString($localname, $contents) {
            if (!$this->pclzip) return false;
            $tempDir = sys_get_temp_dir() . '/zip_temp_' . uniqid();
            mkdir($tempDir, 0777, true);
            $targetPath = $tempDir . '/' . $localname;
            if (!is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0777, true);
            }
            file_put_contents($targetPath, $contents);
            
            $res = $this->pclzip->add($targetPath, PCLZIP_OPT_REMOVE_PATH, $tempDir);
            
            unlink($targetPath);
            @rmdir(dirname($targetPath));
            @rmdir($tempDir);
            
            $list = $this->pclzip->listContent();
            $this->numFiles = is_array($list) ? count($list) : 0;
            return $res !== 0;
        }
        
        public function close() {
            $this->pclzip = null;
            return true;
        }
    }
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';

// ---- AUTH HELPERS ----

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect(APP_URL . '/index.php');
    }
}

function currentUser() {
    if (!isLoggedIn()) return null;
    return db()->fetchOne("SELECT * FROM users WHERE id = ?", 'i', $_SESSION['user_id']);
}

// ---- FORMAT HELPERS ----

function rupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return $diff . ' detik lalu';
    if ($diff < 3600) return floor($diff/60) . ' menit lalu';
    if ($diff < 86400) return floor($diff/3600) . ' jam lalu';
    if ($diff < 2592000) return floor($diff/86400) . ' hari lalu';
    return date('d M Y', $time);
}

function excerpt($text, $length = 100) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ---- URL & REDIRECT ----

function redirect($url) {
    header("Location: $url");
    exit;
}

function currentUrl() {
    return (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// ---- FLASH MESSAGES ----

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash() {
    $flash = getFlash();
    if (!$flash) return;
    $type = $flash['type']; // 'success', 'error', 'warning', 'info'
    $message = addslashes($flash['message']);
    $titleMap = ['success' => 'Berhasil! 🎉', 'error' => 'Gagal! ❌', 'warning' => 'Perhatian! ⚠️', 'info' => 'Info ℹ️'];
    $title = $titleMap[$type] ?? 'Notifikasi';

    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: '{$type}',
                title: '{$title}',
                text: '{$message}',
                background: '#0d1530',
                color: '#e8f0fe',
                confirmButtonColor: '#0066ff',
                customClass: {
                    popup: 'swal2-custom-popup',
                    title: 'swal2-custom-title',
                    htmlContainer: 'swal2-custom-text',
                    confirmButton: 'swal2-custom-confirm-btn',
                    cancelButton: 'swal2-custom-cancel-btn'
                }
            });
        } else {
            // Fallback alert
            const fallback = document.createElement('div');
            fallback.className = 'flash-message flash-{$type}';
            fallback.innerText = '{$message}';
            document.body.prepend(fallback);
        }
    });
    </script>
    ";
}

// ---- UPLOAD FILE ----

function uploadImage($file, $folder = 'products') {
    if (empty($file)) return null;

    // Handle Base64 upload
    if (is_string($file) && preg_match('/^data:image\/(\w+);base64,/', $file, $type)) {
        $data = substr($file, strpos($file, ',') + 1);
        $ext = strtolower($type[1]); // jpg, png, gif, webp

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return null;
        }

        $data = base64_decode($data);
        if (!$data) return null;

        $filename = $folder . '_' . time() . '_' . uniqid() . '.' . $ext;
        $uploadPath = UPLOAD_DIR . $filename;

        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

        if (file_put_contents($uploadPath, $data)) {
            return $filename;
        }
        return null;
    }

    // Handle traditional $_FILES upload
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) return null;

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) return null;
    if ($file['size'] > MAX_FILE_SIZE) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $folder . '_' . time() . '_' . uniqid() . '.' . $ext;
    $uploadPath = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }
    return null;
}

function getImageUrl($filename) {
    if (empty($filename)) return APP_URL . '/assets/img/no-image.png';
    if (filter_var($filename, FILTER_VALIDATE_URL)) return $filename;
    return UPLOAD_URL . $filename;
}

// ---- CATEGORY ICON HELPERS ----

function renderCategoryIcon($icon, $class = '', $style = '') {
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $icon)) {
        $url = getImageUrl($icon);
        return '<img src="' . $url . '" class="' . $class . '" style="width:1.2em;height:1.2em;object-fit:cover;border-radius:4px;vertical-align:middle;display:inline-block;' . $style . '" onerror="this.outerHTML=\'🎮\'">';
    }
    return $icon;
}

function getCategoryIconEmoji($icon) {
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $icon)) {
        return '📁'; // Fallback emoji for plain text elements like <option>
    }
    return $icon;
}

// ---- ORDER HELPERS ----

function generateOrderNumber() {
    return 'GS' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

function platformFee($price) {
    return round($price * PLATFORM_FEE_PERCENT / 100);
}

// ---- QRIS GENERATOR ----

function generateQRIS($amount, $orderId) {
    // Simulasi QRIS EMV string (untuk produksi gunakan API Midtrans/Xendit/Tripay)
    $merchantId = QRIS_MERCHANT_ID;
    $merchantName = QRIS_MERCHANT_NAME;
    $city = QRIS_CITY;

    // Simulasi data QRIS (format menyerupai EMV QRIS standar)
    $qrisData = [
        'merchant_id' => $merchantId,
        'merchant_name' => $merchantName,
        'amount' => $amount,
        'order_id' => $orderId,
        'timestamp' => time(),
        'expired' => time() + 3600,
    ];

    // Generate QR Code menggunakan API gratis (goqr.me)
    $qrContent = urlencode("BOLOTOPUP|ORDER:{$orderId}|AMT:{$amount}|TS:" . time());
    $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$qrContent}&format=png";

    return [
        'qr_image_url' => $qrImageUrl,
        'qr_string' => base64_encode(json_encode($qrisData)),
        'expired_at' => date('Y-m-d H:i:s', time() + 3600),
        'amount' => $amount,
        'order_id' => $orderId,
    ];
}

// ---- NOTIFICATION ----

function sendNotification($userId, $title, $message, $type = 'info', $link = '') {
    db()->insert(
        "INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)",
        'issss', $userId, $title, $message, $type, $link
    );
}

function unreadNotifCount($userId) {
    $result = db()->fetchOne(
        "SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0",
        'i', $userId
    );
    return $result['cnt'] ?? 0;
}

// ---- CART HELPERS ----

function cartCount() {
    if (!isLoggedIn()) return 0;
    $result = db()->fetchOne(
        "SELECT SUM(quantity) as cnt FROM cart WHERE user_id = ?",
        'i', $_SESSION['user_id']
    );
    return $result['cnt'] ?? 0;
}

// ---- SECURITY ----

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfInput() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

// ---- RATING STARS ----

function renderStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= $i <= $rating ? '⭐' : '☆';
    }
    return $stars;
}

// ---- PAGINATION ----

function paginate($total, $perPage, $currentPage, $url) {
    $totalPages = ceil($total / $perPage);
    if ($totalPages <= 1) return '';

    $html = '<div class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? ' active' : '';
        $html .= '<a href="' . $url . '?page=' . $i . '" class="page-btn' . $active . '">' . $i . '</a>';
    }
    $html .= '</div>';
    return $html;
}

// ---- EMAIL SENDER (SMTP) ----

function sendEmail($to, $subject, $body, $altBody = '') {
    // Check if configuration is set
    if (empty(SMTP_USER) || empty(SMTP_PASS)) {
        error_log("Email sending aborted: SMTP credentials are not configured.");
        return false;
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = (strtolower(SMTP_SECURE) === 'ssl') ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Disable SSL certificate verification for local environments (XAMPP/Laragon)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL ?: SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        if (!empty($altBody)) {
            $mail->AltBody = $altBody;
        } else {
            $mail->AltBody = strip_tags(str_replace('<br>', "\n", $body));
        }

        $mail->send();
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log("Email sending failed. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}