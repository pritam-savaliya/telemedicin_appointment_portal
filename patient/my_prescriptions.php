<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

// Fetch all prescriptions for this patient
$sql = "SELECT p.*, u.fullname as doctor_name, a.date as appointment_date, a.status as appointment_status
        FROM prescriptions p
        JOIN users u ON p.doctor_id = u.id
        JOIN appointments a ON p.appointment_id = a.id
        WHERE p.patient_id = $patient_id
        ORDER BY a.date DESC";
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
        <a href="my_appointments.php" class="sidebar-item"><i class="fas fa-history"></i> Consultation History</a>
        <a href="upload_report.php" class="sidebar-item"><i class="fas fa-file-medical"></i> Medical Records</a>
        <a href="my_prescriptions.php" class="sidebar-item active"><i class="fas fa-pills"></i> My Prescriptions</a>
        
        <?php if($has_confirmed): ?>
        <a href="../messages.php" class="sidebar-item"><i class="fas fa-comments"></i> Messages</a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="../profile.php" class="sidebar-item"><i class="fas fa-user-gear"></i> My Profile</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">My Prescriptions</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Access your digitized medical prescriptions and advice.</p>
            </div>
        </div>

        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-glass); background: rgba(255,255,255,0.02);">
                <h3 style="margin: 0; font-size: 1.2rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-file-prescription" style="color: var(--primary);"></i> Issued Prescriptions
                </h3>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: rgba(255,255,255,0.01);">
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Doctor</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Date</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Diagnosis</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="font-weight: 700; color: var(--text-main);">Dr. <?php echo htmlspecialchars($row['doctor_name']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--primary); font-weight: 600;">Verified Provider</div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="font-weight: 600;"><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="font-size: 0.9rem; color: var(--text-main); font-weight: 500;"><?php echo htmlspecialchars($row['diagnosis']); ?></div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem; text-align: center;">
                                        <a href="view_prescription.php?appointment_id=<?php echo $row['appointment_id']; ?>" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.85rem; border-radius: 10px;">
                                            <i class="fas fa-eye"></i> View Full
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="padding: 5rem; text-align: center; color: var(--text-muted);">
                                    <div style="font-size: 3rem; opacity: 0.2; margin-bottom: 1.5rem;">
                                        <i class="fas fa-pills"></i>
                                    </div>
                                    <p style="font-size: 1.1rem; font-weight: 600;">No prescriptions issued yet.</p>
                                    <p style="font-size: 0.9rem; opacity: 0.6;">Consult with a doctor to receive your digital prescription.</p>
                                    <a href="book_appointment.php" class="btn btn-secondary" style="margin-top: 1.5rem;">Book a Consultation</a>
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
