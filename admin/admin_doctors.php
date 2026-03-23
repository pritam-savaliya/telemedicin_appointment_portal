<?php
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// Handle Doctor Onboarding
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['onboard_doctor'])) {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $specialty = $conn->real_escape_string($_POST['specialty']);
    $qualification = $conn->real_escape_string($_POST['qualification']);
    
    // Check if email exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $message = "<div class='badge badge-danger' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-exclamation-circle'></i> This email identity is already registered in the network.</div>";
    } else {
        // Create user
        $sql = "INSERT INTO users (fullname, email, password, role, is_approved, email_verified) VALUES ('$fullname', '$email', '$password', 'doctor', 1, 1)";
        if ($conn->query($sql)) {
            $user_id = $conn->insert_id;
            // Create profile
            $conn->query("INSERT INTO doctor_profiles (user_id, specialty, qualification, experience, bio) VALUES ($user_id, '$specialty', '$qualification', '0 Years', 'New practitioner joined the network.')");
            $message = "<div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-check-circle'></i> New clinician successfully onboarded and authorized.</div>";
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'approved') {
        $message = "<div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-check-circle'></i> Practitioner authorized successfully.</div>";
    } elseif ($_GET['msg'] == 'rejected') {
        $message = "<div class='badge badge-warning' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-user-minus'></i> Account revoked and identity purged.</div>";
    } elseif ($_GET['msg'] == 'error') {
        $message = "<div class='badge badge-danger' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-exclamation-triangle'></i> Decommissioning sequence failed. System safeguards active.</div>";
    }
}

$doctors_result = $conn->query("SELECT u.*, dp.specialty, dp.qualification FROM users u LEFT JOIN doctor_profiles dp ON u.id = dp.user_id WHERE u.role = 'doctor' ORDER BY u.created_at DESC");

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
        <a href="admin_doctors.php" class="sidebar-item active"><i class="fas fa-stethoscope"></i> Doctor Network</a>
        <a href="admin_appointments.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> Global Traffic</a>
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
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Practitioner Grid</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Clinical network verification and authority management.</p>
            </div>
            <div class="badge badge-success"><i class="fas fa-user-doctor"></i> Active Specialists: <?php echo $doctors_result->num_rows; ?></div>
        </div>

        <?php echo $message; ?>

        <!-- Clinician Onboarding Form -->
        <div class="glass-card" style="margin-bottom: 3rem;">
            <h3 style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-glass); padding-bottom: 1rem; display: flex; align-items: center; gap: 1rem;">
                <i class="fas fa-user-plus" style="color: var(--primary);"></i> 
                Network Escalation: Onboard New Clinician
            </h3>
            <form action="" method="POST" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase;">Full Name (with Prefix)</label>
                    <input type="text" name="fullname" class="form-control" placeholder="Dr. Sarah Johnson" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase;">Official Email</label>
                    <input type="email" name="email" class="form-control" placeholder="johnson@medconnect.sys" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase;">Primary Specialty</label>
                    <input type="text" name="specialty" class="form-control" placeholder="Cardiological Surgeon" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase;">Credentials</label>
                    <input type="text" name="qualification" class="form-control" placeholder="MBBS, MD, FRCS" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase;">Access Key (Password)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" name="onboard_doctor" class="btn btn-primary" style="width: 100%; padding: 1.1rem; font-weight: 700;">
                        Authorize Clinician <i class="fas fa-shield-check" style="margin-left: 10px;"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 2rem; border-bottom: 1px solid var(--border-glass); background: rgba(255,255,255,0.02);">
                <h3 style="display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-id-card-clip" style="color: var(--primary);"></i> 
                    Certified Network Nodes
                </h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Identity</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Credentials</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Verification</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Timeline</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $doctors_result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1.5rem 2rem;">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <?php 
                                        $admin_doc_pic = !empty($row['profile_pic']) && $row['profile_pic'] != 'default_user.png' ? BASE_URL . 'assets/uploads/profile_pics/' . $row['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($row['fullname']) . '&background=random&color=fff&size=100';
                                        ?>
                                        <img src="<?php echo $admin_doc_pic; ?>" style="width: 40px; height: 40px; border-radius: 12px; object-fit: cover; border: 2px solid var(--border-glass);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['fullname']); ?>&background=random&color=fff&size=100'">
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-main);">Dr. <?php echo $row['fullname']; ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-dim);"><?php echo $row['email']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.5rem 2rem;">
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;"><?php echo $row['specialty'] ?? 'General Practitioner'; ?></div>
                                    <div style="font-size: 0.75rem; color: var(--primary); font-weight: 600;"><?php echo $row['qualification'] ?? 'MBBS, MD'; ?></div>
                                </td>
                                <td style="padding: 1.5rem 2rem;">
                                    <?php if ($row['is_approved'] == 1): ?>
                                        <span style="color: var(--success); font-weight: 600; font-size: 0.85rem;"><i class="fas fa-certificate"></i> Verified</span>
                                    <?php else: ?>
                                        <span style="color: var(--warning); font-weight: 600; font-size: 0.85rem;"><i class="fas fa-clock"></i> Screening</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1.5rem 2rem; font-size: 0.9rem; color: var(--text-dim);">
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </td>
                                <td style="padding: 1.5rem 2rem;">
                                    <div style="display: flex; gap: 1rem;">
                                        <?php if ($row['is_approved'] == 0): ?>
                                            <a href="update_account_status.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Authorize</a>
                                        <?php endif; ?>
                                        <a href="update_account_status.php?action=reject&id=<?php echo $row['id']; ?>" onclick="return confirm('Revoke clinician access?')" style="color: var(--accent); display: flex; align-items: center; padding: 0.5rem;"><i class="fas fa-trash-can"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>