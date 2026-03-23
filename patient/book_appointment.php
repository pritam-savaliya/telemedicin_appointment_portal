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
        $message = "<div class='badge badge-danger' style='width:100%; border-radius:12px; margin-bottom:1.5rem; padding:1.2rem;'>All fields are required.</div>";
    } else {
        $_SESSION['booking_details'] = [
            'doctor_id' => $doctor_id,
            'date' => $date,
            'time' => $time
        ];
        header("Location: payment.php");
        exit();
    }
}

// Fetch Doctors with average rating
$doctors_sql = "SELECT u.id, u.fullname, u.profile_pic, AVG(r.rating) as avg_rating 
                FROM users u 
                LEFT JOIN reviews r ON u.id = r.doctor_id 
                WHERE u.role = 'doctor' 
                GROUP BY u.id";
$doctors_result = $conn->query($doctors_sql);

$has_confirmed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = " . $_SESSION['user_id'] . " AND status = 'confirmed'")->fetch_assoc()['c'] > 0;

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

        <a href="patient_dashboard.php" class="sidebar-item"><i class="fas fa-th-large"></i> Overview</a>
        <a href="book_appointment.php" class="sidebar-item active"><i class="fas fa-calendar-plus"></i> New Consultation</a>
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
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Schedule Consultation</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Select your specialist and preferred time slot.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1.2fr 1.8fr; gap: 3rem; align-items: start;">
            <!-- Booking Form Card -->
            <div class="glass-card" style="padding: 2.5rem;">
                <?php echo $message; ?>
                <form action="" method="POST" style="display: grid; gap: 2rem;">
                    <div class="form-group">
                        <label class="form-label" style="display: flex; justify-content: space-between;">
                            <span>Select Specialist</span>
                        </label>
                        <div style="position: relative;">
                            <i class="fas fa-user-md" style="position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: var(--primary);"></i>
                            <select name="doctor_id" id="doctor" class="form-control" required onchange="fetchSlots()" style="padding-left: 3.5rem; appearance: none;">
                                <option value="">Choose a Physician</option>
                                <?php
                                $selected_doc_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
                                while ($row = $doctors_result->fetch_assoc()):
                                    $rating = $row['avg_rating'] ? number_format($row['avg_rating'], 1) . " ★" : "No ratings";
                                ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $selected_doc_id) ? 'selected' : ''; ?>>
                                        Dr. <?php echo htmlspecialchars($row['fullname']); ?> (<?php echo $rating; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Preferred Date</label>
                        <div style="position: relative;">
                            <i class="fas fa-calendar-day" style="position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: var(--secondary);"></i>
                            <input type="date" name="date" id="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>" onchange="fetchSlots()" style="padding-left: 3.5rem;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Available Time Slots</label>
                        <div style="position: relative;">
                            <i class="fas fa-clock" style="position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                            <select name="time" id="time" class="form-control" required disabled style="padding-left: 3.5rem; appearance: none;">
                                <option value="">Select Doctor & Date</option>
                            </select>
                        </div>
                        <p id="slot-msg" style="font-size: 0.8rem; margin-top: 10px; font-weight: 500;"></p>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; margin-top: 1rem;">
                        Proceed to Payment <i class="fas fa-credit-card" style="margin-left: 10px;"></i>
                    </button>
                </form>
            </div>

            <!-- Visual Guide / Why Choose Us -->
            <div style="display: grid; gap: 2rem;">
                <div class="glass-card" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, transparent 100%);">
                    <h3 style="margin-bottom: 2rem;">Platform Protocol</h3>
                    <div style="display: grid; gap: 1.5rem;">
                        <div style="display: flex; gap: 1.2rem; align-items: flex-start;">
                            <div class="stat-icon" style="flex-shrink: 0; width: 40px; height: 40px; font-size: 0.8rem; background: rgba(255,255,255,0.05);">1</div>
                            <div>
                                <h4 style="margin-bottom: 0.3rem;">Encryption Guaranteed</h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted);">All video consultations and chat logs are end-to-end encrypted.</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1.2rem; align-items: flex-start;">
                            <div class="stat-icon" style="flex-shrink: 0; width: 40px; height: 40px; font-size: 0.8rem; background: rgba(255,255,255,0.05);">2</div>
                            <div>
                                <h4 style="margin-bottom: 0.3rem;">Instant Confirmation</h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted);">Upon payment, your slot is instantly reserved and the doctor is notified.</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1.2rem; align-items: flex-start;">
                            <div class="stat-icon" style="flex-shrink: 0; width: 40px; height: 40px; font-size: 0.8rem; background: rgba(255,255,255,0.05);">3</div>
                            <div>
                                <h4 style="margin-bottom: 0.3rem;">Pre-session Screening</h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted);">Share your records through the medical portal for better diagnosis.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card" style="text-align: center; padding: 2.5rem; border-color: rgba(16, 185, 129, 0.2);">
                    <div style="color: var(--success); font-size: 2.5rem; margin-bottom: 1.5rem;"><i class="fas fa-shield-heart"></i></div>
                    <h4 style="margin-bottom: 0.5rem;">Verified Specialists Only</h4>
                    <p style="font-size: 0.9rem; color: var(--text-dim);">Every board-certified professional on MedConnect undergoes strict verification by our clinical team.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    function fetchSlots() {
        const doctorId = document.getElementById('doctor').value;
        const date = document.getElementById('date').value;
        const timeSelect = document.getElementById('time');
        const msg = document.getElementById('slot-msg');

        if (!doctorId || !date) return;

        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option>Synchronizing slots...</option>';
        msg.innerText = '';

        fetch(`../api/get_time_slots.php?doctor_id=${doctorId}&date=${date}`)
            .then(res => res.json())
            .then(data => {
                timeSelect.innerHTML = '';
                if (data.available && data.slots.length > 0) {
                    timeSelect.innerHTML = '<option value="">Available Time Slots</option>' + 
                        data.slots.map(s => `<option value="${s.value}">${s.display}</option>`).join('');
                    timeSelect.disabled = false;
                    msg.style.color = 'var(--success)';
                    msg.innerHTML = '<i class="fas fa-check-circle"></i> Slots synchronized successfully.';
                } else {
                    timeSelect.innerHTML = '<option value="">Fully Booked / Unavailable</option>';
                    msg.style.color = 'var(--accent)';
                    msg.innerText = data.message || 'The specialist has no availability on this day.';
                }
            });
    }

    // Call fetchSlots on page load if doctor_id and date are preset
    window.onload = function() {
        if(document.getElementById('doctor').value && document.getElementById('date').value) {
            fetchSlots();
        }
    };
</script>

<?php include '../includes/footer.php'; ?>