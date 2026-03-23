<?php
include '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $message = "<div class='badge badge-success' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-check-circle'></i> Registration successful! Please login.</div>";
    } elseif ($_GET['success'] == 2) {
        $message = "<div class='badge badge-success' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-check-circle'></i> Registration successful! Waiting for admin approval.</div>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $captcha = strtoupper($_POST['captcha'] ?? '');

    if (!isset($_SESSION['captcha_code']) || $captcha !== $_SESSION['captcha_code']) {
        $message = "<div class='badge badge-danger' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-exclamation-circle'></i> Invalid Captcha Code</div>";
    } else {
        $sql = "SELECT id, fullname, role, password, is_approved, profile_pic, email_verified, email, verification_code FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                if ($row['is_approved'] == 0) {
                    $message = "<div class='badge badge-warning' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-clock'></i> Account pending admin approval.</div>";
                } else {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['fullname'] = $row['fullname'];
                    $_SESSION['email'] = $row['email'];
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
                $message = "<div class='badge badge-danger' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-lock'></i> Invalid password.</div>";
            }
        } else {
            $message = "<div class='badge badge-danger' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-user-times'></i> No account found with that email.</div>";
        }
    }
}
?>
<?php
$hide_header = true;
include '../includes/header.php';
?>

<div style="display: flex; min-height: 100vh; background: var(--bg-dark);">
    <!-- Left Side: Visual/Branding -->
    <div style="flex: 1.2; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; padding: 4rem; background: radial-gradient(circle at 30% 30%, rgba(99, 102, 241, 0.2) 0%, transparent 60%);">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;">
            <div style="position: absolute; top: 10%; left: 10%; width: 400px; height: 400px; background: var(--primary); filter: blur(150px); opacity: 0.1;"></div>
            <div style="position: absolute; bottom: 10%; right: 10%; width: 300px; height: 300px; background: var(--secondary); filter: blur(150px); opacity: 0.1;"></div>
        </div>

        <div style="position: relative; z-index: 2; width: 100%; max-width: 600px;" class="fade-in">
            <div class="logo" style="font-size: 2.2rem; margin-bottom: 3rem;">
                <i class="fas fa-heart-pulse"></i> <span>MedConnect</span>
            </div>
            
            <h1 style="font-size: 4rem; line-height: 1.1; margin-bottom: 2rem;">
                Your Health Journey <br>
                <span style="color: var(--secondary);">Simplified.</span>
            </h1>
            
            <p style="font-size: 1.3rem; color: var(--text-muted); margin-bottom: 4rem; line-height: 1.7;">
                Access world-class medical specialists, secure digital records, and instant consultations in one unified platform.
            </p>

            <div style="display: grid; gap: 2rem;">
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); width: 50px; height: 50px;">
                        <i class="fas fa-check" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 1.1rem;">Verified Specialists</h4>
                        <p style="color: var(--text-dim); font-size: 0.9rem;">Rigorous vetting for every doctor on board.</p>
                    </div>
                </div>
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <div class="stat-icon" style="background: rgba(6, 182, 212, 0.1); color: var(--secondary); width: 50px; height: 50px;">
                        <i class="fas fa-lock" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 1.1rem;">End-to-End Privacy</h4>
                        <p style="color: var(--text-dim); font-size: 0.9rem;">Your medical data is encrypted and secure.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Form Area -->
    <div style="flex: 1; min-width: 450px; display: flex; align-items: center; justify-content: center; padding: 3rem; background: rgba(15, 23, 42, 0.3);">
        <div class="glass-card" style="width: 100%; max-width: 480px; padding: 3.5rem;">
            <div style="margin-bottom: 3rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Welcome Back</h2>
                <p style="color: var(--text-muted); font-weight: 500;">Please enter your details to sign in.</p>
            </div>

            <?php if ($message != "") echo $message; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div style="position: relative;">
                        <i class="fas fa-envelope" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                        <input type="email" name="email" class="form-control" placeholder="doctor@medconnect.com" required style="padding-left: 3.5rem;">
                    </div>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                        <label class="form-label" style="margin-bottom: 0;">Password</label>
                        <a href="forgot_password.php" style="font-size: 0.85rem; color: var(--primary); font-weight: 700;">Forgot?</a>
                    </div>
                    <div style="position: relative;">
                        <i class="fas fa-lock" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required style="padding-left: 3.5rem; padding-right: 3.5rem;">
                        <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 1.25rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-dim);"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Security Verification</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="captcha" class="form-control" placeholder="Code" required style="letter-spacing: 4px; font-weight: 800; text-align: center; text-transform: uppercase; width: 120px;">
                        
                        <?php
                        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                        $captcha_code = '';
                        for ($i = 0; $i < 5; $i++) { $captcha_code .= $chars[rand(0, strlen($chars) - 1)]; }
                        $_SESSION['captcha_code'] = $captcha_code;
                        ?>

                        <div id="captcha_container" style="flex: 1; background: var(--bg-glass); border: 1px solid var(--border-glass); padding: 0.8rem; border-radius: var(--radius-md); text-align: center; font-family: monospace; font-weight: 800; color: var(--secondary); letter-spacing: 3px; font-size: 1.1rem; text-decoration: line-through;">
                            <?php echo $captcha_code; ?>
                        </div>
                        
                        <button type="button" id="refresh_captcha" class="btn btn-secondary" style="padding: 1rem; border-radius: var(--radius-md);">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; margin-top: 1rem; font-size: 1.1rem;">
                    Sign In <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </button>
            </form>

            <div style="margin: 2.5rem 0; display: flex; align-items: center; gap: 1.5rem;">
                <div style="flex: 1; height: 1px; background: var(--border-glass);"></div>
                <span style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600; text-transform: uppercase;">or continue with</span>
                <div style="flex: 1; height: 1px; background: var(--border-glass);"></div>
            </div>

            <a href="google_login.php" class="btn btn-secondary" style="width: 100%; border-radius: var(--radius-md);">
                <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" style="width: 18px; margin-right: 12px;"> 
                Sign in with Google
            </a>

            <p style="text-align: center; margin-top: 3rem; color: var(--text-muted); font-weight: 500;">
                New to MedConnect? <a href="register.php" style="color: var(--primary); font-weight: 700;">Create Account</a>
            </p>
        </div>
    </div>
</div>

<script>
    document.getElementById('refresh_captcha').addEventListener('click', function () {
        const icon = this.querySelector('i');
        icon.classList.add('fa-spin');
        fetch('../api/ajax_refresh_captcha.php')
            .then(res => res.text())
            .then(code => {
                document.getElementById('captcha_container').innerText = code;
                setTimeout(() => icon.classList.remove('fa-spin'), 600);
            });
    });

    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>

<?php include '../includes/footer.php'; ?>