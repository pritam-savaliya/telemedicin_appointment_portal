<?php
include 'includes/db.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']);

    // Basic Validation PHP side (Security)
    if (!in_array($role, ['patient', 'doctor'])) {
        $message = "<div class='alert-error'>Invalid role selected</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert-error'>Invalid email format</div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='alert-error'>Password must be at least 6 characters</div>";
    } elseif ($password === 'ad@1308') {
        $message = "<div class='alert-error'>Cannot use the default admin password</div>";
    } else {
        // Hash Password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $checkEmail = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($checkEmail);

        if ($result->num_rows > 0) {
            $message = "<div class='alert-error'>Email already registered!</div>";
        } else {
            // Insert User
            $is_approved = ($role === 'patient') ? 1 : 0;
            $sql = "INSERT INTO users (fullname, email, password, role, is_approved) VALUES ('$fullname', '$email', '$hashed_password', '$role', $is_approved)";

            if ($conn->query($sql) === TRUE) {
                // Redirect to login
                $redirect_code = ($role === 'doctor') ? 2 : 1;
                header("Location: login.php?success=$redirect_code");
                exit();
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }
}
?>
<?php
$hide_header = true;
include 'includes/header.php';
?>

<div class="auth-wrapper" style="padding-top: 0;">
    <div class="auth-container">
        <div style="text-align: center; margin-bottom: 1rem;">
            <i class="fas fa-user-plus" style="font-size: 3rem; color: var(--primary-color);"></i>
        </div>
        <h2>Create Account</h2>
        <p>Join TeleMed to book appointments easily</p>

        <?php if ($message != ""): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="fullname" name="fullname" class="form-control" placeholder="John Doe"
                        required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="john@example.com"
                        required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Create a password" required>
                </div>
                <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 5px; display: block;">Min. 6
                    characters</small>
            </div>

            <div class="form-group">
                <label for="role">I am a</label>
                <div class="input-with-icon">
                    <i class="fas fa-stethoscope"></i>
                    <select name="role" id="role" class="form-control">
                        <option value="patient">Patient</option>
                        <option value="doctor">Doctor</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary auth-btn">Sign Up <i class="fas fa-arrow-right"></i></button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>