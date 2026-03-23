<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$sql = "SELECT appointments.*, users.fullname as doctor_name, users.role as doctor_role,
        (SELECT COUNT(*) FROM prescriptions WHERE appointment_id = appointments.id) as has_prescription,
        (SELECT COUNT(*) FROM reviews WHERE appointment_id = appointments.id) as has_review
        FROM appointments 
        LEFT JOIN users ON appointments.doctor_id = users.id 
        WHERE patient_id = $patient_id 
        ORDER BY appointments.date DESC";
$result = $conn->query($sql);

$has_confirmed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $patient_id AND status = 'confirmed'")->fetch_assoc()['c'] > 0;

include '../includes/header.php'; 
?>

<div class="dashboard-layout fade-in">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
            <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                <?php 
                $pat_sidebar_pic = (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) && $_SESSION['profile_pic'] != 'default_user.png') ? BASE_URL . 'assets/uploads/profile_pics/' . $_SESSION['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname']) . '&background=random&color=fff&size=200';
                ?>
                <img src="<?php echo $pat_sidebar_pic; ?>" 
                     style="width: 70px; height: 70px; border-radius: 20px; border: 2px solid var(--primary); object-fit: cover; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['fullname']); ?>&background=random&color=fff&size=200'">
                <div style="position: absolute; bottom: -2px; right: -2px; width: 16px; height: 16px; background: var(--success); border-radius: 50%; border: 3px solid var(--bg-sidebar);"></div>
            </div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.2rem;"><?php echo $_SESSION['fullname']; ?></h4>
            <span class="badge badge-info" style="font-size: 0.6rem; opacity: 0.8;">Patient Portal</span>
        </div>

        <a href="patient_dashboard.php" class="sidebar-item"><i class="fas fa-th-large"></i> Overview</a>
        <a href="book_appointment.php" class="sidebar-item"><i class="fas fa-calendar-plus"></i> New Consultation</a>
        <a href="my_appointments.php" class="sidebar-item active"><i class="fas fa-history"></i> Consultation History</a>
        <a href="upload_report.php" class="sidebar-item"><i class="fas fa-file-medical"></i> Medical Records</a>
        <a href="my_prescriptions.php" class="sidebar-item"><i class="fas fa-pills"></i> My Prescriptions</a>
        
        <?php if($has_confirmed): ?>
        <a href="../messages.php" class="sidebar-item"><i class="fas fa-comments"></i> Messages</a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="../profile.php" class="sidebar-item"><i class="fas fa-user-gear"></i> My Profile</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Consultation History</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Review your past medical sessions and download prescriptions.</p>
            </div>
            <a href="book_appointment.php" class="btn btn-primary" style="padding: 1rem 2rem;">
                <i class="fas fa-plus"></i> Schedule New
            </a>
        </div>

        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Specialist</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Timestamp</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Status</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Node Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="display: flex; align-items: center; gap: 1.2rem;">
                                            <div class="stat-icon" style="width: 45px; height: 45px; background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: var(--text-main);">
                                                    <?php 
                                                    if($row['is_emergency']) {
                                                        echo "<span style='color: var(--accent);'><i class='fas fa-kit-medical'></i> Emergency Dispatch</span>";
                                                    } else {
                                                        echo "Dr. " . htmlspecialchars($row['doctor_name']); 
                                                    }
                                                    ?>
                                                </div>
                                                <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase;">
                                                    <?php echo $row['is_emergency'] ? 'Immediate Care Protocol' : 'Clinical Specialist'; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="font-weight: 600;"><?php echo date('M d, Y', strtotime($row['date'])); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-dim);"><?php echo date("h:i A", strtotime($row['time'])); ?></div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <?php
                                        $status = $row['status'];
                                        if ($status == 'confirmed') echo '<span class="badge badge-info"><i class="fas fa-check"></i> Confirmed</span>';
                                        elseif ($status == 'completed') echo '<span class="badge badge-success"><i class="fas fa-circle-check"></i> Completed</span>';
                                        elseif ($status == 'rejected') echo '<span class="badge badge-danger"><i class="fas fa-circle-xmark"></i> Terminated</span>';
                                        else echo '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>';
                                        ?>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="display: flex; gap: 0.8rem;">
                                            <?php if ($status == 'confirmed'): ?>
                                                <a href="../chat.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;">
                                                    <i class="fas fa-messages"></i> Enter Chat
                                                </a>
                                            <?php elseif ($status == 'completed'): ?>
                                                <?php if ($row['has_prescription'] > 0): ?>
                                                    <a href="view_prescription.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;">
                                                        <i class="fas fa-file-prescription"></i> Clinical Rx
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($row['has_review'] == 0): ?>
                                                    <a href="rate_doctor.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;">
                                                        <i class="fas fa-star"></i> Feedback
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="cancel_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 0.6rem 1.2rem; font-size: 0.85rem; color: var(--accent); border-color: rgba(244,63,94,0.2);" onclick="return confirm('Abort this consultation request?')">
                                                    <i class="fas fa-ban"></i> Abort
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 5rem; color: var(--text-dim);">
                                    <div style="font-size: 3rem; margin-bottom: 1.5rem; opacity: 0.3;"><i class="fas fa-calendar-xmark"></i></div>
                                    <p>No medical sessions found in your encrypted ledger.</p>
                                    <a href="book_appointment.php" class="btn btn-primary" style="margin-top: 2rem;">Secure First Slot</a>
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
