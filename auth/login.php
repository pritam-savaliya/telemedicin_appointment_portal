<?php
include '../includes/db.php';
session_start();

$message = "";
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $message = "<div class='alert-success' style='padding: 1rem; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);'><i class='fas fa-check-circle'></i> Registration successful! Please login.</div>";
    } elseif ($_GET['success'] == 2) {
        $message = "<div class='alert-success' style='padding: 1rem; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);'><i class='fas fa-check-circle'></i> Registration successful! Waiting for admin approval.</div>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];

    if (!isset($_SESSION['captcha_code']) || $captcha !== $_SESSION['captcha_code']) {
        $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-exclamation-circle'></i> Invalid Captcha Code</div>";
    } else {
        $sql = "SELECT id, fullname, role, password, is_approved, profile_pic, email_verified, email, verification_code FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                if ($row['email_verified'] == 0) {
                    $_SESSION['verification_email'] = $row['email'];

                    // Resend Code Logic could go here or on the verify page, 
                    // for now just redirect to enter the code they (hopefully) received.
                    // If they lost it, we might need a "Resend" button on verify_email.php.
                    // We'll trust the existing code for now.
                    if (empty($row['verification_code'])) {
                        // If no code exists for some reason, generate one
                        $new_code = rand(100000, 999999);
                        $conn->query("UPDATE users SET verification_code = '$new_code' WHERE id = " . $row['id']);
                        // Ideally send email here too
                    }

                    header("Location: verify_email.php");
                    exit();
                } else if ($row['is_approved'] == 0) {
                    $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-clock'></i> Your account is pending admin approval.</div>";
                } else {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['fullname'] = $row['fullname'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['profile_pic'] = $row['profile_pic'];

                    if ($row['role'] == 'admin') {
                        header("Location: " . BASE_URL . "admin/admin_dashboard.php?msg=login_success");
                    } elseif ($row['role'] == 'patient') {
                        header("Location: " . BASE_URL . "patient/patient_dashboard.php?msg=login_success");
                    } else {
                        header("Location: " . BASE_URL . "doctor/doctor_dashboard.php?msg=login_success");
                    }
                    exit();
                }
            } else {
                $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-lock'></i> Invalid password.</div>";
            }
        } else {
            $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-user-times'></i> No account found with that email.</div>";
        }
    }
}
?>
<?php
$hide_header = true;
include '../includes/header.php';
?>

<div class="auth-split-container">
    <!-- Visual Side -->
    <div class="auth-visual-side">
        <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=1200"
            alt="Healthcare" class="bg-img">
        <div class="auth-visual-content animate-up">
            <div style="font-size: 3.5rem; margin-bottom: 1.5rem;"><i class="fas fa-heartbeat"></i></div>
            <h1 style="color: white; font-size: 3rem; margin-bottom: 1.5rem; line-height: 1.2;">Elevating Healthcare
                <br> Through <span>Intelligence</span>.
            </h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">Join thousands of patients and healthcare providers redefining
                meeting medical needs via our smart telemedicine platform.</p>
        </div>
    </div>

    <!-- Form Side -->
    <div class="auth-content-side">
        <div class="card animate-up"
            style="width: 100%; max-width: 480px; padding: 3rem; border: none; box-shadow: none; background: transparent;">
            <div style="margin-bottom: 2.5rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Sign In</h2>
                <p style="color: var(--text-muted);">Enter your credentials to access your account</p>
            </div>

            <?php if ($message != ""):
                echo $message;
            endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label style="color: var(--text-main); font-weight: 600;">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"
                            style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="email" name="email" class="form-control" placeholder="name@company.com" required
                            style="padding-left: 3.5rem;">
                    </div>
                </div>

                <div class="form-group">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label style="color: var(--text-main); font-weight: 600; margin-bottom: 0;">Password</label>
                        <a href="forgot_password.php"
                            style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">Forgot Password?</a>
                    </div>
                    <div class="input-with-icon">
                        <i class="fas fa-key"
                            style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required
                            style="padding-left: 3.5rem;">
                    </div>
                </div>

                <div class="form-group">
                    <label style="color: var(--text-main); font-weight: 600;">Security Check</label>
                    <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 12px; align-items: center;">
                        <input type="text" name="captcha" class="form-control" placeholder="Code" required
                            style="letter-spacing: 4px; font-weight: 700; text-align: center; text-transform: uppercase;">

                        <?php
                        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                        $captcha_code = '';
                        for ($i = 0; $i < 5; $i++) {
                            $captcha_code .= $chars[rand(0, strlen($chars) - 1)];
                        }
                        $_SESSION['captcha_code'] = $captcha_code;
                        ?>

                        <div id="captcha_container"
                            style="background: var(--bg-card); padding: 0.8rem 1.2rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-family: monospace; font-weight: 800; font-size: 1.2rem; letter-spacing: 3px; color: var(--primary); user-select: none; text-decoration: line-through;">
                            <?php echo $captcha_code; ?>
                        </div>

                        <button type="button" id="refresh_captcha" class="theme-toggle-btn"
                            style="width: 45px; height: 45px;" title="Refresh Captcha">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 1rem;">
                    Sign In <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                </button>

                <div style="text-align: center; margin: 2rem 0; position: relative;">
                    <hr style="border: 0; border-top: 1px solid var(--border-color);">
                    <span
                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: var(--bg-body); padding: 0 15px; color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">OR</span>
                </div>

                <a href="google_login.php" class="btn btn-secondary" style="width: 100%; gap: 12px;">
                    <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google"
                        style="width: 20px;">
                    Google Account
                </a>
            </form>

            <p style="text-align: center; margin-top: 2.5rem; color: var(--text-muted); font-weight: 500;">
                New to TeleMed? <a href="register.php" style="color: var(--primary); font-weight: 700;">Create
                    account</a>
            </p>
        </div>
    </div>
</div>

<script>
    document.getElementById('refresh_captcha').addEventListener('click', function () {
        const icon = this.querySelector('i');
        icon.classList.add('fa-spin');
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '../api/ajax_refresh_captcha.php', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById('captcha_container').innerText = xhr.responseText;
                setTimeout(() => icon.classList.remove('fa-spin'), 600);
            }
        };
        xhr.send();
    });
</script>

<?php include '../includes/footer.php'; ?>