<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include '../config/database.php';
mysqli_query($conn, "SET time_zone = '+07:00'");
include '../function/function.php';
if (!isset($_SESSION['id_guru']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil data statistik
$jumlah_guru = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM guru WHERE role = 'guru'"))[0];
$jumlah_siswa = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM siswa"))[0];
$dt = new DateTime($row['tanggal_aktivitas'], new DateTimeZone('UTC'));
$dt->setTimezone(new DateTimeZone('Asia/Jakarta'));
echo $dt->format('d-m-Y H:i');

// Riwayat login guru (aktivitas terbaru)
$riwayat = mysqli_query($conn, "
    SELECT aksi.*, guru.nama_guru 
    FROM aksi 
    JOIN guru ON aksi.id_guru = guru.id_guru 
    ORDER BY aksi.tanggal_aktivitas DESC 
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-green-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-green-600 p-4 text-white flex justify-between items-center">
        <h1 class="text-xl font-semibold">Dashboard Admin</h1>
        <div class="flex items-center gap-4">
            <span><?= $_SESSION['nama_guru'] ?> (Admin)</span>
            <a href="../logout.php" class="bg-white text-green-600 px-3 py-1 rounded hover:bg-green-100">Logout</a>
        </div>
    </nav>

    <!-- Konten -->
    <main class="p-6">
        <h2 class="text-2xl font-bold text-green-700 mb-6">📊 Ringkasan Data</h2>

        <div class="grid md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow-md text-center">
                <p class="text-4xl font-bold text-green-700"><?= $jumlah_guru ?></p>
                <p class="text-gray-600 mt-2">Guru Aktif</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md text-center">
                <p class="text-4xl font-bold text-green-700"><?= $jumlah_siswa ?></p>
                <p class="text-gray-600 mt-2">Jumlah Siswa</p>
            </div>
        </div>
<div class="grid md:grid-cols-2 gap-6 mb-10">
    <a href="riwayat_absensi_a.php" class="bg-white p-6 rounded-xl shadow-md flex items-center justify-between hover:bg-green-50 transition">
        <div>
            <p class="text-lg font-semibold text-green-700">📋 Rekap Absensi Siswa</p>
            <p class="text-sm text-gray-600 mt-1">Lihat data kehadiran siswa berdasarkan tanggal dan kelas</p>
        </div>
        <span class="text-green-600 text-xl">→</span>
    </a>
</div>
        <div class="grid md:grid-cols-2 gap-6 mb-10">
    <a href="laporan_absensi_guru_admin.php" class="bg-white p-6 rounded-xl shadow-md flex items-center justify-between hover:bg-green-50 transition">
        <div>
            <p class="text-lg font-semibold text-green-700">📋Laporan Absen Guru</p>
            <p class="text-sm text-gray-600 mt-1">Lihat data Absensi Guru</p>
        </div>
        <span class="text-green-600 text-xl">→</span>
    </a>
</div>
<div class="grid md:grid-cols-2 gap-6 mb-10">
    <a href="kelola_guru.php" class="bg-white p-6 rounded-xl shadow-md flex items-center justify-between hover:bg-green-50 transition">
        <div>
            <p class="text-lg font-semibold text-green-700">👩‍🎓Kelola Data Guru</p>
            <p class="text-sm text-gray-600 mt-1">Lihat data guru,menambahkan guru,hapus guru</p>
        </div>
        <span class="text-green-600 text-xl">→</span>
    </a>
</div>
<!-- Navigasi Tambahan -->
<div class="grid md:grid-cols-2 gap-6 mb-10">
    <a href="riwayat_absensi_guru.php" class="bg-white p-6 rounded-xl shadow-md flex items-center justify-between hover:bg-green-50 transition">
        <div>
            <p class="text-lg font-semibold text-green-700">📋 Rekap Absensi Guru</p>
            <p class="text-sm text-gray-600 mt-1">Lihat data kehadiran guru</p>
        </div>
        <span class="text-green-600 text-xl">→</span>
    </a>
</div>
<div class="grid md:grid-cols-2 gap-6 mb-10">
    <a href="pelulusanmurid.php" class="bg-white p-6 rounded-xl shadow-md flex items-center justify-between hover:bg-green-50 transition">
        <div>
            <p class="text-lg font-semibold text-green-700">📋 Luluskan Murid Kelas</p>
            <p class="text-sm text-gray-600 mt-1">meluluskan murid kelas yang telah lulus</p>
        </div>
        <span class="text-green-600 text-xl">→</span>
    </a>
</div>
<div class="grid md:grid-cols-2 gap-6 mb-10">
    <a href="kelola_murid_a.php" class="bg-white p-6 rounded-xl shadow-md flex items-center justify-between hover:bg-green-50 transition">
        <div>
            <p class="text-lg font-semibold text-green-700">📋 Kelola Murid dan Kelas</p>
            <p class="text-sm text-gray-600 mt-1">mengelola murid dan kelas</p>
        </div>
        <span class="text-green-600 text-xl">→</span>
    </a>
</div>
<?php
$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$query_filter = "
    SELECT aksi.*, guru.nama_guru 
    FROM aksi 
    JOIN guru ON aksi.id_guru = guru.id_guru 
";

if ($tanggal_filter) {
    $query_filter .= "WHERE DATE(aksi.tanggal_aktivitas) = '$tanggal_filter' ";
}

$query_filter .= "ORDER BY aksi.tanggal_aktivitas DESC LIMIT 10";
$riwayat = mysqli_query($conn, $query_filter);
?>
<!-- Form Filter -->
<form method="get" class="mb-4">
    <label class="text-sm text-gray-700 mr-2">Filter Tanggal Aktivitas:</label>
    <input type="date" name="tanggal" value="<?= $tanggal_filter ?>" class="px-3 py-1 border rounded">
    <button type="submit" class="ml-2 px-4 py-1 bg-green-500 text-white rounded hover:bg-green-600">Filter</button>
    <?php if ($tanggal_filter): ?>
        <a href="dashboardadmin.php" class="ml-2 text-sm text-red-600 hover:underline">Reset</a>
    <?php endif; ?>
</form>


        <h2 class="text-xl font-bold text-green-700 mb-4">🕓 Riwayat Login Guru Terbaru</h2>
        <div class="bg-white p-4 rounded-xl shadow-md overflow-x-auto">
            <table class="w-full text-sm text-left border border-gray-200">
                <thead class="bg-green-100 text-green-900">
                    <tr>
                        <th class="p-3 border">#</th>
                        <th class="p-3 border">Nama Guru</th>
                        <th class="p-3 border">Aktivitas</th>
                        <th class="p-3 border">Tanggal & Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($riwayat)) : ?>
                        <tr class="hover:bg-green-50">
                            <td class="p-3 border"><?= $no++ ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($row['nama_guru']) ?></td>
                            <td class="p-3 border"><?= $row['aktivitas'] ?></td>
                            <?php
$dt = new DateTime($row['tanggal_aktivitas'], new DateTimeZone('Asia/Jakarta'));
?>
<td class="p-3 border"><?= $dt->format('d-m-Y H:i') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
