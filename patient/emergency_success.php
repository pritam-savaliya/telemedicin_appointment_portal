<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$hospital_id = isset($_GET['hospital_id']) ? intval($_GET['hospital_id']) : 0;
$hospital = null;

if ($hospital_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM hospitals WHERE id = ?");
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $hospital = $result->fetch_assoc();
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container section">

    <div style="text-align: center; margin-bottom: 2rem;">
        <div class="checkmark-circle">
            <div class="background"></div>
            <div class="checkmark draw" style="display: block;"></div>
        </div>
        <h1 style="color: var(--success-color); margin-top: 20px;">Emergency Response Activated</h1>
        <p style="color: var(--text-color); font-size: 1.1rem;">Payment confirmed. Please proceed to the hospital
            immediately.</p>
    </div>

    <?php if ($hospital): ?>
        <div class="card" style="max-width: 800px; margin: 0 auto; border-left: 5px solid #d63031;">
            <h2 style="color: #d63031; margin-bottom: 15px;">
                <i class="fas fa-hospital-alt"></i>
                <?php echo $hospital['name']; ?>
            </h2>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div>
                    <h4 style="margin-bottom: 5px; color: var(--text-muted);">Address</h4>
                    <p style="font-size: 1.1rem; line-height: 1.6;">
                        <?php echo $hospital['address']; ?><br>
                        <?php echo $hospital['city']; ?>
                    </p>
                </div>
                <div>
                    <h4 style="margin-bottom: 5px; color: var(--text-muted);">Emergency Contact</h4>
                    <p style="font-size: 1.2rem; font-weight: bold;">
                        <a href="tel:<?php echo $hospital['phone']; ?>"
                            style="color: var(--text-main); text-decoration: none;">
                            <i class="fas fa-phone"></i>
                            <?php echo $hospital['phone']; ?>
                        </a>
                    </p>
                    <span
                        style="background: rgba(108, 92, 231, 0.1); padding: 5px 10px; border-radius: 15px; font-size: 0.9rem; color: var(--secondary-color); display: inline-block; margin-top: 5px;">
                        <?php echo $hospital['specialty']; ?>
                    </span>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="<?php echo $hospital['google_map_url']; ?>" target="_blank" class="btn btn-primary"
                    style="background-color: #4285F4; padding: 15px 30px; font-size: 1.1rem; display: inline-flex; align-items: center; gap: 10px;">
                    <i class="fas fa-map-marker-alt"></i> Get Directions on Google Maps
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert-error" style="text-align: center;">
            Hospital details not found. Please contact support or call 102.
        </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 30px;">
        <a href="patient_dashboard.php" class="btn btn-secondary">Return to Dashboard</a>
    </div>

</div>

<style>
    /* Reuse Checkmark Animation */
    .checkmark-circle {
        width: 100px;
        height: 100px;
        position: relative;
        display: inline-block;
        vertical-align: top;
        margin: 0 auto;
    }

    .checkmark-circle .background {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #2ECC71;
        position: absolute;
    }

    .checkmark-circle .checkmark {
        border-radius: 5px;
    }

    .checkmark-circle .checkmark:after {
        opacity: 1;
        height: 50px;
        width: 25px;
        transform-origin: left top;
        border-right: 15px solid white;
        border-top: 15px solid white;
        content: '';
        left: 25px;
        top: 50px;
        position: absolute;
        transform: scaleX(-1) rotate(135deg);
    }
</style>

<?php include '../includes/footer.php'; ?>