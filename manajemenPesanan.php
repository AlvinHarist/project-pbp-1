<?php include "config.php";?>
<?php include 'includes/headerAdmin.php';?>

<?php
// Query total pendapatan
$query = "SELECT SUM(Total_harga) AS total_pendapatan 
          FROM transaksi 
          WHERE Status = 'Selesai' 
          AND DATE(Tanggal) = CURDATE()";

$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $totalPendapatan = $row['total_pendapatan'] ?? 0;
} else {
    $totalPendapatan = 0;
}

// Query total yang sedang diproses
$query2 = "SELECT COUNT(*) AS totalDiProses 
FROM transaksi 
WHERE Status = 'Pending'";

$result2 = $conn->query($query2);

if ($result2 && $row2 = $result2->fetch_assoc()) {
    $totalDiProses = $row2['totalDiProses'] ?? 0;
} else {
    $totalDiProses = 0;
}

// Query total yang sedang dikirim
$query3 = "SELECT COUNT(*) AS totalDiKirim 
FROM transaksi 
WHERE Status = 'Dikirim'";

$result3 = $conn->query($query3);

if ($result3 && $row3 = $result3->fetch_assoc()) {
    $totalDiKirim = $row3['totalDiKirim'] ?? 0;
} else {
    $totalDiKirim = 0;
}

// Query total yang sedang dibayar
$query4 = "SELECT COUNT(*) AS totalDiBayar 
FROM transaksi 
WHERE Status = 'DiBayar'";

$result4 = $conn->query($query4);

if ($result4 && $row4 = $result4->fetch_assoc()) {
    $totalDiBayar = $row4['totalDiBayar'] ?? 0;
} else {
    $totalDiBayar = 0;
}

// Query total yang sedang selesai
$query5 = "SELECT COUNT(*) AS totalSelesai 
FROM transaksi 
WHERE Status = 'Selesai'";

$result5 = $conn->query($query5);

if ($result5 && $row5 = $result5->fetch_assoc()) {
    $totalSelesai = $row5['totalSelesai'] ?? 0;
} else {
    $totalSelesai = 0;
}

// Query total pesanan
$query6 = "SELECT COUNT(*) AS totalPesanan 
FROM transaksi";

$result6 = $conn->query($query6);

if ($result6 && $row6 = $result6->fetch_assoc()) {
    $totalPesanan = $row6['totalPesanan'] ?? 0;
} else {
    $totalPesanan = 0;
}

// Tutup koneksi di paling akhir
$conn->close();
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BukaBuku</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/manajemenPesanan.css">
</head>
<body>

  <section class="hero">
    <div class="container">
      <div class="hero-content">
        <div class="hero-text">
          <h1>Manajemen Pesanan</h1>
          <p>Kelola dan pantau semua pesanan dari dashboard admin</p>
        </div>

        <div class="hero-status"> 
          <div class="status-container"> 
            <span class="status status1">
              <div class="judul-status">Diproses</div>
              <div class="nilai-status"><?php echo $totalDiProses; ?></div>
            </span>

            <span class="status status2">
              <div class="judul-status">Dikirim</div>
              <div class="nilai-status"><?php echo $totalDiKirim; ?></div>
            </span>

            <span class="status status3">
              <div class="judul-status">Dibayar</div>
              <div class="nilai-status"><?php echo $totalDiBayar; ?></div>
            </span>

            <span class="status status4">
              <div class="judul-status">Selesai</div>
              <div class="nilai-status"><?php echo $totalSelesai; ?></div>
            </span>
          </div> 
        </div>

        <div class="total-container">
          <div class="judul-total">Total Pendapatan</div>
          <div class="nilai-total">Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></div>
        </div>

        <div class="hero-pesanan">
          <div class="pesanan-container">
            <div class="rentang-waktu">
              <p class="judul-pesanan">Daftar Pesanan</p>
              <p class="jumlah-pesanan">Menampilkan <?php echo $totalPesanan; ?> pesanan</p>

              <div class="rentang-btn-container">
                <button class="rentang-btn" data-range="1">1 Hari</button>
                <button class="rentang-btn" data-range="7">7 Hari</button>
                <button class="rentang-btn" data-range="30">1 Bulan</button>
              </div>
            </div>

            <div id="daftar-pesanan"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <script src="manajemenpesanan.js"></script>
</body>
</html>
