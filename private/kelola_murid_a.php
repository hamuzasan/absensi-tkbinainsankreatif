<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['id_guru']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$sukses = "";
$error = "";

// Tambah siswa baru
if (isset($_POST['tambah_siswa'])) {
    $nama = $_POST['nama_siswa'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $id_kelas = $_POST['id_kelas'];
    $no_hp_ortu = $_POST['no_hp_ortu'];

    $query = "INSERT INTO siswa (nama_siswa, tgl_lahir, id_kelas, no_hp_ortu) 
              VALUES ('$nama', '$tgl_lahir', '$id_kelas', '$no_hp_ortu')";
    if (mysqli_query($conn, $query)) {
        $sukses = "Siswa berhasil ditambahkan.";
    } else {
        $error = "Gagal menambahkan siswa.";
    }
}

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
    header("Location: kelola_murid_a.php");
    exit;
}

// Pindahkan siswa ke kelas baru
if (isset($_POST['pindahkan_kelas'])) {
    $kelas_asal = $_POST['kelas_asal'];
    $kelas_tujuan = $_POST['kelas_tujuan'];
    mysqli_query($conn, "UPDATE siswa SET id_kelas = '$kelas_tujuan' WHERE id_kelas = '$kelas_asal'");
    $sukses = "Semua siswa berhasil dipindahkan.";
}

// Luluskan siswa (hapus berdasarkan kelas)
if (isset($_POST['luluskan_kelas'])) {
    $kelas_lulus = $_POST['kelas_lulus'];
    mysqli_query($conn, "DELETE FROM siswa WHERE id_kelas = '$kelas_lulus'");
    $sukses = "Semua siswa dari kelas tersebut telah diluluskan.";
}

// Ambil data kelas dan siswa
$kelas_list = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");

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

</head>
<body class="bg-green-50 min-h-screen">
  <nav class="bg-green-600 p-4 text-white flex justify-between items-center">
    <h1 class="text-xl font-semibold">Kelola Murid</h1>
    <a href="dashboardadmin.php" class="bg-white text-green-600 px-4 py-1 rounded hover:bg-green-100">Kembali</a>
  </nav>

  <main class="p-6 space-y-10">
    <?php if ($sukses) : ?><div class="bg-green-100 text-green-800 px-4 py-2 rounded"><?= $sukses ?></div><?php endif; ?>
    <?php if ($error) : ?><div class="bg-red-100 text-red-800 px-4 py-2 rounded"><?= $error ?></div><?php endif; ?>

    <!-- Form Tambah Siswa -->
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="text-xl font-bold text-green-700 mb-4">👶 Tambah Murid</h2>
      <form method="post" class="grid md:grid-cols-2 gap-4">
        <input type="text" name="nama_siswa" required placeholder="Nama Siswa" class="border px-3 py-2 rounded">
        <input type="date" name="tgl_lahir" required class="border px-3 py-2 rounded">
        <select name="id_kelas" required class="border px-3 py-2 rounded">
          <option value="">-- Pilih Kelas --</option>
          <?php
          mysqli_data_seek($kelas_list, 0);
          while ($k = mysqli_fetch_assoc($kelas_list)):
          ?>
            <option value="<?= $k['id_kelas'] ?>"><?= $k['nama_kelas'] ?></option>
          <?php endwhile; ?>
        </select>
        <input type="text" name="no_hp_ortu" required placeholder="No HP Ortu" class="border px-3 py-2 rounded">
        <div class="md:col-span-2">
          <button name="tambah_siswa" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl">Tambah Murid</button>
        </div>
      </form>
    </div>

    <!-- Form Pindah Kelas -->
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="text-xl font-bold text-green-700 mb-4">🔁 Pindahkan Murid</h2>
      <form method="post" class="grid md:grid-cols-2 gap-4">
        <select name="kelas_asal" required class="border px-3 py-2 rounded">
          <option value="">-- Dari Kelas --</option>
          <?php
          mysqli_data_seek($kelas_list, 0);
          while ($k = mysqli_fetch_assoc($kelas_list)):
          ?>
            <option value="<?= $k['id_kelas'] ?>"><?= $k['nama_kelas'] ?></option>
          <?php endwhile; ?>
        </select>
        <select name="kelas_tujuan" required class="border px-3 py-2 rounded">
          <option value="">-- Ke Kelas --</option>
          <?php
          mysqli_data_seek($kelas_list, 0);
          while ($k = mysqli_fetch_assoc($kelas_list)):
          ?>
            <option value="<?= $k['id_kelas'] ?>"><?= $k['nama_kelas'] ?></option>
          <?php endwhile; ?>
        </select>
        <div class="md:col-span-2">
          <button name="pindahkan_kelas" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-xl">Pindahkan</button>
        </div>
      </form>
    </div>

    <!-- Form Luluskan Kelas -->
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="text-xl font-bold text-green-700 mb-4">🎓 Luluskan Kelas</h2>
      <form method="post" class="flex flex-col md:flex-row gap-4 items-end">
        <select name="kelas_lulus" required class="border px-3 py-2 rounded w-full">
          <option value="">-- Pilih Kelas --</option>
          <?php
          mysqli_data_seek($kelas_list, 0);
          while ($k = mysqli_fetch_assoc($kelas_list)):
          ?>
            <option value="<?= $k['id_kelas'] ?>"><?= $k['nama_kelas'] ?></option>
          <?php endwhile; ?>
        </select>
        <button name="luluskan_kelas" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-xl">Luluskan</button>
      </form>
    </div>

    <!-- Tabel Murid -->
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="text-xl font-bold text-green-700 mb-4">📚 Daftar Murid</h2>
      <?php foreach ($siswa_data as $nama_kelas => $data): ?>
        <h3 class="text-lg font-semibold text-green-600 mt-6 mb-2"><?= $nama_kelas ?></h3>
        <div class="overflow-x-auto mb-4">
          <table class="w-full text-sm text-left border">
            <thead class="bg-green-100 text-green-900">
              <tr>
                <th class="p-2 border">#</th>
                <th class="p-2 border">Nama</th>
                <th class="p-2 border">Tanggal Lahir</th>
                <th class="p-2 border">No HP Ortu</th>
                <th class="p-2 border">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no=1; while ($s = mysqli_fetch_assoc($data)): ?>
                <tr class="hover:bg-green-50">
                  <td class="p-2 border"><?= $no++ ?></td>
                  <td class="p-2 border"><?= htmlspecialchars($s['nama_siswa']) ?></td>
                  <td class="p-2 border"><?= $s['tgl_lahir'] ?></td>
                  <td class="p-2 border" id="hp_field_<?= $s['id_siswa'] ?>">
                    <span><?= htmlspecialchars($s['no_hp_ortu']) ?></span>
                  </td>
                  <td class="p-2 border">
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
        </form>`;
    }
  </script>
</body>
</html>
