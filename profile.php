<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<?php include 'includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-container" style="max-width: 600px; text-align: left;">
        <h2 style="text-align: center; margin-bottom: 2rem;">My Profile</h2>

        <div style="display: flex; gap: 20px; align-items: center; justify-content: center; margin-bottom: 2rem;">
            <div
                style="width: 100px; height: 100px; background: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #555;">
                <i class="fas fa-user"></i>
            </div>
            <div style="margin-left: 20px;">
                <a href="edit_profile.php" class="btn btn-outline" style="padding: 5px 15px; font-size: 0.9rem;">Edit
                    Profile</a>
            </div>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
            <p><strong>Full Name:</strong>
                <?php echo $user['fullname']; ?>
            </p>
            <p><strong>Email Address:</strong>
                <?php echo $user['email']; ?>
            </p>
            <p><strong>Role:</strong> <span
                    style="text-transform: capitalize; color: var(--primary-color); font-weight: bold;">
                    <?php echo $user['role']; ?>
                </span></p>
            <p><strong>Member Since:</strong>
                <?php echo date("F j, Y", strtotime($user['created_at'])); ?>
            </p>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <?php if ($user['role'] == 'patient'): ?>
                <a href="patient_dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <?php else: ?>
                <a href="doctor_dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-outline" style="margin-left: 10px;">Logout</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>