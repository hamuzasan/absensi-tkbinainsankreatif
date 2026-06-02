<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['id_guru']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$success = "";
$error = "";

if (isset($_POST['register'])) {
    $nama = $_POST['nama_guru'];
    $username = $_POST['username'];
    $no_hp = $_POST['no_hp_guru'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Cek apakah username sudah terdaftar
    $cek = mysqli_query($conn, "SELECT * FROM guru WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan. Silakan pilih yang lain.";
    } else {
        // Simpan data ke tabel guru
        $query = "INSERT INTO guru (nama_guru, username, no_hp_guru, password, role) 
                  VALUES ('$nama', '$username', '$no_hp', '$password', '$role')";
        if (mysqli_query($conn, $query)) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Registrasi gagal. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Guru/Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-green-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-semibold text-center mb-6 text-green-700">Registrasi Guru/Admin</h2>

        <?php if ($success) : ?>
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if ($error) : ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label class="block mb-2">Nama Lengkap:</label>
            <input type="text" name="nama_guru" required class="w-full px-3 py-2 mb-4 border rounded">

            <label class="block mb-2">Username:</label>
            <input type="text" name="username" required class="w-full px-3 py-2 mb-4 border rounded">

            <label class="block mb-2">Nomor HP:</label>
            <input type="text" name="no_hp_guru" required class="w-full px-3 py-2 mb-4 border rounded">

            <label class="block mb-2">Password:</label>
            <input type="password" name="password" required class="w-full px-3 py-2 mb-4 border rounded">

            <label class="block mb-2">Role:</label>
            <select name="role" class="w-full px-3 py-2 mb-6 border rounded">
                <option value="guru">Guru</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit" name="register" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-xl font-semibold">Daftar</button>
        </form>

        <p class="text-center mt-4 text-sm">
            Sudah punya akun? <a href="../login.php" class="text-green-600 hover:underline">Login di sini</a>
        </p>
    </div>
</body>
</html>
