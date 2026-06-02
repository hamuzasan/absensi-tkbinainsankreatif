<?php
session_start();
include '../config/database.php';
include '../function/function.php';

if (!isset($_SESSION['id_guru']) || !in_array($_SESSION['role'], ['guru', 'guruPaud'])) {
    // Catatan: Walaupun guru dan guruPaud sekarang bisa mengakses, 
    // disarankan untuk menambahkan pemeriksaan di sekitar blok Tambah/Hapus/Update 
    // agar hanya Admin yang bisa melakukan perubahan.
    header("Location: ../index.php");
    exit;
}

// Ambil semua siswa
$siswa_query = mysqli_query($conn, "SELECT * FROM siswa ORDER BY nama_siswa");
$siswa_sakit = [];

while ($s = mysqli_fetch_assoc($siswa_query)) {
    $id_siswa = $s['id_siswa'];
    $absensi = mysqli_query($conn, "SELECT status FROM absensi WHERE id_siswa = '$id_siswa' ORDER BY tanggal DESC LIMIT 10");

    $count = 0;
    $is_beruntun = true;
    while ($row = mysqli_fetch_assoc($absensi)) {
        if ($row['status'] === 'sakit') {
            $count++;
            if ($count >= 3) break;
        } else {
            $is_beruntun = false;
            break;
        }
    }

    if ($count >= 3 && $is_beruntun) {
        $siswa_sakit[] = $s;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Siswa Sakit 3 Hari Berturut-turut</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-yellow-50 min-h-screen">
<nav class="bg-yellow-500 text-white p-4 flex justify-between">
    <h1 class="text-xl font-semibold">Siswa Sakit 3 Hari Berturut-turut</h1>
    <a href="dashboardguru.php" class="bg-white text-yellow-600 px-4 py-1 rounded hover:bg-yellow-100">Kembali</a>
</nav>

<main class="p-6 max-w-5xl mx-auto">
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-4">Daftar Siswa</h2>

        <table class="min-w-full text-sm border">
            <thead class="bg-yellow-100 text-yellow-800">
                <tr>
                    <th class="p-3 border">#</th>
                    <th class="p-3 border">Nama</th>
                    <th class="p-3 border">No. HP Ortu</th>
                    <th class="p-3 border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($siswa_sakit) > 0): ?>
                    <?php $no = 1; foreach ($siswa_sakit as $s): ?>
                        <tr class="hover:bg-yellow-50">
                            <td class="p-3 border"><?= $no++ ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($s['nama_siswa']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($s['no_hp_ortu']) ?></td>
                            <td class="p-3 border">
                                <a href="https://wa.me/62<?= ltrim($s['no_hp_ortu'], '0') ?>" target="_blank"
                                   class="bg-white-500 hover:bg-green-600 text-green px-3 py-1 rounded border border-green-500">
                                   <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 text-green-500" viewBox="0 0 448 512" fill="currentColor">
  <path d="M380.9 97.1C339.4 55.6 282.5 32 221.1 32c-122.1 0-221 98.9-221 221 0 39 10.2 77.1 29.5 110.6L0 480l119.1-29.6c32.7 17.9 69.5 27.2 106.7 27.2h.1c122 0 221-98.9 221-221 0-61.4-23.6-118.3-65.1-160.5zM221.1 413c-30.6 0-60.7-8.2-86.8-23.7l-6.2-3.7-70.7 17.6 18.8-68.9-4.1-6.5c-17.4-27.5-26.6-59.3-26.6-91.7 0-97.2 79.1-176.3 176.3-176.3 47 0 91.1 18.3 124.3 51.6s51.6 77.3 51.6 124.3c0 97.2-79.1 176.3-176.3 176.3zm101.6-138.6c-5.6-2.8-33.1-16.3-38.2-18.2-5.1-1.9-8.8-2.8-12.5 2.8s-14.3 18.2-17.5 21.9c-3.2 3.7-6.5 4.2-12.1 1.4-33.1-16.5-54.7-29.4-76.5-66.5-5.8-10 5.8-9.3 16.5-30.9 1.8-3.7.9-6.9-.5-9.6-1.4-2.8-12.5-30.2-17.1-41.4-4.5-10.9-9.1-9.4-12.5-9.6-3.2-.2-6.9-.2-10.6-.2s-9.8 1.4-14.9 6.9c-5.1 5.6-19.5 19.1-19.5 46.5s20 53.9 22.8 57.6c2.8 3.7 39.3 59.8 95.3 83.9 13.3 5.7 23.7 9.1 31.8 11.6 13.4 4.2 25.6 3.6 35.2 2.2 10.7-1.6 33.1-13.5 37.7-26.6 4.6-13.1 4.6-24.4 3.2-26.7-1.3-2.3-5.1-3.7-10.7-6.5z" />\n</svg>
                    </a>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center p-4 text-gray-500">Tidak ada siswa dengan status sakit 3 hari berturut-turut.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
