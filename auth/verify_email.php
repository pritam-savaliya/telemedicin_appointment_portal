<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['verification_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['verification_email'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $conn->real_escape_string($_POST['code']);

    // Check code
    $sql = "SELECT * FROM users WHERE email = '$email' AND verification_code = '$code'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Update to verified
        $update = "UPDATE users SET email_verified = 1 WHERE id = " . $row['id'];

        if ($conn->query($update) === TRUE) {
            unset($_SESSION['verification_email']);
            header("Location: login.php?msg=verified");
            exit();
        } else {
            $message = "<div class='alert-error'>Database Error. Try again.</div>";
        }
    } else {
        $message = "<div class='alert-error'>Invalid Verification Code. Please try check your email/log.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - TeleMed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body
    style="background: var(--bg-body); color: var(--text-main); height: 100vh; display: flex; align-items: center; justify-content: center;">

    <div class="container">
        <div style="max-width: 500px; margin: 0 auto;">
            <div class="card" style="padding: 40px; text-align: center;">
                <div
                    style="background: rgba(108, 92, 231, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; color: var(--primary);">
                    <i class="fas fa-envelope-open-text" style="font-size: 2rem;"></i>
                </div>

                <h2 style="margin-bottom: 10px;">Verify Your Email</h2>
                <p style="color: var(--text-muted); margin-bottom: 30px;">
                    We've sent a 6-digit verification code to <strong>
                        <?php echo htmlspecialchars($email); ?>
                    </strong>.
                    <br><small>(Check email_log.txt in project root if local)</small>
                </p>

                <?php if ($message != "")
                    echo $message; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <input type="text" name="code" class="form-control" placeholder="Enter 6-digit Code"
                            maxlength="6" required
                            style="text-align: center; letter-spacing: 5px; font-size: 1.5rem; padding: 15px;">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">Verify
                        Account</button>
                </form>

                <div style="margin-top: 20px;">
                    <a href="login.php" style="color: var(--text-muted); font-size: 0.9rem;">Back to Login</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>