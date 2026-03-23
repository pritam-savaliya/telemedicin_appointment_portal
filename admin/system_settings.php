<?php

include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// Mock Settings Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = "<div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-check-circle'></i> System configuration synchronized successfully.</div>";
}

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
        <a href="admin_appointments.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> Global Traffic</a>
        <a href="admin_reviews.php" class="sidebar-item"><i class="fas fa-star-half-stroke"></i> Quality Control</a>
        
        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="system_settings.php" class="sidebar-item active"><i class="fas fa-gears"></i> Platform Settings</a>
            <a href="support_messages.php" class="sidebar-item"><i class="fas fa-headset"></i> Support Hub</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div style="margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Platform Settings</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Manage global parameters and system heuristics.</p>
        </div>

        <?php echo $message; ?>

        <div class="glass-card">
            <form action="" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div class="form-group">
                        <label class="form-label">Platform Identity Name</label>
                        <input type="text" class="form-control" value="MedConnect Premium" placeholder="Enter brand name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">System Environment</label>
                        <select class="form-control">
                            <option>Production (Secure)</option>
                            <option>Staging</option>
                            <option>Maintenance Mode</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Consultation Fee (Global Default)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">$</span>
                            <input type="number" class="form-control" style="padding-left: 35px;" value="50">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Session Timeout (Minutes)</label>
                        <input type="number" class="form-control" value="60">
                    </div>
                </div>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                    <h3 style="margin-bottom: 1.5rem;">Clinical Security Heuristics</h3>
                    <div style="display: grid; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="checkbox" checked style="width: 20px; height: 20px;">
                            <span>Enforce end-to-end encryption on all video sessions</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="checkbox" checked style="width: 20px; height: 20px;">
                            <span>Enable real-time AI fraud detection on prescription uploads</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="checkbox" style="width: 20px; height: 20px;">
                            <span>Allow guest appointment visual only during emergency overrides</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 3rem; padding: 1.2rem; font-weight: 700;">
                    Commit System Changes <i class="fas fa-microchip" style="margin-left: 10px;"></i>
                </button>
            </form>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
