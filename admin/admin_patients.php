<?php
include '../includes/db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'rejected') {
        $message = "<div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-check-circle'></i> Identity decommissioned and metadata purged.</div>";
    } elseif ($_GET['msg'] == 'error') {
        $message = "<div class='badge badge-danger' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-exclamation-triangle'></i> Decommissioning sequence failed. Foreign constraints active.</div>";
    }
}

$patients_result = $conn->query("SELECT * FROM users WHERE role = 'patient' ORDER BY created_at DESC");

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
        <a href="admin_patients.php" class="sidebar-item active"><i class="fas fa-id-card"></i> Patient Registry</a>
        <a href="admin_doctors.php" class="sidebar-item"><i class="fas fa-stethoscope"></i> Doctor Network</a>
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
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Patient Registry</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Manage citizen identities and medical access rights.</p>
            </div>
            <div class="badge badge-info"><i class="fas fa-database"></i> Total Nodes: <?php echo $patients_result->num_rows; ?></div>
        </div>

        <?php echo $message; ?>

        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 2rem; border-bottom: 1px solid var(--border-glass); background: rgba(255,255,255,0.02);">
                <h3 style="display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-users-viewfinder" style="color: var(--secondary);"></i> 
                    Verified Identities
                </h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;"><i class="fas fa-fingerprint" style="margin-right: 8px; opacity: 0.7;"></i> Node ID</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;"><i class="fas fa-id-card" style="margin-right: 8px; opacity: 0.7;"></i> Full Identity</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;"><i class="fas fa-phone-volume" style="margin-right: 8px; opacity: 0.7;"></i> Communication</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;"><i class="fas fa-clock-rotate-left" style="margin-right: 8px; opacity: 0.7;"></i> Timestamp</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;"><i class="fas fa-microchip" style="margin-right: 8px; opacity: 0.7;"></i> Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $patients_result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1.5rem 2rem; font-family: monospace; color: var(--secondary);">#TX-<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td style="padding: 1.5rem 2rem;">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <?php 
                                        $admin_pat_pic = !empty($row['profile_pic']) && $row['profile_pic'] != 'default_user.png' ? BASE_URL . 'assets/uploads/profile_pics/' . $row['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($row['fullname']) . '&background=random&color=fff&size=100';
                                        ?>
                                        <img src="<?php echo $admin_pat_pic; ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-glass);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['fullname']); ?>&background=random&color=fff&size=100'">
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-main);"><?php echo $row['fullname']; ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-dim);"><?php echo $row['email']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.5rem 2rem; color: var(--text-dim);">
                                    <?php echo $row['phone'] ?: '<i style="opacity: 0.5;">No contact data</i>'; ?>
                                </td>
                                <td style="padding: 1.5rem 2rem; font-size: 0.9rem;">
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </td>
                                <td style="padding: 1.5rem 2rem;">
                                    <a href="update_account_status.php?action=reject&id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('WARNING: This will permanently purge this identity and all associated clinical records. Proceed?')" 
                                       style="color: var(--accent); font-size: 1.1rem; padding: 0.5rem; display: inline-block;">
                                        <i class="fas fa-user-xmark"></i>
                                    </a>
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