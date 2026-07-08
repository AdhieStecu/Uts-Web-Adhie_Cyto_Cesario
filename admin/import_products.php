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
        
        $sheet->setShowGridLines(true);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $headers = [
            'Nama Produk *', 'ID Kategori *', 'Harga Jual *', 'Harga Coret', 
            'Stok *', 'Nama Game', 'Platform', 'Tipe Kirim * (instant/manual)', 'Deskripsi'
        ];
        
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            
            // Mandatory headers (Teal) vs Optional headers (Navy) styling
            $isRequired = (in_array($colIndex, [0, 1, 2, 4, 7]));
            $bgColor = $isRequired ? 'FF0D9488' : 'FF1E3A8A';
            
            $style = $sheet->getStyle($colLetter . '1');
            $style->getFont()->setBold(true)->setName('Segoe UI')->setSize(11)->setColor(new Color(Color::COLOR_WHITE));
            $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bgColor);
            $style->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $style->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }
        
        // Sample Row (Row 2)
        $sample = [
            'Mobile Legends 366 Diamond',
            1,
            85000,
            100000,
            99,
            'Mobile Legends',
            'Android/iOS',
            'instant',
            'Top up diamond cepat otomatis 24 jam'
        ];
        
        $sheet->getRowDimension(2)->setRowHeight(20);
        foreach ($sample as $colIndex => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $val);
            
            // Styling sample data
            $cellStyle = $sheet->getStyle($colLetter . '2');
            $cellStyle->getFont()->setName('Segoe UI')->setSize(10)->setColor(new Color('475569'));
            
            // Alignments
            if (in_array($colIndex, [1, 4, 7])) {
                $cellStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
            
            // Formats
            if (in_array($colIndex, [2, 3])) {
                $cellStyle->getNumberFormat()->setFormatCode('"Rp"#,##0');
            }
        }
        
        // Auto-fit columns
        foreach (range(1, 9) as $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        
        // Borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle('A1:I2')->applyFromArray($styleArray);
        
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
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginLeft' => 600,
            'marginRight' => 600,
            'marginTop' => 800,
            'marginBottom' => 800,
        ]);
        
        $section->addText("TEMPLATE IMPORT PRODUK BOLOTOPUP.ID", ['bold' => true, 'size' => 14, 'color' => '1E3A8A', 'name' => 'Segoe UI'], ['alignment' => 'center']);
        $section->addText("Panduan Susunan Kolom Tabel Import Produk. Kolom bertanda (*) adalah WAJIB diisi.", ['size' => 10, 'italic' => true, 'color' => '475569', 'name' => 'Segoe UI'], ['alignment' => 'center']);
        $section->addTextBreak(1);
        
        $tableStyle = [
            'borderSize' => 6, 
            'borderColor' => 'CBD5E1', 
            'cellMargin' => 80,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
        ];
        $phpWord->addTableStyle('TemplateTable', $tableStyle);
        $table = $section->addTable('TemplateTable');
        
        // Headers
        $table->addRow(400);
        $headers = [
            'Nama Produk *', 'ID Kategori *', 'Harga Jual *', 'Harga Coret', 
            'Stok *', 'Nama Game', 'Platform', 'Tipe Kirim *', 'Deskripsi'
        ];
        
        foreach ($headers as $colIndex => $h) {
            $isRequired = (in_array($colIndex, [0, 1, 2, 4, 7]));
            $bgColor = $isRequired ? '0D9488' : '1E3A8A';
            
            $cell = $table->addCell(1200, ['bgColor' => $bgColor]);
            $cell->addText($h, ['bold' => true, 'color' => 'FFFFFF', 'size' => 9, 'name' => 'Segoe UI'], ['alignment' => 'center']);
        }
        
        // Sample Row
        $table->addRow(300);
        $textRow = ['size' => 9, 'name' => 'Segoe UI', 'color' => '475569'];
        $sample = [
            'MLBB 366 Diamond', '1', '85000', '100000', '99', 
            'Mobile Legends', 'Android/iOS', 'instant', 'Top up diamond otomatis'
        ];
        foreach ($sample as $colIndex => $s) {
            $align = (in_array($colIndex, [1, 4, 7])) ? 'center' : 'left';
            $table->addCell(1200)->addText($s, $textRow, ['alignment' => $align]);
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
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Template Import PDF</title>
            <style>
                body { 
                    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
                    font-size: 11px; 
                    color: #334155; 
                    line-height: 1.6; 
                    margin: 0;
                    padding: 10px;
                }
                .header {
                    border-bottom: 3px solid #1e3a8a;
                    padding-bottom: 12px;
                    margin-bottom: 20px;
                }
                .header-logo {
                    font-size: 20px;
                    font-weight: 800;
                    color: #1e3a8a;
                }
                .title {
                    font-size: 14px;
                    font-weight: bold;
                    color: #0f172a;
                    margin-top: 5px;
                }
                .note { 
                    background-color: #f1f5f9; 
                    padding: 12px; 
                    border-left: 4px solid #0d9488; 
                    border-radius: 4px;
                    margin-bottom: 20px; 
                }
                .badge {
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-weight: bold;
                    font-size: 8px;
                    text-transform: uppercase;
                }
                .badge-req { background-color: #fee2e2; color: #991b1b; }
                .badge-opt { background-color: #f1f5f9; color: #475569; }
                
                table.fields-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 25px;
                }
                table.fields-table th {
                    background-color: #1e3a8a;
                    color: #ffffff;
                    font-weight: bold;
                    padding: 8px;
                    border: 1px solid #cbd5e1;
                    font-size: 10px;
                }
                table.fields-table td {
                    padding: 8px;
                    border: 1px solid #cbd5e1;
                    font-size: 9px;
                }
                table.fields-table tr:nth-child(even) {
                    background-color: #f8fafc;
                }

                .sample-title {
                    font-weight: bold;
                    color: #1e3a8a;
                    margin-bottom: 6px;
                    font-size: 10px;
                }
                .sample-box { 
                    font-family: 'Courier New', Courier, monospace; 
                    background-color: #0f172a; 
                    color: #38bdf8; 
                    padding: 12px; 
                    border-radius: 6px; 
                    font-size: 10px;
                    line-height: 1.5;
                    border: 1px solid #1e293b;
                    margin-bottom: 20px;
                }
                .sample-comment { color: #64748b; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='header-logo'>{$appLogo}</div>
                <div class='title'>PANDUAN & TEMPLATE IMPORT PRODUK (PDF)</div>
            </div>
            
            <div class='note'>
                Untuk melakukan import massal via berkas PDF, silakan tulis daftar produk Anda baris demi baris menggunakan format pemisah pipa (<strong>|</strong>) di dalam dokumen PDF Anda seperti petunjuk di bawah ini.
            </div>
            
            <h3 style='color:#0f172a; font-size:12px; margin-bottom:10px;'>Detail Struktur Kolom</h3>
            <table class='fields-table'>
                <thead>
                    <tr>
                        <th style='width: 5%;'>No</th>
                        <th style='width: 20%;'>Nama Kolom</th>
                        <th style='width: 15%;'>Status</th>
                        <th style='width: 15%;'>Tipe Data</th>
                        <th>Keterangan & Contoh</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style='text-align:center;'>1</td>
                        <td><strong>Nama Produk</strong></td>
                        <td style='text-align:center;'><span class='badge badge-req'>Wajib</span></td>
                        <td>Teks</td>
                        <td>Nama barang game. Contoh: MLBB 366 Diamond</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>2</td>
                        <td><strong>ID Kategori</strong></td>
                        <td style='text-align:center;'><span class='badge badge-req'>Wajib</span></td>
                        <td>Angka (ID)</td>
                        <td>ID Kategori dari Admin Panel. Contoh: 1</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>3</td>
                        <td><strong>Harga Jual</strong></td>
                        <td style='text-align:center;'><span class='badge badge-req'>Wajib</span></td>
                        <td>Angka (Desimal)</td>
                        <td>Harga jual tanpa Rp atau titik. Contoh: 85000</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>4</td>
                        <td><strong>Harga Coret</strong></td>
                        <td style='text-align:center;'><span class='badge badge-opt'>Opsional</span></td>
                        <td>Angka (Desimal)</td>
                        <td>Harga sebelum diskon. Kosongkan jika tidak ada diskon. Contoh: 100000</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>5</td>
                        <td><strong>Stok</strong></td>
                        <td style='text-align:center;'><span class='badge badge-req'>Wajib</span></td>
                        <td>Angka (Bulat)</td>
                        <td>Jumlah stok produk. Contoh: 99</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>6</td>
                        <td><strong>Nama Game</strong></td>
                        <td style='text-align:center;'><span class='badge badge-opt'>Opsional</span></td>
                        <td>Teks</td>
                        <td>Nama game terkait. Contoh: Mobile Legends</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>7</td>
                        <td><strong>Platform</strong></td>
                        <td style='text-align:center;'><span class='badge badge-opt'>Opsional</span></td>
                        <td>Teks</td>
                        <td>Platform game. Contoh: Android/iOS</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>8</td>
                        <td><strong>Tipe Kirim</strong></td>
                        <td style='text-align:center;'><span class='badge badge-req'>Wajib</span></td>
                        <td>Teks</td>
                        <td>Hanya isi dengan: <strong>instant</strong> atau <strong>manual</strong></td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>9</td>
                        <td><strong>Deskripsi</strong></td>
                        <td style='text-align:center;'><span class='badge badge-opt'>Opsional</span></td>
                        <td>Teks</td>
                        <td>Penjelasan produk. Contoh: Proses pengisian 24 jam cepat</td>
                    </tr>
                </tbody>
            </table>
            
            <div class='sample-title'>Format Baris Data:</div>
            <div class='sample-box'>
                <span class='sample-comment'># Kolom dibatasi oleh karakter |</span><br>
                Nama Produk | ID Kategori | Harga Jual | Harga Coret | Stok | Nama Game | Platform | Tipe Kirim | Deskripsi
            </div>
            
            <div class='sample-title'>Contoh Data Riil:</div>
            <div class='sample-box'>
                Mobile Legends 366 Diamond | 1 | 85000 | 100000 | 99 | Mobile Legends | Android/iOS | instant | Top up instan cepat<br>
                Free Fire 140 Diamond | 1 | 20000 | | 150 | Free Fire | Android/iOS | instant | Kirim instan cepat
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
        <a href="<?= APP_URL ?>/admin/products.php" class="active">🎮 Produk</a>
        <a href="<?= APP_URL ?>/admin/categories.php">📂 Kategori</a>
        <a href="<?= APP_URL ?>/admin/orders.php">📦 Pesanan</a>
        <a href="<?= APP_URL ?>/admin/users.php">👥 Pengguna</a>
        <a href="<?= APP_URL ?>/admin/payments.php">💳 Pembayaran</a>
        <a href="<?= APP_URL ?>/admin/withdrawals.php">💸 Penarikan</a>
        <a href="<?= APP_URL ?>/admin/reviews.php">⭐ Ulasan</a>
        <a href="<?= APP_URL ?>/admin/test-smtp.php">📧 Test SMTP</a>
    </aside>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">📥 Import Produk Massal</h1>
            <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline">← Kembali</a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error" style="margin-bottom: 24px;">
                <?php foreach ($errors as $e): ?>
                    <p>⚠️ <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="grid-2" style="gap:24px; align-items: start;">
            <!-- FILE UPLOAD CARD -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head); margin-bottom:16px; font-weight:700;">📁 Unggah Berkas Excel/Word/PDF</h3>
                    <p style="color:var(--text-muted); font-size:13px; margin-bottom:20px; line-height:1.6;">
                        Silakan unggah file berformat Excel (.xlsx), Word (.docx), atau PDF (.pdf) sesuai dengan struktur kolom template yang didukung. Sistem akan memproses dan menambahkan produk secara otomatis ke katalog.
                    </p>
                    
                    <form method="POST" enctype="multipart/form-data" style="background: rgba(255,255,255,0.02); border: 2px dashed var(--border); padding: 25px; border-radius: 12px; text-align: center; margin-bottom: 24px;">
                        <?= csrfInput() ?>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label class="form-label" style="display:block; font-weight:600; margin-bottom:10px;">Pilih File Unggahan</label>
                            <input type="file" name="import_file" class="form-control" accept=".xlsx, .docx, .pdf" required style="padding:10px; background:var(--bg-card); color:var(--text-primary); border-radius:6px; border:1px solid var(--border); display:inline-block; max-width:100%;">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:10px;">
                            📥 Unggah & Proses Import Produk
                        </button>
                    </form>
                    
                    <hr style="border-color:var(--border); margin:24px 0;">
                    
                    <h4 style="font-family:var(--font-head); margin-bottom:14px; font-weight:700; color:var(--accent);">⬇️ Unduh Berkas Contoh (Template)</h4>
                    <p style="color:var(--text-muted); font-size:12px; margin-bottom:16px;">
                        Unduh file contoh berikut sebagai acuan untuk mengisi data produk Anda dengan benar:
                    </p>
                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <a href="?action=template&format=excel" class="btn btn-success btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
                            📊 Unduh Excel Template
                        </a>
                        <a href="?action=template&format=word" class="btn btn-info btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
                            📝 Unduh Word Template
                        </a>
                        <a href="?action=template&format=pdf" class="btn btn-danger btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
                            📕 Unduh PDF Template
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- GUIDE CARD -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-family:var(--font-head); margin-bottom:16px; font-weight:700;">📋 Petunjuk & Referensi Pengisian</h3>
                    
                    <h4 style="font-family:var(--font-head); font-size:14px; margin-bottom:10px; color:var(--text-primary);">1. ID Kategori Terdaftar</h4>
                    <p style="color:var(--text-muted); font-size:12px; margin-bottom:12px; line-height:1.5;">
                        Masukkan nilai angka ID kategori di bawah ini pada kolom <strong>"ID Kategori"</strong> di file Anda untuk memetakan produk dengan benar:
                    </p>
                    <div style="background:var(--bg-card2); border-radius:8px; border:1px solid var(--border); padding:10px; margin-bottom:24px; max-height:220px; overflow-y:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:13px;">
                            <thead>
                                <tr style="border-bottom:2px solid var(--border); text-align:left; color:var(--text-muted);">
                                    <th style="padding:8px;">ID</th>
                                    <th style="padding:8px;">Nama Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr style="border-bottom:1px solid var(--border); hover:background-color:rgba(255,255,255,0.02)">
                                        <td style="padding:8px; color:var(--accent);"><strong><?= $cat['id'] ?></strong></td>
                                        <td style="padding:8px; font-weight:600;"><?= htmlspecialchars($cat['name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <h4 style="font-family:var(--font-head); font-size:14px; margin-bottom:10px; color:var(--text-primary);">2. Aturan Pengisian Kolom</h4>
                    <ul style="font-size:13px; color:var(--text-muted); padding-left:20px; line-height:1.8;">
                        <li><strong style="color:var(--text-primary);">Nama Produk</strong>: Wajib diisi (maksimal 255 karakter).</li>
                        <li><strong style="color:var(--text-primary);">Harga Jual / Coret</strong>: Input berupa angka biasa tanpa RP, titik, atau koma (contoh: <code style="color:var(--accent);">85000</code>).</li>
                        <li><strong style="color:var(--text-primary);">Stok</strong>: Wajib diisi berupa angka bulat positif (contoh: <code style="color:var(--accent);">100</code>).</li>
                        <li><strong style="color:var(--text-primary);">Tipe Kirim</strong>: Hanya diisi dengan teks kecil <code style="color:var(--accent);">instant</code> (otomatis langsung terkirim) atau <code style="color:var(--accent);">manual</code>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
