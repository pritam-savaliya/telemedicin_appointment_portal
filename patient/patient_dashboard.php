<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<?php include '../includes/header.php'; ?>

<?php
$patient_completed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND status = 'completed'")->fetch_assoc()['c'];
$patient_active = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND (status = 'confirmed' OR status = 'pending')")->fetch_assoc()['c'];
?>

<div class="container section">

    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; color: var(--secondary-color); margin-bottom: 10px;">Welcome back,
            <?php echo explode(' ', $_SESSION['fullname'])[0]; ?>!
        </h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">Manage your health journey with ease.</p>
    </div>

    <!-- Stats -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 40px;">
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(0, 184, 148, 0.1); padding: 15px; border-radius: 50%; color: var(--success-color);">
                <i class="fas fa-heartbeat" style="font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo $patient_active; ?></h3>
                <span style="color: var(--text-muted); font-size: 0.9rem;">Active Consultations</span>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(108, 117, 125, 0.1); padding: 15px; border-radius: 50%; color: var(--secondary-color);">
                <i class="fas fa-check-circle" style="font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo $patient_completed; ?></h3>
                <span style="color: var(--text-muted); font-size: 0.9rem;">Completed Treatments</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 4rem;">
        <div class="card" style="text-align: center; padding: 40px 30px;">
            <div
                style="background: rgba(108, 92, 231, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
                <i class="fas fa-user-md" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px;">Find a Doctor</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Browse our list of specialists and book your next
                consultation online.</p>
            <a href="book_appointment.php" class="btn btn-primary">Book Appointment</a>
        </div>

        <div class="card" style="text-align: center; padding: 40px 30px;">
            <div
                style="background: rgba(0, 184, 148, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--success-color);">
                <i class="fas fa-calendar-alt" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px;">My Appointments</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Track your upcoming visits and view past
                consultation history.</p>
            <a href="my_appointments.php" class="btn btn-primary">View History</a>
        </div>

        <div class="card" style="text-align: center; padding: 40px 30px;">
            <div
                style="background: rgba(108, 117, 125, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary-color);">
                <i class="fas fa-file-medical" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px;">Medical Records</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Upload and manage your medical history and
                reports.</p>
            <a href="upload_report.php" class="btn btn-primary">Upload Reports</a>
        </div>

        <div class="card" style="text-align: center; padding: 40px 30px;">
            <div
                style="background: rgba(108, 117, 125, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary-color);">
                <i class="fas fa-user-circle" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px;">Profile Settings</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Update your personal details and manage your
                account preference.</p>
            <a href="../profile.php" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>

    <?php
    $notif_sql = "SELECT * FROM notifications WHERE user_id = $user_id AND is_read = FALSE ORDER BY created_at DESC";
    $notif_result = $conn->query($notif_sql);

    $chat_sql = "SELECT appointments.*, users.fullname as doctor_name,
                 (SELECT COUNT(*) FROM chat_messages WHERE appointment_id = appointments.id AND is_read = 0 AND sender_id != $user_id) as unread_msgs
                 FROM appointments 
                 JOIN users ON appointments.doctor_id = users.id 
                 WHERE patient_id = $user_id AND appointments.status = 'confirmed' 
                 ORDER BY appointments.date DESC LIMIT 5";
    $chat_result = $conn->query($chat_sql);
    ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 40px;">

        <!-- Notifications -->
        <div>
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-bell" style="color: var(--warning-color);"></i> Notifications
            </h3>
            <?php if ($notif_result->num_rows > 0): ?>
                <div class="card" style="padding: 0; overflow: hidden;">
                    <?php while ($notif = $notif_result->fetch_assoc()): ?>
                        <div id="notif-<?php echo $notif['id']; ?>"
                            style="border-left: 4px solid var(--primary-color); padding: 20px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: start;">
                            <span style="font-size: 0.95rem; color: var(--text-main);"><?php echo $notif['message']; ?></span>
                            <button onclick="dismissNotification(<?php echo $notif['id']; ?>)"
                                style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem;">&times;</button>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 30px; color: var(--text-muted);">
                    All caught up! No new notifications.
                </div>
            <?php endif; ?>
        </div>

        <!-- Active Consultations -->
        <div>
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-comments" style="color: var(--success-color);"></i> Active Consultations
            </h3>
            <?php if ($chat_result->num_rows > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php while ($appt = $chat_result->fetch_assoc()): ?>
                        <div class="card"
                            style="padding: 20px; display: flex; justify-content: space-between; align-items: center; border-left: 5px solid var(--success-color);">
                            <div>
                                <h4 style="margin-bottom: 5px;">Dr. <?php echo $appt['doctor_name']; ?></h4>
                                <div style="display: flex; gap: 15px; font-size: 0.9rem; color: var(--text-muted);">
                                    <span><i class="far fa-calendar"></i>
                                        <?php echo date('M d', strtotime($appt['date'])); ?></span>
                                    <span><i class="far fa-clock"></i>
                                        <?php echo date("h:i A", strtotime($appt['time'])); ?></span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <a href="../chat.php?appointment_id=<?php echo $appt['id']; ?>" class="btn btn-secondary"
                                    style="padding: 8px 15px; font-size: 0.9rem; position: relative;">
                                    <i class="fas fa-paper-plane"></i>
                                    <?php if ($appt['unread_msgs'] > 0): ?>
                                        <span
                                            style="position: absolute; top: -5px; right: -5px; background: var(--danger-color); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; display: flex; align-items: center; justify-content: center;">
                                            <?php echo $appt['unread_msgs']; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                                <?php if (isset($appt['is_call_active']) && $appt['is_call_active']): ?>
                                    <a href="../video_consultation.php?appointment_id=<?php echo $appt['id']; ?>"
                                        class="btn btn-primary"
                                        style="padding: 8px 15px; font-size: 0.9rem; background-color: #6c5ce7;">
                                        <i class="fas fa-video"></i>
                                    </a>
                                <?php else: ?>
                                    <button disabled class="btn"
                                        style="padding: 8px 15px; background-color: #e0e0e0; color: #999; cursor: not-allowed; font-size: 0.9rem;">
                                        <i class="fas fa-video-slash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 30px; color: var(--text-muted);">
                    No active consultations when logged in.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function dismissNotification(id) {
        const formData = new FormData();
        formData.append('id', id);
        fetch('<?php echo BASE_URL; ?>api/get_notifications.php?action=mark_read', {
            method: 'POST',
            body: formData
        }).then(() => {
            document.getElementById('notif-' + id).style.display = 'none';
        });
    }
</script>

<?php include '../includes/footer.php'; ?>