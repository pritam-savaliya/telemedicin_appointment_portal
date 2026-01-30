<?php
session_start();
include 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $password = $_POST['password'];

    // Update Query
    if (!empty($password)) {
        // Update password if provided
        if (strlen($password) < 6) {
            $message = "<div style='background: #ffdddd; color: red; padding: 10px; margin-bottom: 20px; border-radius: 5px;'>Password must be at least 6 characters.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET fullname = '$fullname', password = '$hashed_password' WHERE id = $user_id";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['fullname'] = $fullname; // Update session
                $message = "<div style='background: #ddffdd; color: green; padding: 10px; margin-bottom: 20px; border-radius: 5px;'>Profile updated successfully!</div>";
            } else {
                $message = "Error updating profile: " . $conn->error;
            }
        }
    } else {
        // Only update fullname
        $sql = "UPDATE users SET fullname = '$fullname' WHERE id = $user_id";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['fullname'] = $fullname; // Update session
            $message = "<div style='background: #ddffdd; color: green; padding: 10px; margin-bottom: 20px; border-radius: 5px;'>Profile updated successfully!</div>";
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
    }
}

// Fetch Validation Data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<?php include 'includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-container" style="max-width: 600px; text-align: left;">
        <h2 style="text-align: center; margin-bottom: 1rem;">Edit Profile</h2>

        <?php echo $message; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Email (Cannot be changed)</label>
                <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled
                    style="background: #e9ecef; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" name="fullname" class="form-control" value="<?php echo $user['fullname']; ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="password">New Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control" placeholder="Enter new password">
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary auth-btn" style="max-width: 200px;">Update Profile</button>
                <a href="profile.php" class="btn btn-outline"
                    style="margin-left: 10px; display: inline-block; padding: 12px 20px; text-decoration: none;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>