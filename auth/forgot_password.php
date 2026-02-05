<?php
session_start();
include '../includes/db.php';
include '../includes/smtp_config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);

    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $conn->query("UPDATE users SET reset_token = '$token', reset_expiry = '$expiry' WHERE email = '$email'");

        $resetLink = BASE_URL . "auth/reset_password.php?token=$token";
        $subject = "Password Reset Request - TeleMed";
        $body = "<h2>Password Reset</h2><p>Click the link below to reset your password:</p><p><a href='$resetLink' style='background: #6366f1; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>Reset Password</a></p><p>Or copy this link: $resetLink</p><p>This link expires in 1 hour.</p>";

        if (sendEmail($email, $subject, $body)) {
            $message = "<div class='alert-success' style='padding: 1rem; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);'><i class='fas fa-paper-plane'></i> Reset link sent to your email.</div>";
        } else {
            $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-exclamation-triangle'></i> Mail Delivery Failed. Check SMTP config.</div>";
        }
    } else {
        $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-user-times'></i> Email address not found!</div>";
    }
}
?>
<?php
$hide_header = true;
include '../includes/header.php';
?>

<div class="auth-wrapper" style="background: var(--bg-body); position: relative; overflow: hidden;">
    <div
        style="position: absolute; top: -10%; left: -10%; width: 40%; height: 40%; background: var(--primary); opacity: 0.05; border-radius: 50%; filter: blur(100px);">
    </div>

    <div class="auth-card animate-up"
        style="background: var(--bg-card); z-index: 10; width: 100%; max-width: 480px; padding: 3rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-lg);">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div
                style="width: 80px; height: 80px; background: var(--primary-soft); color: var(--primary); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 1.5rem;">
                <i class="fas fa-key"></i>
            </div>
            <h2 style="margin-bottom: 0.5rem; font-size: 2rem;">Forgot Password?</h2>
            <p style="color: var(--text-muted);">No worries, we'll send you reset instructions.</p>
        </div>

        <?php if ($message != "")
            echo $message; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label style="color: var(--text-main); font-weight: 600;">Email Address</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"
                        style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required
                        style="padding-left: 3.5rem;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary"
                style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 1rem;">
                Reset Password <i class="fas fa-paper-plane" style="margin-left: 8px;"></i>
            </button>
        </form>

        <div style="text-align: center; margin-top: 2.5rem;">
            <a href="login.php" style="color: var(--text-muted); font-weight: 600; font-size: 0.95rem;">
                <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to log in
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>