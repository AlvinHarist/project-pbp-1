<?php
// --- PHP VALIDATION & PROCESSING ---
require_once __DIR__ . '/config/config.php';

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($fullname === '') $errors[] = 'Nama lengkap wajib diisi.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if ($alamat === '') $errors[] = 'Alamat wajib diisi.';
    if ($nomor_telepon === '' || !preg_match('/^[0-9]{7,15}$/', $nomor_telepon)) $errors[] = 'Nomor telepon tidak valid.';
    if (strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';
    if ($password !== $password_confirm) $errors[] = 'Konfirmasi password tidak cocok.';
    if (!isset($_POST['terms'])) $errors[] = 'Anda harus menyetujui Terms of Service.';

    if (empty($errors)) {
        // check email unique
        $stmt = $conn->prepare("SELECT id FROM user WHERE Email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $errors[] = 'Email sudah terdaftar.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $id_user = uniqid('U');
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (id, Nama, Email, alamat, Password, Role) VALUES (?, ?, ?, ?, ?, 'Pembeli')");
        $stmt->bind_param('sssss', $id_user, $fullname, $email, $alamat, $hashed_password);
        if ($stmt->execute()) {
            $success_message = 'Registrasi berhasil. Silakan login.';
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan data: ' . $stmt->error;
        }
        $stmt->close();
    }
}

include 'includes/header.php';

?>

<div class="auth-container">
    <div class="auth-form-wrapper">
        <h2>Create Your Account</h2>
        <p>Join BookHaven to discover your next favorite read.</p>

        <?php
        // Display errors if any
        if (!empty($errors)) {
            echo '<div class="error-messages">';
            foreach ($errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }

        // Display success message
        if (!empty($success_message)) {
            echo '<div class="success-message">';
            echo '<p>' . htmlspecialchars($success_message) . '</p>';
            echo '</div>';
        }
        ?>

        <?php if (empty($success_message)): ?>
        <form id="signupForm" action="signup.php" method="POST" novalidate>
            <div class="input-group">
                <label for="fullname">Nama Lengkap</label>
                <i class="fas fa-user input-icon"></i>
                <input type="text" id="fullname" name="fullname" placeholder="contoh: Uzumaki Naruto" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" id="email" name="email" placeholder="contoh: naruto@konoha.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="input-group">
                <label for="alamat">Alamat Rumah</label>
                <i class="fas fa-location-dot input-icon"></i>
                <input type="text" id="alamat" name="alamat" placeholder="contoh: Jl. Melati No. 7, Kecamatan X, Kota Y" required value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <i class="fas fa-lock input-icon"></i>
                <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
                <i class="fas fa-eye toggle-password"></i>
            </div>

            <div class="input-group">
                <label for="password_confirm">Confirm Password</label>
                <i class="fas fa-lock input-icon"></i>
                <input type="password" id="password_confirm" name="password_confirm" placeholder="Repeat your password" required>
                <i class="fas fa-eye toggle-password"></i>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</label>
            </div>
            
            <button type="submit" class="btn-auth">Create Account</button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">
            <p>Already have an account? <a href="#">Log In</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>