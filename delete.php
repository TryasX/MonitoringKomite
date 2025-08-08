<?php
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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = $_GET['id'];

$sql = "DELETE FROM K_Penilaian WHERE ID = ?";
$params = [$id];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    header("Location: view_data.php?msg=deleted");
    exit;
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>
