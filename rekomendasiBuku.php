<?php include "config.php";
include "includes/header2.php";

$books = [];
$query = "SELECT dt.ID_Buku, b.judul, b.penulis, dt.harga_satuan, SUM(dt.Jumlah) AS total_terjual
          FROM detail_transaksi dt
          JOIN buku b ON dt.ID_Buku COLLATE utf8mb4_unicode_ci = b.ID COLLATE utf8mb4_unicode_ci
          GROUP BY dt.ID_Buku, b.judul, dt.harga_satuan
          ORDER BY total_terjual DESC
          LIMIT 10";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($result)) {
    $books[] = $row; 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BukaBuku</title>
  <link rel="stylesheet" href="css/rekomendasiBuku.css"> 
</head>
<body>
    <?php foreach ($books as $book): ?>
    <div class="book-card">
        <div class="book-info">
            <h3><?= $book['judul'] ?></h3>
            <p>Penulis: <?= $book['penulis'] ?></p>
            <p>Harga: Rp <?= number_format($book['harga_satuan'], 0, ',', '.') ?></p>
            <p>Total Terjual: <?= $book['total_terjual'] ?></p>
            <a href="detailproduk.php?id=<?= $book['ID_Buku'] ?>">Detail Produk</a>
        </div>
        <div>
        <img src="images/buku.jpeg" alt="Gambar Buku" class="book-image">
        </div>
    </div>
    <?php endforeach; ?>

    <div class="judul-section">
        <h2>BukaBuku</h2>
        <h4>Rekomendasi Buku</h4>
    </div>
</body>
</html>
