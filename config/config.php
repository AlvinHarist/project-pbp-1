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

// small helper autoloader for app classes (models/controllers)
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/models/' . $class . '.php',
        __DIR__ . '/../app/controllers/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

?>
