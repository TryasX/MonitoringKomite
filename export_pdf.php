<?php
require_once 'vendor/autoload.php';
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

// Ambil filter tanggal dari GET
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';

// Koneksi ke SQL Server
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

// Ambil data dari database
if (!empty($from) && !empty($to)) {
    $sql = "SELECT * FROM K_Penilaian WHERE Tanggal BETWEEN ? AND ? ORDER BY Tanggal DESC";
    $params = [date_create($from), date_create($to)];
    $stmt = sqlsrv_query($conn, $sql, $params);
} else {
    $sql = "SELECT * FROM K_Penilaian ORDER BY Tanggal DESC";
    $stmt = sqlsrv_query($conn, $sql);
}

// Siapkan PDF
$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Monitoring');
$pdf->SetAuthor('Monitoring Komite');
$pdf->SetTitle('Export Penilaian');
$pdf->SetHeaderData('', 0, 'Data Monitoring Penilaian Petugas', '');
$pdf->setPrintFooter(false);
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(10, 20, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 8);
$pdf->AddPage();

// Mulai tabel
$html = '<style>
            table, th, td { border: 1px solid #000; border-collapse: collapse; padding: 4px; }
            th { background-color: #eee; }
         </style>';

$html .= '<table><thead><tr>
            <th>Tanggal</th>
            <th>Observer</th>
            <th>Unit</th>
            <th>Nama</th>
            <th>NIK</th>
            <th>Jabatan</th>';

for ($i = 1; $i <= 18; $i++) {
    $html .= "<th>P$i</th>";
}

$html .= '<th>TTD Observer</th><th>TTD Diobservasi</th>';
$html .= '</tr></thead><tbody>';

// Isi data
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $html .= '<tr>';
    $html .= '<td>' . $row['Tanggal']->format('Y-m-d') . '</td>';
    $html .= '<td>' . htmlspecialchars($row['Observer']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['UnitKerja']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['NamaPetugas']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['NIK']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['Jabatan']) . '</td>';

    for ($i = 1; $i <= 18; $i++) {
        $html .= '<td>' . htmlspecialchars($row["Penilaian$i"]) . '</td>';
    }

    $html .= '<td>' . (!empty($row['TTD_Observer']) ? '✓' : '') . '</td>';
    $html .= '<td>' . (!empty($row['TTD_Diobservasi']) ? '✓' : '') . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';

// Tulis ke PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output ke browser
$pdf->Output('penilaian_petugas.pdf', 'I');

// Tutup koneksi
sqlsrv_close($conn);
exit;
?>
