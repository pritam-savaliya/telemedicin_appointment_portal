<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
$message = "";

// valid appointment check
$sql = "SELECT a.*, u.fullname as doctor_name 
        FROM appointments a 
        JOIN users u ON a.doctor_id = u.id 
        WHERE a.id = $appointment_id AND a.patient_id = " . $_SESSION['user_id'];
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Invalid Appointment");
}

$appt = $result->fetch_assoc();

// Check if already rated
$check_review = $conn->query("SELECT * FROM reviews WHERE appointment_id = $appointment_id");
if ($check_review->num_rows > 0) {
    header("Location: my_appointments.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = intval($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);
    $patient_id = $_SESSION['user_id'];
    $doctor_id = $appt['doctor_id'];

    if ($rating < 1 || $rating > 5) {
        $message = "<div class='alert-error'>Please select a valid rating.</div>";
    } else {
        $ins_sql = "INSERT INTO reviews (appointment_id, patient_id, doctor_id, rating, comment) VALUES ($appointment_id, $patient_id, $doctor_id, $rating, '$comment')";

        if ($conn->query($ins_sql) === TRUE) {
            header("Location: my_appointments.php?msg=review_submitted");
            exit();
        } else {
            $message = "<div class='alert-error'>Error: " . $conn->error . "</div>";
        }
    }
}
?>

<?php 
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

    <main class="main-content">
        <style>
            .rating-container {
                display: flex;
                flex-direction: row-reverse;
                justify-content: center;
                gap: 15px;
                margin: 2rem 0;
            }
            .rating-container input { display: none; }
            .rating-container label {
                font-size: 3rem;
                color: rgba(255,255,255,0.1);
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .rating-container input:checked ~ label,
            .rating-container label:hover,
            .rating-container label:hover ~ label {
                color: #fbbf24;
                transform: scale(1.1);
                filter: drop-shadow(0 0 10px rgba(251, 191, 36, 0.4));
            }
        </style>

        <div style="max-width: 800px; margin: 0 auto; padding: 2rem;">
            <div style="margin-bottom: 4rem; text-align: center;">
                <h1 style="font-size: 3rem; margin-bottom: 1rem;">Experience Review</h1>
                <p style="color: var(--text-muted); font-size: 1.2rem;">How was your clinical session with <span style="color: var(--secondary); font-weight: 700;">Dr. <?php echo $appt['doctor_name']; ?></span>?</p>
            </div>

            <?php if ($message) echo $message; ?>

            <div class="glass-card" style="padding: 4rem 3rem; border: 1px solid var(--border-glass);">
                <form action="" method="POST">
                    <div style="text-align: center; margin-bottom: 3rem;">
                        <h3 style="font-size: 1.2rem; text-transform: uppercase; letter-spacing: 2px; color: var(--text-dim); margin-bottom: 1rem;">Clinical Rating</h3>
                        <div class="rating-container">
                            <input type="radio" name="rating" id="star5" value="5" required> <label for="star5" title="Exceptional"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star4" value="4"> <label for="star4" title="Good"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star3" value="3"> <label for="star3" title="Average"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star2" value="2"> <label for="star2" title="Poor"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star1" value="1"> <label for="star1" title="Unsatisfactory"><i class="fas fa-star"></i></label>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 2.5rem;">
                        <label class="form-label" style="font-size: 0.9rem; color: var(--text-dim); text-transform: uppercase;">Detailed Feedback (Optional)</label>
                        <textarea name="comment" class="form-control" rows="5" placeholder="Please describe the quality of care and consultation accuracy..." style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-glass); padding: 1.5rem; border-radius: 16px;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-weight: 700; font-size: 1.1rem; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">
                        Transmit Review <i class="fas fa-paper-plane" style="margin-left: 10px; opacity: 0.7;"></i>
                    </button>
                    
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="my_appointments.php" style="color: var(--text-muted); font-size: 0.9rem; text-decoration: none;">Abort and Return to Ledger</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
