<?php include "includes/header.php"; ?>

<?php
// login.php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM user WHERE Email='$email' AND Password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        $_SESSION['user'] = $user; // simpan semua data user di session
        // echo "Role kamu adalah: " . $_SESSION['user']['Role'];

        // cek role
        if ($user['Role'] === 'Admin') {
          
          header("Location: dashboardAdmin.php");
        } else {
          header("Location: dashboardAdmin.php");
        }
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - BukaBuku</title>
  <link rel="stylesheet" href="css/style.css"> <!-- umum -->
  <link rel="stylesheet" href="css/login.css"> <!-- khusus login -->
</head>
<body>

  <!-- <header class="navbar">
    <div class="logo">BukaBuku</div>
    <nav>
      <a href="#">Kategori</a>
      <a href="#">Bestsellers</a>
      <a href="#">Terbitan Baru</a>
      <a href="#">About</a>
    </nav>
    <div class="search-bar">
      <input type="text" placeholder="Cari buku, penulis, genre...">
    </div>
  </header>--> 
  


  <main class="login-wrapper">
    <div class="login-card">
      <h2 class="login-title">Masuk</h2>
      <p class="login-subtitle">Masuk ke akun anda untuk mengakses BukaBuku</p>

      <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>

      <form method="POST">
        <label class="login-label">Email</label>
        <input type="email" name="email" class="login-input" required>

        <label class="login-label">Password</label>
        <input type="password" name="password" class="login-input" required>

        <div class="remember">
          <input type="checkbox" id="remember">
          <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="login-button">Masuk</button>
      </form>
    </div>
  </main>
</body>
</html>

