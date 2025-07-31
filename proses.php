<?php
$nama = $_POST['nama'];
$ttd = $_POST['ttd'];

// Hapus bagian "data:image/png;base64,"
$ttd = str_replace('data:image/png;base64,', '', $ttd);
$ttd = str_replace(' ', '+', $ttd);
$data = base64_decode($ttd);

// Simpan file
$filename = 'ttd_' . time() . '.png';
file_put_contents('uploads/' . $filename, $data);

// Simpan ke database (nama + path tanda tangan)
$conn = new mysqli("localhost", "root", "", "dbmu");
$sql = "INSERT INTO users (nama, ttd_path) VALUES ('$nama', '$filename')";
$conn->query($sql);

echo "Data berhasil disimpan!";
?>
