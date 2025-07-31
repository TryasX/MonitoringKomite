<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Ambil filter tanggal dari GET (opsional)
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';

// Koneksi SQL Server
$serverName = "KP_DBSVR1";
$connectionOptions = [
    "Database" => "dba",
    "Uid" => "sa",
    "PWD" => "Fr1endlyshbk",
    "CharacterSet" => "UTF-8"
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Query data
if (!empty($from) && !empty($to)) {
    $sql = "SELECT * FROM K_Penilaian WHERE Tanggal BETWEEN ? AND ? ORDER BY Tanggal DESC";
    $params = [date_create($from), date_create($to)];
    $stmt = sqlsrv_query($conn, $sql, $params);
} else {
    $sql = "SELECT * FROM K_Penilaian ORDER BY Tanggal DESC";
    $stmt = sqlsrv_query($conn, $sql);
}

// Siapkan Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom
$headers = [
    "Tanggal", "Observer", "Unit", "Nama Petugas", "NIK", "Jabatan"
];
for ($i = 1; $i <= 18; $i++) {
    $headers[] = "P $i";
}
$headers[] = "TTD Observer";
$headers[] = "TTD Diobservasi";

// Tulis header
$col = 1;
foreach ($headers as $header) {
    $cell = Coordinate::stringFromColumnIndex($col) . '1';
    $sheet->setCellValue($cell, $header);
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
    $col++;
}

// Tulis data baris
$rowNum = 2;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $colNum = 1;

    $values = [
        $row['Tanggal']->format('Y-m-d'),
        $row['Observer'],
        $row['UnitKerja'],
        $row['NamaPetugas'],
        $row['NIK'],
        $row['Jabatan']
    ];

    for ($i = 1; $i <= 18; $i++) {
        $values[] = $row["Penilaian$i"];
    }

    // Isi data teks
    foreach ($values as $val) {
        $cell = Coordinate::stringFromColumnIndex($colNum) . $rowNum;
        $sheet->setCellValue($cell, $val);
        $colNum++;
    }

    // Gambar TTD Observer
    if (!empty($row['TTD_Observer']) && file_exists($row['TTD_Observer'])) {
        $drawing1 = new Drawing();
        $drawing1->setName('TTD Observer');
        $drawing1->setPath($row['TTD_Observer']);
        $drawing1->setHeight(30); // atur tinggi gambar
        $drawing1->setOffsetX(0); // geser sedikit ke kanan dalam sel
        $drawing1->setOffsetY(5);  // geser ke bawah agar terlihat di tengah
        $drawing1->setCoordinates(Coordinate::stringFromColumnIndex($colNum) . $rowNum);
        $drawing1->setWorksheet($sheet);
    }
    $colNum++;

    // Gambar TTD Diobservasi
    if (!empty($row['TTD_Diobservasi']) && file_exists($row['TTD_Diobservasi'])) {
        $drawing2 = new Drawing();
        $drawing2->setName('TTD Diobservasi');
        $drawing2->setPath($row['TTD_Diobservasi']);
        $drawing2->setHeight(30);
        $drawing2->setOffsetX(0);
        $drawing2->setOffsetY(5);
        $drawing2->setCoordinates(Coordinate::stringFromColumnIndex($colNum) . $rowNum);
        $drawing2->setWorksheet($sheet);
    }

    // Set tinggi baris agar gambar tidak terpotong
    $sheet->getRowDimension($rowNum)->setRowHeight(65);

    $rowNum++;
}

// Output Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="data_penilaian.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

sqlsrv_close($conn);
exit;
