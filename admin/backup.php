<?php
// File: admin/backup.php
$pageTitle = 'Backup Database';
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$backupDir = __DIR__ . '/../backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Function to generate pure PHP database SQL dump
function generateSqlDump() {
    $conn = db()->getConn();
    
    // Get all tables
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    $sqlDump = "-- Database Backup for " . APP_NAME . "\n";
    $sqlDump .= "-- Generated on " . date('Y-m-d H:i:s') . "\n";
    $sqlDump .= "-- PHP Version: " . phpversion() . "\n\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        // Table structure
        $resStructure = $conn->query("SHOW CREATE TABLE `$table`");
        if ($resStructure) {
            $rowStructure = $resStructure->fetch_row();
            $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
            $sqlDump .= $rowStructure[1] . ";\n\n";
        }
        
        // Table data
        $resData = $conn->query("SELECT * FROM `$table`");
        if ($resData && $resData->num_rows > 0) {
            while ($rowData = $resData->fetch_assoc()) {
                $keys = array_keys($rowData);
                $values = array_values($rowData);
                
                $escapedValues = array_map(function($val) use ($conn) {
                    if ($val === null) return 'NULL';
                    return "'" . $conn->real_escape_string($val) . "'";
                }, $values);
                
                $sqlDump .= "INSERT INTO `$table` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
            }
            $sqlDump .= "\n";
        }
    }
    
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $sqlDump;
}

// Function to execute SQL dump queries
function restoreSqlDump($sqlContent) {
    $conn = db()->getConn();
    $conn->query("SET FOREIGN_KEY_CHECKS=0;");
    
    // Clean sql formatting
    // Remove comments
    $sqlContent = preg_replace('/--(.*)\n/i', '', $sqlContent);
    $sqlContent = preg_replace('/\/\*(.*)\*\//i', '', $sqlContent);
    
    // Split queries by semicolon + newline (which is how we generated it)
    $queries = explode(";\n", $sqlContent);
    
    $success = 0;
    $errors = 0;
    
    foreach ($queries as $q) {
        $q = trim($q);
        if (empty($q)) continue;
        
        // Remove trailing semicolons if present to prevent mysqli query errors
        if (substr($q, -1) === ';') {
            $q = substr($q, 0, -1);
        }
        
        if ($conn->query($q)) {
            $success++;
        } else {
            $errors++;
            error_log("Restore Error: " . $conn->error . " | Query: " . $q);
        }
    }
    
    $conn->query("SET FOREIGN_KEY_CHECKS=1;");
    return ['success' => $success, 'errors' => $errors];
}

$action = sanitize($_GET['action'] ?? '');
$filename = sanitize($_GET['file'] ?? '');

// Handle Actions
if ($action === 'download') {
    $dump = generateSqlDump();
    $fileName = 'backup_' . DB_NAME . '_' . date('Ymd_His') . '.sql';
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . strlen($dump));
    echo $dump;
    exit;
}

if ($action === 'save') {
    if (!verifyCsrf($_GET['csrf_token'] ?? '')) {
        setFlash('error', 'Token CSRF tidak valid.');
        redirect(APP_URL . '/admin/backup.php');
    }
    $dump = generateSqlDump();
    $fileName = 'backup_' . DB_NAME . '_' . date('Ymd_His') . '.sql';
    
    if (file_put_contents($backupDir . $fileName, $dump)) {
        setFlash('success', 'Backup database berhasil disimpan di server! 🗄️');
    } else {
        setFlash('error', 'Gagal menyimpan berkas backup ke server.');
    }
    redirect(APP_URL . '/admin/backup.php');
}

if ($action === 'delete' && !empty($filename)) {
    if (!verifyCsrf($_GET['csrf_token'] ?? '')) {
        setFlash('error', 'Token CSRF tidak valid.');
        redirect(APP_URL . '/admin/backup.php');
    }
    // Prevent directory traversal
    $targetFile = basename($filename);
    $filePath = $backupDir . $targetFile;
    
    if (file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
        setFlash('success', 'Berkas backup berhasil dihapus dari server.');
    } else {
        setFlash('error', 'Berkas backup tidak ditemukan.');
    }
    redirect(APP_URL . '/admin/backup.php');
}

if ($action === 'restore' && !empty($filename)) {
    if (!verifyCsrf($_GET['csrf_token'] ?? '')) {
        setFlash('error', 'Token CSRF tidak valid.');
        redirect(APP_URL . '/admin/backup.php');
    }
    $targetFile = basename($filename);
    $filePath = $backupDir . $targetFile;
    
    if (file_exists($filePath) && is_file($filePath)) {
        $content = file_get_contents($filePath);
        $res = restoreSqlDump($content);
        if ($res['errors'] === 0) {
            setFlash('success', "Database berhasil di-restore! {$res['success']} query dieksekusi dengan sukses. 🎉");
        } else {
            setFlash('warning', "Database di-restore dengan beberapa error. {$res['success']} query sukses, {$res['errors']} gagal.");
        }
    } else {
        setFlash('error', 'Berkas backup tidak ditemukan.');
    }
    redirect(APP_URL . '/admin/backup.php');
}

// Handle restore from uploaded file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_sql'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token CSRF tidak valid.');
    } else {
        $file = $_FILES['upload_sql'];
        if ($file['error'] === UPLOAD_ERR_OK && pathinfo($file['name'], PATHINFO_EXTENSION) === 'sql') {
            $content = file_get_contents($file['tmp_name']);
            $res = restoreSqlDump($content);
            if ($res['errors'] === 0) {
                setFlash('success', "Upload berhasil! Database di-restore: {$res['success']} query sukses. 🎉");
            } else {
                setFlash('warning', "Database di-restore dengan error: {$res['success']} sukses, {$res['errors']} gagal.");
            }
        } else {
            setFlash('error', 'Berkas unggahan salah atau bukan bertipe .sql.');
        }
    }
    redirect(APP_URL . '/admin/backup.php');
}

// List backups in directory
$backupFiles = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filePath = $backupDir . $file;
            $backupFiles[] = [
                'name' => $file,
                'size' => filesize($filePath),
                'date' => date('d M Y, H:i', filemtime($filePath)),
                'raw_date' => filemtime($filePath)
            ];
        }
    }
}
// Sort backups newest first
usort($backupFiles, function($a, $b) {
    return $b['raw_date'] - $a['raw_date'];
});

// Count total tables
$tableResult = db()->getConn()->query("SHOW TABLES");
$totalTables = $tableResult ? $tableResult->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Database | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.2.1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js?v=1.2.1" defer></script>
</head>
<body>
<div class="admin-topbar">
    <div class="brand">⚡ BoloTopup Admin</div>
    <div class="top-actions">
        <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="<?= APP_URL ?>" class="btn btn-outline btn-sm">🌐 Lihat Website</a>
        <a href="<?= APP_URL ?>/pages/logout.php" class="btn btn-danger btn-sm">Keluar</a>
    </div>
</div>
<div class="admin-wrap">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="brand" style="padding:20px;">⚡ Admin Panel</div>
        <a href="<?= APP_URL ?>/admin/index.php">📊 Dashboard</a>
        <a href="<?= APP_URL ?>/admin/products.php">🎮 Produk</a>
        <a href="<?= APP_URL ?>/admin/categories.php">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/tinjau-seller.php">🔍 Tinjau Seller</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
        <a href="<?= APP_URL ?>/admin/backup.php" class="active">🗄️ Backup Database</a>
    </aside>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">🗄️ Backup & Restore Database</h1>
            <a href="<?= APP_URL ?>/admin/index.php" class="btn btn-outline">← Dashboard</a>
        </div>
        
        <?php showFlash(); ?>
        
        <div class="grid-2" style="gap:24px; align-items: start;">
            <!-- BACKUP ACTIONS CARD -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head); margin-bottom:16px; font-weight:700;">🚀 Tindakan Backup</h3>
                    <p style="color:var(--text-muted); font-size:13px; margin-bottom:24px; line-height:1.6;">
                        Simpan salinan database MySQL Anda sewaktu-waktu. Rekomendasi dilakukan secara berkala sebelum pembaruan sistem atau data sensitif.
                    </p>
                    
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border); padding: 18px; border-radius: 8px; margin-bottom: 24px;">
                        <h4 style="font-family:var(--font-head); margin-bottom:8px; font-size:14px; color:var(--text-primary);">Informasi Database Saat Ini</h4>
                        <table style="width:100%; font-size:13px; color:var(--text-muted);">
                            <tr>
                                <td style="padding:4px 0;">Host MySQL</td>
                                <td style="text-align:right; color:var(--accent);"><strong><?= DB_HOST ?></strong></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0;">Nama Database</td>
                                <td style="text-align:right; color:var(--accent);"><strong><?= DB_NAME ?></strong></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0;">Jumlah Tabel Terbaca</td>
                                <td style="text-align:right; color:var(--accent);"><strong><?= $totalTables ?> Tabel</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <a href="?action=download" class="btn btn-primary btn-block btn-lg" style="justify-content:center;">
                            📥 Unduh SQL Backup (Langsung)
                        </a>
                        <a href="?action=save&csrf_token=<?= csrfToken() ?>" class="btn btn-secondary btn-block btn-lg" style="justify-content:center;">
                            🗄️ Simpan Backup ke Server
                        </a>
                    </div>
                    
                    <hr style="border-color:var(--border); margin:24px 0;">
                    
                    <h3 style="font-family:var(--font-head); margin-bottom:14px; font-weight:700; color:var(--accent);">📤 Unggah Berkas SQL (.sql)</h3>
                    <p style="color:var(--text-muted); font-size:12px; margin-bottom:16px;">
                        Pulihkan database dari berkas backup lokal komputer Anda:
                    </p>
                    <form method="POST" enctype="multipart/form-data" style="background: rgba(255,255,255,0.01); border: 1px dashed var(--border); padding: 15px; border-radius: 8px; text-align: center;">
                        <?= csrfInput() ?>
                        <input type="file" name="upload_sql" accept=".sql" required style="font-size:12px; margin-bottom:12px; max-width:100%;">
                        <button type="submit" class="btn btn-danger btn-sm btn-block" style="justify-content:center;" onclick="return confirm('PENTING: Memulihkan database dari SQL berkas akan menimpa tabel-tabel saat ini! Lanjutkan?')">
                            🔥 Unggah & Restore Sekarang
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- BACKUP LIST CARD -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head); margin-bottom:16px; font-weight:700;">📂 Daftar Backup di Server</h3>
                    <p style="color:var(--text-muted); font-size:13px; margin-bottom:20px;">
                        Berikut berkas backup database yang tersimpan di direktori server Anda (`/backups/`):
                    </p>
                    
                    <?php if (empty($backupFiles)): ?>
                        <div style="text-align:center; padding:40px; color:var(--text-muted); background:rgba(255,255,255,0.01); border-radius:10px; border:1px solid var(--border);">
                            📁 Belum ada berkas backup yang tersimpan di server.
                        </div>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:12px; max-height:480px; overflow-y:auto; padding-right:4px;">
                            <?php foreach ($backupFiles as $f): ?>
                                <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border); border-radius:8px; padding:12px; display:flex; justify-content:space-between; align-items:center; gap:10px;">
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:13px; font-weight:600; word-break:break-all; color:var(--text-primary);"><?= htmlspecialchars($f['name']) ?></div>
                                        <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                                            📅 <?= $f['date'] ?> · 💾 <?= round($f['size'] / 1024, 2) ?> KB
                                        </div>
                                    </div>
                                    <div style="display:flex; gap:6px;">
                                        <a href="?action=restore&file=<?= urlencode($f['name']) ?>&csrf_token=<?= csrfToken() ?>" class="btn btn-success btn-xs" onclick="return confirm('PENTING: Memulihkan database akan menimpa seluruh tabel saat ini! Lanjutkan?')">
                                            Restore
                                        </a>
                                        <a href="?action=delete&file=<?= urlencode($f['name']) ?>&csrf_token=<?= csrfToken() ?>" class="btn btn-danger btn-xs" onclick="return confirm('Hapus berkas backup ini dari server?')">
                                            Hapus
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
