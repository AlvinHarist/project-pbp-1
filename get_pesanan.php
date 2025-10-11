<?php
include 'config.php';

$range = isset($_GET['range']) ? $_GET['range'] : 'all';

if ($range === 'all') {
  $sql = "SELECT id, Tanggal, status, Total_harga, id_user, id_buku 
          FROM transaksi
          ORDER BY Tanggal DESC";
} else {
  $range = intval($range);
  $sql = "SELECT id, Tanggal, status, Total_harga, id_user, id_buku 
          FROM transaksi
          WHERE Tanggal >= DATE_SUB(CURDATE(), INTERVAL $range DAY)
          ORDER BY Tanggal DESC";
}

$result = $conn->query($sql);
$pesanan = [];

while ($row = $result->fetch_assoc()) {
  $pesanan[] = $row;
}

echo json_encode($pesanan);
?>