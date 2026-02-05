<?php
session_start();
include '../includes/db.php';

$message = "";
$valid_token = false;

if (isset($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $valid_token = true;
    } else {
        $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-times-circle'></i> This reset link is invalid or has expired.</div>";
    }
} else {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'>Password must be at least 6 characters.</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'>Passwords do not match.</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed_password, $token);

        if ($stmt->execute()) {
            $message = "<div class='alert-success' style='padding: 1rem; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);'><i class='fas fa-check-double'></i> Password successfully reset! <a href='login.php' style='color: inherit; text-decoration: underline; font-weight: 700;'>Login now</a></div>";
            $valid_token = false;
        } else {
            $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'>System error. Try again later.</div>";
        }
    }
}
?>
<?php
$hide_header = true;
include '../includes/header.php';
?>

<div class="auth-wrapper" style="background: var(--bg-body); position: relative; overflow: hidden;">
    <div
        style="position: absolute; bottom: -10%; right: -10%; width: 40%; height: 40%; background: var(--secondary); opacity: 0.05; border-radius: 50%; filter: blur(100px);">
    </div>

    <div class="auth-card animate-up"
        style="background: var(--bg-card); z-index: 10; width: 100%; max-width: 480px; padding: 3rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-lg);">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div
                style="width: 80px; height: 80px; background: var(--success); background-opacity: 0.1; color: var(--success); background: rgba(16, 185, 129, 0.1); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 1.5rem;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2 style="margin-bottom: 0.5rem; font-size: 2rem;">Reset Password</h2>
            <p style="color: var(--text-muted);">Choose a new, strong password for your account.</p>
        </div>

        <?php if ($message != "")
            echo $message; ?>

        <?php if ($valid_token): ?>
            <form action="" method="POST" style="display: grid; gap: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: var(--text-main); font-weight: 600;">New Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"
                            style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required
                            style="padding-left: 3.5rem;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: var(--text-main); font-weight: 600;">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-redo"
                            style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required
                            style="padding-left: 3.5rem;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 0.5rem;">
                    Update Password <i class="fas fa-save" style="margin-left: 8px;"></i>
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="login.php" class="btn btn-secondary" style="width: 100%;">Return to Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>