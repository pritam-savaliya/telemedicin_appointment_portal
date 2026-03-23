<?php

include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

// Handle Status Updates (Simplified for rewrite)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $appt_id = intval($_GET['id']);
    $status = $_GET['action'];
    if (in_array($status, ['confirmed', 'rejected'])) {
        $update_sql = "UPDATE appointments SET status = '$status' WHERE id = $appt_id AND doctor_id = $doctor_id";
        $conn->query($update_sql);
        // Notification logic would go here
    }
    header("Location: doctor_dashboard.php");
    exit();
}

// Fetch Stats
$stats_total = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id")->fetch_assoc()['c'];
$stats_pending = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'pending'")->fetch_assoc()['c'];
$stats_active = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'confirmed'")->fetch_assoc()['c'];
$stats_completed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'completed'")->fetch_assoc()['c'];
?>

<?php 
$doctor_id = $_SESSION['user_id'];
$has_active_consults = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'confirmed'")->fetch_assoc()['c'] > 0;
include '../includes/header.php'; 
?>

<div class="dashboard-layout fade-in">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
            <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                <?php 
                $doc_sidebar_pic = (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) && $_SESSION['profile_pic'] != 'default_user.png') ? BASE_URL . 'assets/uploads/profile_pics/' . $_SESSION['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname']) . '&background=random&color=fff&size=200';
                ?>
                <img src="<?php echo $doc_sidebar_pic; ?>" 
                     style="width: 70px; height: 70px; border-radius: 20px; border: 2px solid var(--primary); object-fit: cover; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['fullname']); ?>&background=random&color=fff&size=200'">
                <div style="position: absolute; bottom: -2px; right: -2px; width: 16px; height: 16px; background: var(--success); border-radius: 50%; border: 3px solid var(--bg-sidebar);"></div>
            </div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.2rem;">Dr. <?php echo str_replace('Dr. ', '', $_SESSION['fullname']); ?></h4>
            <span class="badge badge-success" style="font-size: 0.6rem; opacity: 0.8; background: rgba(16,185,129,0.1); color: var(--success);">Verified Clinician</span>
        </div>

        <a href="doctor_dashboard.php" class="sidebar-item active"><i class="fas fa-chart-line"></i> Command Center</a>
        <a href="manage_schedule.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> My Schedule</a>
        <a href="view_patient_records.php" class="sidebar-item"><i class="fas fa-user-injured"></i> Patient Ledger</a>
        <a href="reports.php" class="sidebar-item"><i class="fas fa-file-contract"></i> Clinical Analytics</a>
        
        <?php if($has_active_consults): ?>
        <a href="../messages.php" class="sidebar-item"><i class="fas fa-comments"></i> Consultations</a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="../profile.php" class="sidebar-item"><i class="fas fa-user-doctor"></i> My Profile</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Doctor's Panel</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Manage appointments and patient healthcare performance.</p>
            </div>
            <div style="text-align: right;">
                <div class="badge badge-info" style="margin-bottom: 0.5rem;"><?php echo date('l, M d'); ?></div>
                <div id="realtime-clock" style="font-family: var(--font-heading); font-weight: 800; font-size: 1.2rem; color: var(--primary);">00:00:00</div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="stats-grid">
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $stats_total; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Appointments</span>
                </div>
            </div>
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $stats_pending; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Pending</span>
                </div>
            </div>
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(6, 182, 212, 0.1); color: var(--secondary);">
                    <i class="fas fa-video"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $stats_active; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Active Calls</span>
                </div>
            </div>
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                    <i class="fas fa-check-double"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $stats_completed; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Completed</span>
                </div>
            </div>
        </div>

        <!-- Appointments Management -->
        <div class="glass-card" style="padding: 0;">
            <div style="padding: 2rem; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-clipboard-list" style="color: var(--primary);"></i> Appointment Management
                </h3>
                <div style="display: flex; gap: 1rem;">
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-size: 0.8rem;"></i>
                        <input type="text" placeholder="Search patients..." class="form-control" style="padding: 0.6rem 2.5rem; font-size: 0.85rem; width: 250px;">
                    </div>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Patient Details</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Schedule</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Status</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $appt_sql = "SELECT appointments.*, users.fullname as patient_name, users.profile_pic as pat_pic,
                                    (SELECT COUNT(*) FROM chat_messages WHERE appointment_id = appointments.id AND is_read = 0 AND sender_id != $doctor_id) as unread
                                    FROM appointments 
                                    JOIN users ON appointments.patient_id = users.id 
                                    WHERE doctor_id = $doctor_id ORDER BY appointments.date DESC";
                        $appt_res = $conn->query($appt_sql);
                        
                        if ($appt_res->num_rows > 0):
                            while ($row = $appt_res->fetch_assoc()): 
                                $status = $row['status'];
                                $badge = 'badge-warning';
                                if($status == 'confirmed') $badge = 'badge-success';
                                elseif($status == 'completed') $badge = 'badge-info';
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1.5rem 2rem;">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <?php 
                                        $pat_pic_url = !empty($row['pat_pic']) && $row['pat_pic'] != 'default_user.png' ? BASE_URL . 'assets/uploads/profile_pics/' . $row['pat_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($row['patient_name']) . '&background=random&color=fff&size=100';
                                        ?>
                                        <img src="<?php echo $pat_pic_url; ?>" 
                                             style="width: 45px; height: 45px; border-radius: 12px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['patient_name']); ?>&background=random&color=fff&size=100'">
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-main);"><?php echo $row['patient_name']; ?></div>
                                            <a href="view_patient_records.php?patient_id=<?php echo $row['patient_id']; ?>" style="font-size: 0.75rem; color: var(--primary); font-weight: 700;">View History →</a>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.5rem 2rem;">
                                    <div style="font-weight: 600;"><?php echo date('M d, Y', strtotime($row['date'])); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-dim);"><?php echo date("h:i A", strtotime($row['time'])); ?></div>
                                </td>
                                <td style="padding: 1.5rem 2rem;">
                                    <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($status); ?></span>
                                </td>
                                <td style="padding: 1.5rem 2rem;">
                                    <div style="display: flex; gap: 10px;">
                                        <?php if ($status == 'pending'): ?>
                                            <a href="doctor_dashboard.php?action=confirmed&id=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 0.6rem 1rem; font-size: 0.8rem;">Accept</a>
                                            <a href="doctor_dashboard.php?action=rejected&id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 0.6rem 1rem; font-size: 0.8rem; color: var(--accent);">Decline</a>
                                        <?php elseif ($status == 'confirmed'): ?>
                                            <a href="../chat.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 0.6rem; position: relative;" title="Chat">
                                                <i class="fas fa-comment"></i>
                                                <?php if($row['unread'] > 0): ?>
                                                    <span style="position: absolute; top: -5px; right: -5px; background: var(--accent); color: white; width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; border: 2px solid var(--bg-card);"><?php echo $row['unread']; ?></span>
                                                <?php endif; ?>
                                            </a>
                                            <button onclick="startVideoCall(<?php echo $row['id']; ?>)" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.8rem;">
                                                <i class="fas fa-video"></i> Start Call
                                            </button>
                                            <a href="write_prescription.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 0.6rem; color: var(--success);" title="Finalize & Prescribe">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        <?php elseif ($status == 'completed'): ?>
                                            <a href="write_prescription.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 0.6rem 1rem; font-size: 0.8rem; background: rgba(16,185,129,0.05); border-color: rgba(16,185,129,0.2); color: var(--success);">
                                                <i class="fas fa-file-signature"></i> Prescribe
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; 
                        else: ?>
                            <tr><td colspan="4" style="padding: 4rem; text-align: center; color: var(--text-dim);">No appointment logs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    function updateTime() {
        const now = new Date();
        document.getElementById('realtime-clock').innerText = now.toLocaleTimeString();
    }
    setInterval(updateTime, 1000);
    updateTime();

    function startVideoCall(id) {
        const formData = new FormData();
        formData.append('appointment_id', id);
        formData.append('status', 1);
        fetch('../api/toggle_call_status.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => data.status === 'success' ? window.location.href = '../video_consultation.php?appointment_id=' + id : alert(data.message));
    }

    function markComplete(id) {
        if (confirm('Mark this treatment as successfully completed?')) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', 'completed');
            fetch('update_appointment_status.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => data.status === 'success' ? location.reload() : alert(data.message));
        }
    }
</script>

<?php include '../includes/footer.php'; ?>