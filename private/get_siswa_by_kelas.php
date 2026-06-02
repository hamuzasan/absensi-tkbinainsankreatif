<?php
include '../config/database.php';

$id_kelas = $_GET['id_kelas'] ?? '';
$data = [];

if ($id_kelas) {
    $q = mysqli_query($conn, "SELECT id_siswa, nama_siswa FROM siswa WHERE id_kelas = '$id_kelas' ORDER BY nama_siswa");
    while ($row = mysqli_fetch_assoc($q)) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
