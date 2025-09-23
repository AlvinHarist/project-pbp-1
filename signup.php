<?php
// --- PHP VALIDATION SCRIPT ---
// This block of code must be at the very top of the file, before any HTML.

include "config.php";

$errors = [];
$success_message = '';

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Sanitize and retrieve form data
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 2. Validate Full Name
    if (empty($fullname)) {
        $errors[] = "Full Name is required.";
    }

    // 3. Validate Email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // 4. Validate Password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif ($password !== $password_confirm) {
        $errors[] = "Passwords do not match.";
    }
    
    // 5. Check Terms of Service
    if (!isset($_POST['terms'])) {
        $errors[] = "You must agree to the Terms of Service.";
    }


    // If there are no errors, process the data
    if (empty($errors)) {
        // --- THIS IS WHERE YOU WOULD SAVE THE USER TO A DATABASE ---

        // IMPORTANT: NEVER store plain-text passwords. Always hash them.
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Example database insertion (pseudo-code):
        // $sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
        // $stmt = $pdo->prepare($sql);
        // $stmt->execute([$fullname, $email, $hashed_password]);

        $success_message = "Account created successfully! You can now log in.";
        // Optionally, redirect to a login page:
        // header('Location: login.php');
        // exit();
    }

    // Save the data into the database
    $id_user = uniqid("U");

    // hash password
    $hashed_password = md5($password);

    $stmt = $conn->prepare("INSERT INTO user (id, Nama, Email, Password, Role) VALUES (?, ?, ?, ?, 'Pembeli')");
    $stmt->bind_param("ssss", $id_user, $fullname, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "Registrasi berhasil!";
    } else {
        echo "Error: " . $stmt->error;
    }

            
}

// --- END OF PHP SCRIPT ---
?>
<?php include 'includes/header.php'; ?>

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
                <label for="fullname">Full Name</label>
                <i class="fas fa-user input-icon"></i>
                <input type="text" id="fullname" name="fullname" placeholder="e.g., John Doe" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
            </div>

            <div class="input-group">
                <label for="email">Email Address</label>
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" id="email" name="email" placeholder="e.g., john.doe@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
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