<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// Handle Delete Review
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if ($conn->query("DELETE FROM reviews WHERE id = $delete_id") === TRUE) {
        $message = "<div class='alert-success'>Review deleted successfully.</div>";
    } else {
        $message = "<div class='alert-error'>Error deleting review: " . $conn->error . "</div>";
    }
}

// Fetch Reviews
$sql = "SELECT r.*, 
        p.fullname as patient_name, 
        d.fullname as doctor_name 
        FROM reviews r 
        JOIN users p ON r.patient_id = p.id 
        JOIN users d ON r.doctor_id = d.id";

if (isset($_GET['doctor_id'])) {
    $doctor_id = intval($_GET['doctor_id']);
    $sql .= " WHERE r.doctor_id = $doctor_id";
}

$sql .= " ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>

<?php include '../includes/header.php'; ?>

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
        <a href="admin_appointments.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> Global Traffic</a>
        <a href="admin_reviews.php" class="sidebar-item active"><i class="fas fa-star-half-stroke"></i> Quality Control</a>
        
        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="system_settings.php" class="sidebar-item"><i class="fas fa-gears"></i> Platform Settings</a>
            <a href="support_messages.php" class="sidebar-item"><i class="fas fa-headset"></i> Support Hub</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Quality Control</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Monitoring physician performance and patient satisfaction levels.</p>
            </div>
            <div class="badge badge-warning"><i class="fas fa-star"></i> Avg Rating: 4.8/5.0</div>
        </div>

        <?php echo $message; ?>

        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 2rem; border-bottom: 1px solid var(--border-glass); background: rgba(255,255,255,0.02);">
                <h3 style="display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-clipboard-list" style="color: var(--primary);"></i> 
                    Platform Feedback Logs
                </h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Timeline</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Practitioner</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Citizen Info</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Satisfaction Index</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.5rem 2rem; font-size: 0.9rem; color: var(--text-dim);">
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="font-weight: 700; color: var(--primary);">Dr. <?php echo $row['doctor_name']; ?></div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="font-weight: 600; color: var(--text-main);"><?php echo $row['patient_name']; ?></div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="display: flex; gap: 5px; margin-bottom: 8px;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="<?php echo ($i <= $row['rating']) ? 'fas' : 'far'; ?> fa-star" style="color: #ffd700; font-size: 0.8rem;"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--text-dim); font-style: italic; max-width: 300px; line-height: 1.4;">
                                            "<?php echo htmlspecialchars($row['comment']); ?>"
                                        </div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <a href="admin_reviews.php?action=delete&id=<?php echo $row['id']; ?>"
                                           onclick="return confirm('WARNING: Critical data loss. Purge this feedback?')"
                                           style="color: var(--accent); font-size: 1.1rem; padding: 0.5rem; display: inline-block;">
                                            <i class="fas fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding: 5rem; text-align: center; color: var(--text-muted);">
                                    <i class="fas fa-comment-slash fa-3x" style="opacity: 0.2; margin-bottom: 1.5rem;"></i>
                                    <p style="font-size: 1.2rem; font-weight: 500;">No feedback logs found in the archives.</p>
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
