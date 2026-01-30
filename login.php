<?php
include 'includes/db.php';
session_start();

$message = "";
if (isset($_GET['success'])) {
    $message = "<div class='alert-success'>Registration successful! Please login.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];

    if (!isset($_SESSION['captcha_code']) || $captcha !== $_SESSION['captcha_code']) {
        $message = "<div class='alert-error'>Invalid Captcha Code</div>";
    } else {
        $sql = "SELECT id, fullname, role, password FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Password correct
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['role'] = $row['role'];

                // Redirect based on role
                if ($row['role'] == 'admin') {
                    header("Location: admin_dashboard.php?msg=login_success");
                } elseif ($row['role'] == 'patient') {
                    header("Location: patient_dashboard.php?msg=login_success");
                } else {
                    header("Location: doctor_dashboard.php?msg=login_success");
                }
                exit();
            } else {
                $message = "<div class='alert-error'>Invalid password.</div>";
            }
        } else {
            $message = "<div class='alert-error'>No account found with that email.</div>";
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
            <i class="fas fa-user-circle" style="font-size: 3rem; color: var(--primary-color);"></i>
        </div>
        <h2>Welcome Back</h2>
        <p>Login to manage your appointments</p>

        <?php if ($message != ""): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <form action="" method="POST">
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
                        placeholder="Enter your password" required>
                </div>
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label for="captcha">Security Check</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="input-with-icon" style="flex:1;">
                        <i class="fas fa-shield-alt"></i>
                        <input type="text" id="captcha" name="captcha" class="form-control" placeholder="Enter code"
                            required style="letter-spacing: 2px;">
                    </div>

                    <?php
                    // Simple inline CAPTCHA
                    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                    $captcha_code = '';
                    for ($i = 0; $i < 5; $i++) {
                        $captcha_code .= $chars[rand(0, strlen($chars) - 1)];
                    }
                    $_SESSION['captcha_code'] = $captcha_code;
                    ?>

                    <div id="captcha_container" style="
                        background: #f8f9fa;
                        padding: 10px 15px;
                        border: 1px solid #ddd;
                        border-radius: var(--radius-sm);
                        font-family: 'Courier New', monospace;
                        font-weight: bold;
                        font-size: 20px;
                        letter-spacing: 5px;
                        color: var(--secondary-color);
                        user-select: none;
                        text-decoration: line-through;
                        cursor: default;
                    ">
                        <?php echo $captcha_code; ?>
                    </div>
                    <button type="button" id="refresh_captcha" class="btn btn-outline"
                        style="padding: 0 10px; height: 100%; border: none; background: transparent; color: var(--primary-color); cursor: pointer;"
                        title="Refresh Captcha">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <div style="text-align: right; margin-bottom: 15px;">
                <a href="#" style="font-size: 0.9rem; color: var(--primary-color);">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary auth-btn">Login <i class="fas fa-sign-in-alt"></i></button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('refresh_captcha').addEventListener('click', function () {
        var icon = this.querySelector('i');
        icon.classList.add('fa-spin'); // Add spinning animation

        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'ajax_refresh_captcha.php', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById('captcha_container').innerText = xhr.responseText;
                setTimeout(function () {
                    icon.classList.remove('fa-spin'); // Stop spinning after a short delay
                }, 500);
            }
        };
        xhr.send();
    });
</script>

<?php include 'includes/footer.php'; ?>