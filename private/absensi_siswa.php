<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include '../config/database.php';
include '../function/function.php';

if (!isset($_SESSION['id_guru']) || !in_array($_SESSION['role'], ['guru', 'guruPaud'])) {
    // Catatan: Walaupun guru dan guruPaud sekarang bisa mengakses, 
    // disarankan untuk menambahkan pemeriksaan di sekitar blok Tambah/Hapus/Update 
    // agar hanya Admin yang bisa melakukan perubahan.
    header("Location: ../index.php");
    exit;
}

$id_guru = $_SESSION['id_guru'];
$sukses = "";

// Ambil semua kelas untuk dropdown filter
$kelas_options = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");

// Cek apakah filter dipilih
$filter_kelas = $_GET['kelas'] ?? '';

// Ambil daftar siswa berdasarkan filter kelas
$result = null;
if ($filter_kelas) {
    $result = mysqli_query($conn, "
        SELECT siswa.*, kelas.nama_kelas
        FROM siswa
        JOIN kelas ON siswa.id_kelas = kelas.id_kelas
        WHERE siswa.id_kelas = '$filter_kelas'
        ORDER BY siswa.nama_siswa ASC
    ");
}

// Simpan absensi
if (isset($_POST['submit_absensi'])) {
    $tanggal = date('Y-m-d');

    foreach ($_POST['status'] as $id_siswa => $status) {
        $cek = mysqli_query($conn, "SELECT * FROM absensi WHERE id_siswa='$id_siswa' AND tanggal='$tanggal'");
        if (mysqli_num_rows($cek) == 0) {
            mysqli_query($conn, "INSERT INTO absensi (id_siswa, id_guru, tanggal, status)
                                 VALUES ('$id_siswa', '$id_guru', '$tanggal', '$status')");
        }
    }

    $sukses = "Absensi berhasil disimpan!";
}

$tanggal_hari_ini = date('Y-m-d');
$jam_sekarang = date('H:i:s');
$tanggal_ditampilkan = formatTanggalIndonesia($tanggal_hari_ini) . ' - ' . $jam_sekarang;


// Edit Kehadiran
$edit_success = "";
if (isset($_POST['edit_kehadiran'])) {
    $edit_siswa = $_POST['edit_siswa'];
    $edit_status = $_POST['edit_status'];

    $cek_edit = mysqli_query($conn, "SELECT * FROM absensi WHERE id_siswa='$edit_siswa' AND tanggal='$tanggal_hari_ini'");
    if (mysqli_num_rows($cek_edit) > 0) {
        mysqli_query($conn, "UPDATE absensi SET status='$edit_status' WHERE id_siswa='$edit_siswa' AND tanggal='$tanggal_hari_ini'");
        $edit_success = "Status kehadiran berhasil diperbarui!";
    } else {
        $edit_success = "Data absensi belum tersedia untuk siswa ini.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Absensi Siswa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-green-50 min-h-screen">
<nav class="bg-green-500 p-4 text-white flex justify-between items-center">
  <h1 class="text-xl font-semibold">Absensi Siswa</h1>
  <a href="dashboardguru.php" class="bg-white text-green-600 px-4 py-1 rounded hover:bg-green-100">Kembali</a>
</nav>

<main class="p-6">
  <div class="bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-green-700 mb-4">📋 Absensi Harian</h2>
    <p class="text-gray-700 mb-4">Tanggal Hari Ini: <span class="font-semibold text-green-700"><?= $tanggal_ditampilkan ?></span></p>

    <button type="button" onclick="openModal()" class="ml-4 bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-xl transition">Edit Kehadiran</button>

    <?php if ($sukses): ?>
      <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4"><?= $sukses ?></div>
    <?php endif; ?>

    <form method="get" class="mb-6">
      <label for="kelas" class="block mb-2 font-medium text-gray-700">Pilih Kelas:</label>
      <select name="kelas" id="kelas" onchange="this.form.submit()" class="w-full md:w-1/2 px-3 py-2 border rounded">
        <option value="">-- Pilih Kelas --</option>
        <?php while ($kelas = mysqli_fetch_assoc($kelas_options)): ?>
          <option value="<?= $kelas['id_kelas'] ?>" <?= $kelas['id_kelas'] == $filter_kelas ? 'selected' : '' ?>>
            <?= $kelas['nama_kelas'] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </form>

    <?php if ($filter_kelas && $result && mysqli_num_rows($result) > 0): ?>
      <form method="post">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left border border-gray-200">
            <thead class="bg-green-100 text-green-900">
              <tr>
                <th class="p-3 border">#</th>
                <th class="p-3 border">Nama Siswa</th>
                <th class="p-3 border">Status Kehadiran</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; while ($siswa = mysqli_fetch_assoc($result)): ?>
                <tr class="hover:bg-green-50">
                  <td class="p-3 border"><?= $no++ ?></td>
                  <td class="p-3 border"><?= htmlspecialchars($siswa['nama_siswa']) ?></td>
                  <td class="p-3 border">
                    <div class="grid grid-cols-2 gap-2">
                      <?php foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'] as $val => $label): ?>
                        <label class="flex items-center space-x-2">
                          <input type="radio" name="status[<?= $siswa['id_siswa'] ?>]" value="<?= $val ?>" <?= $val === 'hadir' ? 'checked' : '' ?> required class="form-radio text-green-600">
                          <span><?= $label ?></span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <button type="submit" name="submit_absensi" class="mt-6 bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl transition">
          Simpan Absensi
        </button>
      </form>
    <?php elseif ($filter_kelas): ?>
      <div class="text-red-600 mt-4">Tidak ada siswa di kelas ini.</div>
    <?php endif; ?>
  </div>
</main>

<!-- Modal Edit Kehadiran -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-30 z-50 hidden items-center justify-center">
  <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
    <h2 class="text-xl font-bold mb-4 text-yellow-600">Edit Kehadiran </h2>
    <form method="post" class="space-y-4">
      <div>
        <label class="block font-medium mb-1">Pilih Kelas</label>
        <select id="kelasEdit" onchange="loadSiswa()" class="w-full border px-3 py-2 rounded">
          <option value="">-- Pilih Kelas --</option>
          <?php
          $kelas_list_modal = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");
          while ($k = mysqli_fetch_assoc($kelas_list_modal)):
          ?>
            <option value="<?= $k['id_kelas'] ?>"><?= $k['nama_kelas'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label class="block font-medium mb-1">Pilih Siswa</label>
        <select name="edit_siswa" id="siswaDropdown" class="w-full border px-3 py-2 rounded" required>
          <option value="">-- Pilih Siswa --</option>
        </select>
      </div>
      <div>
        <label class="block font-medium mb-1">Status Kehadiran</label>
        <div class="flex gap-4">
          <?php foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'] as $val => $label): ?>
            <label class="inline-flex items-center">
              <input type="radio" name="edit_status" value="<?= $val ?>" required class="form-radio text-yellow-600">
              <span class="ml-1"><?= $label ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="flex justify-end mt-4">
        <button type="button" onclick="closeModal()" class="mr-2 px-4 py-2 rounded border">Batal</button>
        <button type="submit" name="edit_kehadiran" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal() {
  document.getElementById('editModal').classList.remove('hidden');
  document.getElementById('editModal').classList.add('flex');
}
function closeModal() {
  document.getElementById('editModal').classList.add('hidden');
  document.getElementById('editModal').classList.remove('flex');
}
function loadSiswa() {
  const kelasId = document.getElementById('kelasEdit').value;
  const siswaDropdown = document.getElementById('siswaDropdown');
  siswaDropdown.innerHTML = '<option>Memuat...</option>';

  fetch(`get_siswa_by_kelas.php?id_kelas=${kelasId}`)
    .then(res => res.json())
    .then(data => {
      siswaDropdown.innerHTML = '<option value="">-- Pilih Siswa --</option>';
      data.forEach(siswa => {
        const opt = document.createElement('option');
        opt.value = siswa.id_siswa;
        opt.textContent = siswa.nama_siswa;
        siswaDropdown.appendChild(opt);
      });
    });
}
</script>
</body>
</html>
