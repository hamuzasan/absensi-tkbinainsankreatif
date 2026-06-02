<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include '../config/database.php';
mysqli_query($conn, "SET time_zone = '+07:00'");
include '../function/function.php';

// --- Autorisasi Admin ---
if (!isset($_SESSION['id_guru']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- Persiapan Filter dan Data Guru ---
// Ambil daftar semua guru untuk filter dropdown
$query_guru = "SELECT id_guru, nama_guru FROM guru WHERE role = 'guru' ORDER BY nama_guru ASC";
$result_guru = mysqli_query($conn, $query_guru);

// Tentukan nilai default filter
// Default tanggal_awal: Awal bulan ini
$tanggal_awal = isset($_GET['tanggal_awal']) && !empty($_GET['tanggal_awal']) ? 
    mysqli_real_escape_string($conn, $_GET['tanggal_awal']) : 
    date('Y-m-01');

// Default tanggal_akhir: Hari ini
$tanggal_akhir = isset($_GET['tanggal_akhir']) && !empty($_GET['tanggal_akhir']) ? 
    mysqli_real_escape_string($conn, $_GET['tanggal_akhir']) : 
    date('Y-m-d');

$filter_guru_id = isset($_GET['filter_guru']) && $_GET['filter_guru'] !== '' ? 
    mysqli_real_escape_string($conn, $_GET['filter_guru']) : 
    '';

// --- Query Utama Laporan ---
$query_laporan = "
    SELECT 
        a.tanggal, 
        a.status, 
        a.waktu_absen,
        g.nama_guru
    FROM 
        absensi_guru a
    JOIN 
        guru g ON a.id_guru = g.id_guru
    WHERE 
        a.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
";

// Tambahkan filter guru jika dipilih
if ($filter_guru_id) {
    $query_laporan .= " AND a.id_guru = '$filter_guru_id'";
}

$query_laporan .= " ORDER BY a.tanggal DESC, a.waktu_absen DESC";

$result_laporan = mysqli_query($conn, $query_laporan);

// Tentukan judul laporan berdasarkan filter
$judul_laporan = "Laporan Absensi Guru Periode " . date('d/m/Y', strtotime($tanggal_awal)) . " s/d " . date('d/m/Y', strtotime($tanggal_akhir));
if ($filter_guru_id) {
    // Cari nama guru yang difilter
    $nama_guru_filter = '';
    mysqli_data_seek($result_guru, 0); 
    while ($guru = mysqli_fetch_assoc($result_guru)) {
        if ($guru['id_guru'] == $filter_guru_id) {
            $nama_guru_filter = $guru['nama_guru'];
            break;
        }
    }
    $judul_laporan = "Laporan Absensi Guru: " . htmlspecialchars($nama_guru_filter) . " (" . date('d/m/Y', strtotime($tanggal_awal)) . " - " . date('d/m/Y', strtotime($tanggal_akhir)) . ")";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Guru (Admin)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="bg-green-600 p-4 text-white flex justify-between items-center">
        <h1 class="text-xl font-bold">📋 Laporan Absensi Guru</h1>
        <a href="dashboardadmin.php" class="bg-white text-green-600 px-3 py-1 rounded hover:bg-green-100 font-medium">Kembali ke Dashboard</a>
    </nav>

    <main class="p-6 max-w-7xl mx-auto">
        <div class="bg-white shadow p-6 rounded-xl">
            <h2 class="text-2xl font-bold text-green-700 mb-6"><?= $judul_laporan ?></h2>
            
            <!-- Form Filter -->
            <form method="get" class="mb-8 p-4 border border-green-200 bg-green-50 rounded-lg grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                
                <!-- Filter Guru -->
                <div>
                    <label for="filter_guru" class="block mb-1 text-sm font-medium text-gray-700">Filter Guru:</label>
                    <select name="filter_guru" id="filter_guru" class="border rounded w-full p-2.5 text-sm focus:ring-green-500 focus:border-green-500">
                        <option value="">-- Semua Guru --</option>
                        <?php 
                        mysqli_data_seek($result_guru, 0); // Reset pointer
                        while ($guru = mysqli_fetch_assoc($result_guru)): ?>
                            <option value="<?= $guru['id_guru'] ?>" <?= $filter_guru_id == $guru['id_guru'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($guru['nama_guru']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Filter Tanggal Awal -->
                <div>
                    <label for="tanggal_awal" class="block mb-1 text-sm font-medium text-gray-700">Tanggal Awal:</label>
                    <input type="date" name="tanggal_awal" id="tanggal_awal" value="<?= $tanggal_awal ?>" required class="border rounded w-full p-2.5 text-sm focus:ring-green-500 focus:border-green-500">
                </div>
                
                <!-- Filter Tanggal Akhir -->
                <div>
                    <label for="tanggal_akhir" class="block mb-1 text-sm font-medium text-gray-700">Tanggal Akhir:</label>
                    <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="<?= $tanggal_akhir ?>" required class="border rounded w-full p-2.5 text-sm focus:ring-green-500 focus:border-green-500">
                </div>
                
                <!-- Tombol Submit & Reset -->
                <div class="flex space-x-2">
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-medium text-sm">Tampilkan</button>
                    <a href="laporan_absensi_guru_admin.php" class="w-full text-center bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition font-medium text-sm">Reset</a>
                </div>
            </form>

            <!-- Tabel Laporan -->
            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-green-800 uppercase tracking-wider">No.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-green-800 uppercase tracking-wider">Nama Guru</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-green-800 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-green-800 uppercase tracking-wider">Jam Absen</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-green-800 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php 
                        $no = 1;
                        // Tentukan batas waktu normal
                        $batas_awal = '06:30:00';
                        $batas_akhir = '07:16:00';
                        $error_date = '01-01-1970'; // Tanggal yang dianggap error
                        
                        if (mysqli_num_rows($result_laporan) > 0):
                            while ($row = mysqli_fetch_assoc($result_laporan)): 
                                
                                // FIX: Ambil tanggal record yang benar dari kolom 'tanggal' untuk ditampilkan
                                $tanggal_absensi = date('d-m-Y', strtotime($row['tanggal']));

                                // Ambil waktu absen untuk jam dan pengecekan error (1970)
                                $waktu_absen_timestamp = strtotime($row['waktu_absen']);
                                $tanggal_dari_waktu_absen = date('d-m-Y', $waktu_absen_timestamp); // Digunakan untuk cek error 1970
                                $jam_saja = date('H:i:s', $waktu_absen_timestamp);
                                
                                // Inisialisasi tampilan jam default
                                $jam_tampil = $jam_saja;
                                
                                // Baris dikembalikan ke warna latar belakang normal (putih)
                                $row_class = 'hover:bg-gray-50';
                                $text_color_class = 'text-gray-900';
                                
                                // Default background untuk sel waktu absen
                                $time_bg_class = 'bg-gray-100 text-gray-500';
                                
                                // --- LOGIKA KONDISIONAL JAM ABSEN & DISPLAY (Revisi untuk menghilangkan jam 1970) ---
                                
                                if ($tanggal_dari_waktu_absen == $error_date) {
                                    // JIKA TANGGAL ERROR (dilihat dari waktu_absen): Hilangkan waktu absen dan gunakan styling netral
                                    $jam_tampil = '-'; // Tampilkan tanda hubung
                                    $time_bg_class = 'bg-gray-100 text-gray-500'; 
                                } else if ($row['status'] == 'hadir') {
                                    // Hanya cek keterlambatan jika statusnya 'hadir'
                                    if ($jam_saja >= $batas_awal && $jam_saja <= $batas_akhir) {
                                        // Tepat Waktu: Hijau
                                        $time_bg_class = 'bg-green-100 text-green-800 font-semibold';
                                    } else {
                                        // Di luar rentang: Kuning (Waktu Abnormal/Terlambat/Terlalu Cepat)
                                        $time_bg_class = 'bg-yellow-100 text-yellow-800 font-semibold';
                                    }
                                } 
                                // Jika status bukan hadir (izin, sakit, dll) dan bukan tanggal error, biarkan default netral
                                // ------------------------------------

                                // Tentukan warna status kehadiran
                                $status_class = '';
                                $status_text = ucfirst($row['status']);
                                switch ($row['status']) {
                                    case 'hadir':
                                        $status_class = 'bg-green-100 text-green-800';
                                        break;
                                    case 'izin':
                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'sakit':
                                        $status_class = 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'alpha':
                                        $status_class = 'bg-red-100 text-red-800';
                                        $status_text = 'Alpha';
                                        break;
                                    default:
                                        $status_class = 'bg-gray-100 text-gray-800';
                                        break;
                                }
                        ?>
                        <tr class="<?= $row_class ?>">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium <?= $text_color_class ?>"><?= $no++ ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold <?= $text_color_class ?>"><?= htmlspecialchars($row['nama_guru']) ?></td>
                            <!-- Gunakan $tanggal_absensi (dari kolom 'tanggal' yang benar) -->
                            <td class="px-4 py-3 whitespace-nowrap text-sm <?= $text_color_class ?>"><?= $tanggal_absensi ?></td>
                            
                            <!-- Sel Jam Absen dengan Conditional Styling dan menggunakan $jam_tampil -->
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-mono">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 rounded-full <?= $time_bg_class ?>">
                                    <?= $jam_tampil ?>
                                </span>
                            </td>
                            
                            <!-- Sel Status Kehadiran -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                    <?= $status_text ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; 
                        else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada data absensi dalam periode dan filter yang dipilih.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </main>
</body>
</html>
