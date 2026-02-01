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

<div class="auth-wrapper" style="padding-top: 4rem;">
    <div class="auth-card" style="max-width: 500px;">
        <div style="text-align: center; margin-bottom: 2rem; position: relative;">
            <div
                style="width: 120px; height: 120px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 4px solid white; box-shadow: var(--shadow-md);">
                <i class="fas fa-user" style="font-size: 3rem; color: #ccc;"></i>
            </div>
            <h2 style="margin-top: 15px; margin-bottom: 5px;"><?php echo $user['fullname']; ?></h2>
            <span class="badge"
                style="background: var(--primary-light); color: var(--primary-dark); font-size: 0.9rem;">
                <?php echo ucfirst($user['role']); ?>
            </span>
        </div>

        <div style="background: #f8f9fa; padding: 25px; border-radius: var(--radius-md); margin-bottom: 30px;">
            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                <div style="width: 40px; text-align: center; color: var(--primary-color);"><i
                        class="fas fa-envelope"></i></div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--text-muted); display: block;">Email Address</label>
                    <span style="font-weight: 500;"><?php echo $user['email']; ?></span>
                </div>
            </div>
            <div style="display: flex; align-items: center;">
                <div style="width: 40px; text-align: center; color: var(--primary-color);"><i
                        class="fas fa-calendar-alt"></i></div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--text-muted); display: block;">Joined On</label>
                    <span style="font-weight: 500;"><?php echo date("F j, Y", strtotime($user['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <div style="display: grid; gap: 10px;">
            <?php if ($user['role'] == 'patient'): ?>
                <a href="patient_dashboard.php" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-columns" style="margin-right: 8px;"></i> Dashboard
                </a>
            <?php elseif ($user['role'] == 'doctor'): ?>
                <a href="doctor_dashboard.php" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-columns" style="margin-right: 8px;"></i> Dashboard
                </a>
            <?php else: ?>
                <a href="admin_dashboard.php" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-columns" style="margin-right: 8px;"></i> Dashboard
                </a>
            <?php endif; ?>

            <a href="logout.php" class="btn btn-outline"
                style="width: 100%; justify-content: center; color: var(--danger-color); border-color: var(--danger-color);">
                <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Logout
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>