<?php
session_start();
include '../config/database.php';
include '../function/function.php';
// Cek login dan role guru
if (!isset($_SESSION['id_guru']) || !in_array($_SESSION['role'], ['guru', 'guruPaud'])) {
    // Catatan: Walaupun guru dan guruPaud sekarang bisa mengakses, 
    // disarankan untuk menambahkan pemeriksaan di sekitar blok Tambah/Hapus/Update 
    // agar hanya Admin yang bisa melakukan perubahan.
    header("Location: ../index.php");
    exit;
}

// Ambil data kelas dari database
$query = $conn->query("SELECT nama_kelas FROM kelas ORDER BY nama_kelas ASC");
$kelas_list = $query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Riwayat Absensi Bulanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="bg-green-500 p-4 text-white flex justify-between items-center">
        <h1 class="text-xl font-semibold">Riwayat Absensi Bulanan</h1>
        <a href="dashboardguru.php" class="bg-white text-green-600 px-4 py-1 rounded hover:bg-green-100">Kembali</a>
    </nav>

    <main class="p-6 px-4">
        <div class="bg-white p-6 rounded-xl shadow-md max-w-md mx-auto">
            <form action="riwayat_absensi_bulanan_view.php" method="GET" class="space-y-4">
                <label class="block font-semibold text-green-700">Pilih Kelas</label>
                <select name="kelas" required class="w-full p-2 border rounded">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas_list as $kelas): ?>
                        <option value="<?= htmlspecialchars($kelas['nama_kelas']) ?>">
                            <?= htmlspecialchars($kelas['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label class="block font-semibold text-green-700">Pilih Bulan & Tahun</label>
                <input type="month" name="bulan_tahun" required class="w-full p-2 border rounded" />

                <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600 transition">
                    Tampilkan & Export
                </button>
            </form>
        </div>
    </main>
</body>
</html>
