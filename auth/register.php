<?php
include '../includes/db.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']);

    if (!in_array($role, ['patient', 'doctor'])) {
        $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-exclamation-circle'></i> Invalid role selected</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-envelope-open-text'></i> Invalid email format</div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-key'></i> Password must be at least 6 characters</div>";
    } elseif ($password === 'ad@1308') {
        $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-shield-alt'></i> Prohibited password selection</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $checkEmail = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($checkEmail);

        if ($result->num_rows > 0) {
            $message = "<div class='alert-error' style='padding: 1rem; border-radius: 12px; background: rgba(244, 63, 94, 0.1); color: #f43f5e; margin-bottom: 20px; border: 1px solid rgba(244, 63, 94, 0.2);'><i class='fas fa-user-check'></i> Email already registered!</div>";
        } else {
            $is_approved = ($role === 'patient') ? 1 : 0;
            $verification_code = rand(100000, 999999);

            // Insert user with verification code and email_verified = 0
            $sql = "INSERT INTO users (fullname, email, password, role, is_approved, verification_code, email_verified) 
                    VALUES ('$fullname', '$email', '$hashed_password', '$role', $is_approved, '$verification_code', 0)";

            if ($conn->query($sql) === TRUE) {
                // Send Verification Email
                $subject = "Verify Your Email - TeleMed";
                $msg_body = "Hi $fullname,\n\nThank you for registering. Your verification code is: $verification_code\n\nPlease enter this code to verify your account.";
                $headers = "From: no-reply@telemed.com";

                // Attempt to send email
                // Note: On localhost, mail() requires SMTP setup. We will also log it to a file for testing convenience.
                mail($email, $subject, $msg_body, $headers);

                // Log for localhost testing
                $log_content = "To: $email\nCode: $verification_code\n------------------\n";
                file_put_contents("../email_log.txt", $log_content, FILE_APPEND);

                // Redirect to verification page
                $_SESSION['verification_email'] = $email;
                header("Location: verify_email.php");
                exit();
            } else {
                $message = "<div class='alert-error'>Error: " . $conn->error . "</div>";
            }
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
        <img src="https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1200"
            alt="Medical Workspace" class="bg-img">
        <div class="auth-visual-content animate-up">
            <div style="font-size: 3.5rem; margin-bottom: 1.5rem;"><i class="fas fa-user-md"></i></div>
            <h1 style="color: white; font-size: 3rem; margin-bottom: 1.5rem; line-height: 1.2;">Be Part of the <br>
                <span>Healthcare</span> Future.
            </h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">Sign up today to access professional medical consultants or join
                our growing network of certified doctors.</p>
        </div>
    </div>

    <!-- Form Side -->
    <div class="auth-content-side">
        <div class="card animate-up"
            style="width: 100%; max-width: 550px; padding: 3rem; border: none; box-shadow: none; background: transparent;">
            <div style="margin-bottom: 2.5rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Create Account</h2>
                <p style="color: var(--text-muted);">Fill in the details to create your secure medicine portal</p>
            </div>

            <?php if ($message != ""):
                echo $message;
            endif; ?>

            <form action="" method="POST" style="display: grid; gap: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label
                        style="color: var(--text-main); font-weight: 600; display: block; margin-bottom: 0.75rem;">Full
                        Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"
                            style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" name="fullname" class="form-control" placeholder="John Doe" required
                            style="padding-left: 3.5rem;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label
                        style="color: var(--text-main); font-weight: 600; display: block; margin-bottom: 0.75rem;">Email
                        Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"
                            style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" required
                            style="padding-left: 3.5rem;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label
                        style="color: var(--text-main); font-weight: 600; display: block; margin-bottom: 0.75rem;">Create
                        Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"
                            style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required
                            style="padding-left: 3.5rem;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label
                        style="color: var(--text-main); font-weight: 600; display: block; margin-bottom: 0.75rem;">Account
                        Type</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <label style="cursor: pointer;">
                            <input type="radio" name="role" value="patient" checked style="display: none;"
                                onchange="updateRoleUI(this)">
                            <div class="role-selector active" id="role-patient"
                                style="border: 2px solid var(--primary); background: var(--primary-soft); color: var(--primary); padding: 1rem; border-radius: var(--radius-sm); text-align: center; font-weight: 700; transition: var(--transition);">
                                <i class="fas fa-user-injured"
                                    style="display: block; font-size: 1.2rem; margin-bottom: 5px;"></i> Patient
                            </div>
                        </label>
                        <label style="cursor: pointer;">
                            <input type="radio" name="role" value="doctor" style="display: none;"
                                onchange="updateRoleUI(this)">
                            <div class="role-selector" id="role-doctor"
                                style="border: 2px solid var(--border-color); color: var(--text-muted); padding: 1rem; border-radius: var(--radius-sm); text-align: center; font-weight: 700; transition: var(--transition);">
                                <i class="fas fa-user-md"
                                    style="display: block; font-size: 1.2rem; margin-bottom: 5px;"></i> Doctor
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 1rem;">
                    Sign Up <i class="fas fa-check-circle" style="margin-left: 8px;"></i>
                </button>
            </form>

            <p style="text-align: center; margin-top: 2.5rem; color: var(--text-muted); font-weight: 500;">
                Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 700;">Sign in
                    here</a>
            </p>
        </div>
    </div>
</div>

<script>
    function updateRoleUI(radio) {
        const patientDiv = document.getElementById('role-patient');
        const doctorDiv = document.getElementById('role-doctor');
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
        const primarySoft = getComputedStyle(document.documentElement).getPropertyValue('--primary-soft').trim();
        const borderColor = getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim();
        const textMuted = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim();

        if (radio.value === 'patient') {
            patientDiv.style.borderColor = primaryColor;
            patientDiv.style.background = primarySoft;
            patientDiv.style.color = primaryColor;

            doctorDiv.style.borderColor = borderColor;
            doctorDiv.style.background = 'transparent';
            doctorDiv.style.color = textMuted;
        } else {
            doctorDiv.style.borderColor = primaryColor;
            doctorDiv.style.background = primarySoft;
            doctorDiv.style.color = primaryColor;

            patientDiv.style.borderColor = borderColor;
            patientDiv.style.background = 'transparent';
            patientDiv.style.color = textMuted;
        }
    }
</script>

<?php include '../includes/footer.php'; ?>