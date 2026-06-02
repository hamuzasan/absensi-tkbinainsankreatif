<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include '../config/database.php';
// Penting: Set timezone database sesuai dengan PHP untuk konsistensi
mysqli_query($conn, "SET time_zone = '+07:00'"); 
include '../function/function.php';

if (!isset($_SESSION['id_guru']) || !in_array($_SESSION['role'], ['guru', 'guruPaud'])) {
    // Catatan: Walaupun guru dan guruPaud sekarang bisa mengakses, 
    // disarankan untuk menambahkan pemeriksaan di sekitar blok Tambah/Hapus/Update 
    // agar hanya Admin yang bisa melakukan perubahan.
    header("Location: ../index.php");
    exit;
}

$id_guru = $_SESSION['id_guru'];
$tanggal = date('Y-m-d');
$status = '';
$sukses = '';
$error = '';

// Cek apakah guru sudah absen hari ini
$cek = mysqli_query($conn, "SELECT * FROM absensi_guru WHERE id_guru = '$id_guru' AND tanggal = '$tanggal'");
$sudah_absen = mysqli_num_rows($cek) > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$sudah_absen) {
    $status = $_POST['status'];
    $waktu_absen = date('Y-m-d H:i:s'); // Mengambil tanggal dan jam saat ini (WIB)

    if (!empty($status)) {
        // Query INSERT yang sudah diperbarui dan benar, menyertakan waktu_absen
        $query = "INSERT INTO absensi_guru (id_guru, tanggal, status, waktu_absen) VALUES ('$id_guru', '$tanggal', '$status', '$waktu_absen')";
        
        if (mysqli_query($conn, $query)) {
            $sukses = "✅ Absensi berhasil dicatat pada jam " . date('H:i:s', strtotime($waktu_absen)) . ".";
            $sudah_absen = true;
        } else {
            // Tampilkan error jika query gagal
            $error = "❌ Gagal menyimpan absensi. Error: " . mysqli_error($conn); 
        }
    } else {
        $error = "❌ Silakan pilih status kehadiran.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Absensi Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="bg-green-600 p-4 text-white flex justify-between">
        <h1 class="text-xl font-bold">📘 Absensi Hari Ini</h1>
        <a href="dashboardguru.php" class="bg-white text-green-600 px-3 py-1 rounded hover:bg-green-100">Kembali</a>
    </nav>

    <main class="p-6 max-w-xl mx-auto">
        <div class="bg-white shadow p-6 rounded-xl">
            <h2 class="text-lg font-semibold text-green-700 mb-4">Tanggal: <?= date('d-m-Y') ?></h2>

            <?php if ($sudah_absen): 
                // Jika sudah absen, ambil data absensi untuk ditampilkan
                $data_absen_terakhir = mysqli_fetch_assoc($cek);
                $status_tampil = ucfirst($data_absen_terakhir['status']);
            ?>
                <p class="text-green-700 font-medium">✅ Anda sudah absen hari ini.</p>
                <div class="mt-4 p-4 border border-green-200 rounded-lg bg-green-50">
                    <p class="text-sm font-semibold text-gray-700">Status: <span class="text-green-600"><?= $status_tampil ?></span></p>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="bg-red-100 text-red-700 p-2 rounded mb-2"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($sukses): ?>
                    <div class="bg-green-100 text-green-700 p-2 rounded mb-2"><?= $sukses ?></div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block mb-1 text-sm text-gray-700">Status Kehadiran:</label>
                        <select name="status" class="border rounded w-full p-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">-- Pilih Status --</option>
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpha">Alpha</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition w-full font-medium">Submit Absensi</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
