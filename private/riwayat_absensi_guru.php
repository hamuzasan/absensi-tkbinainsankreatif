<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['id_guru']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil nilai filter dari form
$bulan_tahun = $_GET['bulan_tahun'] ?? '';
$role_filter = $_GET['role_filter'] ?? 'all'; // Menambahkan filter role, default: 'all'

// Query untuk mengambil semua guru, difilter berdasarkan role
$where_role = "";
if ($role_filter != 'all') {
    // Pastikan nilai role aman dari injeksi SQL
    $safe_role = mysqli_real_escape_string($conn, $role_filter);
    $where_role = " WHERE role = '{$safe_role}'";
}

// Ambil semua guru yang sesuai dengan filter role
$guru_result = mysqli_query($conn, "SELECT id_guru, nama_guru, role FROM guru" . $where_role . " ORDER BY nama_guru ASC");
$guru_list = [];
while ($row = mysqli_fetch_assoc($guru_result)) {
    $guru_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi Guru - Filter Bulanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Pustaka untuk Ekspor PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.js"></script>
    <style>
        /* Gaya CSS Kustom untuk tampilan yang lebih baik saat di layar */
        .table-container {
            max-height: calc(100vh - 250px); /* Ketinggian maksimal untuk scroll */
        }
        .table-sticky-header th {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="bg-green-600 p-4 text-white flex justify-between items-center">
        <h1 class="text-xl font-bold">📋 Rekap Absensi Guru</h1>
        <a href="dashboardadmin.php" class="bg-white text-green-600 px-4 py-1 rounded hover:bg-green-100 font-medium transition duration-150">Kembali</a>
    </nav>

    <main class="p-6 max-w-7xl mx-auto">
        <!-- 🔍 Filter Form -->
        <form method="GET" id="filterForm" class="mb-6 flex flex-wrap items-center gap-4 bg-white p-4 rounded-xl shadow-md">
            
            <label class="text-sm text-gray-700 font-medium">Pilih Bulan dan Tahun:</label>
            <input type="month" name="bulan_tahun" value="<?= htmlspecialchars($bulan_tahun) ?>" class="border rounded-lg p-2.5 focus:ring-green-500 focus:border-green-500">
            
            <label class="text-sm text-gray-700 font-medium">Filter Role Guru:</label>
            <select name="role_filter" class="border rounded-lg p-2.5 bg-white focus:ring-green-500 focus:border-green-500 capitalize">
                <option value="all" <?= $role_filter == 'all' ? 'selected' : '' ?>>Semua Guru</option>
                <option value="guru" <?= $role_filter == 'guru' ? 'selected' : '' ?>>Guru SD</option>
                <option value="guruPaud" <?= $role_filter == 'guruPaud' ? 'selected' : '' ?>>Guru PAUD</option>
            </select>
            
            <button type="submit" class="bg-green-600 text-white px-4 py-2.5 rounded-lg hover:bg-green-700 font-medium transition duration-150">Tampilkan Rekap</button>

            <?php if (!empty($bulan_tahun)): ?>
                <button type="button" onclick="exportPDF()" class="bg-red-600 text-white px-4 py-2.5 rounded-lg hover:bg-red-700 font-medium transition duration-150 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"></path></svg>
                    Export ke PDF
                </button>
            <?php endif; ?>
        </form>

        <?php if (!empty($bulan_tahun)): ?>
            <?php
                // Logika perhitungan tanggal
                $bulan = date('m', strtotime($bulan_tahun));
                $tahun = date('Y', strtotime($bulan_tahun));
                $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

                $tanggal_list = [];
                for ($i = 1; $i <= $jumlah_hari; $i++) {
                    $tanggal_list[] = sprintf("%04d-%02d-%02d", $tahun, $bulan, $i);
                }

                // Tentukan judul role untuk header PDF
                $role_title = match($role_filter) {
                    'guru' => 'Guru SD',
                    'guruPaud' => 'Guru PAUD',
                    default => 'Semua Guru'
                };
                $pdf_title = "REKAP ABSENSI GURU - {$role_title} - " . date('F Y', strtotime($bulan_tahun));
            ?>
            <section class="mb-10 bg-white p-4 rounded-xl shadow-md">
                <h2 class="text-lg font-bold text-green-700 mb-4">
                    📅 Rekap Absensi - <?= date('F Y', strtotime($bulan_tahun)) ?> 
                    <?php if ($role_filter != 'all'): ?>
                        (Role: <span class="capitalize"><?= htmlspecialchars($role_title) ?></span>)
                    <?php endif; ?>
                </h2>

                <div class="overflow-x-auto table-container">
                    <!-- Perubahan: Mengubah table-fixed menjadi table-auto agar lebar kolom menyesuaikan konten -->
                    <table id="attendanceTable" class="min-w-full text-sm border border-gray-300 rounded-lg table-auto">
                        <thead class="bg-green-100 text-green-900 table-sticky-header">
                            <tr>
                                <!-- Disesuaikan: padding p-1 dan lebar w-7 untuk kolom "No" agar lebih ringkas -->
                                <th class="border p-1 w-7">No</th> 
                                <!-- Perubahan: min-w-[120px] dihilangkan, membiarkan table-auto menghitung lebar secara murni berdasarkan konten terpanjang -->
                                <th class="border p-2">Nama Guru</th>
                                <?php foreach ($tanggal_list as $tgl): ?>
                                    <!-- Disesuaikan: Lebar w-7 untuk kolom tanggal -->
                                    <th class="border p-1 w-7"><?= date('j', strtotime($tgl)) ?></th>
                                <?php endforeach; ?>
                                <!-- Disesuaikan: padding p-1 dan lebar w-7 untuk kolom Total -->
                                <th class="border p-1 w-7 bg-yellow-200">H</th>
                                <th class="border p-1 w-7 bg-blue-200">S</th>
                                <th class="border p-1 w-7 bg-indigo-200">I</th>
                                <th class="border p-1 w-7 bg-red-200">A</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($guru_list as $index => $guru): ?>
                                <?php
                                    $rekap = [];
                                    $hitung = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];

                                    foreach ($tanggal_list as $tgl) {
                                        $status = '-';
                                        $id_guru_safe = mysqli_real_escape_string($conn, $guru['id_guru']);
                                        $tgl_safe = mysqli_real_escape_string($conn, $tgl);
                                        
                                        $absen_q = mysqli_query($conn, "
                                            SELECT status FROM absensi_guru 
                                            WHERE id_guru = {$id_guru_safe} AND tanggal = '{$tgl_safe}'
                                        ");
                                        
                                        if ($row = mysqli_fetch_assoc($absen_q)) {
                                            $initial = strtoupper(substr($row['status'], 0, 1));
                                            
                                            if (in_array($initial, ['H', 'S', 'I', 'A'])) {
                                                $status = $initial;
                                                $hitung[$status]++;
                                            } else {
                                                $status = '-';
                                            }
                                        }
                                        $rekap[] = $status;
                                    }
                                ?>
                                <tr class="hover:bg-green-50">
                                    <!-- Disesuaikan: padding p-1 -->
                                    <td class="border p-1 text-center"><?= $index + 1 ?></td>
                                    <!-- Padding sudah p-1. Kita biarkan ini. -->
                                    <td class="border p-1 font-medium text-gray-700"><?= htmlspecialchars($guru['nama_guru']) ?></td>
                                    <?php foreach ($rekap as $val): ?>
                                        <?php
                                            $class = match ($val) {
                                                'H' => 'bg-green-100 text-green-700 font-bold',
                                                'S' => 'bg-yellow-100 text-yellow-700',
                                                'I' => 'bg-blue-100 text-blue-700',
                                                'A' => 'bg-red-100 text-red-700 font-bold',
                                                default => 'bg-gray-50 text-gray-400',
                                            };
                                            $symbol = ($val === '-') ? '-' : $val; 
                                        ?>
                                        <!-- Padding di sini sudah minimal (p-1) -->
                                        <td class="border p-1 text-center <?= $class ?>" data-status="<?= $symbol ?>"><?= $symbol ?></td>
                                    <?php endforeach; ?>
                                    <!-- Disesuaikan: padding p-1 untuk kolom Total -->
                                    <td class="border p-1 text-center bg-yellow-50 font-semibold"><?= $hitung['H'] ?></td>
                                    <td class="border p-1 text-center bg-blue-50 font-semibold"><?= $hitung['S'] ?></td>
                                    <td class="border p-1 text-center bg-indigo-50 font-semibold"><?= $hitung['I'] ?></td>
                                    <td class="border p-1 text-center bg-red-50 font-semibold"><?= $hitung['A'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <script>
                // Fungsi untuk Export ke PDF
                function exportPDF() {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF('landscape'); // Menggunakan format landscape karena banyak kolom
                    const table = document.getElementById('attendanceTable');

                    // 1. Ambil Judul Header
                    const headers = Array.from(table.tHead.querySelectorAll('th')).map(th => th.innerText.trim());

                    // 2. Ambil Data Body
                    const body = [];
                    table.tBodies[0].querySelectorAll('tr').forEach(row => {
                        const rowData = Array.from(row.querySelectorAll('td')).map(td => td.innerText.trim());
                        body.push(rowData);
                    });

                    // 3. Tambahkan Judul ke PDF
                    const titleText = "<?= $pdf_title ?>";
                    doc.setFontSize(14);
                    doc.text(titleText, 14, 20);

                    // 4. Gunakan autoTable untuk membuat tabel PDF
                    doc.autoTable({
                        startY: 25, // Mulai di bawah judul
                        head: [headers],
                        body: body,
                        theme: 'grid',
                        tableWidth: 'wrap', // Mempertahankan tabel agar tidak meregang, hanya menggunakan lebar kolom yang ditentukan
                        styles: { 
                            fontSize: 9, // PERUBAHAN: Menaikkan font dari 7 ke 9
                            cellPadding: 1.5, // Sedikit memperbesar padding
                            lineColor: 200,
                            lineWidth: 0.1
                        },
                        headStyles: { 
                            fillColor: [34, 139, 34], // Hijau tua untuk header
                            textColor: 255,
                            fontStyle: 'bold'
                        },
                        didParseCell: (data) => {
                             // Memastikan semua teks rata tengah kecuali Nama Guru
                            if (data.section === 'body' && data.column.index !== 1) {
                                data.cell.styles.halign = 'center';
                            }
                            // Memastikan lebar kolom Nama Guru (index 1) cukup besar
                            if (data.column.index === 1) {
                                // PERUBAHAN: Menaikkan lebar kolom Nama Guru agar proporsional dengan font yang lebih besar
                                data.column.width = 25; 
                            } else {
                                // PERUBAHAN: Menaikkan lebar kolom tanggal/total agar proporsional dengan font yang lebih besar
                                data.column.width = 8.5; 
                            }
                        }
                    });

                    // 5. Simpan file PDF
                    doc.save('rekap_absensi_guru_<?= date('Y-m', strtotime($bulan_tahun)) ?>_<?= htmlspecialchars($role_filter) ?>.pdf');
                }
            </script>
        <?php else: ?>
            <p class="text-gray-600 bg-white p-4 rounded-xl shadow-md">Silakan pilih bulan, tahun, dan filter role guru terlebih dahulu untuk melihat rekap absensi.</p>
        <?php endif; ?>
    </main>
</body>
</html>
