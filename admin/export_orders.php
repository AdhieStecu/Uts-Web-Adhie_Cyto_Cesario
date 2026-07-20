<?php
// File: admin/export_orders.php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;

use Dompdf\Dompdf;
use Dompdf\Options;

$format = sanitize($_GET['format'] ?? '');
$status = sanitize($_GET['status'] ?? '');

// Fetch data
$where = '';
$params = [];
$types = '';
if ($status) {
    $where = "WHERE o.status = ?";
    $params[] = $status;
    $types = 's';
}

$orders = db()->fetchAll(
    "SELECT o.*, u.username as buyer_name, u.email as buyer_email, p.name as product_name, s.username as seller_name 
     FROM orders o 
     JOIN users u ON o.buyer_id = u.id 
     JOIN products p ON o.product_id = p.id 
     JOIN users s ON o.seller_id = s.id
     $where 
     ORDER BY o.created_at DESC",
    $types ?: null, ...$params
);

$statusLabel = $status ? ucfirst($status) : 'Semua';
$titleReport = "Laporan Pesanan BoloTopup.ID (" . $statusLabel . ")";

if ($format === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Pesanan');

    // Title Block
    $sheet->setCellValue('A1', $titleReport);
    $sheet->mergeCells('A1:J1');
    $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setName('Segoe UI')->setColor(new Color('1E3A8A'));
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A2', "Tanggal Cetak: " . date('d F Y, H:i') . " WIB | Total: " . count($orders) . " Transaksi");
    $sheet->mergeCells('A2:J2');
    $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->setName('Segoe UI')->setColor(new Color('64748B'));
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Headers on Row 4
    $headers = [
        'No. Order', 'Pembeli', 'Seller', 'Produk', 'Jumlah', 
        'Harga Unit', 'Platform Fee', 'Total Harga', 'Status', 'Tanggal'
    ];
    $colLetter = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colLetter . '4', $header);
        $colLetter++;
    }

    // Header styling
    $headerRange = 'A4:J4';
    $sheet->getRowDimension(4)->setRowHeight(28);
    $sheet->getStyle($headerRange)->getFont()->setBold(true)->setName('Segoe UI')->setSize(11)->setColor(new Color(Color::COLOR_WHITE));
    $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
    $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    // Freeze panes below header
    $sheet->freezePane('A5');

    // Data rows starting at Row 5
    $rowNum = 5;
    foreach ($orders as $order) {
        $sheet->setCellValue('A' . $rowNum, $order['order_number'] ?? '');
        $sheet->setCellValue('B' . $rowNum, $order['buyer_name'] ?? '');
        $sheet->setCellValue('C' . $rowNum, $order['seller_name'] ?? '');
        $sheet->setCellValue('D' . $rowNum, $order['product_name'] ?? '');
        $sheet->setCellValue('E' . $rowNum, $order['quantity'] ?? 0);
        $sheet->setCellValue('F' . $rowNum, $order['price'] ?? 0.0);
        $sheet->setCellValue('G' . $rowNum, $order['platform_fee'] ?? 0.0);
        $sheet->setCellValue('H' . $rowNum, $order['total_price'] ?? 0.0);
        $sheet->setCellValue('I' . $rowNum, ucfirst($order['status'] ?? ''));
        $sheet->setCellValue('J' . $rowNum, !empty($order['created_at']) ? date('d-m-Y H:i', strtotime($order['created_at'])) : '');
        
        // Row height and font
        $sheet->getRowDimension($rowNum)->setRowHeight(20);
        $sheet->getStyle('A' . $rowNum . ':J' . $rowNum)->getFont()->setName('Segoe UI')->setSize(10)->setColor(new Color('334155'));

        // Alignments
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Number formats
        $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('H' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        
        $rowNum++;
    }

    // Summary Row
    $totalRow = $rowNum;
    $sheet->setCellValue('A' . $totalRow, 'TOTAL');
    $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
    $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true)->setName('Segoe UI')->setSize(10)->setColor(new Color('0F172A'));
    $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    
    // Sum formulas
    $sheet->setCellValue('E' . $totalRow, '=SUM(E5:E' . ($totalRow - 1) . ')');
    $sheet->setCellValue('F' . $totalRow, '=AVERAGE(F5:F' . ($totalRow - 1) . ')');
    $sheet->setCellValue('G' . $totalRow, '=SUM(G5:G' . ($totalRow - 1) . ')');
    $sheet->setCellValue('H' . $totalRow, '=SUM(H5:H' . ($totalRow - 1) . ')');
    
    $sheet->getRowDimension($totalRow)->setRowHeight(22);
    $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->getFont()->setBold(true)->setName('Segoe UI')->setColor(new Color('0F172A'));
    $sheet->getStyle('E' . $totalRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F' . $totalRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
    $sheet->getStyle('G' . $totalRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
    $sheet->getStyle('H' . $totalRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');

    // Summary styling with double-bottom border
    $summaryStyle = [
        'borders' => [
            'top' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF0D9488'],
            ],
            'bottom' => [
                'borderStyle' => Border::BORDER_DOUBLE,
                'color' => ['argb' => 'FF0D9488'],
            ],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFF1F5F9']
        ]
    ];
    $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->applyFromArray($summaryStyle);

    // Auto-fit column widths
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Gridlines explicitly enabled
    $sheet->setShowGridLines(true);

    // Apply Slate Borders
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FFCBD5E1'],
            ],
        ],
    ];
    if ($totalRow > 4) {
        $sheet->getStyle('A4:J' . $totalRow)->applyFromArray($styleArray);
    }

    $tempFile = tempnam(sys_get_temp_dir(), 'xls');
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempFile);

    while (ob_get_level()) {
        ob_end_clean();
    }
    error_reporting(0);
    ini_set('display_errors', 0);

    // Output
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Laporan_Pesanan_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    header('Content-Length: ' . filesize($tempFile));
    readfile($tempFile);
    unlink($tempFile);
    exit;

} elseif ($format === 'word') {
    $phpWord = new PhpWord();
    // Use Landscape orientation with custom margins for a cleaner horizontal look
    $section = $phpWord->addSection([
        'orientation' => 'landscape',
        'marginLeft' => 600,
        'marginRight' => 600,
        'marginTop' => 800,
        'marginBottom' => 800,
    ]);

    // Title
    $section->addText($titleReport, ['size' => 16, 'bold' => true, 'color' => '1E3A8A', 'name' => 'Segoe UI'], ['alignment' => 'center']);
    $section->addText("Tanggal Cetak: " . date('d F Y, H:i') . " WIB", ['size' => 10, 'italic' => true, 'color' => '64748B', 'name' => 'Segoe UI'], ['alignment' => 'center']);
    $section->addTextBreak(1);

    // Table Style (Navy headers, soft borders)
    $tableStyle = [
        'borderSize' => 6,
        'borderColor' => 'CBD5E1',
        'cellMargin' => 100,
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ];
    $firstRowStyle = [
        'bgColor' => '1E3A8A'
    ];
    $phpWord->addTableStyle('OrdersTable', $tableStyle, $firstRowStyle);
    $table = $section->addTable('OrdersTable');

    // Headers with explicit widths
    $table->addRow(400);
    $textStyleHeader = ['bold' => true, 'color' => 'FFFFFF', 'size' => 9, 'name' => 'Segoe UI'];
    $table->addCell(1400)->addText('No. Order', $textStyleHeader, ['alignment' => 'center']);
    $table->addCell(1100)->addText('Pembeli', $textStyleHeader);
    $table->addCell(1100)->addText('Seller', $textStyleHeader);
    $table->addCell(1800)->addText('Nama Produk', $textStyleHeader);
    $table->addCell(600)->addText('Qty', $textStyleHeader, ['alignment' => 'center']);
    $table->addCell(1200)->addText('Harga Unit', $textStyleHeader, ['alignment' => 'right']);
    $table->addCell(1000)->addText('Platform Fee', $textStyleHeader, ['alignment' => 'right']);
    $table->addCell(1200)->addText('Total Harga', $textStyleHeader, ['alignment' => 'right']);
    $table->addCell(900)->addText('Status', $textStyleHeader, ['alignment' => 'center']);
    $table->addCell(1100)->addText('Tanggal', $textStyleHeader, ['alignment' => 'center']);

    // Data rows
    $textStyleRow = ['size' => 9, 'name' => 'Segoe UI', 'color' => '334155'];
    $totalQty = 0;
    $totalFee = 0.0;
    $totalRevenue = 0.0;

    foreach ($orders as $order) {
        $totalQty += $order['quantity'];
        $totalFee += $order['platform_fee'];
        $totalRevenue += $order['total_price'];

        $table->addRow(300);
        $table->addCell(1400)->addText($order['order_number'] ?? '', $textStyleRow, ['alignment' => 'center']);
        $table->addCell(1100)->addText($order['buyer_name'] ?? '', $textStyleRow);
        $table->addCell(1100)->addText($order['seller_name'] ?? '', $textStyleRow);
        $table->addCell(1800)->addText($order['product_name'] ?? '', $textStyleRow);
        $table->addCell(600)->addText($order['quantity'] ?? 0, $textStyleRow, ['alignment' => 'center']);
        $table->addCell(1200)->addText(rupiah((float)($order['price'] ?? 0)), $textStyleRow, ['alignment' => 'right']);
        $table->addCell(1000)->addText(rupiah((float)($order['platform_fee'] ?? 0)), $textStyleRow, ['alignment' => 'right']);
        $table->addCell(1200)->addText(rupiah((float)($order['total_price'] ?? 0)), ['bold' => true, 'size' => 9, 'name' => 'Segoe UI', 'color' => '1E3A8A'], ['alignment' => 'right']);
        $table->addCell(900)->addText(ucfirst($order['status'] ?? ''), $textStyleRow, ['alignment' => 'center']);
        $table->addCell(1100)->addText(!empty($order['created_at']) ? date('d-m-Y', strtotime($order['created_at'])) : '', $textStyleRow, ['alignment' => 'center']);
    }

    // Summary Row
    $table->addRow(350);
    $textStyleSummary = ['bold' => true, 'size' => 9, 'name' => 'Segoe UI', 'color' => '0F172A'];
    $table->addCell(5400, ['gridSpan' => 4, 'bgColor' => 'F1F5F9'])->addText('TOTAL', $textStyleSummary, ['alignment' => 'right']);
    $table->addCell(600, ['bgColor' => 'F1F5F9'])->addText($totalQty, $textStyleSummary, ['alignment' => 'center']);
    $table->addCell(1200, ['bgColor' => 'F1F5F9'])->addText('', $textStyleRow);
    $table->addCell(1000, ['bgColor' => 'F1F5F9'])->addText(rupiah($totalFee), $textStyleSummary, ['alignment' => 'right']);
    $table->addCell(1200, ['bgColor' => 'F1F5F9'])->addText(rupiah($totalRevenue), ['bold' => true, 'size' => 9, 'name' => 'Segoe UI', 'color' => '0D9488'], ['alignment' => 'right']);
    $table->addCell(900, ['bgColor' => 'F1F5F9'])->addText('', $textStyleRow);
    $table->addCell(1100, ['bgColor' => 'F1F5F9'])->addText('', $textStyleRow);

    $tempFile = tempnam(sys_get_temp_dir(), 'doc');
    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($tempFile);

    while (ob_get_level()) {
        ob_end_clean();
    }
    error_reporting(0);
    ini_set('display_errors', 0);

    // Output
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment;filename="Laporan_Pesanan_' . date('Ymd_His') . '.docx"');
    header('Cache-Control: max-age=0');
    header('Content-Length: ' . filesize($tempFile));
    readfile($tempFile);
    unlink($tempFile);
    exit;

} elseif ($format === 'pdf') {
    // Math stats for summary cards
    $totalQty = 0;
    $totalFee = 0.0;
    $totalRevenue = 0.0;
    foreach ($orders as $order) {
        $totalQty += $order['quantity'];
        $totalFee += $order['platform_fee'];
        $totalRevenue += $order['total_price'];
    }

    $rowsHtml = '';
    foreach ($orders as $order) {
        $rowsHtml .= "
        <tr>
            <td style='text-align: center;'><code>" . htmlspecialchars($order['order_number'] ?? '') . "</code></td>
            <td>" . htmlspecialchars($order['buyer_name'] ?? '') . "</td>
            <td>" . htmlspecialchars($order['seller_name'] ?? '') . "</td>
            <td>" . htmlspecialchars($order['product_name'] ?? '') . "</td>
            <td style='text-align: center;'>" . ($order['quantity'] ?? 0) . "</td>
            <td style='text-align: right;'>" . rupiah((float)($order['price'] ?? 0)) . "</td>
            <td style='text-align: right;'>" . rupiah((float)($order['platform_fee'] ?? 0)) . "</td>
            <td style='text-align: right; color: #1e3a8a; font-weight: bold;'>" . rupiah((float)($order['total_price'] ?? 0)) . "</td>
            <td style='text-align: center;'><span class='badge status-" . htmlspecialchars($order['status'] ?? '') . "'>" . ucfirst(htmlspecialchars($order['status'] ?? '')) . "</span></td>
            <td style='text-align: center;'>" . (!empty($order['created_at']) ? date('d-m-Y H:i', strtotime($order['created_at'])) : '') . "</td>
        </tr>
        ";
    }

    $appLogo = '⚡ BoloTopup.ID';

    $html = "
    <!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <title>{$titleReport}</title>
        <style>
            body {
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                font-size: 10px;
                color: #334155;
                background-color: #ffffff;
                margin: 0;
                padding: 0;
            }
            .header-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                border-bottom: 3px solid #1e3a8a;
                padding-bottom: 12px;
            }
            .header-logo {
                font-size: 22px;
                font-weight: 800;
                color: #1e3a8a;
            }
            .header-title {
                text-align: right;
                font-size: 14px;
                font-weight: bold;
                color: #475569;
            }
            
            /* Stats Summary Cards */
            .stats-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
            }
            .stat-card {
                padding: 10px 14px;
                background-color: #f8fafc;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
            }
            .stat-card-title {
                font-size: 8px;
                color: #64748b;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .stat-card-value {
                font-size: 15px;
                font-weight: bold;
                color: #0f172a;
                margin-top: 4px;
            }

            table.data-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            table.data-table th {
                background-color: #1e3a8a;
                color: #ffffff;
                font-weight: bold;
                padding: 8px;
                border: 1px solid #0f172a;
                text-align: left;
                font-size: 9px;
            }
            table.data-table td {
                padding: 8px;
                border: 1px solid #cbd5e1;
                font-size: 9px;
                vertical-align: middle;
            }
            table.data-table tr:nth-child(even) {
                background-color: #f8fafc;
            }
            code {
                font-family: monospace;
                color: #0f172a;
                font-size: 9px;
                background-color: #e2e8f0;
                padding: 2px 4px;
                border-radius: 4px;
            }
            .badge {
                padding: 3px 6px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 8px;
                text-transform: uppercase;
                display: inline-block;
            }
            .status-pending { background-color: #fef3c7; color: #92400e; }
            .status-processing { background-color: #dbeafe; color: #1e40af; }
            .status-completed { background-color: #dcfce7; color: #166534; }
            .status-cancelled { background-color: #fee2e2; color: #991b1b; }
            .status-refunded { background-color: #f1f5f9; color: #475569; }
            .status-paid { background-color: #dcfce7; color: #166534; }
            
            .footer {
                position: fixed;
                bottom: -30px;
                left: 0px;
                right: 0px;
                height: 30px;
                text-align: center;
                font-size: 8px;
                color: #94a3b8;
                border-top: 1px solid #e2e8f0;
                padding-top: 6px;
            }
        </style>
    </head>
    <body>
        <table class='header-table'>
            <tr>
                <td class='header-logo'>{$appLogo}</td>
                <td class='header-title'>LAPORAN TRANSAKSI PESANAN</td>
            </tr>
        </table>
        
        <!-- Summary Dashboard Cards -->
        <table class='stats-table'>
            <tr>
                <td style='width: 31%;'>
                    <div class='stat-card' style='border-left: 4px solid #1e3a8a;'>
                        <div class='stat-card-title'>Total Transaksi</div>
                        <div class='stat-card-value'>" . count($orders) . " Pesanan</div>
                    </div>
                </td>
                <td style='width: 3%;'>&nbsp;</td>
                <td style='width: 31%;'>
                    <div class='stat-card' style='border-left: 4px solid #0d9488;'>
                        <div class='stat-card-title'>Total Nilai Penjualan</div>
                        <div class='stat-card-value'>" . rupiah($totalRevenue) . "</div>
                    </div>
                </td>
                <td style='width: 3%;'>&nbsp;</td>
                <td style='width: 31%;'>
                    <div class='stat-card' style='border-left: 4px solid #f59e0b;'>
                        <div class='stat-card-title'>Pendapatan Platform (Fee)</div>
                        <div class='stat-card-value'>" . rupiah($totalFee) . "</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class='info-table' style='width:100%; font-size:9px; margin-bottom:12px; color: #64748b;'>
            <tr>
                <td style='width: 50%;'>Status Filter: <strong>" . htmlspecialchars($statusLabel) . "</strong></td>
                <td style='width: 50%; text-align: right;'>Cetak: <strong>" . date('d F Y, H:i') . " WIB</strong></td>
            </tr>
        </table>
 
        <table class='data-table'>
            <thead>
                <tr>
                    <th style='width: 12%; text-align: center;'>No. Order</th>
                    <th style='width: 10%;'>Pembeli</th>
                    <th style='width: 10%;'>Seller</th>
                    <th>Nama Produk</th>
                    <th style='width: 4%; text-align: center;'>Qty</th>
                    <th style='width: 11%; text-align: right;'>Harga Unit</th>
                    <th style='width: 9%; text-align: right;'>Platform Fee</th>
                    <th style='width: 12%; text-align: right;'>Total</th>
                    <th style='width: 10%; text-align: center;'>Status</th>
                    <th style='width: 11%; text-align: center;'>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                {$rowsHtml}
                <!-- Bottom Grand Total Row -->
                <tr style='background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #1e3a8a;'>
                    <td colspan='4' style='text-align: right; font-size: 9px; padding: 8px;'>TOTAL</td>
                    <td style='text-align: center; padding: 8px; font-size: 9px;'>{$totalQty}</td>
                    <td style='text-align: right; padding: 8px;'>-</td>
                    <td style='text-align: right; padding: 8px; font-size: 9px; color: #475569;'>" . rupiah($totalFee) . "</td>
                    <td style='text-align: right; padding: 8px; font-size: 9px; color: #0d9488;'>" . rupiah($totalRevenue) . "</td>
                    <td colspan='2' style='padding: 8px;'></td>
                </tr>
            </tbody>
        </table>
 
        <div class='footer'>
            Dokumen Laporan Keuangan BoloTopup.ID - Dihasilkan secara otomatis pada " . date('d-m-Y H:i:s') . ".
        </div>
    </body>
    </html>
    ";

    while (ob_get_level()) {
        ob_end_clean();
    }
    error_reporting(0);
    ini_set('display_errors', 0);

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("Laporan_Pesanan_" . date('Ymd_His') . ".pdf", ["Attachment" => false]);
    exit;
} else {
    die("Format tidak didukung");
}
