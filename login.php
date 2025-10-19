<?php
// login.php
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include 'includes/header2.php';


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

  


  <main class="login-wrapper">
    <div class="login-card">
      <h2 class="login-title">Masuk</h2>
      <p class="login-subtitle">Masuk ke akun anda untuk mengakses BukaBuku</p>

      <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>

      <form method="POST">
        <label class="login-label">Email</label>
        <input type="email" name="email" class="login-input" required>

        <label class="login-label">Password</label>
        <div style="position:relative; display:flex; align-items:center;">
          <input type="password" id="password" name="password" class="login-input" required style="padding-right:38px;">
          <button type="button" id="togglePassword" aria-label="Toggle password visibility" style="position:absolute; right:6px; background:transparent; border:none; cursor:pointer; padding:6px;">
            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.79 21.79 0 0 1 5-5.94"></path><path d="M1 1l22 22"></path></svg>
          </button>
        </div>

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
<script>
document.addEventListener('DOMContentLoaded', function(){
  var pwd = document.getElementById('password');
  var btn = document.getElementById('togglePassword');
  var eyeOpen = document.getElementById('eyeOpen');
  var eyeClosed = document.getElementById('eyeClosed');
  if (!pwd || !btn) return;
  btn.addEventListener('click', function(e){
    e.preventDefault();
    if (pwd.type === 'password') {
      pwd.type = 'text';
      if (eyeOpen) eyeOpen.style.display = 'none';
      if (eyeClosed) eyeClosed.style.display = 'inline';
    } else {
      pwd.type = 'password';
      if (eyeOpen) eyeOpen.style.display = 'inline';
      if (eyeClosed) eyeClosed.style.display = 'none';
    }
    // keep focus on input after toggle
    pwd.focus();
  });
});
</script>

