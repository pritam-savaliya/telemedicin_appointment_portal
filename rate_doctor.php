<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
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

<?php include 'includes/header.php'; ?>

<style>
    .rating-container {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .rating-container input {
        display: none;
    }

    .rating-container label {
        font-size: 2.5rem;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s;
    }

    .rating-container input:checked~label,
    .rating-container label:hover,
    .rating-container label:hover~label {
        color: #ffd700;
    }
</style>

<div class="container section">
    <div style="max-width: 600px; margin: 0 auto;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 10px;">Rate Your Experience</h2>
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 30px;">
                How was your appointment with <strong>Dr.
                    <?php echo $appt['doctor_name']; ?>
                </strong>?
            </p>

            <?php echo $message; ?>

            <form action="" method="POST">
                <div class="form-group" style="text-align: center;">
                    <label style="display: block; margin-bottom: 10px;">Select Rating</label>
                    <div class="rating-container">
                        <input type="radio" name="rating" id="star5" value="5" required> <label for="star5"
                            title="Excellent"><i class="fas fa-star"></i></label>
                        <input type="radio" name="rating" id="star4" value="4"> <label for="star4" title="Good"><i
                                class="fas fa-star"></i></label>
                        <input type="radio" name="rating" id="star3" value="3"> <label for="star3" title="Average"><i
                                class="fas fa-star"></i></label>
                        <input type="radio" name="rating" id="star2" value="2"> <label for="star2" title="Poor"><i
                                class="fas fa-star"></i></label>
                        <input type="radio" name="rating" id="star1" value="1"> <label for="star1" title="Terrible"><i
                                class="fas fa-star"></i></label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comment">Your Review (Optional)</label>
                    <textarea name="comment" class="form-control" rows="4"
                        placeholder="Share your experience..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1.1rem;">
                    Submit Review <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>