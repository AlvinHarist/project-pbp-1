<?php
// login.php
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include 'includes/header.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST["email"] ?? '';
  $password = $_POST["password"] ?? '';

  $stmt = $conn->prepare('SELECT * FROM user WHERE Email = ? LIMIT 1');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res && $res->num_rows === 1) {
    $user = $res->fetch_assoc();
    $stored = $user['Password'] ?? '';
    $ok = false;
    // Normal case: stored is a password_hash() value
    if (!empty($stored) && password_verify($password, $stored)) {
      $ok = true;
    } else {
      // Legacy cases: stored value might be MD5 hash or even plaintext
      // If stored equals md5(entered password) or stored equals the plaintext password,
      // accept and upgrade to a secure password_hash()
      if (!empty($stored) && (md5($password) === $stored || $stored === $password)) {
        $ok = true;
        // Re-hash using password_hash and update DB
        $newHash = password_hash($password, PASSWORD_DEFAULT);
          // user id in this app appears to be a string (uniqid starting with 'U'),
          // so bind as string. Also trim stored value to avoid whitespace mismatches.
          $userid = isset($user['id']) ? $user['id'] : '';
          $ustmt = $conn->prepare('UPDATE user SET Password = ? WHERE id = ?');
          if ($ustmt) {
            $ustmt->bind_param('ss', $newHash, $userid);
          $ustmt->execute();
          $ustmt->close();
        }
      }
    }

    if ($ok) {
      // remove password before storing in session
      unset($user['Password']);
      $_SESSION['user'] = $user;
      if (($user['Role'] ?? '') === 'Admin') {
        header("Location: dashboardAdmin.php");
      } else {
        header("Location: DashboardPembeli.php");
      }
      exit;
    } else {
      $error = "Email atau password salah!";
    }
  } else {
    $error = "Email atau password salah!";
  }
  $stmt->close();
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

