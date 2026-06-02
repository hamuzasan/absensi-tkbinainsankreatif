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

if (!isset($_GET['kelas']) || !isset($_GET['bulan_tahun'])) {
    echo "Parameter tidak lengkap.";
    exit;
}

$kelas = $_GET['kelas'];
$bulan_tahun = $_GET['bulan_tahun']; // Format: YYYY-MM

// Ambil nama bulan dan tahun
$bulan = date('m', strtotime($bulan_tahun));
$tahun = date('Y', strtotime($bulan_tahun));

// Ambil data siswa berdasarkan nama kelas
$query_siswa = $conn->prepare("SELECT siswa.*, kelas.nama_kelas
FROM siswa
JOIN kelas ON siswa.id_kelas = kelas.id_kelas
WHERE kelas.nama_kelas = ?");
if (!$query_siswa) {
    die("Prepare failed (query_siswa): " . $conn->error);
}
$query_siswa->bind_param("s", $kelas);
$query_siswa->execute();
$result_siswa = $query_siswa->get_result();

$siswa_list = [];
while ($row = $result_siswa->fetch_assoc()) {
    $siswa_list[] = $row;
}

// Buat daftar tanggal di bulan yang dipilih
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
$tanggal_list = [];
for ($i = 1; $i <= $jumlah_hari; $i++) {
    $tanggal_list[] = sprintf("%04d-%02d-%02d", $tahun, $bulan, $i);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Rekap Bulanan Kelas <?= htmlspecialchars($kelas) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
    /* ------------------------------------------- */
    /* [PERUBAHAN UTAMA] GAYA TABEL WEB (Sama dengan PDF) */
    /* ------------------------------------------- */
    .table-responsive table {
        border-collapse: collapse;
        width: 100%;
        table-layout: fixed;
        font-size: 11px; /* Ukuran font lebih kecil agar ringkas seperti PDF */
    }

    .table-responsive th, .table-responsive td {
        border: 1px solid #333;
        /* Menggunakan padding ketat dan line-height/height konsisten untuk sentralisasi vertikal */
        padding: 1px 2px; 
        vertical-align: middle; 
        text-align: center; 
        height: 20px; /* Tinggi baris konsisten */
        line-height: 18px; /* Line height konsisten */
    }

    .table-responsive th { background-color: #c6f6d5; }
    
    /* Mengatur lebar kolom No */
    .table-responsive th:nth-child(1),
    .table-responsive td:nth-child(1) {
        width: 30px; 
        min-width: 30px;
    }
    
    /* Mengatur lebar kolom Nama */
    .name-col {
        width: 150px;
        min-width: 150px;
        text-align: left !important; /* Penting agar nama tidak di tengah */
    }
    
    /* Mengatur lebar kolom Hari yang lebih ringkas */
    .date-col {
        width: 20px; /* Diperkecil menjadi 20px */
        min-width: 20px;
    }

    /* Mengatur lebar kolom total (H, S, I, A) */
    .total-col {
        width: 25px; /* Diperlebar menjadi 25px */
        min-width: 25px;
    }

    /* ------------------------------------------- */
    /* GAYA PRINT */
    /* ------------------------------------------- */
    @media print {
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt; 
        }
        .print-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            text-align: left;
            margin-bottom: 20px;
        }
        .print-header h2, .print-header p {
            margin: 5px 0;
        }
        /* Style tabel untuk print: inherit dari gaya tabel utama */
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        th, td {
            font-size: 9pt; /* Override agar font sedikit lebih kecil dari 11px di web */
        }
    }
</style>

</head>
<body class="bg-green-50 min-h-screen">
    <nav class="bg-green-500 p-4 text-white flex justify-between items-center print:hidden">
        <h1 class="text-lg font-semibold">Rekap Absensi Bulan <?= date('F Y', strtotime($bulan_tahun)) ?> - Kelas <?= htmlspecialchars($kelas) ?></h1>
        <a href="riwayat_absensi_bulanan.php" class="bg-white text-green-600 px-4 py-1 rounded hover:bg-green-100">Kembali</a>
    </nav>

    <main class="p-4 overflow-auto">
<div class="mb-4 flex gap-2 print:hidden">
    <button onclick="printTable()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
        Print Tabel
    </button>
    <button onclick="savePDF()" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">
        Save sebagai PDF
    </button>
</div>

            <div class="overflow-auto table-responsive">
                <!-- Hapus class text-xs, CSS kini mengatur ukuran font -->
                <table id="attendance-table" class="min-w-full border border-gray-300">
                <div class="print-header flex items-center justify-center mb-4 gap-4">
    <div>
        <h2 class="text-lg font-bold">PAUD Terpadu Inklusi Bina Insan Kreatif</h2>
        <p class="text-sm text-gray-600">Rekapitulasi Absensi Bulan <?= date('F Y', strtotime($bulan_tahun)) ?> - Kelas <?= htmlspecialchars($kelas) ?></p>
    </div>
</div>
                    <thead class="bg-green-200 text-gray-800">
                        <tr>
                            <!-- Hapus class date-header karena sudah diatur di CSS tabel global -->
                            <th class="border">No</th>
                            <!-- Tambahkan class name-col -->
                            <th class="border name-col">Nama</th>
                            <?php for ($i = 1; $i <= $jumlah_hari; $i++): ?>
                                <!-- Tambahkan class date-col -->
                                <th class="border date-col"><?= $i ?></th>
                            <?php endfor; ?>
                            <!-- Tambahkan class total-col -->
                            <th class="border total-col">H</th>
                            <th class="border total-col">S</th>
                            <th class="border total-col">I</th>
                            <th class="border total-col">A</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswa_list as $index => $siswa): ?>
                            <?php
                                $absen = [];
                                $hitung = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];
                                for ($i = 1; $i <= $jumlah_hari; $i++) {
                                    $tanggal = sprintf("%04d-%02d-%02d", $tahun, $bulan, $i);
                                    $status = '-';
                                    $query_absen = $conn->prepare("SELECT status FROM absensi WHERE id_siswa = ? AND tanggal = ?");
                                    $query_absen->bind_param("is", $siswa['id_siswa'], $tanggal);
                                    $query_absen->execute();
                                    $result_absen = $query_absen->get_result();
                                    if ($result_absen->num_rows > 0) {
                                        $data = $result_absen->fetch_assoc();
                                        $status = strtoupper($data['status'][0]); // Ambil huruf pertama
                                        if (in_array($status, ['H', 'S', 'I', 'A'])) {
                                            $hitung[$status]++;
                                        }
                                    }
                                    $absen[$i] = $status;
                                }
                            ?>
                            <tr class="hover:bg-green-50">
                                <!-- Hapus class attendance-cell -->
                                <td class="border"><?= $index + 1 ?></td>
                                <!-- Tambahkan class name-col -->
                                <td class="border name-col"><?= htmlspecialchars($siswa['nama_siswa']) ?></td>
                                <?php for ($i = 1; $i <= $jumlah_hari; $i++): ?>
                                    <?php
                                        // Mengubah simbol menjadi huruf tunggal untuk ringkasan
                                        $symbol = match ($absen[$i]) {
                                            'H' => 'H', // Hadir
                                            'S' => 'S', // Sakit
                                            'I' => 'I', // Izin
                                            'A' => 'A', // Alpa
                                            default => '-',
                                        };
                                    ?>
                                    <!-- Tambahkan class date-col -->
                                    <td class="border date-col"><?= $symbol ?></td>
                                <?php endfor; ?>
                                <!-- Tambahkan class total-col -->
                                <td class="border total-col"><?= $hitung['H'] ?></td>
                                <td class="border total-col"><?= $hitung['S'] ?></td>
                                <td class="border total-col"><?= $hitung['I'] ?></td>
                                <td class="border total-col"><?= $hitung['A'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script>
function printTable() {
    const tableElement = document.getElementById('attendance-table');
    const headerElement = document.querySelector('.print-header');

    const tableClone = tableElement.cloneNode(true);
    const headerClone = headerElement.cloneNode(true);

    const printWindow = window.open('', '', 'height=700,width=1000');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Rekap Absensi</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 9pt; }
                .print-header { text-align: center; margin-bottom: 20px; }
                .print-header h2, .print-header p { margin: 5px 0; }
                
                /* Menggunakan style yang sama dengan web */
                table { border-collapse: collapse; width: 100%; table-layout: fixed; }
                th, td { 
                    border: 1px solid #333; 
                    padding: 1px 2px; 
                    text-align: center; 
                    font-size: 9pt; /* Khusus untuk print */
                    vertical-align: middle; 
                    height: 20px; 
                    line-height: 18px; 
                }
                th { background-color: #c6f6d5; }

                /* Lebar kolom di print */
                table th:nth-child(1), table td:nth-child(1) { width: 30px; min-width: 30px; }
                .name-col { width: 150px; min-width: 150px; text-align: left !important; }
                .date-col { width: 20px; min-width: 20px; }
                .total-col { width: 25px; min-width: 25px; }
            </style>
        </head>
        <body>
            ${headerClone.outerHTML}
            ${tableClone.outerHTML}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 300);
}

function savePDF() {
    const tableElement = document.getElementById('attendance-table');
    const headerElement = document.querySelector('.print-header');

    const container = document.createElement('div');
    container.style.padding = '20px';
    container.style.backgroundColor = '#fff';
    container.style.width = 'fit-content'; 
    
    // Font size untuk Canvas harus sedikit lebih besar dari 9pt PDF (agar tidak terlalu blur)
    container.style.fontSize = '9px'; 

    const headerClone = headerElement.cloneNode(true);
    headerClone.querySelector('h2').style.fontSize = '12px';
    headerClone.querySelector('p').style.fontSize = '10px';
    
    const tableClone = tableElement.cloneNode(true);
    // Menggunakan gaya yang sama dengan CSS utama tabel
    tableClone.style.fontSize = '9px'; 
    tableClone.style.borderCollapse = 'collapse';
    tableClone.style.width = 'auto'; 

    // Apply minimal padding/width to cloned cells and set vertical-align
    tableClone.querySelectorAll('th, td').forEach(cell => {
        cell.style.padding = '1px 2px'; 
        cell.style.border = '1px solid #333';
        cell.style.fontSize = '9px'; // Font 9px untuk kualitas PDF
        cell.style.verticalAlign = 'middle'; 
        cell.style.textAlign = 'center'; 
        cell.style.height = '20px'; 
        cell.style.lineHeight = '18px'; 
    });

    // Enforce column widths for PDF
    tableClone.querySelectorAll('th:nth-child(1), td:nth-child(1)').forEach(cell => {
        cell.style.width = '30px';
        cell.style.minWidth = '30px';
    });
    tableClone.querySelectorAll('.name-col').forEach(cell => {
        cell.style.width = '150px';
        cell.style.minWidth = '150px';
        cell.style.textAlign = 'left';
    });
    tableClone.querySelectorAll('.date-col').forEach(cell => {
        cell.style.width = '20px'; 
        cell.style.minWidth = '20px';
    });
    tableClone.querySelectorAll('.total-col').forEach(cell => {
        cell.style.width = '25px'; 
        cell.style.minWidth = '25px';
    });


    container.appendChild(headerClone);
    container.appendChild(tableClone);

    document.body.appendChild(container);

    setTimeout(() => {
        // Skala 3 untuk kualitas yang baik
        html2canvas(container, { scale: 3 }).then(canvas => { 
            const imgData = canvas.toDataURL('image/jpeg', 1.0); 
            const { jsPDF } = window.jspdf;
            
            // Menggunakan A3 Landscape
            const pdf = new jsPDF('landscape', 'pt', 'a3'); 

            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            
            const imgWidth = canvas.width / 3; 
            const imgHeight = canvas.height / 3;

            const ratio = Math.min((pageWidth - 40) / imgWidth, (pageHeight - 40) / imgHeight);
            
            const finalWidth = imgWidth * ratio;
            const finalHeight = imgHeight * ratio;

            const x = (pageWidth - finalWidth) / 2;
            const y = 20;

            pdf.addImage(imgData, 'JPEG', x, y, finalWidth, finalHeight);

            pdf.save(`Rekap_Absensi_${'<?= $kelas ?>'}_${'<?= $bulan_tahun ?>'}.pdf`);
            container.remove();
        });
    }, 50); 
}
</script>

</body>
</html>
