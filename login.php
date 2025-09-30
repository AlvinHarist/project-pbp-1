<?php
session_start();
$page_css = 'css/login.css';
include 'includes/header.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // server-side validation
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // cek credentials dengan prepared statement
        $stmt = $conn->prepare("SELECT * FROM user WHERE Email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();
            // NOTE: currently passwords are stored plain in DB; if hashed, use password_verify()
            if ($user['Password'] === $password) {
                $_SESSION['user'] = $user;
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email atau password salah.';
            }
        } else {
            $error = 'Email atau password salah.';
        }
        $stmt->close();
    }
}
?>

<main class="login-wrapper">
    <div class="login-card">
      <h2 class="login-title">Masuk</h2>
      <p class="login-subtitle">Masuk ke akun anda untuk mengakses BukaBuku</p>

      <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>

      <form method="POST" id="login-form" novalidate>
        <label class="login-label">Email</label>
        <input type="email" name="email" class="login-input" required>

        <label class="login-label">Password</label>
        <input type="password" name="password" class="login-input" required minlength="6">

        <div class="remember">
          <input type="checkbox" id="remember">
          <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="login-button">Masuk</button>
      </form>
    </div>
  </main>

<?php include 'includes/footer.php'; ?>

