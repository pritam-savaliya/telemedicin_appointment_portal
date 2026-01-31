<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle Booking Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = $_SESSION['user_id'];
    $doctor_id = $conn->real_escape_string($_POST['doctor_id']);
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['time']);

    // Validation
    if (empty($doctor_id) || empty($date) || empty($time)) {
        $message = "<div style='background: #ffdddd; color: red; padding: 10px; margin-bottom: 10px; border-radius: 5px;'>All fields are required.</div>";
    } else {
        $sql = "INSERT INTO appointments (patient_id, doctor_id, date, time) VALUES ('$patient_id', '$doctor_id', '$date', '$time')";

        if ($conn->query($sql) === TRUE) {
            header("Location: home.php?msg=appointment_booked");
            exit();
        } else {
            $message = "<div style='background: #ffdddd; color: red; padding: 10px; margin-bottom: 10px; border-radius: 5px;'>Error: " . $conn->error . "</div>";
        }
    }
}

// Fetch Doctors
$doctors_sql = "SELECT id, fullname FROM users WHERE role = 'doctor'";
$doctors_result = $conn->query($doctors_sql);
?>

<?php include 'includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-container" style="max-width: 600px; text-align: left;">
        <h2 style="text-align: center;">Book Appointment</h2>
        <p style="text-align: center; margin-bottom: 2rem;">Select a doctor and schedule your visit.</p>

        <?php echo $message; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="doctor">Select Doctor</label>
                <select name="doctor_id" id="doctor" class="form-control" required>
                    <option value="">-- Choose a Doctor --</option>
                    <?php while ($row = $doctors_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>">Dr.
                            <?php echo $row['fullname']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="time">Time</label>
                <input type="time" name="time" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary auth-btn">Confirm Booking</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>