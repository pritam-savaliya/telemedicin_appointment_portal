<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch all active chats for this user
// We look for appointments where the user is involved and status is 'confirmed' or 'completed'
if ($role == 'doctor') {
    $sql = "SELECT a.id as appointment_id, a.date, a.time, u.fullname as other_party_name, u.profile_pic as other_party_pic,
            (SELECT message FROM chat_messages WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1) as last_msg,
            (SELECT created_at FROM chat_messages WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1) as last_msg_time,
            (SELECT COUNT(*) FROM chat_messages WHERE appointment_id = a.id AND is_read = 0 AND sender_id != $user_id) as unread_count
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            WHERE a.doctor_id = $user_id AND (a.status = 'confirmed' OR a.status = 'completed')
            ORDER BY last_msg_time DESC, a.date DESC";
} else {
    $sql = "SELECT a.id as appointment_id, a.date, a.time, u.fullname as other_party_name, u.profile_pic as other_party_pic,
            (SELECT message FROM chat_messages WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1) as last_msg,
            (SELECT created_at FROM chat_messages WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1) as last_msg_time,
            (SELECT COUNT(*) FROM chat_messages WHERE appointment_id = a.id AND is_read = 0 AND sender_id != $user_id) as unread_count
            FROM appointments a
            JOIN users u ON a.doctor_id = u.id
            WHERE a.patient_id = $user_id AND (a.status = 'confirmed' OR a.status = 'completed')
            ORDER BY last_msg_time DESC, a.date DESC";
}

$result = $conn->query($sql);

include 'includes/header.php'; 
?>

<div class="dashboard-layout fade-in">
    <!-- Role-Based Sidebar -->
    <?php if ($role == 'patient'): ?>
        <aside class="sidebar">
            <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
                <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                    <?php 
                    $sidebar_pic = (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) && $_SESSION['profile_pic'] != 'default_user.png') ? BASE_URL . 'assets/uploads/profile_pics/' . $_SESSION['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname']) . '&background=random&color=fff&size=200';
                    ?>
                    <img src="<?php echo $sidebar_pic; ?>" 
                         style="width: 70px; height: 70px; border-radius: 50%; border: 2px solid var(--primary); object-fit: cover; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['fullname']); ?>&background=random&color=fff&size=200'">
                    <div style="position: absolute; bottom: 2px; right: 2px; width: 14px; height: 14px; background: var(--success); border-radius: 50%; border: 3px solid var(--bg-sidebar);"></div>
                </div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.2rem;"><?php echo $_SESSION['fullname']; ?></h4>
                <span class="badge badge-info" style="font-size: 0.6rem; opacity: 0.8; background: rgba(99,102,241,0.1); color: var(--primary);">Patient Portal</span>
            </div>

            <a href="patient/patient_dashboard.php" class="sidebar-item"><i class="fas fa-th-large"></i> Overview</a>
            <a href="patient/book_appointment.php" class="sidebar-item"><i class="fas fa-calendar-plus"></i> New Consultation</a>
            <a href="patient/my_appointments.php" class="sidebar-item"><i class="fas fa-history"></i> Consultation History</a>
            <a href="patient/upload_report.php" class="sidebar-item"><i class="fas fa-file-medical"></i> Medical Records</a>
            <a href="patient/my_prescriptions.php" class="sidebar-item"><i class="fas fa-pills"></i> My Prescriptions</a>
            <a href="messages.php" class="sidebar-item active"><i class="fas fa-comments"></i> Messages</a>

            <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                <a href="profile.php" class="sidebar-item"><i class="fas fa-user-gear"></i> My Profile</a>
                <a href="auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
            </div>
        </aside>
    <?php elseif ($role == 'doctor'): ?>
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

            <a href="doctor/doctor_dashboard.php" class="sidebar-item"><i class="fas fa-chart-line"></i> Command Center</a>
            <a href="doctor/manage_schedule.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> My Schedule</a>
            <a href="doctor/view_patient_records.php" class="sidebar-item"><i class="fas fa-user-injured"></i> Patient Ledger</a>
            <a href="doctor/reports.php" class="sidebar-item"><i class="fas fa-file-contract"></i> Clinical Analytics</a>
            <a href="messages.php" class="sidebar-item active"><i class="fas fa-comments"></i> Consultations</a>

            <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                <a href="profile.php" class="sidebar-item"><i class="fas fa-user-doctor"></i> My Profile</a>
                <a href="auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
            </div>
        </aside>
    <?php endif; ?>

    <main class="main-content">
        <div style="margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Messages</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Secure conversations with your <?php echo $role == 'doctor' ? 'patients' : 'doctors'; ?>.</p>
        </div>

        <div class="glass-card" style="padding: 0; overflow: hidden; max-width: 900px;">
            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-glass); background: rgba(255,255,255,0.02); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.2rem;"><i class="fas fa-inbox" style="color: var(--primary); margin-right: 10px;"></i> Recent Conversations</h3>
            </div>

            <div class="chat-inbox-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $pic_url = !empty($row['other_party_pic']) && $row['other_party_pic'] != 'default_user.png' ? BASE_URL . 'assets/uploads/profile_pics/' . $row['other_party_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($row['other_party_name']) . '&background=random&color=fff&size=100';
                    ?>
                        <a href="chat.php?appointment_id=<?php echo $row['appointment_id']; ?>" 
                           style="display: flex; align-items: center; gap: 20px; padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-glass); transition: 0.3s; text-decoration: none; color: inherit;"
                           onmouseover="this.style.background='var(--bg-glass)'"
                           onmouseout="this.style.background='transparent'">
                            
                            <div style="position: relative; flex-shrink: 0;">
                                <img src="<?php echo $pic_url; ?>" style="width: 60px; height: 60px; border-radius: 15px; object-fit: cover; border: 2px solid var(--border-glass);">
                                <?php if ($row['unread_count'] > 0): ?>
                                    <span style="position: absolute; top: -5px; right: -5px; background: var(--accent); color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; border: 3px solid var(--bg-card);"><?php echo $row['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>

                            <div style="flex-grow: 1; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <h4 style="margin: 0; font-size: 1.1rem; color: var(--text-main);"><?php echo htmlspecialchars($row['other_party_name']); ?></h4>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                                        <?php echo $row['last_msg_time'] ? date('M d', strtotime($row['last_msg_time'])) : date('M d', strtotime($row['date'])); ?>
                                    </span>
                                </div>
                                <div style="font-size: 0.9rem; color: var(--text-dim); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; <?php echo ($row['unread_count'] > 0) ? 'font-weight: 700; color: var(--text-main);' : ''; ?>">
                                    <?php echo $row['last_msg'] ? htmlspecialchars($row['last_msg']) : 'No messages yet. Click to start conversation.'; ?>
                                </div>
                            </div>

                            <div style="color: var(--primary); font-size: 1.2rem; opacity: 0.3;">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="padding: 5rem 2rem; text-align: center; color: var(--text-muted);">
                        <div style="font-size: 3.5rem; opacity: 0.15; margin-bottom: 1.5rem;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 style="margin-bottom: 0.5rem; color: var(--text-main);">No active conversations</h3>
                        <p>Once you have a confirmed appointment, you can message your <?php echo $role == 'doctor' ? 'patient' : 'doctor'; ?> here.</p>
                        <a href="<?php echo $role == 'doctor' ? 'doctor/doctor_dashboard.php' : 'patient/book_appointment.php'; ?>" class="btn btn-primary" style="margin-top: 1.5rem;">
                            <?php echo $role == 'doctor' ? 'View Dashbaord' : 'Book a Consultation'; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
