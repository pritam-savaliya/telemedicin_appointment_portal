<?php
include '../includes/db.php';
// session_start(); // Already handled in db.php

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action_id = intval($_GET['id']);
    $action = $_GET['action']; 

    if ($action == 'delete') {
        $conn->query("DELETE FROM chat_messages WHERE appointment_id = $action_id");
        if ($conn->query("DELETE FROM appointments WHERE id = $action_id") === TRUE) {
            $message = "<div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-check-circle'></i> Appointment trace purged successfully.</div>";
        }
    } elseif ($action == 'confirm') {
        $conn->query("UPDATE appointments SET status = 'confirmed' WHERE id = $action_id");
        $message = "<div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-check-circle'></i> Consultation slot confirmed.</div>";
    } elseif ($action == 'cancel') {
        $conn->query("UPDATE appointments SET status = 'cancelled' WHERE id = $action_id");
        $message = "<div class='badge badge-warning' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-ban'></i> Appointment decommissioned.</div>";
    }
}

// Filter Logic
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$where_clause = "";
if ($filter_status != 'all') {
    $filter_status_safe = $conn->real_escape_string($filter_status);
    $where_clause = "WHERE a.status = '$filter_status_safe'";
}

// Fetch Appointments
$sql = "SELECT a.*, p.fullname AS patient_name, d.fullname AS doctor_name, p.profile_pic as patient_pic, d.profile_pic as doctor_pic 
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        JOIN users d ON a.doctor_id = d.id 
        $where_clause
        ORDER BY a.date DESC, a.time ASC";
$result = $conn->query($sql);

include '../includes/header.php'; 
?>

<div class="dashboard-layout fade-in">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
            <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem;"><i class="fas fa-shield-halved"></i></div>
            <h4>Admin Terminal</h4>
            <span class="badge badge-danger" style="font-size: 0.6rem; margin-top: 0.5rem; background: rgba(244,63,94,0.1); color: var(--accent);">System Administrator</span>
        </div>

        <a href="admin_dashboard.php" class="sidebar-item"><i class="fas fa-gauge-high"></i> Control Center</a>
        <a href="admin_patients.php" class="sidebar-item"><i class="fas fa-id-card"></i> Patient Registry</a>
        <a href="admin_doctors.php" class="sidebar-item"><i class="fas fa-stethoscope"></i> Doctor Network</a>
        <a href="admin_appointments.php" class="sidebar-item active"><i class="fas fa-calendar-check"></i> Global Traffic</a>
        <a href="admin_reviews.php" class="sidebar-item"><i class="fas fa-star-half-stroke"></i> Quality Control</a>
        
        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="system_settings.php" class="sidebar-item"><i class="fas fa-gears"></i> Platform Settings</a>
            <a href="support_messages.php" class="sidebar-item"><i class="fas fa-headset"></i> Support Hub</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Global Traffic</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Monitoring and orchestrating platform consultations.</p>
            </div>
            <div class="badge badge-info"><i class="fas fa-route"></i> Active Streams: <?php echo $result->num_rows; ?></div>
        </div>

        <?php echo $message; ?>

        <!-- Enhanced Filtering -->
        <div class="glass-card" style="padding: 0.5rem; margin-bottom: 2rem; border-radius: 15px;">
            <div style="display: flex; gap: 5px;">
                <?php 
                $tabs = ['all' => 'Every Stream', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Resolved', 'cancelled' => 'Dropped'];
                foreach($tabs as $key => $label): 
                    $active = ($filter_status == $key) ? 'background: var(--primary); color: white;' : 'color: var(--text-dim);';
                ?>
                    <a href="admin_appointments.php?status=<?php echo $key; ?>" 
                       style="flex: 1; padding: 0.8rem; text-align: center; border-radius: 10px; font-weight: 700; font-size: 0.85rem; text-decoration: none; transition: 0.3s; <?php echo $active; ?>">
                       <?php echo $label; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Engagement ID</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Participants</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Schedule</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">State</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase; text-align: right;">Operations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): 
                                $status = $row['status'];
                                $status_color = 'var(--warning)';
                                $status_icon = 'clock';
                                if($status == 'confirmed') { $status_color = 'var(--success)'; $status_icon = 'check-double'; }
                                elseif($status == 'cancelled' || $status == 'rejected') { $status_color = 'var(--accent)'; $status_icon = 'circle-xmark'; }
                                elseif($status == 'completed') { $status_color = 'var(--primary)'; $status_icon = 'flag-checkered'; }
                            ?>
                                <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.5rem 2rem; font-family: monospace; color: var(--secondary);">#APT-<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="display: flex; flex-direction: column; gap: 8px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <i class="fas fa-user-injured" style="width: 15px; font-size: 0.8rem; color: var(--text-dim);"></i>
                                                <span style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;"><?php echo $row['patient_name']; ?></span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <i class="fas fa-user-md" style="width: 15px; font-size: 0.8rem; color: var(--text-dim);"></i>
                                                <span style="font-weight: 500; color: var(--text-dim); font-size: 0.85rem;">Dr. <?php echo str_replace('Dr. ', '', $row['doctor_name']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="font-weight: 700; color: var(--text-main);"><?php echo date('M d, Y', strtotime($row['date'])); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--primary); font-weight: 600;"><?php echo date('h:i A', strtotime($row['time'])); ?></div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <span style="display: inline-flex; align-items: center; gap: 8px; color: <?php echo $status_color; ?>; font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">
                                            <i class="fas fa-<?php echo $status_icon; ?>"></i> <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1.5rem 2rem; text-align: right;">
                                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                            <?php if ($status == 'pending'): ?>
                                                <a href="admin_appointments.php?action=confirm&id=<?php echo $row['id']; ?>&status=<?php echo $filter_status; ?>" 
                                                   class="btn btn-primary" style="padding: 0.5rem 0.8rem; font-size: 0.75rem; border-radius: 8px;" title="Confirm">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="admin_appointments.php?action=cancel&id=<?php echo $row['id']; ?>&status=<?php echo $filter_status; ?>" 
                                                   class="btn btn-secondary" style="padding: 0.5rem 0.8rem; font-size: 0.75rem; border-radius: 8px;" title="Cancel">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="admin_appointments.php?action=delete&id=<?php echo $row['id']; ?>&status=<?php echo $filter_status; ?>" 
                                               onclick="return confirm('WARNING: Critical data loss. Delete appointment?')" 
                                               class="btn btn-secondary" style="padding: 0.5rem 0.8rem; font-size: 0.75rem; border-radius: 8px; border-color: var(--accent); color: var(--accent);" title="Purge">
                                                <i class="fas fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding: 5rem; text-align: center; color: var(--text-muted);">
                                    <i class="fas fa-calendar-xmark fa-3x" style="opacity: 0.2; margin-bottom: 1.5rem;"></i>
                                    <p style="font-size: 1.2rem; font-weight: 500;">No active traffic patterns found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>