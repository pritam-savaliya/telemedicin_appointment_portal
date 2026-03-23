<?php
include '../includes/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Clear "New support message" notifications for this admin
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id AND message LIKE 'New support message%'");

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
        <a href="admin_reviews.php" class="sidebar-item"><i class="fas fa-star-half-stroke"></i> Quality Control</a>
        
        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="system_settings.php" class="sidebar-item"><i class="fas fa-gears"></i> Platform Settings</a>
            <a href="support_messages.php" class="sidebar-item active"><i class="fas fa-headset"></i> Support Hub</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Support Hub</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Managing incoming inquiries and platform support tickets.</p>
            </div>
            <div class="badge badge-info"><i class="fas fa-envelope-open-text"></i> Total Inquiries: <?php echo $conn->query("SELECT COUNT(*) FROM contact_messages")->fetch_row()[0]; ?></div>
        </div>

        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 2rem; border-bottom: 1px solid var(--border-glass); background: rgba(255,255,255,0.02);">
                <h3 style="display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-inbox" style="color: var(--primary);"></i> 
                    External Communication Logs
                </h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">ID</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Sender Identity</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Engagement Topic</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Message Content</th>
                            <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr style='border-bottom: 1px solid var(--border-glass); transition: 0.2s;' onmouseover=\"this.style.background='rgba(255,255,255,0.01)'\" onmouseout=\"this.style.background='transparent'\">";
                                echo "<td style='padding: 1.5rem 2rem; font-family: monospace; color: var(--secondary);'>#QC-" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td style='padding: 1.5rem 2rem;'>
                                        <div style='font-weight: 700; color: var(--text-main);'>" . htmlspecialchars($row['name']) . "</div>
                                        <div style='font-size: 0.8rem; color: var(--text-dim);'>" . htmlspecialchars($row['email']) . "</div>
                                      </td>";
                                echo "<td style='padding: 1.5rem 2rem; font-weight: 600; color: var(--primary);'>" . htmlspecialchars($row['subject']) . "</td>";
                                echo "<td style='padding: 1.5rem 2rem;'><div style='max-width: 400px; font-size: 0.9rem; color: var(--text-dim); line-height: 1.4;'>" . htmlspecialchars($row['message']) . "</div></td>";
                                echo "<td style='padding: 1.5rem 2rem; font-size: 0.85rem; color: var(--text-muted);'>" . date('M d, Y', strtotime($row['created_at'])) . "<br><small>" . date('h:i A', strtotime($row['created_at'])) . "</small></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='padding: 5rem; text-align: center; color: var(--text-muted);'><i class='fas fa-folder-open fa-3x' style='opacity: 0.2; margin-bottom: 1.5rem;'></i><p style='font-size: 1.2rem; font-weight: 500;'>No pending inquiries found.</p></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>