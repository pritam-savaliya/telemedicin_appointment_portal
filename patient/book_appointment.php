<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// Handle Booking Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = $_SESSION['user_id'];
    $doctor_id = $conn->real_escape_string($_POST['doctor_id']);
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['time']);

    if (empty($doctor_id) || empty($date) || empty($time)) {
        $message = "<div class='alert-error'>All fields are required.</div>";
    } else {
        // Store Details in Session
        $_SESSION['booking_details'] = [
            'doctor_id' => $doctor_id,
            'date' => $date,
            'time' => $time
        ];

        // Redirect to Payment
        header("Location: payment.php");
        exit();
    }
}

// Fetch Doctors with average rating
$doctors_sql = "SELECT u.id, u.fullname, AVG(r.rating) as avg_rating 
                FROM users u 
                LEFT JOIN reviews r ON u.id = r.doctor_id 
                WHERE u.role = 'doctor' 
                GROUP BY u.id";
$doctors_result = $conn->query($doctors_sql);
?>

<?php include '../includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div
                style="background: rgba(0, 184, 148, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: var(--success-color);">
                <i class="fas fa-calendar-check" style="font-size: 2.5rem;"></i>
            </div>
        </div>
        <h2 style="text-align: center; margin-bottom: 10px; color: var(--secondary-color);">Book Appointment</h2>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: 30px;">Select a doctor and schedule your
            visit.</p>

        <?php echo $message; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="doctor">Select Doctor</label>
                <div class="input-with-icon">
                    <i class="fas fa-user-md"></i>
                    <select name="doctor_id" id="doctor" class="form-control" required onchange="fetchSlots()">
                        <option value="">-- Choose a Doctor --</option>
                        <?php while ($row = $doctors_result->fetch_assoc()): ?>
                            <?php
                            $rating_display = "";
                            if ($row['avg_rating']) {
                                $rating_display = " (" . number_format($row['avg_rating'], 1) . " ⭐)";
                            }
                            ?>
                            <option value="<?php echo $row['id']; ?>">Dr. <?php echo $row['fullname'] . $rating_display; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="date">Date</label>
                <div class="input-with-icon">
                    <i class="fas fa-calendar"></i>
                    <input type="date" name="date" id="date" class="form-control" required
                        min="<?php echo date('Y-m-d'); ?>" onchange="fetchSlots()">
                </div>
            </div>

            <div class="form-group">
                <label for="time">Available Slots</label>
                <div class="input-with-icon">
                    <i class="fas fa-clock"></i>
                    <select name="time" id="time" class="form-control" required disabled>
                        <option value="">-- Select Date & Doctor First --</option>
                    </select>
                </div>
                <small id="slot-msg" style="color: var(--text-muted); display: block; margin-top: 5px;"></small>
            </div>

            <button type="submit" class="btn btn-primary"
                style="width: 100%; padding: 12px; font-size: 1.1rem; margin-top: 15px;">Confirm Booking <i
                    class="fas fa-arrow-right"></i></button>
        </form>

        <div style="text-align: center; margin-top: 25px;">
            <a href="<?php echo BASE_URL; ?>patient/patient_dashboard.php"
                style="color: var(--text-muted); font-size: 0.9rem;">Back to Dashboard</a>
        </div>
    </div>
</div>

<script>
    function fetchSlots() {
        const doctorId = document.getElementById('doctor').value;
        const date = document.getElementById('date').value;
        const timeSelect = document.getElementById('time');
        const msg = document.getElementById('slot-msg');

        if (!doctorId || !date) {
            timeSelect.innerHTML = '<option value="">-- Select Date & Doctor First --</option>';
            timeSelect.disabled = true;
            return;
        }

        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option>Loading slots...</option>';
        msg.innerText = '';
        msg.style.color = 'var(--text-muted)';

        fetch(`../api/get_time_slots.php?doctor_id=${doctorId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                timeSelect.innerHTML = ''; // Clear loading

                if (data.available && data.slots.length > 0) {
                    let options = '<option value="">-- Select Time --</option>';
                    data.slots.forEach(slot => {
                        options += `<option value="${slot.value}">${slot.display}</option>`;
                    });
                    timeSelect.innerHTML = options;
                    timeSelect.disabled = false;
                } else {
                    timeSelect.innerHTML = '<option value="">No slots available</option>';
                    msg.innerText = data.message || 'The doctor is not available on this day or all slots are booked.';
                    msg.style.color = 'var(--danger-color)';
                }
            })
            .catch(err => {
                console.error(err);
                timeSelect.innerHTML = '<option value="">Error</option>';
                msg.innerText = 'Failed to load time slots. Please try again.';
                msg.style.color = 'var(--danger-color)';
            });
    }
</script>

<?php include '../includes/footer.php'; ?>