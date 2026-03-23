<?php

include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch Stats
$patient_completed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND status = 'completed'")->fetch_assoc()['c'];
$patient_active = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND (status = 'confirmed' OR status = 'pending')")->fetch_assoc()['c'];
$total_reports = $conn->query("SELECT COUNT(*) as c FROM medical_records WHERE patient_id = $user_id")->fetch_assoc()['c'] ?? 0;

// Check for Confirmed appointments (for messaging visibility)
$has_confirmed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND status = 'confirmed'")->fetch_assoc()['c'] > 0;

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

        <a href="patient_dashboard.php" class="sidebar-item active"><i class="fas fa-th-large"></i> Overview</a>
        <a href="book_appointment.php" class="sidebar-item"><i class="fas fa-calendar-plus"></i> New Consultation</a>
        <a href="my_appointments.php" class="sidebar-item"><i class="fas fa-history"></i> Consultation History</a>
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

    <!-- Main Content -->
    <main class="main-content">
        <div style="margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Good Day, <?php echo explode(' ', $_SESSION['fullname'])[0]; ?>! 👋</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Here's what's happening with your health today.</p>
        </div>

        <!-- Progress Stats -->
        <div class="stats-grid">
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $patient_active; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Active Consults</span>
                </div>
            </div>
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $patient_completed; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Completed</span>
                </div>
            </div>
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(6, 182, 212, 0.1); color: var(--secondary);">
                    <i class="fas fa-file-waveform"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $total_reports; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Medical Reports</span>
                </div>
            </div>
        </div>

        <!-- Emergency & Quick Actions Row -->
        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; margin-bottom: 3rem;">
            <!-- Emergency Card -->
            <div onclick="window.location.href='emergency_booking.php'" class="glass-card" style="background: linear-gradient(135deg, rgba(244, 63, 94, 0.15) 0%, rgba(244, 63, 94, 0.05) 100%); cursor: pointer; border-color: rgba(244, 63, 94, 0.3); display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div class="badge badge-danger" style="margin-bottom: 1rem;"><i class="fas fa-circle-exclamation"></i> URGENT CARE</div>
                    <h2 style="margin-bottom: 0.5rem; color: var(--accent);">Emergency Assistance</h2>
                    <p style="color: var(--text-muted); max-width: 400px;">Immediate connection to emergency medical staff. 24/7 prioritized booking.</p>
                </div>
                <div style="font-size: 4rem; color: var(--accent); opacity: 0.4;">
                    <i class="fas fa-ambulance"></i>
                </div>
            </div>

            <!-- Quick Book -->
            <div class="glass-card" style="text-align: center; display: flex; flex-direction: column; justify-content: center; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, transparent 100%);">
                <h3 style="margin-bottom: 1rem;">Need a Specialist?</h3>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.9rem;">Book a slot with top specialists in minutes.</p>
                <a href="book_appointment.php" class="btn btn-primary" style="width: 100%;">Book Now</a>
            </div>
        </div>

        <!-- Consultations & Activity -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Active Consultations -->
            <div>
                <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-video" style="color: var(--secondary);"></i> Active Consultations
                </h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php
                    $chat_sql = "SELECT appointments.*, users.fullname as doctor_name, users.profile_pic as doc_pic
                                 FROM appointments 
                                 JOIN users ON appointments.doctor_id = users.id 
                                 WHERE patient_id = $user_id AND appointments.status = 'confirmed' 
                                 ORDER BY appointments.date DESC LIMIT 3";
                    $chat_result = $conn->query($chat_sql);
                    
                    if ($chat_result->num_rows > 0): 
                        while ($appt = $chat_result->fetch_assoc()): ?>
                        <div class="glass-card" style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; gap: 1.2rem; align-items: center;">
                                <?php 
                                $doc_list_pic = !empty($appt['doc_pic']) && $appt['doc_pic'] != 'default_user.png' ? BASE_URL . 'assets/uploads/profile_pics/' . $appt['doc_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($appt['doctor_name']) . '&background=random&color=fff&size=100';
                                ?>
                                <img src="<?php echo $doc_list_pic; ?>" 
                                     style="width: 50px; height: 50px; border-radius: 12px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($appt['doctor_name']); ?>&background=random&color=fff&size=100'">
                                <div>
                                    <h4 style="margin-bottom: 2px;">Dr. <?php echo $appt['doctor_name']; ?></h4>
                                    <div style="font-size: 0.8rem; color: var(--text-dim); display: flex; gap: 10px;">
                                        <span><i class="far fa-calendar"></i> <?php echo date('M d', strtotime($appt['date'])); ?></span>
                                        <span><i class="far fa-clock"></i> <?php echo date("h:i A", strtotime($appt['time'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <a href="../chat.php?appointment_id=<?php echo $appt['id']; ?>" class="btn btn-secondary" style="padding: 0.6rem; border-radius: 10px;" title="Message">
                                    <i class="fas fa-comment-dots"></i>
                                </a>
                                <?php if (isset($appt['is_call_active']) && $appt['is_call_active']): ?>
                                    <a href="../video_consultation.php?appointment_id=<?php echo $appt['id']; ?>" class="btn btn-primary" style="padding: 0.6rem 1.2rem; border-radius: 10px;">
                                        Join <i class="fas fa-video" style="margin-left: 5px;"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; 
                    else: ?>
                        <div class="glass-card" style="text-align: center; padding: 3rem; border-style: dashed; opacity: 0.7;">
                            <p style="color: var(--text-dim);">No active appointments scheduled.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notifications -->
            <div>
                <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-bell" style="color: var(--warning);"></i> Recent Alerts
                </h3>
                <div class="glass-card" style="padding: 0; overflow: hidden;">
                    <?php
                    $notif_sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 4";
                    $notif_result = $conn->query($notif_sql);
                    if ($notif_result->num_rows > 0):
                        while ($notif = $notif_result->fetch_assoc()): ?>
                        <div style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center;">
                            <p style="font-size: 0.9rem; color: var(--text-main); font-weight: 500;"><?php echo $notif['message']; ?></p>
                            <span style="font-size: 0.7rem; color: var(--text-dim); flex-shrink: 0;">Just now</span>
                        </div>
                    <?php endwhile;
                    else: ?>
                        <div style="padding: 3rem; text-align: center;">
                            <p style="color: var(--text-dim);">All caught up!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>