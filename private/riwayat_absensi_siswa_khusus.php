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

$kelas_id = $_GET['kelas'] ?? '';
$siswa_id = $_GET['siswa'] ?? '';

// Ambil daftar kelas
$kelas_query = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");

// Ambil siswa jika kelas dipilih
$siswa_query = null;
if ($kelas_id) {
    $siswa_query = mysqli_query($conn, "SELECT * FROM siswa WHERE id_kelas = '$kelas_id' ORDER BY nama_siswa");
}

// Ambil data absensi jika siswa dipilih
$data_siswa = null;
$absensi = null;
if ($siswa_id) {
    $get_siswa = mysqli_query($conn, "
        SELECT s.nama_siswa, k.nama_kelas
        FROM siswa s 
        JOIN kelas k ON s.id_kelas = k.id_kelas 
        WHERE s.id_siswa = '$siswa_id'
    ");

    if (mysqli_num_rows($get_siswa)) {
        $data_siswa = mysqli_fetch_assoc($get_siswa);
        $absensi = mysqli_query($conn, "
            SELECT tanggal, status 
            FROM absensi 
            WHERE id_siswa = '$siswa_id' 
            ORDER BY tanggal DESC
        ");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Kehadiran Siswa</title>
  <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-green-50 min-h-screen">
  <nav class="bg-green-500 text-white p-4 flex justify-between">
    <h1 class="text-xl font-semibold">Riwayat Kehadiran Siswa</h1>
    <a href="kelola_murid.php" class="bg-white text-green-600 px-4 py-1 rounded hover:bg-green-100">Kembali</a>
  </nav>

  <main class="p-6 max-w-4xl mx-auto">
    <div class="bg-white p-6 rounded-xl shadow space-y-4">
      <form method="get" class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block font-medium mb-1">Pilih Kelas</label>
          <select name="kelas" onchange="this.form.submit()" class="w-full border px-3 py-2 rounded">
            <option value="">-- Pilih Kelas --</option>
            <?php while ($k = mysqli_fetch_assoc($kelas_query)):
              $selected = $kelas_id == $k['id_kelas'] ? 'selected' : '';
            ?>
              <option value="<?= $k['id_kelas'] ?>" <?= $selected ?>><?= $k['nama_kelas'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <?php if ($kelas_id && $siswa_query): ?>
        <div>
          <label class="block font-medium mb-1">Pilih Siswa</label>
          <select name="siswa" onchange="this.form.submit()" class="w-full border px-3 py-2 rounded">
            <option value="">-- Pilih Siswa --</option>
            <?php while ($s = mysqli_fetch_assoc($siswa_query)):
              $selected = $siswa_id == $s['id_siswa'] ? 'selected' : '';
            ?>
              <option value="<?= $s['id_siswa'] ?>" <?= $selected ?>><?= $s['nama_siswa'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <?php endif; ?>
      </form>

      <?php if ($data_siswa): ?>
      <div class="mt-6">
        <h2 class="text-xl font-semibold">Riwayat Kehadiran: <?= htmlspecialchars($data_siswa['nama_siswa']) ?></h2>
        <p class="mb-4">Kelas: <strong><?= $data_siswa['nama_kelas'] ?></strong></p>

        <table class="min-w-full text-sm border">
          <thead class="bg-green-100 text-green-800">
            <tr>
              <th class="p-3 border">Tanggal</th>
              <th class="p-3 border">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($absensi && mysqli_num_rows($absensi) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($absensi)): ?>
                <tr class="hover:bg-green-50">
                  <td class="p-3 border"><?= formatTanggalIndonesia($row['tanggal']) ?></td>
                  <td class="p-3 border capitalize"><?= htmlspecialchars($row['status']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="2" class="text-center p-4 text-gray-500">Belum ada data absensi.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
