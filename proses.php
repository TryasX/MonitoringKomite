<?php
// Konfigurasi koneksi ke SQL Server
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

// Fungsi untuk menyimpan TTD sebagai PNG
function saveSignatureImage($base64Data, $prefix) {
    if (!$base64Data) return null;
    
    $base64Image = explode(',', $base64Data)[1];
    $image = base64_decode($base64Image);

    $filename = $prefix . '_' . uniqid() . '.png';
    $folderPath = __DIR__ . '/uploads/ttd/';
    
    // Pastikan folder ada
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    $filePath = $folderPath . $filename;

    // Simpan file PNG
    file_put_contents($filePath, $image);

    // Path relatif untuk database
    return 'uploads/ttd/' . $filename;
}

// Ambil data dari form
$tanggal      = $_POST['tanggal'];
$observer   = $_POST['observer'];
$unit_kerja   = $_POST['unit_kerja'];
$nama_petugas = $_POST['nama_petugas'];
$nik          = $_POST['nik'];
$jabatan      = $_POST['jabatan'];
$keterangan      = $_POST['keterangan'];

// Ambil semua nilai penilaian 1-18
$penilaian = [];
for ($i = 1; $i <= 18; $i++) {
    $penilaian[$i] = $_POST['penilaian' . $i] ?? null;
}

// Tanda tangan
$ttd_observer_data     = $_POST['ttd_observer'];
$ttd_diobservasi_data  = $_POST['ttd_diobservasi'];

$path_ttd_observer     = saveSignatureImage($ttd_observer_data, 'observer');
$path_ttd_diobservasi  = saveSignatureImage($ttd_diobservasi_data, 'diobservasi');

// Query insert lengkap
$sql = "INSERT INTO K_Penilaian (
    Tanggal, Observer, UnitKerja, NamaPetugas, NIK, Jabatan,
    Penilaian1, Penilaian2, Penilaian3, Penilaian4, Penilaian5, Penilaian6,
    Penilaian7, Penilaian8, Penilaian9, Penilaian10, Penilaian11, Penilaian12,
    Penilaian13, Penilaian14, Penilaian15, Penilaian16, Penilaian17, Penilaian18,
    TTD_Observer, TTD_Diobservasi, Keterangan
) VALUES (
    ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?,
    ?, ?, ?
)";

// Gabungkan semua parameter untuk query
$params = [
    $tanggal, $observer, $unit_kerja, $nama_petugas, $nik, $jabatan,
    $penilaian[1], $penilaian[2], $penilaian[3], $penilaian[4], $penilaian[5], $penilaian[6],
    $penilaian[7], $penilaian[8], $penilaian[9], $penilaian[10], $penilaian[11], $penilaian[12],
    $penilaian[13], $penilaian[14], $penilaian[15], $penilaian[16], $penilaian[17], $penilaian[18],
    $path_ttd_observer, $path_ttd_diobservasi, $keterangan
];

// Eksekusi query
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
} else {
    echo "<script>alert('Data berhasil disimpan!'); window.location.href='index.html';</script>";
}

sqlsrv_close($conn);
?>
