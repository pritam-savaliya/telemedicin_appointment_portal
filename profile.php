<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Dynamic Dashboard URL
$dash_url = "index.php";
if ($user['role'] == 'patient') $dash_url = "patient/patient_dashboard.php";
elseif ($user['role'] == 'doctor') $dash_url = "doctor/doctor_dashboard.php";
elseif ($user['role'] == 'admin') $dash_url = "admin/admin_dashboard.php";

include 'includes/header.php'; 
?>

<div class="dashboard-layout fade-in">
    <!-- Role-Based Sidebar -->
    <?php if ($user['role'] == 'patient'): ?>
        <aside class="sidebar">
            <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
                <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                    <?php 
                    $sidebar_pic = (!empty($user['profile_pic']) && $user['profile_pic'] != 'default_user.png') ? BASE_URL . 'assets/uploads/profile_pics/' . $user['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['fullname']) . '&background=random&color=fff&size=200';
                    ?>
                    <img src="<?php echo $sidebar_pic; ?>" 
                         style="width: 70px; height: 70px; border-radius: 50%; border: 2px solid var(--primary); object-fit: cover; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname']); ?>&background=random&color=fff&size=200'">
                    <div style="position: absolute; bottom: 2px; right: 2px; width: 14px; height: 14px; background: var(--success); border-radius: 50%; border: 3px solid var(--bg-sidebar);"></div>
                </div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.2rem;"><?php echo $user['fullname']; ?></h4>
                <span class="badge badge-info" style="font-size: 0.6rem; opacity: 0.8; background: rgba(99,102,241,0.1); color: var(--primary);">Patient Portal</span>
            </div>

            <a href="patient/patient_dashboard.php" class="sidebar-item"><i class="fas fa-th-large"></i> Overview</a>
            <a href="patient/book_appointment.php" class="sidebar-item"><i class="fas fa-calendar-plus"></i> New Consultation</a>
            <a href="patient/my_appointments.php" class="sidebar-item"><i class="fas fa-history"></i> Consultation History</a>
            <a href="patient/upload_report.php" class="sidebar-item"><i class="fas fa-file-medical"></i> Medical Records</a>
            <a href="patient/my_prescriptions.php" class="sidebar-item"><i class="fas fa-pills"></i> My Prescriptions</a>

            <?php 
            $has_confirmed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND status = 'confirmed'")->fetch_assoc()['c'] > 0;
            if($has_confirmed): ?>
            <a href="messages.php" class="sidebar-item"><i class="fas fa-comments"></i> Messages</a>
            <?php endif; ?>

            <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                <a href="profile.php" class="sidebar-item active"><i class="fas fa-user-gear"></i> My Profile</a>
                <a href="auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
            </div>
        </aside>

    <?php elseif ($user['role'] == 'doctor'): ?>
        <aside class="sidebar">
            <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
                <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                    <?php 
                    $doc_sidebar_pic = (!empty($user['profile_pic']) && $user['profile_pic'] != 'default_user.png') ? BASE_URL . 'assets/uploads/profile_pics/' . $user['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['fullname']) . '&background=random&color=fff&size=200';
                    ?>
                    <img src="<?php echo $doc_sidebar_pic; ?>" 
                         style="width: 70px; height: 70px; border-radius: 20px; border: 2px solid var(--primary); object-fit: cover; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname']); ?>&background=random&color=fff&size=200'">
                    <div style="position: absolute; bottom: -2px; right: -2px; width: 16px; height: 16px; background: var(--success); border-radius: 50%; border: 3px solid var(--bg-sidebar);"></div>
                </div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.2rem;">Dr. <?php echo str_replace('Dr. ', '', $user['fullname']); ?></h4>
                <span class="badge badge-success" style="font-size: 0.6rem; opacity: 0.8; background: rgba(16,185,129,0.1); color: var(--success);">Verified Clinician</span>
            </div>

            <a href="doctor/doctor_dashboard.php" class="sidebar-item"><i class="fas fa-chart-line"></i> Command Center</a>
            <a href="doctor/manage_schedule.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> My Schedule</a>
            <a href="doctor/view_patient_records.php" class="sidebar-item"><i class="fas fa-user-injured"></i> Patient Ledger</a>
            <a href="doctor/reports.php" class="sidebar-item"><i class="fas fa-file-contract"></i> Clinical Analytics</a>
            
            <?php 
            $has_active_consults = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $user_id AND status = 'confirmed'")->fetch_assoc()['c'] > 0;
            if($has_active_consults): ?>
            <a href="messages.php" class="sidebar-item"><i class="fas fa-comments"></i> Consultations</a>
            <?php endif; ?>

            <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                <a href="profile.php" class="sidebar-item active"><i class="fas fa-user-doctor"></i> My Profile</a>
                <a href="auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
            </div>
        </aside>

    <?php elseif ($user['role'] == 'admin'): ?>
        <aside class="sidebar">
            <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
                <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem;"><i class="fas fa-shield-halved"></i></div>
                <h4>Admin Terminal</h4>
                <span class="badge badge-danger" style="font-size: 0.6rem; margin-top: 0.5rem; background: rgba(244,63,94,0.1); color: var(--accent);">System Administrator</span>
            </div>

            <a href="admin/admin_dashboard.php" class="sidebar-item"><i class="fas fa-gauge-high"></i> Control Center</a>
            <a href="admin/admin_patients.php" class="sidebar-item"><i class="fas fa-id-card"></i> Patient Registry</a>
            <a href="admin/admin_doctors.php" class="sidebar-item"><i class="fas fa-stethoscope"></i> Doctor Network</a>
            <a href="admin/admin_appointments.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> Global Traffic</a>
            <a href="admin/admin_reviews.php" class="sidebar-item"><i class="fas fa-star-half-stroke"></i> Quality Control</a>
            
            <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                <a href="admin/system_settings.php" class="sidebar-item"><i class="fas fa-gears"></i> Platform Settings</a>
                <a href="admin/support_messages.php" class="sidebar-item"><i class="fas fa-headset"></i> Support Hub</a>
                <a href="profile.php" class="sidebar-item active"><i class="fas fa-user-gear"></i> My Profile</a>
                <a href="auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
            </div>
        </aside>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Clinical Profile</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Manage your identity and medical ledger information.</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="edit_profile.php" class="btn btn-primary" style="padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-pen-to-square"></i> Modify Details
                </a>
                <a href="<?php echo $dash_url; ?>" class="btn btn-secondary" style="padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-arrow-left"></i> Hub
                </a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Info Matrix - Personal -->
            <div class="glass-card" style="border: 1px solid var(--border-glass);">
                <h4 style="margin-bottom: 2rem; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; color: var(--primary); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-shield-heart"></i> Communication Node
                </h4>
                <div style="display: grid; gap: 2rem;">
                    <div style="display: flex; gap: 1.5rem; align-items: center; background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-glass);">
                        <div class="stat-icon" style="width: 50px; height: 50px; background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 1.2rem;"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Identity Email</div>
                            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-main);"><?php echo $user['email']; ?></div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1.5rem; align-items: center; background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-glass);">
                        <div class="stat-icon" style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); color: var(--success); font-size: 1.2rem;"><i class="fas fa-mobile-screen"></i></div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Priority Phone</div>
                            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-main);"><?php echo $user['phone'] ?: '<i style="opacity:0.3">Unlinked</i>'; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Matrix - Biological/Biometric -->
            <div class="glass-card" style="border: 1px solid var(--border-glass);">
                <h4 style="margin-bottom: 2rem; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; color: var(--secondary); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-dna"></i> Personal Metadata
                </h4>
                <div style="display: grid; gap: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div style="background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-glass);">
                            <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px;"><i class="fas fa-venus-mars" style="margin-right: 5px;"></i> Gender</div>
                            <div style="font-weight: 700; font-size: 1rem; color: var(--text-main);"><?php echo $user['gender'] ?: 'Unspecified'; ?></div>
                        </div>
                        <div style="background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-glass);">
                            <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px;"><i class="fas fa-cake-candles" style="margin-right: 5px;"></i> Birthday</div>
                            <div style="font-weight: 700; font-size: 1rem; color: var(--text-main);"><?php echo $user['dob'] ? date("d M Y", strtotime($user['dob'])) : 'Unknown'; ?></div>
                        </div>
                    </div>
                    <div style="background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-glass);">
                        <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px;"><i class="fas fa-location-dot" style="margin-right: 5px;"></i> Residential Node</div>
                        <div style="font-weight: 700; font-size: 1rem; color: var(--text-main); line-height: 1.5;"><?php echo $user['address'] ?: '<i style="opacity:0.3">No address recorded</i>'; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>