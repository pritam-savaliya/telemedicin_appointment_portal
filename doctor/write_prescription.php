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
            header("Location: doctor_dashboard.php?msg=prescription_saved");
            exit();
        } else {
            $message = "<div class='alert-error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Insert
        $ins_sql = "INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, diagnosis, prescription_text, notes) VALUES ($appointment_id, $patient_id, $doctor_id, '$diagnosis', '$prescription_text', '$notes')";
        if ($conn->query($ins_sql) === TRUE) {

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
?>

<?php include '../includes/header.php'; ?>

<div class="container section">
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin-bottom: 5px;">Write Prescription</h2>
                <p style="color: var(--text-muted);">Patient: <span
                        style="font-weight: 600; color: var(--primary-color);">
                        <?php echo htmlspecialchars($appt['patient_name']); ?>
                    </span></p>
            </div>
            <a href="<?php echo BASE_URL; ?>doctor/doctor_dashboard.php" class="btn btn-outline"><i
                    class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?php echo $message; ?>

        <div class="card">
            <form action="" method="POST">
                <div class="form-group">
                    <label for="diagnosis">Diagnosis</label>
                    <textarea name="diagnosis" class="form-control" rows="3" placeholder="e.g. Viral Fever, Migraine..."
                        required><?php echo $existing_pres ? $existing_pres['diagnosis'] : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="prescription_text">Medications (Name, Dosage, Frequency)</label>
                    <textarea name="prescription_text" class="form-control" rows="6"
                        placeholder="e.g. &#10;1. Paracetamol 500mg - 1-0-1 after food&#10;2. Vitamin C - 1-0-0"
                        required><?php echo $existing_pres ? $existing_pres['prescription_text'] : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes / Advice</label>
                    <textarea name="notes" class="form-control" rows="3"
                        placeholder="e.g. Drink plenty of water, rest for 2 days..."><?php echo $existing_pres ? $existing_pres['notes'] : ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 12px 25px; font-size: 1.1rem;">
                    <i class="fas fa-save"></i> Save Prescription
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>