<?php
session_start();
include 'includes/db.php';

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<?php include 'includes/header.php'; ?>

<?php
// Fetch Patient Stats
$patient_completed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND status = 'completed'")->fetch_assoc()['c'];
$patient_active = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND (status = 'confirmed' OR status = 'pending')")->fetch_assoc()['c'];
?>

<div class="container" style="padding: 4rem 5%; max-width: 1200px; margin: 0 auto; min-height: 70vh;">
    <!-- Header Section -->
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; color: var(--dark-bg); margin-bottom: 10px;">Welcome back,
            <?php echo explode(' ', $_SESSION['fullname'])[0]; ?>!
        </h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">Manage your health journey with ease.</p>
    </div>

    <!-- Stats Overview -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div
            style="background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(0, 184, 148, 0.1); padding: 15px; border-radius: 50%; color: var(--success-color);">
                <i class="fas fa-heartbeat" style="font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo $patient_active; ?></h3>
                <span style="color: var(--text-muted); font-size: 0.9rem;">Active Consultations</span>
            </div>
        </div>
        <div
            style="background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 20px;">
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

    <!-- Features Grid -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 4rem;">
        <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: var(--shadow-sm); text-align: center; transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
            <div
                style="background: rgba(0, 123, 255, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
                <i class="fas fa-user-md" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px; color: var(--dark-bg);">Find a Doctor</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Browse our list of specialists and book your next
                consultation online.</p>
            <a href="book_appointment.php" class="btn btn-primary" style="padding: 10px 25px; border-radius: 30px;">Book
                Appointment</a>
        </div>

        <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: var(--shadow-sm); text-align: center; transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
            <div
                style="background: rgba(0, 184, 148, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--success-color);">
                <i class="fas fa-calendar-alt" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px; color: var(--dark-bg);">My Appointments</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Track your upcoming visits and view past
                consultation history.</p>
            <a href="my_appointments.php" class="btn btn-primary" style="padding: 10px 25px; border-radius: 30px;">View
                History</a>
        </div>

        <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: var(--shadow-sm); text-align: center; transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
            <div
                style="background: rgba(108, 117, 125, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary-color);">
                <i class="fas fa-user-circle" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px; color: var(--dark-bg);">Profile Settings</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Update your personal details and manage your
                account preference.</p>
            <a href="profile.php" class="btn btn-primary" style="padding: 10px 25px; border-radius: 30px;">Edit
                Profile</a>
        </div>
    </div>
</div>

<?php
// Fetch Notifications
$notif_sql = "SELECT * FROM notifications WHERE user_id = $user_id AND is_read = FALSE ORDER BY created_at DESC";
$notif_result = $conn->query($notif_sql);

// Fetch Confirmed Appointments for Chat
$chat_sql = "SELECT appointments.*, users.fullname as doctor_name,
             (SELECT COUNT(*) FROM chat_messages WHERE appointment_id = appointments.id AND is_read = 0 AND sender_id != $user_id) as unread_msgs
             FROM appointments 
             JOIN users ON appointments.doctor_id = users.id 
             WHERE patient_id = $user_id AND appointments.status = 'confirmed' 
             ORDER BY appointments.date DESC LIMIT 5";
$chat_result = $conn->query($chat_sql);
?>

<div class="container" style="max-width: 1200px; margin: 0 auto 3rem; padding: 0 5%;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 40px;">

        <!-- Notifications Column -->
        <div>
            <h3 style="margin-bottom: 20px; color: var(--dark-bg); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-bell" style="color: var(--warning-color);"></i> Notifications
            </h3>
            <?php if ($notif_result->num_rows > 0): ?>
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm);">
                    <?php while ($notif = $notif_result->fetch_assoc()): ?>
                        <div id="notif-<?php echo $notif['id']; ?>"
                            style="border-left: 4px solid var(--primary-color); background: #f8f9fa; padding: 15px; margin-bottom: 10px; border-radius: 4px; display: flex; justify-content: space-between; align-items: start;">
                            <span style="font-size: 0.95rem; color: var(--text-main);"><?php echo $notif['message']; ?></span>
                            <button onclick="dismissNotification(<?php echo $notif['id']; ?>)"
                                style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0 0 0 10px; font-size: 1.2rem; line-height: 1;">&times;</button>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div
                    style="background: #f8f9fa; padding: 20px; border-radius: 12px; text-align: center; color: var(--text-muted);">
                    All caught up! No new notifications.
                </div>
            <?php endif; ?>
        </div>

        <!-- Active Chats Column -->
        <div>
            <h3 style="margin-bottom: 20px; color: var(--dark-bg); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-comments" style="color: var(--success-color);"></i> Active Consultations
            </h3>
            <?php if ($chat_result->num_rows > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php while ($appt = $chat_result->fetch_assoc()): ?>
                        <div
                            style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; justify-content: space-between; align-items: center; border-left: 5px solid var(--success-color);">
                            <div>
                                <h4 style="margin-bottom: 5px; font-size: 1.1rem;">Dr. <?php echo $appt['doctor_name']; ?></h4>
                                <div style="display: flex; gap: 15px; font-size: 0.85rem; color: var(--text-muted);">
                                    <span><i class="far fa-calendar"></i>
                                        <?php echo date('M d', strtotime($appt['date'])); ?></span>
                                    <span><i class="far fa-clock"></i>
                                        <?php echo date("h:i A", strtotime($appt['time'])); ?></span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <a href="chat.php?appointment_id=<?php echo $appt['id']; ?>" class="btn btn-primary"
                                    style="padding: 8px 15px; border-radius: 8px; position: relative;">
                                    <i class="fas fa-paper-plane"></i> Chat
                                    <?php if ($appt['unread_msgs'] > 0): ?>
                                        <span
                                            style="position: absolute; top: -8px; right: -8px; background: var(--danger-color); color: white; border-radius: 50%; width: 22px; height: 22px; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <?php echo $appt['unread_msgs']; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                                <?php if (isset($appt['is_call_active']) && $appt['is_call_active']): ?>
                                    <a href="video_consultation.php?appointment_id=<?php echo $appt['id']; ?>"
                                        class="btn btn-primary"
                                        style="padding: 8px 15px; border-radius: 8px; background-color: #6c5ce7; border-color: #6c5ce7;">
                                        <i class="fas fa-video"></i> Join Call
                                    </a>
                                <?php else: ?>
                                    <button disabled class="btn"
                                        style="padding: 8px 15px; border-radius: 8px; background-color: #e0e0e0; color: #999; border: 1px solid #ccc; cursor: not-allowed;">
                                        <i class="fas fa-video-slash"></i> Waiting for Doctor
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div
                    style="background: #f8f9fa; padding: 20px; border-radius: 12px; text-align: center; color: var(--text-muted);">
                    No active consultations at the moment.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function dismissNotification(id) {
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        }).then(() => {
            document.getElementById('notif-' + id).style.display = 'none';
        });
    }
</script>

<?php include 'includes/footer.php'; ?>