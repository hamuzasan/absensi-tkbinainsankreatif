<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TK Bina Insan Kreatif</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body class="bg-green-50 text-gray-800">
  <!-- Hero Section -->
  <header class="bg-green-600 text-white py-16 px-6 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">Selamat Datang di TK Bina Insan Kreatif</h1>
    <p class="mt-4 text-lg md:text-xl">Belajar, Bermain, dan Bertumbuh Bersama 👦👧</p>
    <a href="login.php" class="inline-block mt-6 px-6 py-3 bg-white text-green-600 font-semibold rounded-full shadow hover:bg-green-100 transition">Masuk Aplikasi</a>
  </header>

  <!-- Features Section -->
  <section class="py-12 px-6 max-w-6xl mx-auto">
    <h2 class="text-3xl font-bold text-center text-green-700 mb-10">Kenapa Memilih Kami?</h2>
    <div class="grid md:grid-cols-3 gap-8">
      <div class="bg-white rounded-xl shadow p-6 text-center border border-green-100">
        <img src="https://img.icons8.com/color/96/teacher.png" alt="Guru Berpengalaman" class="mx-auto mb-4">
        <h3 class="font-bold text-lg">Guru Berpengalaman</h3>
        <p class="text-sm text-gray-600">Tenaga pendidik profesional yang sabar dan berdedikasi.</p>
      </div>
      <div class="bg-white rounded-xl shadow p-6 text-center border border-green-100">
        <img src="https://img.icons8.com/color/96/abc.png" alt="Kurikulum Kreatif" class="mx-auto mb-4">
        <h3 class="font-bold text-lg">Kurikulum Kreatif</h3>
        <p class="text-sm text-gray-600">Menggabungkan bermain dan belajar untuk perkembangan optimal anak.</p>
      </div>
      <div class="bg-white rounded-xl shadow p-6 text-center border border-green-100">
        <img src="https://img.icons8.com/color/96/classroom.png" alt="Lingkungan Nyaman" class="mx-auto mb-4">
        <h3 class="font-bold text-lg">Lingkungan Nyaman</h3>
        <p class="text-sm text-gray-600">Ruang belajar yang aman, bersih, dan menyenangkan.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-green-600 text-white py-6 text-center mt-12">
    <p>&copy; <?= date('Y') ?> TK Bina Insan Kreatif. All rights reserved.</p>
  </footer>
</body>
</html>
