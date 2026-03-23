<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

$view_mode = ($patient_id > 0) ? 'detail' : 'list';

if ($view_mode == 'detail') {
    // Fetch Patient Info
    $pat_sql = "SELECT fullname, email, profile_pic FROM users WHERE id = $patient_id";
    $pat_res = $conn->query($pat_sql);

    if ($pat_res->num_rows == 0) {
        $view_mode = 'list';
        $error_msg = "Requested patient not found.";
    } else {
        $patient = $pat_res->fetch_assoc();
        // Fetch Records
        $sql = "SELECT * FROM medical_records WHERE patient_id = $patient_id ORDER BY uploaded_at DESC";
        $result = $conn->query($sql);
    }
}

if ($view_mode == 'list') {
    // Fetch unique patients for this doctor
    $list_sql = "SELECT DISTINCT u.id, u.fullname, u.email, u.profile_pic 
                 FROM users u 
                 JOIN appointments a ON u.id = a.patient_id 
                 WHERE a.doctor_id = $doctor_id 
                 ORDER BY u.fullname ASC";
    $list_res = $conn->query($list_sql);
}
?>

<?php include '../includes/header.php'; ?>

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

        <a href="doctor_dashboard.php" class="sidebar-item"><i class="fas fa-chart-line"></i> Command Center</a>
        <a href="manage_schedule.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> My Schedule</a>
        <a href="view_patient_records.php" class="sidebar-item active"><i class="fas fa-user-injured"></i> Patient Ledger</a>
        <a href="reports.php" class="sidebar-item"><i class="fas fa-file-contract"></i> Clinical Analytics</a>
        
        <?php 
        $has_active_consults = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'confirmed'")->fetch_assoc()['c'] > 0;
        if($has_active_consults): ?>
        <a href="../messages.php" class="sidebar-item"><i class="fas fa-comments"></i> Consultations</a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="../profile.php" class="sidebar-item"><i class="fas fa-user-doctor"></i> My Profile</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <!-- Header Section -->
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <?php if ($view_mode == 'detail'): ?>
                    <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Clinical Ledger</h1>
                    <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Reviewing medical history for <span style="color: var(--primary); font-weight: 700;"><?php echo $patient['fullname']; ?></span>.</p>
                <?php else: ?>
                    <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Patient Registry</h1>
                    <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Comprehensive list of all nodes under your clinical supervision.</p>
                <?php endif; ?>
            </div>
            <?php if ($view_mode == 'detail'): ?>
                <a href="view_patient_records.php" class="btn btn-secondary" style="padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-arrow-left"></i> All Patients
                </a>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($view_mode == 'detail'): ?>
                <a href="view_patient_records.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> All Patients
                </a>
            <?php else: ?>
                <a href="doctor_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($view_mode == 'detail'): ?>
        <!-- DETAIL VIEW: Patient Records -->
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem 2rem; background: var(--bg-sidebar); border-bottom: 1px solid var(--border-glass);">
                <h4 style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-folder-open" style="color: var(--secondary);"></i> 
                    Patient Records Archive
                </h4>
            </div>
            
            <div style="overflow-x: auto;">
                <?php if ($result && $result->num_rows > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                                <th style="padding: 1.2rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Upload Date</th>
                                <th style="padding: 1.2rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Medical Description</th>
                                <th style="padding: 1.2rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Filename</th>
                                <th style="padding: 1.2rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): 
                                $file_ext = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
                                $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif']);
                                $file_url = BASE_URL . str_replace('../', '', $row['file_path']);
                            ?>
                                <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.5rem 2rem; font-weight: 600; font-size: 0.9rem;">
                                        <?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <div style="width: 40px; height: 40px; flex-shrink: 0; background: rgba(99, 102, 241, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                                <?php if($is_image): ?>
                                                    <i class="fas fa-image"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-file-pdf"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['description']); ?></div>
                                                <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo htmlspecialchars($row['file_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <?php if($is_image): ?>
                                            <div style="position: relative; width: 60px; height: 40px; border-radius: 6px; overflow: hidden; border: 1px solid var(--border-glass); cursor: pointer;" onclick="window.open('<?php echo $file_url; ?>', '_blank')">
                                                <img src="<?php echo $file_url; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                                                    <i class="fas fa-search-plus" style="color: white; font-size: 0.8rem;"></i>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div style="width: 60px; height: 40px; background: rgba(0,0,0,0.05); border-radius: 6px; border: 1px dashed var(--border-glass); display: flex; align-items: center; justify-content: center; color: var(--text-dim); font-size: 0.7rem;">
                                                PDF
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 1.5rem 2rem; text-align: center;">
                                        <a href="<?php echo $file_url; ?>" target="_blank" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.75rem; border-radius: 8px;">
                                            <i class="fas fa-external-link-alt"></i> View Full
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 5rem 2rem; color: var(--text-muted); display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                        <div style="width: 80px; height: 80px; background: var(--bg-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; opacity: 0.4;">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div>
                            <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 5px;">No Records Found</p>
                            <p style="font-size: 0.9rem; opacity: 0.7;">This patient hasn't uploaded any medical documents yet.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- LIST VIEW: All Patients -->
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem 2rem; background: var(--bg-sidebar); border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0; font-size: 1.1rem;">Assigned Patients Directory</h4>
                <div style="font-size: 0.8rem; color: var(--primary); font-weight: 700; text-transform: uppercase;">
                    Total: <?php echo $list_res->num_rows; ?> Records
                </div>
            </div>

            <?php if (isset($error_msg)): ?>
                <div class="badge badge-danger" style="margin: 1rem 2rem; padding: 1rem; width: calc(100% - 4rem); border-radius: 8px;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div style="overflow-x: auto;">
                <?php if ($list_res->num_rows > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                                <th style="padding: 1.2rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Patient Profile</th>
                                <th style="padding: 1.2rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Contact Info</th>
                                <th style="padding: 1.2rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $list_res->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <img src="<?php echo !empty($row['profile_pic']) ? BASE_URL . 'assets/uploads/profile_pics/' . $row['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($row['fullname']) . '&background=random&color=fff'; ?>" 
                                                 style="width: 42px; height: 42px; border-radius: 10px; object-fit: cover; border: 1px solid var(--border-glass);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['fullname']); ?>&background=random&color=fff'">
                                            <div style="font-weight: 700; color: var(--text-main); font-size: 1rem;"><?php echo $row['fullname']; ?></div>
                                        </div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="color: var(--text-muted); font-size: 0.9rem;"><i class="far fa-envelope" style="width: 20px;"></i> <?php echo $row['email']; ?></div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem; text-align: right;">
                                        <a href="view_patient_records.php?patient_id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1.2rem; font-size: 0.8rem; border-radius: 8px;">
                                            <i class="fas fa-history"></i> Medical History
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 5rem 2rem; color: var(--text-muted);">
                        <i class="fas fa-user-friends fa-3x" style="opacity: 0.2; margin-bottom: 1.5rem;"></i>
                        <p style="font-size: 1.2rem; font-weight: 500;">No patients found in your record.</p>
                        <p style="opacity: 0.6;">Patients will appear here once they book an appointment with you.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
