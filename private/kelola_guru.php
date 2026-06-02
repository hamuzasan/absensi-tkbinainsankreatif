<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['id_guru']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$sukses = "";
$error = "";

// Tambah Guru
if (isset($_POST['tambah_guru'])) {
    $nama_guru = $_POST['nama_guru'];
    $username = $_POST['username'];
    $no_hp = $_POST['no_hp'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Termasuk guruPaud

    // Catatan: Sebaiknya gunakan mysqli_real_escape_string untuk keamanan
    $nama_guru = mysqli_real_escape_string($conn, $nama_guru);
    $username = mysqli_real_escape_string($conn, $username);
    $no_hp = mysqli_real_escape_string($conn, $no_hp);
    $password = mysqli_real_escape_string($conn, $password);
    $role = mysqli_real_escape_string($conn, $role);

    $cek = mysqli_query($conn, "SELECT * FROM guru WHERE username = '$username'");
    if (mysqli_num_rows($cek) == 0) {
        $query = "INSERT INTO guru (nama_guru, username, no_hp_guru, password, role) VALUES ('$nama_guru', '$username', '$no_hp', '$password', '$role')";
        if (mysqli_query($conn, $query)) {
            $sukses = "Guru berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan guru: " . mysqli_error($conn);
        }
    } else {
        $error = "Username sudah digunakan.";
    }
}

// Hapus Guru
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM guru WHERE id_guru = '$id'");
    // Menggunakan header tanpa parameter sukses/error agar proses hapus bersih
    header("Location: kelola_guru.php"); 
    exit;
}

// Update Guru
if (isset($_POST['edit_guru'])) {
    $id = $_POST['id_guru'];
    $username = $_POST['username'];
    $no_hp = $_POST['no_hp'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Role baru

    // Catatan: Sebaiknya gunakan mysqli_real_escape_string untuk keamanan
    $id = mysqli_real_escape_string($conn, $id);
    $username = mysqli_real_escape_string($conn, $username);
    $no_hp = mysqli_real_escape_string($conn, $no_hp);
    $password = mysqli_real_escape_string($conn, $password);
    $role = mysqli_real_escape_string($conn, $role);

    // Query UPDATE kini mencakup ROLE
    $update = mysqli_query($conn, "UPDATE guru SET username='$username', no_hp_guru='$no_hp', password='$password', role='$role' WHERE id_guru='$id'");
    if ($update) {
        $sukses = "Data guru berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($conn);
    }
}

$guru_result = mysqli_query($conn, "SELECT * FROM guru ORDER BY nama_guru");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      function enableEdit(id) {
        const row = document.getElementById('row-' + id);
        // Mengaktifkan Input Teks (username, no_hp, password)
        const inputs = row.querySelectorAll('input[type="text"]');
        inputs.forEach(input => {
            input.removeAttribute('readonly');
            input.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-default');
            input.classList.add('bg-white', 'text-gray-900', 'border-blue-400');
        });
        
        // Mengaktifkan Input Select (role)
        const select = row.querySelector('select[name="role"]');
        if (select) {
            select.removeAttribute('disabled');
            select.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-default');
            select.classList.add('bg-white', 'text-gray-900', 'border-blue-400');
        }

        // Menampilkan tombol Simpan
        row.querySelector('button[name="edit_guru"]').classList.remove('hidden');
        // Menyembunyikan tombol Edit
        row.querySelector('.edit-btn').classList.add('hidden');
      }
    </script>
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="bg-green-600 p-4 text-white flex justify-between items-center">
        <h1 class="text-xl font-bold">Kelola Guru</h1>
        <a href="dashboardadmin.php" class="bg-white text-green-600 px-4 py-1 rounded-lg hover:bg-green-100 font-medium transition duration-150">Kembali</a>
    </nav>

    <main class="p-6 max-w-5xl mx-auto">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-green-700 mb-6">Tambah Guru Baru</h2>

            <?php if ($sukses): ?><div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg mb-4 border border-green-300"><?= $sukses ?></div><?php endif; ?>
            <?php if ($error): ?><div class="bg-red-100 text-red-800 px-4 py-3 rounded-lg mb-4 border border-red-300"><?= $error ?></div><?php endif; ?>

            <form method="post" class="grid md:grid-cols-3 gap-4">
                <input type="text" name="nama_guru" placeholder="Nama Guru" class="border rounded-lg px-3 py-2.5 focus:ring-green-500 focus:border-green-500" required>
                <input type="text" name="username" placeholder="Username" class="border rounded-lg px-3 py-2.5 focus:ring-green-500 focus:border-green-500" required>
                <input type="text" name="no_hp" placeholder="Nomor HP" class="border rounded-lg px-3 py-2.5 focus:ring-green-500 focus:border-green-500" required>
                <input type="text" name="password" placeholder="Password" class="border rounded-lg px-3 py-2.5 focus:ring-green-500 focus:border-green-500" required>
                <select name="role" class="border rounded-lg px-3 py-2.5 bg-white focus:ring-green-500 focus:border-green-500" required>
                    <option value="guru">Guru SD</option>
                    <option value="guruPaud">Guru PAUD</option> <!-- Role Baru -->
                    <option value="admin">Admin</option>
                </select>
                <div class="md:col-span-1">
                    <button type="submit" name="tambah_guru" class="w-full bg-green-600 text-white px-6 py-2.5 rounded-lg hover:bg-green-700 transition font-medium">Tambah Guru</button>
                </div>
            </form>
        </div>

        <div class="bg-white mt-8 p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-green-700 mb-6">Daftar Guru</h2>
            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="bg-green-100 text-green-800">
                        <tr>
                            <th class="p-3 text-left">#</th>
                            <th class="p-3 text-left">Nama</th>
                            <th class="p-3 text-left">Username</th>
                            <th class="p-3 text-left">Nomor HP</th>
                            <th class="p-3 text-left">Password</th>
                            <th class="p-3 text-left">Role</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $no = 1; while ($guru = mysqli_fetch_assoc($guru_result)): ?>
                            <tr id="row-<?= $guru['id_guru'] ?>" class="hover:bg-green-50">
                                <form method="post">
                                    <td class="p-3"><?= $no++ ?></td>
                                    <td class="p-3 font-medium text-gray-700"><?= htmlspecialchars($guru['nama_guru']) ?></td>
                                    
                                    <!-- Field Username -->
                                    <td class="p-3">
                                        <input type="hidden" name="id_guru" value="<?= $guru['id_guru'] ?>">
                                        <input type="text" name="username" value="<?= htmlspecialchars($guru['username']) ?>" readonly class="border rounded px-2 py-1 w-28 bg-gray-100 text-gray-500 cursor-default">
                                    </td>
                                    
                                    <!-- Field Nomor HP -->
                                    <td class="p-3">
                                        <input type="text" name="no_hp" value="<?= htmlspecialchars($guru['no_hp_guru']) ?>" readonly class="border rounded px-2 py-1 w-28 bg-gray-100 text-gray-500 cursor-default">
                                    </td>
                                    
                                    <!-- Field Password -->
                                    <td class="p-3">
                                        <input type="text" name="password" value="<?= htmlspecialchars($guru['password']) ?>" readonly class="border rounded px-2 py-1 w-28 bg-gray-100 text-gray-500 cursor-default">
                                    </td>
                                    
                                    <!-- Field Role (SELECT BARU) -->
                                    <td class="p-3">
                                        <select name="role" disabled class="border rounded px-2 py-1 w-full capitalize bg-gray-100 text-gray-500 cursor-default">
                                            <option value="guru" <?= $guru['role'] == 'guru' ? 'selected' : '' ?>>Guru SD</option>
                                            <option value="guruPaud" <?= $guru['role'] == 'guruPaud' ? 'selected' : '' ?>>Guru PAUD</option>
                                            <option value="admin" <?= $guru['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </td>
                                    
                                    <!-- Aksi -->
                                    <td class="p-3 text-center w-24">
                                        <div class="flex flex-col gap-1 items-center">
                                            <button type="button" onclick="enableEdit(<?= $guru['id_guru'] ?>)" class="text-blue-600 hover:text-blue-800 transition font-medium edit-btn">Edit</button>
                                            <button type="submit" name="edit_guru" class="bg-green-500 text-white px-3 py-0.5 rounded-lg hover:bg-green-600 transition hidden text-xs">Simpan</button>
                                            <a href="kelola_guru.php?hapus=<?= $guru['id_guru'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus guru <?= htmlspecialchars($guru['nama_guru']) ?>?')" class="text-red-500 hover:text-red-700 transition font-medium text-xs">Hapus</a>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
