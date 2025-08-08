

<?php
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

// Ambil filter tanggal jika ada
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to   = isset($_GET['to']) ? $_GET['to'] : '';

if (!empty($from) && !empty($to)) {
    $sql = "SELECT * FROM K_Penilaian WHERE Tanggal BETWEEN ? AND ? ORDER BY Tanggal DESC";
    $params = [date_create($from), date_create($to)];
    $stmt = sqlsrv_query($conn, $sql, $params);
} else {
    $sql = "SELECT * FROM K_Penilaian ORDER BY Tanggal DESC";
    $stmt = sqlsrv_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Monitoring Penilaian</title>
  <link rel="icon" type="image/png" href="logo.png" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: 'Data berhasil dihapus!',
    showConfirmButton: false,
    timer: 2000
});
</script>
<?php endif; ?>
  <div class="max-w-7xl mx-auto py-10 px-4">
  <!-- Header -->
  <div class="flex flex-wrap md:flex-nowrap items-center justify-between mb-6 space-y-2 md:space-y-0">
    <!-- Logo -->
    <div class="flex items-center space-x-2">
      <img src="logo shbk.png" alt="Logo" class="h-10 w-auto">
      
    </div>

    <!-- Judul Tengah -->
    <h1 class="text-xl md:text-2xl font-bold text-center md:text-left flex-1 md:ml-4 text-gray-800">
      Data Monitoring Penilaian Petugas
    </h1>

    <!-- Tombol Kembali -->
    <div class="w-full md:w-auto text-right">
      <a href="index.html" class="text-blue-600 hover:underline whitespace-nowrap">
        &lt; Kembali ke Form
      </a>
    </div>
  </div>


    <!-- Filter Tanggal -->
    <form method="GET" class="mb-4 flex flex-wrap gap-4 items-end">
      <div>
        <label class="text-sm font-medium text-gray-700">Dari:</label>
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="border border-gray-300 rounded px-3 py-1 w-44" required>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Sampai:</label>
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="border border-gray-300 rounded px-3 py-1 w-44" required>
      </div>
      <div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Tampilkan</button>
        <a href="view_data.php" class="ml-2 text-sm text-gray-600 hover:underline">Reset</a>
      </div>
    </form>

    <!-- Tombol Export -->
    <div class="text-right mb-4">
      <a href="export_excel.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Export ke Excel</a>
      <!-- <a href="export_pdf.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="ml-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Export ke PDF</a> -->
    </div>

    <!-- Tombol Export PDF -->


    <!-- Tabel Data -->
    <div class="overflow-auto rounded border border-gray-300">
      <table class="min-w-full text-sm table-auto">
      <thead class="bg-gray-200">
        <tr class="text-left">
          <th class="px-3 py-2">Tanggal</th>
          <th class="px-3 py-2">Observer</th>
          <th class="px-3 py-2">Unit</th>
          <th class="px-3 py-2">Nama</th>
          <th class="px-3 py-2">NIK</th>
          <th class="px-3 py-2">Jabatan</th>
          <?php for ($i = 1; $i <= 18; $i++): ?>
            <th class="px-2 py-2 text-center">P<?= $i ?></th>
          <?php endfor; ?>
          <th class="px-3 py-2">TTD Observer</th>
          <th class="px-3 py-2">TTD Diobservasi</th>
          <th class="px-3 py-2 text-center">Aksi</th> <!-- Tambah kolom -->
        </tr>
      </thead>
      <tbody>
        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
          <tr class="border-t">
            <td class="px-3 py-2"><?= $row['Tanggal']->format('Y-m-d') ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($row['Observer']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($row['UnitKerja']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($row['NamaPetugas']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($row['NIK']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($row['Jabatan']) ?></td>
            <?php for ($i = 1; $i <= 18; $i++): ?>
              <td class="px-2 py-2 text-center"><?= htmlspecialchars($row["Penilaian$i"]) ?></td>
            <?php endfor; ?>
            <td class="px-3 py-2">
              <?php if (!empty($row['TTD_Observer'])): ?>
                <a href="<?= htmlspecialchars($row['TTD_Observer']) ?>" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
              <?php endif; ?>
            </td>
            <td class="px-3 py-2">
              <?php if (!empty($row['TTD_Diobservasi'])): ?>
                <a href="<?= htmlspecialchars($row['TTD_Diobservasi']) ?>" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
              <?php endif; ?>
            </td>
            <td class="px-3 py-2 text-center">
                <button 
                    onclick="confirmDelete('<?= $row['ID'] ?>')" 
                    class="text-red-600 hover:underline">
                    Hapus
                </button>
            </td>

          </tr>
        <?php endwhile; ?>
      </tbody>

      </table>
    </div>

  </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Yakin hapus data ini?',
        text: "Data yang sudah dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'delete.php?id=' + id;
        }
    });
}
</script>

</body>
</html>
