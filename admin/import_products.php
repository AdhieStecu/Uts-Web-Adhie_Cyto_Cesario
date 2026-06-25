<?php
// File: admin/import_products.php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use Smalot\PdfParser\Parser as PdfParser;

$action = sanitize($_GET['action'] ?? '');
$format = sanitize($_GET['format'] ?? '');

// Handle template download
if ($action === 'template') {
    $filename = "Template_Import_Produk_" . date('Ymd_His');
    
    if ($format === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');
        
        $headers = ['Nama Produk', 'ID Kategori', 'Harga Jual', 'Harga Coret', 'Stok', 'Nama Game', 'Platform', 'Tipe Kirim (instant/manual)', 'Deskripsi'];
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }
        
        // Sample Row
        $sample = [
            'Mobile Legends 366 Diamond',
            1,
            85000,
            100000,
            99,
            'Mobile Legends',
            'Android/iOS',
            'instant',
            'Top up diamond cepat 24 jam'
        ];
        foreach ($sample as $colIndex => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }
        
        foreach (range(1, 9) as $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        
        $tempFile = tempnam(sys_get_temp_dir(), 'xls');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($tempFile));
        readfile($tempFile);
        unlink($tempFile);
        exit;
        
    } elseif ($format === 'word') {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        $section->addText("TEMPLATE IMPORT PRODUK BOLOTOPUP.ID", ['bold' => true, 'size' => 14]);
        $section->addText("Pastikan tabel memiliki susunan kolom di bawah ini dan baris pertama dilewati (sebagai header).");
        $section->addTextBreak(1);
        
        $tableStyle = ['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 50];
        $firstRowStyle = ['bgColor' => '4f46e5'];
        $phpWord->addTableStyle('TemplateTable', $tableStyle, $firstRowStyle);
        $table = $section->addTable('TemplateTable');
        
        // Headers
        $table->addRow();
        $textHeader = ['bold' => true, 'color' => 'FFFFFF', 'size' => 8];
        $headers = ['Nama', 'ID Kat', 'Harga', 'Orig Harga', 'Stok', 'Game', 'Platform', 'Tipe Kirim', 'Deskripsi'];
        foreach ($headers as $h) {
            $table->addCell(1100)->addText($h, $textHeader);
        }
        
        // Sample Row
        $table->addRow();
        $textRow = ['size' => 8];
        $sample = ['MLBB 366 Diamond', '1', '85000', '100000', '99', 'Mobile Legends', 'Android/iOS', 'instant', 'Top up diamond cepat'];
        foreach ($sample as $s) {
            $table->addCell(1100)->addText($s, $textRow);
        }
        
        $tempFile = tempnam(sys_get_temp_dir(), 'doc');
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $filename . '.docx"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($tempFile));
        readfile($tempFile);
        unlink($tempFile);
        exit;
        
    } elseif ($format === 'pdf') {
        $appLogo = '⚡ BoloTopup.ID';
        $html = "
        <html>
        <head>
            <title>Template Import PDF</title>
            <style>
                body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.6; }
                h2 { color: #4f46e5; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; }
                .note { background: #f1f5f9; padding: 10px; border-left: 4px solid #4f46e5; margin-bottom: 15px; }
                .sample { font-family: monospace; background: #0f172a; color: #38bdf8; padding: 12px; border-radius: 6px; }
            </style>
        </head>
        <body>
            <h2>{$appLogo} - TEMPLATE IMPORT PRODUK (PDF)</h2>
            <div class='note'>
                Untuk melakukan import via PDF, silakan tulis list produk Anda baris demi baris menggunakan format pembatas pipa (<strong>|</strong>) seperti contoh di bawah ini:
            </div>
            
            <p><strong>Format Kolom:</strong></p>
            <div class='sample'>
                Nama Produk | ID Kategori | Harga Jual | Harga Coret | Stok | Nama Game | Platform | Tipe Kirim (instant/manual) | Deskripsi
            </div>
            
            <p><strong>Contoh Baris Data:</strong></p>
            <div class='sample'>
                Mobile Legends 366 Diamond | 1 | 85000 | 100000 | 99 | Mobile Legends | Android/iOS | instant | Top up instan cepat<br>
                Free Fire 140 Diamond | 1 | 20000 | 25000 | 150 | Free Fire | Android/iOS | instant | Kirim instan
            </div>
        </body>
        </html>";
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename . ".pdf", ["Attachment" => true]);
        exit;
    }
}

// Helper function to extract text recursively from PHPWord element
function getElementTextRecursive($element) {
    if (!is_object($element)) {
        return is_string($element) ? $element : '';
    }
    if (method_exists($element, 'getText')) {
        return $element->getText();
    }
    $text = '';
    if (method_exists($element, 'getElements')) {
        foreach ($element->getElements() as $child) {
            $text .= getElementTextRecursive($child);
        }
    }
    return $text;
}

// Function to extract text cleanly from PHPWord Cell
function getWordCellText($cell) {
    if (is_string($cell)) {
        return trim($cell);
    }
    if (!is_object($cell)) {
        return '';
    }
    return trim(getElementTextRecursive($cell));
}

// Process Uploaded File
$errors = [];
$successCount = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF tidak valid.';
    } else {
        $file = $_FILES['import_file'];
        $tmpPath = $file['tmp_name'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (empty($tmpPath) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Gagal mengunggah file. Silakan coba lagi.';
        } else {
            $productsToInsert = [];
            
            // 1. EXCEL PARSING
            if ($ext === 'xlsx') {
                try {
                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tmpPath);
                    $spreadsheet = $reader->load($tmpPath);
                    $sheet = $spreadsheet->getActiveSheet();
                    $rows = $sheet->toArray();
                    
                    // Skip header row
                    for ($i = 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        if (empty($row[0])) continue; // Skip empty rows
                        
                        $productsToInsert[] = [
                            'name' => sanitize($row[0] ?? ''),
                            'category_id' => (int)($row[1] ?? 1),
                            'price' => (float)($row[2] ?? 0),
                            'original_price' => !empty($row[3]) ? (float)$row[3] : null,
                            'stock' => (int)($row[4] ?? 0),
                            'game_name' => sanitize($row[5] ?? ''),
                            'platform' => sanitize($row[6] ?? ''),
                            'delivery_type' => sanitize($row[7] ?? 'instant'),
                            'description' => sanitize($row[8] ?? ''),
                        ];
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Gagal membaca format file Excel: ' . $e->getMessage();
                }
                
            // 2. WORD PARSING
            } elseif ($ext === 'docx') {
                try {
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($tmpPath);
                    foreach ($phpWord->getSections() as $section) {
                        foreach ($section->getElements() as $element) {
                            if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                                $rows = $element->getRows();
                                // Skip header row
                                for ($i = 1; $i < count($rows); $i++) {
                                    $cells = $rows[$i]->getCells();
                                    if (count($cells) >= 3) {
                                        $name = sanitize(getWordCellText($cells[0] ?? ''));
                                        if (empty($name)) continue;
                                        
                                        $productsToInsert[] = [
                                            'name' => $name,
                                            'category_id' => (int)sanitize(getWordCellText($cells[1] ?? '1')),
                                            'price' => (float)sanitize(getWordCellText($cells[2] ?? '0')),
                                            'original_price' => !empty(getWordCellText($cells[3] ?? '')) ? (float)sanitize(getWordCellText($cells[3] ?? '')) : null,
                                            'stock' => (int)sanitize(getWordCellText($cells[4] ?? '0')),
                                            'game_name' => sanitize(getWordCellText($cells[5] ?? '')),
                                            'platform' => sanitize(getWordCellText($cells[6] ?? '')),
                                            'delivery_type' => sanitize(getWordCellText($cells[7] ?? 'instant')),
                                            'description' => sanitize(getWordCellText($cells[8] ?? '')),
                                        ];
                                    }
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Gagal membaca format file Word: ' . $e->getMessage();
                }
                
            // 3. PDF PARSING
            } elseif ($ext === 'pdf') {
                try {
                    $pdfParser = new PdfParser();
                    $pdf = $pdfParser->parseFile($tmpPath);
                    $text = $pdf->getText();
                    $lines = explode("\n", $text);
                    
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        
                        $parts = explode("|", $line);
                        // Skip headers if line contains header keywords
                        if (stripos($parts[0], 'Nama Produk') !== false || stripos($parts[0], 'Format Kolom') !== false) {
                            continue;
                        }
                        
                        if (count($parts) >= 3) {
                            $name = sanitize($parts[0] ?? '');
                            if (empty($name)) continue;
                            
                            $productsToInsert[] = [
                                'name' => $name,
                                'category_id' => (int)($parts[1] ?? 1),
                                'price' => (float)($parts[2] ?? 0),
                                'original_price' => !empty($parts[3]) && trim($parts[3]) !== '' ? (float)$parts[3] : null,
                                'stock' => (int)($parts[4] ?? 0),
                                'game_name' => sanitize($parts[5] ?? ''),
                                'platform' => sanitize($parts[6] ?? ''),
                                'delivery_type' => sanitize($parts[7] ?? 'instant'),
                                'description' => sanitize($parts[8] ?? ''),
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Gagal membaca format file PDF: ' . $e->getMessage();
                }
            } else {
                $errors[] = 'Format file tidak didukung. Unggah file .xlsx, .docx, atau .pdf saja.';
            }
            
            // DB insertion
            if (empty($errors)) {
                if (empty($productsToInsert)) {
                    $errors[] = 'Tidak ada baris data produk valid yang ditemukan dalam berkas.';
                } else {
                    foreach ($productsToInsert as $prod) {
                        $slug = slugify($prod['name']) . '-' . time() . '-' . uniqid();
                        $insertId = db()->insert(
                            "INSERT INTO products (name, slug, category_id, seller_id, description, price, original_price, stock, game_name, platform, delivery_type, status, is_featured) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 0)",
                            'ssiisddisss',
                            $prod['name'], $slug, $prod['category_id'], $_SESSION['user_id'], $prod['description'],
                            $prod['price'], $prod['original_price'], $prod['stock'], $prod['game_name'], $prod['platform'], $prod['delivery_type']
                        );
                        if ($insertId) {
                            $successCount++;
                        }
                    }
                    
                    if ($successCount > 0) {
                        setFlash('success', "Berhasil mengimpor {$successCount} produk ke database! 🎉");
                        redirect(APP_URL . '/admin/products.php');
                    } else {
                        $errors[] = 'Gagal menyimpan produk ke database. Coba lagi.';
                    }
                }
            }
        }
    }
}

// Get categories for list reference
$categories = db()->fetchAll("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Produk | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js" defer></script>
</head>
<body>
<div class="admin-topbar">
    <div class="brand">⚡ BoloTopup Admin</div>
    <div style="display:flex;gap:12px;">
        <a href="<?= APP_URL ?>" class="btn btn-outline btn-sm">🌐 Website</a>
        <a href="<?= APP_URL ?>/pages/logout.php" class="btn btn-danger btn-sm">Keluar</a>
    </div>
</div>
<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="brand" style="padding:20px;">⚡ Admin</div>
        <a href="<?= APP_URL ?>/admin/index.php">📊 Dashboard</a>
        <a href="<?= APP_URL ?>/admin/products.php" class="active">🎮 Produk</a>
        <a href="<?= APP_URL ?>/admin/categories.php">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
    </aside>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">📥 Import Produk massal</h1>
            <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline">← Kembali</a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error">
                <?php foreach ($errors as $e): ?>
                    <p>⚠️ <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="grid-2" style="gap:24px; align-items: start;">
            <!-- FILE UPLOAD CARD -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head); margin-bottom:16px;">📁 Unggah Berkas</h3>
                    <p style="color:var(--text-secondary); font-size:14px; margin-bottom:20px;">
                        Pilih file Excel (.xlsx), Word (.docx), atau PDF (.pdf) Anda dan klik tombol di bawah untuk mengimpor produk.
                    </p>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrfInput() ?>
                        <div class="form-group">
                            <label class="form-label">Berkas File *</label>
                            <input type="file" name="import_file" class="form-control" accept=".xlsx, .docx, .pdf" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">🚀 Mulai Import Produk</button>
                    </form>
                    
                    <hr style="border-color:var(--border); margin:24px 0;">
                    
                    <h4 style="font-family:var(--font-head); margin-bottom:12px;">⬇️ Unduh Berkas Contoh (Template)</h4>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <a href="?action=template&format=excel" class="btn btn-success btn-sm">📊 Excel Template</a>
                        <a href="?action=template&format=word" class="btn btn-info btn-sm">📝 Word Template</a>
                        <a href="?action=template&format=pdf" class="btn btn-danger btn-sm">📕 PDF Template</a>
                    </div>
                </div>
            </div>
            
            <!-- GUIDE CARD -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head); margin-bottom:16px;">📋 Petunjuk & Referensi</h3>
                    <h4 style="font-family:var(--font-head); font-size:14px; margin-bottom:8px;">1. ID Kategori Terdaftar</h4>
                    <p style="color:var(--text-secondary); font-size:13px; margin-bottom:10px;">
                        Gunakan angka ID di bawah ini pada kolom "ID Kategori" di file Anda:
                    </p>
                    <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:20px;">
                        <thead>
                            <tr style="border-bottom:2px solid var(--border); text-align:left;">
                                <th style="padding:6px 0;">ID Kategori</th>
                                <th style="padding:6px 0;">Nama Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:6px 0;"><strong><?= $cat['id'] ?></strong></td>
                                    <td style="padding:6px 0;"><?= htmlspecialchars($cat['name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <h4 style="font-family:var(--font-head); font-size:14px; margin-bottom:8px;">2. Aturan Format</h4>
                    <ul style="font-size:13px; color:var(--text-secondary); padding-left:20px; line-height:1.8;">
                        <li><strong>Harga Jual / Coret</strong>: Input berupa angka biasa tanpa RP atau tanda titik (contoh: 85000).</li>
                        <li><strong>Stok</strong>: Masukkan jumlah angka bulat (contoh: 100).</li>
                        <li><strong>Tipe Kirim</strong>: Isi hanya dengan <code style="color:var(--accent);">instant</code> (pengiriman instan) atau <code style="color:var(--accent);">manual</code>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
