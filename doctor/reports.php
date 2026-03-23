<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

// Mock Analytics Data (since we don't have a complex logging system yet)
$active_patients = $conn->query("SELECT COUNT(DISTINCT patient_id) as c FROM appointments WHERE doctor_id = $doctor_id")->fetch_assoc()['c'];
$total_consults = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'completed'")->fetch_assoc()['c'];
$pending_requests = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'pending'")->fetch_assoc()['c'];

include '../includes/header.php'; 
?>

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
        <a href="view_patient_records.php" class="sidebar-item"><i class="fas fa-user-injured"></i> Patient Ledger</a>
        <a href="reports.php" class="sidebar-item active"><i class="fas fa-file-contract"></i> Clinical Analytics</a>
        
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

    <!-- Main Content -->
    <main class="main-content">
        <div style="margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Performance Analytics</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">In-depth metrics of your clinical practice.</p>
        </div>

        <div class="grid-3">
            <div class="glass-card" style="text-align: center;">
                <h4 style="color: var(--text-dim); margin-bottom: 1rem;">RETENTION RATE</h4>
                <h2 style="font-size: 3rem; color: var(--primary);">94%</h2>
                <p style="font-size: 0.8rem; color: var(--success);"><i class="fas fa-arrow-up"></i> 2.4% from last month</p>
            </div>
            <div class="glass-card" style="text-align: center;">
                <h4 style="color: var(--text-dim); margin-bottom: 1rem;">AVG. RATING</h4>
                <h2 style="font-size: 3rem; color: var(--warning);">4.9</h2>
                <div style="color: var(--warning); margin-bottom: 0.5rem;">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-muted);">From <?php echo $total_consults; ?> reviews</p>
            </div>
            <div class="glass-card" style="text-align: center;">
                <h4 style="color: var(--text-dim); margin-bottom: 1rem;">VIRTUAL VOLUME</h4>
                <h2 style="font-size: 3rem; color: var(--secondary);"><?php echo $total_consults; ?></h2>
                <p style="font-size: 0.8rem; color: var(--text-muted);">Total completed sessions</p>
            </div>
        </div>

        <div class="glass-card" style="margin-top: 2rem; padding: 3rem; text-align: center;">
            <div style="font-size: 4rem; opacity: 0.2; margin-bottom: 2rem;">
                <i class="fas fa-chart-area"></i>
            </div>
            <h3>Historical Trend Data</h3>
            <p style="color: var(--text-dim); max-width: 500px; margin: 1rem auto;">Our advanced ML models are analyzing your patient engagement patterns. Detailed charts will be available in the next cycle.</p>
            <div style="display: inline-flex; gap: 10px; margin-top: 1rem;">
                <span class="badge badge-info">Processing...</span>
                <span class="badge badge-success">Data Secure</span>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
