<?php
include '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'patient'; 

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='badge badge-danger' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-envelope'></i> Invalid email format</div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='badge badge-danger' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-key'></i> Password must be at least 6 characters.</div>";
    } elseif (!preg_match('@[A-Z]@', $password)) {
        $message = "<div class='badge badge-danger' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-font'></i> Password must include at least one capital letter.</div>";
    } elseif (!preg_match('@[0-9]@', $password)) {
        $message = "<div class='badge badge-danger' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-hashtag'></i> Password must include at least one number.</div>";
    } elseif (!preg_match('@[^\w]@', $password)) {
        $message = "<div class='badge badge-danger' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-atom'></i> Password must include at least one special character.</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='badge badge-danger' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-exclamation-triangle'></i> Passwords do not match</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $checkEmail = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($checkEmail);

        if ($result->num_rows > 0) {
            $message = "<div class='badge badge-warning' style='width: 100%; padding: 1rem; margin-bottom: 20px;'><i class='fas fa-user-check'></i> Email already exists</div>";
        } else {
            $is_approved = 1;
            $sql = "INSERT INTO users (fullname, email, password, role, is_approved, verification_code, email_verified) 
                    VALUES ('$fullname', '$email', '$hashed_password', '$role', $is_approved, '0', 1)";

            if ($conn->query($sql) === TRUE) {
                header("Location: " . BASE_URL . "auth/login.php?success=1");
                exit();
            } else {
                $message = "<div class='badge badge-danger'>Error: " . $conn->error . "</div>";
            }
        }
    }
}
?>
<?php
$hide_header = true;
include '../includes/header.php';
?>

<div style="display: flex; min-height: 100vh; background: var(--bg-dark);">
    <div style="flex: 1.2; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; padding: 4rem; background: radial-gradient(circle at 70% 70%, rgba(6, 182, 212, 0.2) 0%, transparent 60%);">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;">
            <div style="position: absolute; top: 15%; right: 10%; width: 450px; height: 450px; background: var(--secondary); filter: blur(150px); opacity: 0.1;"></div>
            <div style="position: absolute; bottom: 15%; left: 10%; width: 350px; height: 350px; background: var(--primary); filter: blur(150px); opacity: 0.1;"></div>
        </div>

        <div style="position: relative; z-index: 2; width: 100%; max-width: 600px;" class="fade-in">
            <div class="logo" style="font-size: 2.2rem; margin-bottom: 3rem;">
                <i class="fas fa-heart-pulse"></i> <span>MedConnect</span>
            </div>
            
            <h1 style="font-size: 4rem; line-height: 1.1; margin-bottom: 2rem;">
                Join the <br>
                <span style="color: var(--primary);">Health Revolution.</span>
            </h1>
            
            <p style="font-size: 1.3rem; color: var(--text-muted); margin-bottom: 4rem; line-height: 1.7;">
                Create your secure health profile today and unlock instant access to top-tier medical specialists worldwide.
            </p>

            <div style="display: grid; gap: 2.5rem;">
                <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success); width: 50px; height: 50px; flex-shrink: 0;">
                        <i class="fas fa-hospital-user" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Seamless Onboarding</h4>
                        <p style="color: var(--text-dim); font-size: 0.9rem;">Set up your profile and book your first call in under 2 minutes.</p>
                    </div>
                </div>
                <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning); width: 50px; height: 50px; flex-shrink: 0;">
                        <i class="fas fa-cloud-medical" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Digital Health Vault</h4>
                        <p style="color: var(--text-dim); font-size: 0.9rem;">Manage all your prescriptions and reports in one secure location.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="flex: 1; min-width: 500px; display: flex; align-items: center; justify-content: center; padding: 3rem; background: rgba(15, 23, 42, 0.3);">
        <div class="glass-card" style="width: 100%; max-width: 520px; padding: 3.5rem;">
            <div style="margin-bottom: 3rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Create Account</h2>
                <p style="color: var(--text-muted); font-weight: 500;">Start your journey to better health.</p>
            </div>

            <?php if ($message != "") echo $message; ?>

            <form action="" method="POST" style="display: grid; gap: 1.8rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Full Name</label>
                    <div style="position: relative;">
                        <i class="fas fa-user-tag" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                        <input type="text" name="fullname" class="form-control" placeholder="John Doe" required style="padding-left: 3.5rem;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Email Address</label>
                    <div style="position: relative;">
                        <i class="fas fa-envelope" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                        <input type="email" name="email" class="form-control" placeholder="john@example.com" required style="padding-left: 3.5rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Password</label>
                        <div style="position: relative;">
                            <i class="fas fa-lock" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                            <input type="password" name="password" class="form-control" placeholder="••••••" required style="padding-left: 3.5rem;">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Confirm</label>
                        <div style="position: relative;">
                            <i class="fas fa-shield-check" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                            <input type="password" name="confirm_password" class="form-control" placeholder="••••••" required style="padding-left: 3.5rem;">
                        </div>
                    </div>
                </div>

                <input type="hidden" name="role" value="patient">

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; margin-top: 1rem; font-size: 1.1rem;">
                    Create Free Account <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </button>
            </form>

            <p style="text-align: center; margin-top: 3rem; color: var(--text-muted); font-weight: 500;">
                Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 700;">Sign In</a>
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>