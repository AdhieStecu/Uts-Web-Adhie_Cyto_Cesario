<?php
// File: admin/export_orders.php
require_once __DIR__ . '/../includes/functions.php';
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

    // Title
    $sheet->setCellValue('A1', $titleReport);
    $sheet->mergeCells('A1:J1');
    $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Headers
    $headers = [
        'No. Order', 'Pembeli', 'Seller', 'Produk', 'Jumlah', 
        'Harga Unit', 'Platform Fee', 'Total Harga', 'Status', 'Tanggal'
    ];
    $colLetter = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colLetter . '3', $header);
        $colLetter++;
    }

    // Header styling
    $headerRange = 'A3:J3';
    $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new Color(Color::COLOR_WHITE));
    $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0066FF');
    $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Data rows
    $rowNum = 4;
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
        
        // Formats
        $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('H' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        
        $rowNum++;
    }

    // Auto-fit columns
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Borders
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FFCCCCCC'],
            ],
        ],
    ];
    if ($rowNum > 4) {
        $sheet->getStyle('A3:J' . ($rowNum - 1))->applyFromArray($styleArray);
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
    $section = $phpWord->addSection();

    // Title styling
    $section->addText($titleReport, ['size' => 16, 'bold' => true, 'color' => '0066FF'], ['alignment' => 'center']);
    $section->addTextBreak(1);

    // Table
    $tableStyle = [
        'borderSize' => 6,
        'borderColor' => 'CCCCCC',
        'cellMargin' => 80
    ];
    $firstRowStyle = [
        'bgColor' => '0066FF'
    ];
    $phpWord->addTableStyle('OrdersTable', $tableStyle, $firstRowStyle);
    $table = $section->addTable('OrdersTable');

    // Header cells
    $table->addRow();
    $textStyleHeader = ['bold' => true, 'color' => 'FFFFFF', 'size' => 9];
    $table->addCell(1500)->addText('No. Order', $textStyleHeader);
    $table->addCell(1200)->addText('Pembeli', $textStyleHeader);
    $table->addCell(1200)->addText('Seller', $textStyleHeader);
    $table->addCell(2000)->addText('Produk', $textStyleHeader);
    $table->addCell(600)->addText('Qty', $textStyleHeader);
    $table->addCell(1200)->addText('Harga', $textStyleHeader);
    $table->addCell(1000)->addText('Fee', $textStyleHeader);
    $table->addCell(1200)->addText('Total', $textStyleHeader);
    $table->addCell(1000)->addText('Status', $textStyleHeader);
    $table->addCell(1200)->addText('Tanggal', $textStyleHeader);

    // Data rows
    $textStyleRow = ['size' => 9];
    foreach ($orders as $order) {
        $table->addRow();
        $table->addCell(1500)->addText($order['order_number'] ?? '', $textStyleRow);
        $table->addCell(1200)->addText($order['buyer_name'] ?? '', $textStyleRow);
        $table->addCell(1200)->addText($order['seller_name'] ?? '', $textStyleRow);
        $table->addCell(2000)->addText($order['product_name'] ?? '', $textStyleRow);
        $table->addCell(600)->addText($order['quantity'] ?? 0, $textStyleRow);
        $table->addCell(1200)->addText(rupiah((float)($order['price'] ?? 0)), $textStyleRow);
        $table->addCell(1000)->addText(rupiah((float)($order['platform_fee'] ?? 0)), $textStyleRow);
        $table->addCell(1200)->addText(rupiah((float)($order['total_price'] ?? 0)), $textStyleRow);
        $table->addCell(1000)->addText(ucfirst($order['status'] ?? ''), $textStyleRow);
        $table->addCell(1200)->addText(!empty($order['created_at']) ? date('d-m-Y', strtotime($order['created_at'])) : '', $textStyleRow);
    }

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
    // Generate beautiful PDF HTML template with dark theme/corporate touch
    $rowsHtml = '';
    foreach ($orders as $order) {
        $rowsHtml .= "
        <tr>
            <td><code>" . htmlspecialchars($order['order_number'] ?? '') . "</code></td>
            <td>" . htmlspecialchars($order['buyer_name'] ?? '') . "</td>
            <td>" . htmlspecialchars($order['seller_name'] ?? '') . "</td>
            <td>" . htmlspecialchars($order['product_name'] ?? '') . "</td>
            <td style='text-align: center;'>" . ($order['quantity'] ?? 0) . "</td>
            <td>" . rupiah((float)($order['price'] ?? 0)) . "</td>
            <td>" . rupiah((float)($order['platform_fee'] ?? 0)) . "</td>
            <td><strong>" . rupiah((float)($order['total_price'] ?? 0)) . "</strong></td>
            <td><span class='badge status-" . htmlspecialchars($order['status'] ?? '') . "'>" . ucfirst(htmlspecialchars($order['status'] ?? '')) . "</span></td>
            <td>" . (!empty($order['created_at']) ? date('d M Y H:i', strtotime($order['created_at'])) : '') . "</td>
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
                font-size: 11px;
                color: #333333;
                background-color: #ffffff;
                margin: 0;
                padding: 0;
            }
            .header-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
                border-bottom: 2px solid #0066ff;
                padding-bottom: 10px;
            }
            .header-logo {
                font-size: 20px;
                font-weight: bold;
                color: #0066ff;
            }
            .header-title {
                text-align: right;
                font-size: 16px;
                font-weight: bold;
                color: #555555;
            }
            .info-table {
                width: 100%;
                margin-bottom: 20px;
                font-size: 11px;
            }
            .info-table td {
                padding: 4px 0;
            }
            table.data-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            table.data-table th {
                background-color: #0066ff;
                color: #ffffff;
                font-weight: bold;
                padding: 8px;
                border: 1px solid #0055dd;
                text-align: left;
                font-size: 10px;
            }
            table.data-table td {
                padding: 8px;
                border: 1px solid #e0e0e0;
                font-size: 10px;
                vertical-align: middle;
            }
            table.data-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            code {
                font-family: monospace;
                color: #e91e63;
                font-size: 11px;
                background-color: #f8f9fa;
                padding: 2px 4px;
                border-radius: 4px;
            }
            .badge {
                padding: 3px 8px;
                border-radius: 12px;
                font-weight: bold;
                font-size: 9px;
                text-transform: uppercase;
                display: inline-block;
            }
            .status-pending { background-color: #fff3cd; color: #856404; }
            .status-processing { background-color: #cce5ff; color: #004085; }
            .status-completed { background-color: #d4edda; color: #155724; }
            .status-cancelled { background-color: #f8d7da; color: #721c24; }
            .status-refunded { background-color: #e2e3e5; color: #383d41; }
            .status-paid { background-color: #d4edda; color: #155724; }
            .footer {
                position: fixed;
                bottom: -30px;
                left: 0px;
                right: 0px;
                height: 30px;
                text-align: center;
                font-size: 9px;
                color: #aaaaaa;
                border-top: 1px solid #eeeeee;
                padding-top: 5px;
            }
        </style>
    </head>
    <body>
        <table class='header-table'>
            <tr>
                <td class='header-logo'>{$appLogo}</td>
                <td class='header-title'>LAPORAN PESANAN</td>
            </tr>
        </table>
        
        <table class='info-table'>
            <tr>
                <td style='width: 15%; font-weight: bold;'>Kriteria Filter:</td>
                <td>Status: " . htmlspecialchars($statusLabel) . "</td>
                <td style='text-align: right; font-weight: bold; width: 25%;'>Tanggal Cetak:</td>
                <td style='text-align: right; width: 20%;'>" . date('d F Y, H:i') . " WIB</td>
            </tr>
            <tr>
                <td style='font-weight: bold;'>Total Transaksi:</td>
                <td>" . count($orders) . " Pesanan</td>
                <td></td>
                <td></td>
            </tr>
        </table>
 
        <table class='data-table'>
            <thead>
                <tr>
                    <th style='width: 14%;'>No. Order</th>
                    <th style='width: 10%;'>Pembeli</th>
                    <th style='width: 10%;'>Seller</th>
                    <th>Nama Produk</th>
                    <th style='width: 5%; text-align: center;'>Qty</th>
                    <th style='width: 11%;'>Harga Unit</th>
                    <th style='width: 8%;'>Platform Fee</th>
                    <th style='width: 12%;'>Total</th>
                    <th style='width: 8%;'>Status</th>
                    <th style='width: 12%;'>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                {$rowsHtml}
            </tbody>
        </table>
 
        <div class='footer'>
            Laporan Pesanan BoloTopup.ID - Dokumen ini dihasilkan secara otomatis.
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
