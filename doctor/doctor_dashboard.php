<?php
session_start();
include '../includes/db.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: " . BASE_URL . "auth/login.php");
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
        include_once '../includes/smtp_config.php';

        $pat_sql = "SELECT u.id, u.email, u.fullname FROM users u 
                    JOIN appointments a ON u.id = a.patient_id 
                    WHERE a.id = $appt_id";
        $pat_res = $conn->query($pat_sql);

        if ($pat_res->num_rows > 0) {
            $pat_row = $pat_res->fetch_assoc();
            $patient_id = $pat_row['id'];
            $patient_email = $pat_row['email'];
            $patient_name = $pat_row['fullname'];
            $doc_name = $_SESSION['fullname'];

            $subject = "Appointment Status Update - TeleMed";
            $message_text = "Your appointment has been <b>$status</b> by Dr. $doc_name.";

            if ($status == 'confirmed') {
                $message_text .= "<br>Please indicate your availability at the scheduled time.";

                // Add in-app notification
                $notif_sql = "INSERT INTO notifications (user_id, message) VALUES ($patient_id, 'Your appointment has been confirmed by Dr. $doc_name.')";
                $conn->query($notif_sql);
            } elseif ($status == 'rejected') {
                $message_text .= "<br>Please contact us for more information or book another slot.";
            }

            sendEmail($patient_email, $subject, "Hi $patient_name,<br><br>$message_text");
        }
    }
    header("Location: " . BASE_URL . "doctor/doctor_dashboard.php");
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

<?php include '../includes/header.php'; ?>

<div class="container section">

    <!-- Header Section -->
    <div style="margin-bottom: 2rem;">
        <h1 style="color: var(--secondary-color);">Doctor Dashboard</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">Welcome back, <span
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
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 40px;">
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(0, 123, 255, 0.1); padding: 16px; border-radius: 50%; color: var(--primary-color);">
                <i class="fas fa-calendar-check" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h3 style="font-size: 2rem; margin-bottom: 5px;"><?php echo $stats_total; ?></h3>
                <span style="color: var(--text-muted);">Total Appointments</span>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(253, 203, 110, 0.1); padding: 16px; border-radius: 50%; color: var(--warning-color);">
                <i class="fas fa-user-clock" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h3 style="font-size: 2rem; margin-bottom: 5px;"><?php echo $stats_pending; ?></h3>
                <span style="color: var(--text-muted);">Pending Requests</span>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(0, 184, 148, 0.1); padding: 16px; border-radius: 50%; color: var(--success-color);">
                <i class="fas fa-comments" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h3 style="font-size: 2rem; margin-bottom: 5px;"><?php echo $stats_confirmed; ?></h3>
                <span style="color: var(--text-muted);">Active Consultations</span>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(108, 117, 125, 0.1); padding: 16px; border-radius: 50%; color: var(--secondary-color);">
                <i class="fas fa-check-double" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h3 style="font-size: 2rem; margin-bottom: 5px;"><?php echo $stats_completed; ?></h3>
                <span style="color: var(--text-muted);">Completed Treatments</span>
            </div>
        </div>
    </div>

    <!-- Appointment List -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 25px; border-bottom: 1px solid #eee;">
            <h3 style="margin: 0;"><i class="fas fa-list-alt" style="color: var(--secondary-color);"></i> Appointment
                Management</h3>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight: 500;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div
                                            style="background: #e9ecef; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                                            <i class="fas fa-user" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <?php echo $row['patient_name']; ?>
                                        <a href="view_patient_records.php?patient_id=<?php echo $row['patient_id']; ?>"
                                            title="View Medical Records"
                                            style="color: var(--primary-color); font-size: 0.9rem;">
                                            <i class="fas fa-file-medical-alt"></i>
                                        </a>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted);">
                                    <?php echo date('M d, Y', strtotime($row['date'])); ?>
                                </td>
                                <td style="color: var(--text-muted);">
                                    <?php echo date("h:i A", strtotime($row['time'])); ?>
                                </td>
                                <td>
                                    <?php
                                    $status = $row['status'];
                                    $badge_class = 'badge-pending';
                                    if ($status == 'confirmed')
                                        $badge_class = 'badge-success';
                                    elseif ($status == 'rejected')
                                        $badge_class = 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <?php if ($status == 'pending'): ?>
                                            <a href="doctor_dashboard.php?action=confirmed&id=<?php echo $row['id']; ?>"
                                                class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;">
                                                Accept
                                            </a>
                                            <a href="doctor_dashboard.php?action=rejected&id=<?php echo $row['id']; ?>"
                                                class="btn btn-outline"
                                                style="padding: 6px 12px; font-size: 0.85rem; color: #ff7675; border-color: #ff7675;">
                                                Reject
                                            </a>
                                        <?php elseif ($status == 'confirmed'): ?>
                                            <a href="../chat.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-secondary"
                                                style="padding: 6px 12px; font-size: 0.85rem; position: relative;">
                                                <i class="fas fa-comments"></i> Chat
                                                <?php if ($row['unread_msgs'] > 0): ?>
                                                    <span
                                                        style="position: absolute; top: -5px; right: -5px; background: var(--danger-color); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; display: flex; align-items: center; justify-content: center;">
                                                        <?php echo $row['unread_msgs']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </a>
                                            <button onclick="startVideoCall(<?php echo $row['id']; ?>)" class="btn btn-primary"
                                                style="padding: 6px 12px; font-size: 0.85rem; background: #6c5ce7; border-color: #6c5ce7;">
                                                <i class="fas fa-video"></i> Call
                                            </button>
                                            <button onclick="markComplete(<?php echo $row['id']; ?>)" class="btn btn-success"
                                                style="padding: 6px 12px; font-size: 0.85rem; background: var(--success-color); border: none; color: white;">
                                                <i class="fas fa-check"></i> Done
                                            </button>
                                        <?php elseif ($status == 'completed'): ?>
                                            <span style="color: var(--success-color); font-weight: 500;"><i
                                                    class="fas fa-check-double"></i> Completed</span>
                                            <a href="write_prescription.php?appointment_id=<?php echo $row['id']; ?>"
                                                class="btn btn-outline"
                                                style="padding: 4px 10px; font-size: 0.8rem; margin-left: 10px;">
                                                <i class="fas fa-prescription"></i> Prescribe
                                            </a>
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
    function startVideoCall(id) {
        const formData = new FormData();
        formData.append('appointment_id', id);
        formData.append('status', 1); // Set active

        fetch('../api/toggle_call_status.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = '../video_consultation.php?appointment_id=' + id;
                } else {
                    alert('Error starting call: ' + data.message);
                }
            });
    }

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
<?php include '../includes/footer.php'; ?>