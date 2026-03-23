<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$doctor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$doctor = null;

if ($doctor_id > 0) {
    // Basic User Details
    $stmt = $conn->prepare("SELECT fullname, email, created_at, role, profile_pic FROM users WHERE id = ? AND role = 'doctor'");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $doctor = $result->fetch_assoc();

        // Fetch Additional Details (Specialty, Bio, etc.) if table exists
        // Assuming we might have a distinct table 'doctor_profiles' or we will just use placeholders for now as per previous context
        // We will query for 'doctor_profiles' safely
        $profile_sql = "SELECT * FROM doctor_profiles WHERE user_id = $doctor_id";
        $profile_res = $conn->query($profile_sql);
        if ($profile_res && $profile_res->num_rows > 0) {
            $details = $profile_res->fetch_assoc();
            $doctor = array_merge($doctor, $details);
        } else {
            // Default placeholders
            $doctor['specialty'] = 'General Practitioner';
            $doctor['qualification'] = 'MBBS, MD';
            $doctor['experience'] = '5+ Years';
            $doctor['bio'] = 'Experienced medical professional dedicated to patient care.';
        }

        // Fetch Reviews (Simulated or Real)
        $rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE doctor_id = $doctor_id";
        $rating_res = $conn->query($rating_sql);
        $rating_data = $rating_res->fetch_assoc();
        $doctor['rating'] = number_format($rating_data['avg_rating'] ?? 0, 1);
        $doctor['reviews_count'] = $rating_data['count'] ?? 0;
    }
}

if (!$doctor) {
    echo "Doctor not found.";
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<div class="container section">

    <div style="margin-bottom: 20px;">
        <a href="patient_dashboard.php" style="color: var(--text-muted); text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Profile Header -->
    <div class="card"
        style="display: flex; flex-direction: column; md:flex-row; gap: 30px; align-items: start; margin-bottom: 30px;">
        <div style="flex-shrink: 0; text-align: center; width: 100%; max-width: 200px; margin: 0 auto;">
            <?php
            $avatar_url = !empty($doctor['profile_pic']) && $doctor['profile_pic'] != 'default_user.png' ? BASE_URL . 'assets/uploads/profile_pics/' . $doctor['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($doctor['fullname']) . '&background=random&color=fff&size=200';
            ?>
            <img src="<?php echo $avatar_url; ?>" alt="Profile"
                style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-soft);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($doctor['fullname']); ?>&background=random&color=fff&size=200'">

            <div style="margin-top: 15px;">
                <span
                    style="background: #ffeaa7; color: #d35400; padding: 5px 10px; border-radius: 15px; font-weight: bold; font-size: 0.9rem;">
                    <i class="fas fa-star"></i>
                    <?php echo $doctor['rating']; ?> (
                    <?php echo $doctor['reviews_count']; ?>)
                </span>
            </div>
        </div>

        <div style="flex-grow: 1;">
            <h1 style="margin-bottom: 5px;">Dr.
                <?php echo $doctor['fullname']; ?>
            </h1>
            <h3 style="color: var(--secondary-color); font-weight: 500; margin-bottom: 15px;">
                <?php echo $doctor['specialty']; ?>
            </h3>

            <p style="color: var(--text-color); line-height: 1.6; margin-bottom: 20px;">
                <?php echo $doctor['bio']; ?>
            </p>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <div style="background: var(--bg-section); padding: 15px; border-radius: 8px;">
                    <small style="color: var(--text-muted); display: block; margin-bottom: 5px;">Experience</small>
                    <strong style="color: var(--primary-color);">
                        <?php echo $doctor['experience']; ?>
                    </strong>
                </div>
                <div style="background: var(--bg-section); padding: 15px; border-radius: 8px;">
                    <small style="color: var(--text-muted); display: block; margin-bottom: 5px;">Qualification</small>
                    <strong style="color: var(--primary-color);">
                        <?php echo $doctor['qualification']; ?>
                    </strong>
                </div>
                <div style="background: var(--bg-section); padding: 15px; border-radius: 8px;">
                    <small style="color: var(--text-muted); display: block; margin-bottom: 5px;">Joined</small>
                    <strong>
                        <?php echo date('M Y', strtotime($doctor['created_at'])); ?>
                    </strong>
                </div>
            </div>

            <div style="display: flex; gap: 15px;">
                <form action="book_appointment.php" method="POST" style="display: inline;">
                    <!-- Auto-select doctor in booking page if possible, otherwise just redirect -->
                    <!-- Since book_appointment.php uses POST to select, we might need to adjust it or just send ID in URL if supported. 
                         The current book_appointment.php supports selection via dropdown. 
                         Let's just link to book_appointment.php simple for now or pass GET param if it supported it.
                         Current book_appointment.php logic: reads POST doctor_id on submit.
                         I will update book_appointment to accept GET. -->
                    <a href="book_appointment.php?doctor_id=<?php echo $doctor_id; ?>" class="btn btn-primary">
                        Book Appointment
                    </a>
                </form>
                <button class="btn btn-secondary" disabled title="Feature coming soon">Message</button>
            </div>
        </div>
    </div>

    <!-- Reviews Section (Placeholder) -->
    <div class="card">
        <h3>Patient Reviews</h3>
        <?php if ($doctor['reviews_count'] > 0): ?>
            <!-- Fetch reviews logic would go here -->
            <p style="color: var(--text-muted); margin-top: 10px;">Reviews are available.</p>
        <?php else: ?>
            <p style="color: var(--text-muted); margin-top: 10px;">No reviews yet.</p>
        <?php endif; ?>
    </div>

</div>

<?php include '../includes/footer.php'; ?>