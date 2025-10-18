<?php include "config.php"?>
<?php include 'includes/headerAdmin.php'; ?>


<?php 
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['user']['Role'] !== 'Admin') {
    header("Location: dashboardAdmin.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BukaBuku</title>
    <!-- FONT Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS lokal -->
    <link rel="stylesheet" href="css/dashboardAdmin.css">

</head>

<body>
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Selamat Datang di <br>Dashboard Admin</h1>
                    <p>Pantau aktivitas dan statistik BukaBuku secara real-time</p>
                </div>
                <div class="hero-buttons">
                    <a href="manajemenProduk.php" class="btn btn-primary">Manajemen Produk</a>
                    <a href="manajemenPesanan.php" class="btn btn-secondary">Manajemen Pesanan</a>
                </div>
                <div class="hero-image">
                    <img src="images/keajaiban-toko-kelontong-namiya.jpg" alt="Beautiful library with many books" class="book1" >
                    <img src="images/B001.jpg" alt="Laut Bercerita" class="book2" >
                    <img src="images/B002.jpg" alt="Cantik itu Luka" class="book3">
                    <img src="images/B003.jpg" alt="Luka Cita" class="book4">
                </div>
            </div>
        </div>
    </section>
</body>
</html>