<?php
session_start();
include '../config/database.php';

// Cek apakah user sudah login dan rolenya guru
if (!isset($_SESSION['id_guru']) || !in_array($_SESSION['role'], ['guru', 'guruPaud'])) {
    // Catatan: Walaupun guru dan guruPaud sekarang bisa mengakses, 
    // disarankan untuk menambahkan pemeriksaan di sekitar blok Tambah/Hapus/Update 
    // agar hanya Admin yang bisa melakukan perubahan.
    header("Location: ../index.php");
    exit;
}

$nama_guru = $_SESSION['nama_guru'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>

<body class="bg-green-50 min-h-screen">
    <nav class="bg-green-500 p-4 text-white flex justify-between items-center">
        <h1 class="text-xl font-semibold">Dashboard Guru</h1>
        <a href="../logout.php" class="bg-white text-green-600 px-4 py-1 rounded hover:bg-green-100">Logout</a>
    </nav>

    <main class="p-6 px-4">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-2xl font-bold text-green-700 mb-4">Selamat datang, <?= htmlspecialchars($nama_guru) ?> 👋</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <!-- Tombol navigasi fitur -->
                <a href="absensi_siswa.php" class="block p-4 bg-green-100 rounded-xl shadow hover:bg-green-200 transition">
                    <h3 class="text-lg font-semibold">📋 Absensi Siswa</h3>
                    <p class="text-sm text-gray-600">Input kehadiran siswa hari ini</p>
                </a>

                <a href="riwayat_absensi.php" class="block p-4 bg-green-100 rounded-xl shadow hover:bg-green-200 transition">
                    <h3 class="text-lg font-semibold">📖 Riwayat Absensi</h3>
                    <p class="text-sm text-gray-600">Lihat absensi sebelumnya</p>
                </a>
                <a href="siswa_sakit.php" class="block p-4 bg-green-100 rounded-xl shadow hover:bg-green-200 transition">
                    <h3 class="text-lg font-semibold">✙ Siswa Sakit</h3>
                    <p class="text-sm text-gray-600">Catat kehadiran pribadi</p>
                </a>

                <a href="absensi_guru.php" class="block p-4 bg-green-100 rounded-xl shadow hover:bg-green-200 transition">
                    <h3 class="text-lg font-semibold">🙋‍♂️ Absensi Saya</h3>
                    <p class="text-sm text-gray-600">Catat kehadiran pribadi</p>
                </a>
                <a href="riwayat_absensi_bulanan.php" class="block p-4 bg-green-100 rounded-xl shadow hover:bg-green-200 transition">
    <h3 class="text-lg font-semibold">📅 Riwayat Absensi Bulanan</h3>
    <p class="text-sm text-gray-600">Pilih kelas & ekspor data absensi</p>
</a>

                <a href="riwayat_absensi_siswa_khusus.php" class="block p-4 bg-green-100 rounded-xl shadow hover:bg-green-200 transition">
    <h3 class="text-lg font-semibold">👦 Riwayat Absensi Siswa Tertentu</h3>
    <p class="text-sm text-gray-600">Lihat riwayat absensi siswa tertentu</p>
</a> <!-- Tambahkan penutup ini -->

<a href="kelola_murid.php" class="block p-4 bg-green-100 rounded-xl shadow hover:bg-green-200 transition">
    <h3 class="text-lg font-semibold">👶 Kelola Murid</h3>
    <p class="text-sm text-gray-600">Tambah & lihat data murid</p>
</a>



            </div>
        </div>
    </main>
</body>
</html>
