<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;

// Fetch Appointment Details
$sql = "SELECT appointments.*, users.fullname as patient_name, users.id as patient_id, users.email as patient_email 
        FROM appointments 
        JOIN users ON appointments.patient_id = users.id 
        WHERE appointments.id = $appointment_id AND appointments.doctor_id = " . $_SESSION['user_id'];
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Invalid Appointment");
}

$appt = $result->fetch_assoc();

// Check if prescription already exists
$check_pres = $conn->query("SELECT * FROM prescriptions WHERE appointment_id = $appointment_id");
if (!$check_pres) {
    die("Error checking prescription: " . $conn->error);
}
$existing_pres = $check_pres->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $diagnosis = $conn->real_escape_string($_POST['diagnosis']);
    $prescription_text = $conn->real_escape_string($_POST['prescription_text']);
    $notes = $conn->real_escape_string($_POST['notes']);
    $patient_id = $appt['patient_id'];
    $doctor_id = $_SESSION['user_id'];

    if ($existing_pres) {
        // Update
        $upd_sql = "UPDATE prescriptions SET diagnosis='$diagnosis', prescription_text='$prescription_text', notes='$notes' WHERE id=" . $existing_pres['id'];
        if ($conn->query($upd_sql) === TRUE) {
            // Also ensure appointment is completed
            $conn->query("UPDATE appointments SET status='completed' WHERE id=$appointment_id");
            header("Location: doctor_dashboard.php?msg=prescription_saved");
            exit();
        } else {
            $message = "<div class='alert-error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert
        $ins_sql = "INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, diagnosis, prescription_text, notes) VALUES ($appointment_id, $patient_id, $doctor_id, '$diagnosis', '$prescription_text', '$notes')";
        if ($conn->query($ins_sql) === TRUE) {
            // Mark appointment as completed
            $conn->query("UPDATE appointments SET status='completed' WHERE id=$appointment_id");

            // Send Email to Patient
            include_once '../includes/smtp_config.php';
            $patient_email = $appt['patient_email'];
            $subject = "Prescription Issued - TeleMed";
            $link = BASE_URL . "patient/view_prescription.php?appointment_id=$appointment_id";
            $body = "Hi " . htmlspecialchars($appt['patient_name']) . ",<br><br>Dr. " . htmlspecialchars($_SESSION['fullname']) . " has issued a prescription for you.<br><br><a href='$link'>Click here to view prescription</a>";
            sendEmail($patient_email, $subject, $body);

            header("Location: doctor_dashboard.php?msg=prescription_saved");
            exit();
        } else {
            $message = "<div class='alert-error'>Error: " . $conn->error . "</div>";
        }
    }
}

// Fetch Patient Medical Records
$patient_id = $appt['patient_id'];
$records_sql = "SELECT * FROM medical_records WHERE patient_id = $patient_id ORDER BY uploaded_at DESC";
$records_result = $conn->query($records_sql);
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

        <a href="doctor_dashboard.php" class="sidebar-item active"><i class="fas fa-chart-line"></i> Command Center</a>
        <a href="manage_schedule.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> My Schedule</a>
        <a href="view_patient_records.php" class="sidebar-item"><i class="fas fa-user-injured"></i> Patient Ledger</a>
        <a href="reports.php" class="sidebar-item"><i class="fas fa-file-contract"></i> Clinical Analytics</a>
        
        <?php 
        $doc_id = $_SESSION['user_id'];
        $has_active_consults = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doc_id AND status = 'confirmed'")->fetch_assoc()['c'] > 0;
        if($has_active_consults): ?>
        <a href="../messages.php" class="sidebar-item"><i class="fas fa-comments"></i> Consultations</a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="../profile.php" class="sidebar-item"><i class="fas fa-user-doctor"></i> My Profile</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Clinical Prescription</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Drafting medical advice for <span style="color: var(--primary); font-weight: 700;"><?php echo htmlspecialchars($appt['patient_name']); ?></span>.</p>
            </div>
            <a href="doctor_dashboard.php" class="btn btn-secondary" style="padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 600;">
                <i class="fas fa-arrow-left"></i> Abort Drafting
            </a>
        </div>

        <?php echo $message; ?>

        <div class="glass-card" style="border: 1px solid var(--border-glass);">
            <form action="" method="POST">
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label">Clinical Diagnosis</label>
                    <textarea name="diagnosis" class="form-control" rows="3" placeholder="e.g. Acute Viral Syndrome, Hypertension..." style="background: rgba(255,255,255,0.02); border-color: var(--border-glass); font-weight: 600;" required><?php echo $existing_pres ? $existing_pres['diagnosis'] : ''; ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label">Medications (Name, Dosage, Interval)</label>
                    <textarea name="prescription_text" class="form-control" rows="6" placeholder="e.g. &#10;1. Azithromycin 500mg - 1-0-0 x 5 days&#10;2. PCM 650mg - PRN for fever" style="background: rgba(255,255,255,0.02); border-color: var(--border-glass); font-family: monospace;" required><?php echo $existing_pres ? $existing_pres['prescription_text'] : ''; ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 3rem;">
                    <label class="form-label">Advisory Notes / Lifestyle Guidance</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Maintain hydration, rest for 72 hours..." style="background: rgba(255,255,255,0.02); border-color: var(--border-glass);"><?php echo $existing_pres ? $existing_pres['notes'] : ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-weight: 700; font-size: 1.1rem; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">
                    <i class="fas fa-save" style="margin-right: 10px; opacity: 0.7;"></i> Transmit Digital Prescription
                </button>
            </form>
        </div>

        <!-- Patient Medical History Section -->
        <div style="margin-top: 4rem;">
            <div style="margin-bottom: 2rem; border-left: 4px solid var(--secondary); padding-left: 1.5rem;">
                <h3 style="font-size: 1.5rem; margin-bottom: 0.2rem;"><i class="fas fa-history"></i> Clinical History Node</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Historical clinical documentation for this node.</p>
            </div>
            <div class="glass-card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-glass);">
                <?php if ($records_result->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--border-glass);">
                                    <th style="padding: 1.2rem 2rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-dim); letter-spacing: 1px;">Clinical Date</th>
                                    <th style="padding: 1.2rem 2rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-dim); letter-spacing: 1px;">Record ID / Title</th>
                                    <th style="padding: 1.2rem 2rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-dim); text-align: center; letter-spacing: 1px;">Access</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($rec = $records_result->fetch_assoc()): 
                                    $file_ext = strtolower(pathinfo($rec['file_name'], PATHINFO_EXTENSION));
                                    $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif']);
                                    $file_url = BASE_URL . str_replace('../', '', $rec['file_path']);
                                ?>
                                    <tr style="border-bottom: 1px solid var(--border-glass);">
                                        <td style="padding: 1rem 1.5rem; font-size: 0.9rem;">
                                            <?php echo date('M d, Y', strtotime($rec['uploaded_at'])); ?>
                                        </td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($rec['description']); ?></div>
                                            <div style="font-size: 0.7rem; color: var(--text-dim);"><?php echo htmlspecialchars($rec['file_name']); ?></div>
                                        </td>
                                        <td style="padding: 1rem 1.5rem; text-align: center;">
                                            <a href="<?php echo $file_url; ?>" target="_blank" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                                                <i class="fas <?php echo $is_image ? 'fa-image' : 'fa-file-pdf'; ?>"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="padding: 3rem; text-align: center; opacity: 0.6;">
                        <i class="fas fa-folder-open" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>No medical records shared by this patient yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>