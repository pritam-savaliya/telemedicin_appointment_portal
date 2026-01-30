<?php
session_start();
include 'includes/db.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit();
}

// Handle Status Updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $appt_id = $_GET['id'];
    $status = $_GET['action']; // 'confirmed' or 'rejected'

    // Security check to ensure status is valid
    if (in_array($status, ['confirmed', 'rejected'])) {
        $update_sql = "UPDATE appointments SET status = '$status' WHERE id = $appt_id AND doctor_id = " . $_SESSION['user_id'];
        $conn->query($update_sql);

        // Send Notification if confirmed
        if ($status == 'confirmed') {
            // Get patient ID
            $pat_sql = "SELECT patient_id FROM appointments WHERE id = $appt_id";
            $pat_res = $conn->query($pat_sql);
            if ($pat_res->num_rows > 0) {
                $patient_id = $pat_res->fetch_assoc()['patient_id'];
                $doc_name = $_SESSION['fullname'];
                $message = "Your appointment has been confirmed by Dr. $doc_name. You can now chat.";
                $notif_sql = "INSERT INTO notifications (user_id, message) VALUES ($patient_id, '$message')";
                $conn->query($notif_sql);
            }
        }
    }
    header("Location: doctor_dashboard.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$sql = "SELECT appointments.*, users.fullname as patient_name,
        (SELECT COUNT(*) FROM chat_messages WHERE appointment_id = appointments.id AND is_read = 0 AND sender_id != $doctor_id) as unread_msgs
        FROM appointments 
        JOIN users ON appointments.patient_id = users.id 
        WHERE doctor_id = $doctor_id 
        ORDER BY appointments.date ASC";
$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="padding: 4rem 5%; max-width: 1200px; margin: 0 auto; min-height: 70vh;">


    <!-- Header Section -->
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.2rem; margin-bottom: 0.5rem; color: var(--dark-bg);">Doctor Dashboard</h1>
        <p style="color: var(--text-muted);">Welcome back, <span
                style="color: var(--primary-color); font-weight: 600;">Dr. <?php echo $_SESSION['fullname']; ?></span>
        </p>
    </div>

    <!-- Stats Overview -->
    <?php
    $stats_total = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id")->fetch_assoc()['c'];
    $stats_pending = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'pending'")->fetch_assoc()['c'];
    $stats_confirmed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'confirmed'")->fetch_assoc()['c'];
    $stats_completed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'completed'")->fetch_assoc()['c'];
    ?>
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 20px; transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div
                style="background: rgba(0, 123, 255, 0.1); padding: 15px; border-radius: 50%; color: var(--primary-color);">
                <i class="fas fa-calendar-check" style="font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats_total; ?></h3>
                <span style="color: var(--text-muted); font-size: 0.9rem;">Total Appointments</span>
            </div>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 20px; transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div
                style="background: rgba(253, 203, 110, 0.1); padding: 15px; border-radius: 50%; color: var(--warning-color);">
                <i class="fas fa-user-clock" style="font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats_pending; ?></h3>
                <span style="color: var(--text-muted); font-size: 0.9rem;">Pending Requests</span>
            </div>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 20px; transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div
                style="background: rgba(0, 184, 148, 0.1); padding: 15px; border-radius: 50%; color: var(--success-color);">
                <i class="fas fa-comments" style="font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats_confirmed; ?></h3>
                <span style="color: var(--text-muted); font-size: 0.9rem;">Active Consultations</span>
            </div>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 20px; transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div
                style="background: rgba(108, 117, 125, 0.1); padding: 15px; border-radius: 50%; color: var(--secondary-color);">
                <i class="fas fa-check-double" style="font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo $stats_completed; ?></h3>
                <span style="color: var(--text-muted); font-size: 0.9rem;">Completed Treatments</span>
            </div>
        </div>
    </div>

    <!-- Appointment List -->
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow-sm);">
        <h3
            style="margin-bottom: 25px; color: var(--dark-bg); display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
            <i class="fas fa-list-alt" style="color: var(--secondary-color);"></i> Appointment Management
        </h3>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th
                            style="padding: 15px; text-align: left; border-bottom: 2px solid #eee; color: var(--text-muted); font-weight: 600;">
                            Patient</th>
                        <th
                            style="padding: 15px; text-align: left; border-bottom: 2px solid #eee; color: var(--text-muted); font-weight: 600;">
                            Date</th>
                        <th
                            style="padding: 15px; text-align: left; border-bottom: 2px solid #eee; color: var(--text-muted); font-weight: 600;">
                            Time</th>
                        <th
                            style="padding: 15px; text-align: left; border-bottom: 2px solid #eee; color: var(--text-muted); font-weight: 600;">
                            Status</th>
                        <th
                            style="padding: 15px; text-align: left; border-bottom: 2px solid #eee; color: var(--text-muted); font-weight: 600;">
                            Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr style="transition: background 0.2s;" onmouseover="this.style.background='#f9f9f9'"
                                onmouseout="this.style.background='transparent'">
                                <td style="padding: 15px; border-bottom: 1px solid #eee; font-weight: 500;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div
                                            style="background: #e9ecef; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                                            <i class="fas fa-user" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <?php echo $row['patient_name']; ?>
                                    </div>
                                </td>
                                <td style="padding: 15px; border-bottom: 1px solid #eee; color: var(--text-muted);">
                                    <?php echo date('M d, Y', strtotime($row['date'])); ?>
                                </td>
                                <td style="padding: 15px; border-bottom: 1px solid #eee; color: var(--text-muted);">
                                    <?php echo date("h:i A", strtotime($row['time'])); ?>
                                </td>
                                <td style="padding: 15px; border-bottom: 1px solid #eee;">
                                    <?php
                                    $status = $row['status'];
                                    $bg_color = '#ffeaa7';
                                    $text_color = '#fdcb6e';
                                    $icon = 'fa-clock';

                                    if ($status == 'confirmed') {
                                        $bg_color = '#55efc4';
                                        $text_color = '#00b894';
                                        $icon = 'fa-check-circle';
                                    } elseif ($status == 'rejected') {
                                        $bg_color = '#ff7675';
                                        $text_color = '#d63031';
                                        $icon = 'fa-times-circle';
                                    } elseif ($status == 'completed') {
                                        $bg_color = '#74b9ff';
                                        $text_color = '#0984e3';
                                        $icon = 'fa-clipboard-check';
                                    }
                                    ?>
                                    <span
                                        style="background: <?php echo $bg_color; ?>; color: <?php echo $text_color; ?>; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: capitalize; display: inline-flex; align-items: center; gap: 5px;">
                                        <i class="fas <?php echo $icon; ?>"></i> <?php echo $status; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; border-bottom: 1px solid #eee;">
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <?php if ($status == 'pending'): ?>
                                            <a href="doctor_dashboard.php?action=confirmed&id=<?php echo $row['id']; ?>"
                                                class="btn btn-primary"
                                                style="padding: 6px 12px; font-size: 0.85rem; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,123,255,0.2);">
                                                Accept
                                            </a>
                                            <a href="doctor_dashboard.php?action=rejected&id=<?php echo $row['id']; ?>"
                                                class="btn btn-outline"
                                                style="padding: 6px 12px; font-size: 0.85rem; color: #ff7675; border-color: #ff7675; border-radius: 6px;">
                                                Reject
                                            </a>
                                        <?php elseif ($status == 'confirmed'): ?>
                                            <a href="chat.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-primary"
                                                style="padding: 8px 16px; font-size: 0.9rem; background-color: var(--secondary-color); border-color: var(--secondary-color); position: relative; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;">
                                                <i class="fas fa-comments"></i> Chat
                                                <?php if ($row['unread_msgs'] > 0): ?>
                                                    <span
                                                        style="position: absolute; top: -8px; right: -8px; background: var(--danger-color); color: white; border-radius: 50%; width: 22px; height: 22px; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                        <?php echo $row['unread_msgs']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </a>
                                            <button onclick="markComplete(<?php echo $row['id']; ?>)" class="btn btn-primary"
                                                style="padding: 8px 16px; font-size: 0.9rem; margin-left: 5px; background-color: var(--primary-color); position: relative; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;">
                                                <i class="fas fa-check"></i> Done
                                            </button>
                                        <?php elseif ($status == 'completed'): ?>
                                            <span style="color: var(--primary-color); font-weight: 500;"><i
                                                    class="fas fa-check-double"></i> Completed</span>
                                        <?php else: ?>
                                            <span style="color: #b2bec3; font-style: italic;">No actions</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <div style="margin-bottom: 15px; color: #dfe6e9;">
                                    <i class="fas fa-calendar-times" style="font-size: 3rem;"></i>
                                </div>
                                No appointments found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function markComplete(id) {
        if (confirm('Are you sure you want to mark this treatment/appointment as completed?')) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', 'completed');

            fetch('update_appointment_status.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }
    }
</script>
<?php include 'includes/footer.php'; ?>