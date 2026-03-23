<?php
session_start();
include '../includes/db.php';

$message = "";

// Generate a simple arithmetic CAPTCHA
if (!isset($_SESSION['captcha_ans']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['captcha_n1'] = rand(1, 9);
    $_SESSION['captcha_n2'] = rand(1, 9);
    $_SESSION['captcha_ans'] = $_SESSION['captcha_n1'] + $_SESSION['captcha_n2'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $new_pass = $_POST['new_password'];
    $repeat_pass = $_POST['repeat_password'];
    $user_captcha = intval($_POST['captcha']);

    if ($user_captcha !== $_SESSION['captcha_ans']) {
        $message = "<div class='alert-error'><i class='fas fa-robot'></i> Invalid CAPTCHA verification!</div>";
    } elseif ($new_pass !== $repeat_pass) {
        $message = "<div class='alert-error'><i class='fas fa-times-circle'></i> Passwords do not match!</div>";
    } else {
        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        $res = $conn->query($check_sql);
        if ($res->num_rows > 0) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = '$hashed_pass' WHERE email = '$email'";
            if ($conn->query($update_sql)) {
                $message = "<div class='alert-success'><i class='fas fa-check-circle'></i> Password reset successfully! <a href='login.php' style='color:inherit; font-weight:700;'>Log In Now</a></div>";
            } else {
                $message = "<div class='alert-error'><i class='fas fa-exclamation-circle'></i> System Error. Please try again.</div>";
            }
        } else {
            $message = "<div class='alert-error'><i class='fas fa-user-times'></i> Email address not found in our system!</div>";
        }
    }
    
    // Refresh CAPTCHA after attempt
    $_SESSION['captcha_n1'] = rand(1, 9);
    $_SESSION['captcha_n2'] = rand(1, 9);
    $_SESSION['captcha_ans'] = $_SESSION['captcha_n1'] + $_SESSION['captcha_n2'];
}

$hide_header = true;
include '../includes/header.php';
?>

<div class="auth-wrapper" style="background: var(--bg-body); position: relative; overflow: hidden; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div style="position: absolute; top: -10%; left: -10%; width: 40%; height: 40%; background: var(--primary); opacity: 0.05; border-radius: 50%; filter: blur(100px);"></div>
    <div style="position: absolute; bottom: -10%; right: -10%; width: 40%; height: 40%; background: var(--secondary); opacity: 0.05; border-radius: 50%; filter: blur(100px);"></div>

    <div class="auth-card animate-up" style="background: var(--bg-card); z-index: 10; width: 100%; max-width: 480px; padding: 3rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-lg); border-radius: 24px;">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div style="width: 70px; height: 70px; background: var(--primary-soft); color: var(--primary); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1.5rem; transform: rotate(-10deg);">
                <i class="fas fa-shield-keyhole"></i>
            </div>
            <h2 style="margin-bottom: 0.5rem; font-size: 1.75rem; letter-spacing: -0.5px;">Account Recovery</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Authenticate and update your credentials.</p>
        </div>

        <?php if ($message != ""): ?>
            <div style="margin-bottom: 2rem;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" style="display: grid; gap: 1.5rem;">
            <div class="form-group">
                <label style="color: var(--text-main); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem; display: block;">Registered Email</label>
                <div class="input-with-icon" style="position: relative;">
                    <i class="fas fa-envelope" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--primary); opacity: 0.5;"></i>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required style="padding-left: 3.5rem; background: rgba(0,0,0,0.02); height: 55px; border-radius: 12px; border: 1px solid var(--border-glass);">
                </div>
            </div>

            <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label style="color: var(--text-main); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem; display: block;">New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••" required style="height: 55px; border-radius: 12px; background: rgba(0,0,0,0.02); border: 1px solid var(--border-glass);">
                </div>
                <div class="form-group">
                    <label style="color: var(--text-main); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.8rem; display: block;">Confirm</label>
                    <input type="password" name="repeat_password" class="form-control" placeholder="••••••••" required style="height: 55px; border-radius: 12px; background: rgba(0,0,0,0.02); border: 1px solid var(--border-glass);">
                </div>
            </div>

            <div class="form-group" style="background: var(--primary-soft); padding: 1.2rem; border-radius: 16px; border: 1px dashed var(--primary-color); position: relative;">
                <label style="color: var(--primary); font-weight: 700; font-size: 0.9rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-shield-halved"></i> Security Verification</span>
                    <button type="button" id="refresh_math_captcha" style="background: transparent; border: none; color: var(--primary); cursor: pointer; opacity: 0.7; transition: 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span id="math_captcha_display" style="font-size: 1.2rem; font-weight: 800; color: var(--primary); background: white; padding: 0.5rem 1rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                        <?php echo $_SESSION['captcha_n1']; ?> + <?php echo $_SESSION['captcha_n2']; ?> =
                    </span>
                    <input type="number" name="captcha" class="form-control" placeholder="?" required style="flex: 1; height: 50px; text-align: center; font-size: 1.2rem; font-weight: 700; border-radius: 8px; border: 2px solid var(--primary-color);">
                </div>
                <p style="font-size: 0.75rem; color: var(--primary); opacity: 0.7; margin-top: 0.8rem; font-weight: 500;">Please solve this arithmetic to prove you are human.</p>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; border-radius: 16px; font-weight: 700; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">
                Reset & Secure Account <i class="fas fa-lock-open" style="margin-left: 8px; opacity: 0.7;"></i>
            </button>
        </form>

        <div style="text-align: center; margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="login.php" style="color: var(--text-muted); font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <i class="fas fa-arrow-left"></i> Back to login terminal
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('refresh_math_captcha').addEventListener('click', function () {
    const icon = this.querySelector('i');
    icon.classList.add('fa-spin');
    fetch('../api/ajax_refresh_math_captcha.php')
        .then(res => res.json())
        .then(data => {
            document.getElementById('math_captcha_display').innerText = data.n1 + ' + ' + data.n2 + ' =';
            setTimeout(() => icon.classList.remove('fa-spin'), 600);
        });
});
</script>

<style>
.alert-error {
    padding: 1rem;
    border-radius: 12px;
    background: rgba(244, 63, 94, 0.1);
    color: #f43f5e;
    border: 1px solid rgba(244, 63, 94, 0.2);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert-success {
    padding: 1rem;
    border-radius: 12px;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.2);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>

<?php include '../includes/footer.php'; ?>