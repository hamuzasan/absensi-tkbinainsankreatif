<?php
session_start();
include '../config/database.php';
include '../function/function.php';

if (!isset($_SESSION['id_guru']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$default_tanggal = date('Y-m-d');
$kelas = $_GET['kelas'] ?? '';
$tanggal = $_GET['tanggal'] ?? $default_tanggal;
$status = $_GET['status'] ?? '';

// Ambil daftar kelas
$kelas_query = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");

// Ambil data absensi berdasarkan filter
$result = null;
if ($kelas != '') {
    $where = ["k.id_kelas = '$kelas'"];
    if ($tanggal != '') $where[] = "a.tanggal = '$tanggal'";
    if ($status != '') $where[] = "a.status = '$status'";

    $filter_sql = 'WHERE ' . implode(' AND ', $where);

    $query = "
        SELECT a.tanggal, a.status, s.nama_siswa, k.nama_kelas
        FROM absensi a
        JOIN siswa s ON a.id_siswa = s.id_siswa
        JOIN kelas k ON s.id_kelas = k.id_kelas
        $filter_sql
        ORDER BY a.tanggal DESC
    ";

    $result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Absensi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-green-100 min-h-screen">

  <div class="bg-green-500 text-white py-4 px-6 flex justify-between items-center">
    <h1 class="text-xl font-bold">Riwayat Absensi</h1>
    <a href="dashboardadmin.php" class="bg-white text-green-600 font-semibold px-4 py-1 rounded hover:bg-gray-100">Kembali</a>
  </div>

  <div class="max-w-6xl mx-auto mt-6 bg-white rounded-xl shadow p-6">
    <h2 class="text-2xl font-semibold mb-4">Data Absensi Siswa 📋</h2>

    <!-- Filter -->
    <form method="get" class="grid md:grid-cols-4 gap-4 mb-6">
      <div>
        <label class="block mb-1 font-medium text-gray-700">Kelas</label>
        <select name="kelas" class="w-full border px-3 py-2 rounded-lg" onchange="this.form.submit()" required>
          <option value="">-- Pilih Kelas --</option>
          <?php while ($row_kelas = mysqli_fetch_assoc($kelas_query)):
              $selected = ($kelas == $row_kelas['id_kelas']) ? 'selected' : '';
          ?>
            <option value="<?= $row_kelas['id_kelas'] ?>" <?= $selected ?>><?= $row_kelas['nama_kelas'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div>
        <label class="block mb-1 font-medium text-gray-700">Status Kehadiran</label>
        <select name="status" class="w-full border px-3 py-2 rounded-lg" onchange="this.form.submit()">
          <option value="">-- Semua Status --</option>
          <?php
          $statuses = ['Sakit' => 'Sakit', 'Izin' => 'Izin', 'Alpha' => 'Alpha'];
          foreach ($statuses as $val => $label):
              $selected = ($status === $val) ? 'selected' : '';
              echo "<option value=\"$val\" $selected>$label</option>";
          endforeach;
          ?>
        </select>
      </div>

      <div>
        <label class="block mb-1 font-medium text-gray-700">Tanggal</label>
        <input type="date" name="tanggal" class="w-full border px-3 py-2 rounded-lg"
               value="<?= htmlspecialchars($tanggal) ?>" onchange="this.form.submit()">
      </div>

      <div class="flex items-end gap-2">
        <a href="riwayat_absensi.php" class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded-lg">Reset</a>
      </div>
    </form>

    <!-- Tabel -->
    <div class="overflow-x-auto">
      <?php if ($kelas == ''): ?>
        <div class="text-center text-gray-500 py-4">Silakan pilih kelas untuk melihat data absensi.</div>
      <?php elseif ($result && mysqli_num_rows($result) > 0): ?>
        <table class="min-w-full bg-white border border-gray-300 rounded-lg">
          <thead class="bg-green-200 text-green-800 font-semibold">
            <tr>
              <th class="text-left px-4 py-2 border">Nama Siswa</th>
              <th class="text-left px-4 py-2 border">Tanggal</th>
              <th class="text-left px-4 py-2 border">Status Kehadiran</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr class="hover:bg-gray-100">
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                <td class="px-4 py-2 border"><?= formatTanggalIndonesia($row['tanggal']) ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['status']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="text-center text-gray-500 py-4">Tidak ada data absensi untuk filter yang dipilih.</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
<?php
session_start();
include '../config/database.php';
include '../function/function.php';

if (!isset($_SESSION['id_guru']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$default_tanggal = date('Y-m-d');
$kelas = $_GET['kelas'] ?? '';
$tanggal = $_GET['tanggal'] ?? $default_tanggal;
$status = $_GET['status'] ?? '';

// Ambil daftar kelas
$kelas_query = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");

// Ambil data absensi berdasarkan filter
$result = null;
if ($kelas != '') {
    $where = ["k.id_kelas = '$kelas'"];
    if ($tanggal != '') $where[] = "a.tanggal = '$tanggal'";
    if ($status != '') $where[] = "a.status = '$status'";

    $filter_sql = 'WHERE ' . implode(' AND ', $where);

    $query = "
        SELECT a.tanggal, a.status, s.nama_siswa, k.nama_kelas
        FROM absensi a
        JOIN siswa s ON a.id_siswa = s.id_siswa
        JOIN kelas k ON s.id_kelas = k.id_kelas
        $filter_sql
        ORDER BY a.tanggal DESC
    ";

    $result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Absensi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-green-100 min-h-screen">

  <div class="bg-green-500 text-white py-4 px-6 flex justify-between items-center">
    <h1 class="text-xl font-bold">Riwayat Absensi</h1>
    <a href="dashboardadmin.php" class="bg-white text-green-600 font-semibold px-4 py-1 rounded hover:bg-gray-100">Kembali</a>
  </div>

  <div class="max-w-6xl mx-auto mt-6 bg-white rounded-xl shadow p-6">
    <h2 class="text-2xl font-semibold mb-4">Data Absensi Siswa 📋</h2>

    <!-- Filter -->
    <form method="get" class="grid md:grid-cols-4 gap-4 mb-6">
      <div>
        <label class="block mb-1 font-medium text-gray-700">Kelas</label>
        <select name="kelas" class="w-full border px-3 py-2 rounded-lg" onchange="this.form.submit()" required>
          <option value="">-- Pilih Kelas --</option>
          <?php while ($row_kelas = mysqli_fetch_assoc($kelas_query)):
              $selected = ($kelas == $row_kelas['id_kelas']) ? 'selected' : '';
          ?>
            <option value="<?= $row_kelas['id_kelas'] ?>" <?= $selected ?>><?= $row_kelas['nama_kelas'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div>
        <label class="block mb-1 font-medium text-gray-700">Status Kehadiran</label>
        <select name="status" class="w-full border px-3 py-2 rounded-lg" onchange="this.form.submit()">
          <option value="">-- Semua Status --</option>
          <?php
          $statuses = ['Sakit' => 'Sakit', 'Izin' => 'Izin', 'Alpha' => 'Alpha'];
          foreach ($statuses as $val => $label):
              $selected = ($status === $val) ? 'selected' : '';
              echo "<option value=\"$val\" $selected>$label</option>";
          endforeach;
          ?>
        </select>
      </div>

      <div>
        <label class="block mb-1 font-medium text-gray-700">Tanggal</label>
        <input type="date" name="tanggal" class="w-full border px-3 py-2 rounded-lg"
               value="<?= htmlspecialchars($tanggal) ?>" onchange="this.form.submit()">
      </div>

      <div class="flex items-end gap-2">
        <a href="riwayat_absensi.php" class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded-lg">Reset</a>
      </div>
    </form>

    <!-- Tabel -->
    <div class="overflow-x-auto">
      <?php if ($kelas == ''): ?>
        <div class="text-center text-gray-500 py-4">Silakan pilih kelas untuk melihat data absensi.</div>
      <?php elseif ($result && mysqli_num_rows($result) > 0): ?>
        <table class="min-w-full bg-white border border-gray-300 rounded-lg">
          <thead class="bg-green-200 text-green-800 font-semibold">
            <tr>
              <th class="text-left px-4 py-2 border">Nama Siswa</th>
              <th class="text-left px-4 py-2 border">Tanggal</th>
              <th class="text-left px-4 py-2 border">Status Kehadiran</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr class="hover:bg-gray-100">
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                <td class="px-4 py-2 border"><?= formatTanggalIndonesia($row['tanggal']) ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['status']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="text-center text-gray-500 py-4">Tidak ada data absensi untuk filter yang dipilih.</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
