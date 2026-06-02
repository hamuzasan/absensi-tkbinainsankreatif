<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['id_guru']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$sukses = "";
$error = "";

// Update nomor HP siswa
if (isset($_POST['edit_hp'])) {
    $id_siswa = $_POST['id_siswa'];
    $no_hp_baru = $_POST['no_hp_ortu'];
    $update = mysqli_query($conn, "UPDATE siswa SET no_hp_ortu = '$no_hp_baru' WHERE id_siswa = '$id_siswa'");
    $sukses = $update ? "Nomor HP berhasil diperbarui!" : "Gagal memperbarui nomor HP.";
}

// Hapus siswa
if (isset($_GET['hapus_siswa'])) {
    $id_siswa = $_GET['hapus_siswa'];
    mysqli_query($conn, "DELETE FROM siswa WHERE id_siswa = '$id_siswa'");
    header("Location: kelola_murid.php");
    exit;
}

// Pindahkan siswa ke kelas baru
if (isset($_POST['pindahkan_kelas'])) {
    $kelas_asal = $_POST['kelas_asal'];
    $kelas_tujuan = $_POST['kelas_tujuan'];
    mysqli_query($conn, "UPDATE siswa SET id_kelas = '$kelas_tujuan' WHERE id_kelas = '$kelas_asal'");
    $sukses = "Semua siswa berhasil dipindahkan ke kelas baru.";
}

// Luluskan siswa (hapus berdasarkan kelas)
if (isset($_POST['luluskan_kelas'])) {
    $kelas_lulus = $_POST['kelas_lulus'];
    mysqli_query($conn, "DELETE FROM siswa WHERE id_kelas = '$kelas_lulus'");
    $sukses = "Semua siswa dari kelas tersebut telah diluluskan.";
}

// Ambil semua kelas
$kelas_list = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");

// Ambil daftar siswa per kelas
$siswa_data = [];
$kelas_query = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");
while ($k = mysqli_fetch_assoc($kelas_query)) {
    $id_kelas = $k['id_kelas'];
    $kelas_nama = $k['nama_kelas'];
    $siswa_result = mysqli_query($conn, "
        SELECT siswa.*, kelas.nama_kelas 
        FROM siswa 
        JOIN kelas ON kelas.id_kelas = siswa.id_kelas 
        WHERE kelas.id_kelas = $id_kelas
        ORDER BY siswa.nama_siswa
    ");
    $siswa_data[$kelas_nama] = $siswa_result;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Murid</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-green-50 min-h-screen">
  <nav class="bg-green-500 p-4 text-white flex justify-between items-center">
    <h1 class="text-xl font-semibold">Kelola Murid</h1>
    <a href="dashboardadmin.php" class="bg-white text-green-600 px-4 py-1 rounded hover:bg-green-100">Kembali</a>
  </nav>

  <main class="p-6 space-y-10">
    <?php if ($sukses) : ?>
      <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4"><?= $sukses ?></div>
    <?php endif; ?>

    <!-- Form Luluskan Kelas -->
    <div class="bg-white p-6 rounded-xl shadow-md">
      <h2 class="text-xl font-bold text-green-700 mb-4">🎓 Luluskan Siswa</h2>
      <form method="post" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1">
          <label class="block mb-1 font-medium">Pilih Kelas yang Akan Diluluskan</label>
          <select name="kelas_lulus" required class="w-full px-3 py-2 border rounded">
            <option value="">-- Pilih Kelas --</option>
            <?php
            $kelas_opsi = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");
            while ($k = mysqli_fetch_assoc($kelas_opsi)) :
            ?>
              <option value="<?= $k['id_kelas'] ?>"><?= $k['nama_kelas'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <button type="submit" name="luluskan_kelas" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-xl">
          Luluskan Siswa
        </button>
      </form>
    </div>

    <!-- Tabel Murid -->
    <div class="bg-white p-6 rounded-xl shadow-md">
      <h2 class="text-xl font-bold text-green-700 mb-4">📚 Daftar Murid</h2>

      <?php foreach ($siswa_data as $nama_kelas => $data): ?>
        <h3 class="text-lg font-semibold text-green-600 mt-6 mb-2"><?= $nama_kelas ?></h3>
        <div class="overflow-x-auto mb-6">
          <table class="w-full text-sm text-left border border-gray-200">
            <thead class="bg-green-100 text-green-900">
              <tr>
                <th class="p-3 border">#</th>
                <th class="p-3 border">Nama</th>
                <th class="p-3 border">Tanggal Lahir</th>
                <th class="p-3 border">No HP Ortu</th>
                <th class="p-3 border">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no=1; while ($s = mysqli_fetch_assoc($data)): ?>
                <tr class="hover:bg-green-50">
                  <td class="p-3 border"><?= $no++ ?></td>
                  <td class="p-3 border"><?= htmlspecialchars($s['nama_siswa']) ?></td>
                  <td class="p-3 border"><?= $s['tgl_lahir'] ?></td>
                  <td class="p-3 border" id="hp_field_<?= $s['id_siswa'] ?>">
                    <span><?= htmlspecialchars($s['no_hp_ortu']) ?></span>
                  </td>
                  <td class="p-3 border">
                    <button onclick="toggleEdit(<?= $s['id_siswa'] ?>, '<?= $s['no_hp_ortu'] ?>')" class="text-blue-600 text-sm hover:underline">Edit</button>
                    <a href="?hapus_siswa=<?= $s['id_siswa'] ?>" onclick="return confirm('Hapus murid ini?')" class="text-red-600 text-sm hover:underline ml-2">Hapus</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <script>
    function toggleEdit(id, currentHp) {
      const td = document.getElementById('hp_field_' + id);
      td.innerHTML = `
        <form method="post" class="flex items-center gap-2">
          <input type="hidden" name="id_siswa" value="${id}">
          <input type="text" name="no_hp_ortu" value="${currentHp}" class="border px-2 py-1 rounded w-full">
          <button type="submit" name="edit_hp" class="text-sm text-blue-600 hover:underline">Simpan</button>
        </form>
      `;
    }
  </script>
</body>
</html>
