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
    if (!empty($stored) && password_verify($password, $stored)) {
      $ok = true;
    } elseif ($stored === $password) {
      // legacy plaintext match
      $ok = true;
      // optionally rehash and update DB
      $newHash = password_hash($password, PASSWORD_DEFAULT);
      $ustmt = $conn->prepare('UPDATE user SET Password = ? WHERE id = ?');
      $ustmt->bind_param('ss', $newHash, $user['id']);
      $ustmt->execute();
      $ustmt->close();
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

