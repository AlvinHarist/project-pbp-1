<?php
$host = "localhost";   // atau 127.0.0.1
$user = "root";        // sesuaikan dengan user MySQL kamu
$pass = "";            // password MySQL (default kosong di XAMPP)
$db   = "toko_buku";   // nama database

$conn = mysqli_connect($host, $user, $pass, $db);

// cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
