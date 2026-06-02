<?php
session_start();
include './config/database.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query berdasarkan username sekarang
    $query = "SELECT * FROM guru WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $_SESSION['id_guru'] = $user['id_guru'];
        $_SESSION['nama_guru'] = $user['nama_guru'];
        $_SESSION['role'] = $user['role'];

        $id_guru = $user['id_guru'];
        $aktivitas = "Login ke sistem";
        $tanggal = date('Y-m-d H:i:s');
        mysqli_query($conn, "INSERT INTO aksi (id_guru, tanggal_aktivitas, aktivitas) VALUES ('$id_guru', '$tanggal', '$aktivitas')");

        if ($user['role'] == 'admin') {
            header("Location: ./private/dashboardadmin.php");
        } else {
            header("Location: ./private/dashboardguru.php");
        }
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Absensi TK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-100 to-green-200 min-h-screen flex items-center justify-center px-4">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-green-700">Selamat Datang</h2>
            <p class="text-sm text-gray-500">Silakan login untuk melanjutkan</p>
        </div>

        <?php if (isset($error)) : ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-sm border border-red-300">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-5">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" required placeholder="Masukkan username"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 border-gray-300">
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required placeholder="Masukkan password"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 border-gray-300">
            </div>

            <button type="submit" name="login"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-xl font-semibold transition duration-200">
                Login
            </button>
        </form>


    </div>
</body>
</html>
