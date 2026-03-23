<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

// Handle Emergency Booking Selection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hospital_id'])) {

    // Set Emergency Booking Details
    $_SESSION['booking_details'] = [
        'doctor_id' => 0, // 0 indicates Emergency/Hospital based
        'hospital_id' => $_POST['hospital_id'],
        'hospital_name' => $_POST['hospital_name'],
        'hospital_map' => $_POST['hospital_map'], // Store map URL for redirect
        'date' => date('Y-m-d'),
        'time' => date('H:i:s'),
        'is_emergency' => true
    ];

    // Redirect to Payment
    header("Location: payment.php");
    exit();
}

// Fetch Hospitals
$sql = "SELECT * FROM hospitals";
$result = $conn->query($sql);
?>

<?php include '../includes/header.php'; ?>

<div class="container section">

    <!-- Emergency Header -->
    <div
        style="text-align: center; margin-bottom: 2rem; background: #ffeaa7; padding: 20px; border-radius: 10px; border: 2px solid #fdcb6e;">
        <h1
            style="color: #d63031; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 10px;">
            <i class="fas fa-ambulance"></i> Emergency Services
        </h1>
        <p style="color: #2d3436; font-weight: bold; font-size: 1.1rem;">
            For life-threatening emergencies, please call <span style="color: #d63031; font-size: 1.3rem;">102</span> or
            <span style="color: #d63031; font-size: 1.3rem;">108</span> immediately.
        </p>
    </div>

    <!-- Illness Selector -->
    <div class="glass-card" style="margin-bottom: 30px; border-left: 5px solid var(--primary);">
        <h3 style="margin-bottom: 15px;">What is the emergency?</h3>
        <div class="form-group">
            <label for="illnessSelect">Select Illness / Condition</label>
            <select id="illnessSelect" class="form-control" onchange="filterHospitals()"
                style="font-size: 1.1rem; padding: 12px;">
                <option value="">-- Select Emergency Type --</option>
                <option value="Cardiology">Heart Attack / Chest Pain</option>
                <option value="Trauma & Accident">Road Accident / Severe Trauma</option>
                <option value="General & Emergency">Severe Fever / General Emergency</option>
                <option value="Pediatrics">Child / Pediatric Emergency</option>
                <option value="Orthopedics">Bone Fracture / Injury</option>
                <option value="Maternity & Gynecology">Pregnancy / Labor</option>
                <option value="Neurology">Stroke / Seizure / Numbness</option>
                <option value="Ophthalmology">Eye Injury / Vision Loss</option>
                <option value="Critical Care">Breathing Difficulty / Critical Care</option>
                <option value="ALL">Other / Not Listed</option>
            </select>
        </div>
    </div>

    <!-- Hospitals Table -->
    <div id="hospitalsContainer" class="glass-card" style="padding: 0; overflow: hidden; display: none;">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-glass);">
            <h3 style="margin: 0; color: var(--primary);"><i class="fas fa-hospital-user"></i> Available Emergency Facilities</h3>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--primary); color: white;">
                    <tr>
                        <th style="padding: 15px; text-align: left;">Hospital Name</th>
                        <th style="padding: 15px; text-align: left;">Specialty</th>
                        <th style="padding: 15px; text-align: left;">Location</th>
                        <th style="padding: 15px; text-align: left;">Contact</th>
                        <th style="padding: 15px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hospital-row" data-specialty="<?php echo $row['specialty']; ?>"
                                style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; font-weight: 500;">
                                    <?php echo $row['name']; ?>
                                </td>
                                <td style="padding: 15px; color: var(--secondary);">
                                    <span
                                        style="background: rgba(108, 92, 231, 0.1); padding: 5px 10px; border-radius: 15px; font-size: 0.85rem;">
                                        <?php echo $row['specialty']; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; color: var(--text-muted);">
                                    <?php echo $row['address'] . ', ' . $row['city']; ?>
                                </td>
                                <td style="padding: 15px;">
                                    <a href="tel:<?php echo $row['phone']; ?>"
                                        style="color: var(--text-main); text-decoration: none;">
                                        <i class="fas fa-phone-alt" style="color: var(--success); margin-right: 5px;"></i>
                                        <?php echo $row['phone']; ?>
                                    </a>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <form method="POST" action="">
                                        <input type="hidden" name="hospital_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="hospital_name" value="<?php echo $row['name']; ?>">
                                        <input type="hidden" name="hospital_map" value="<?php echo $row['google_map_url']; ?>">

                                        <button type="submit" class="btn btn-danger"
                                            style="background-color: #d63031; border: none; padding: 8px 15px; font-size: 0.9rem;">
                                            Book & Navigate
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">No hospitals found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- No Match Message -->
    <div id="noHospitalsMsg" style="display: none; text-align: center; padding: 20px; color: var(--text-muted);">
        No specific hospitals found for this condition. Please call 102.
    </div>

    <div style="text-align: center; margin-top: 25px;">
        <a href="patient_dashboard.php" style="color: var(--text-muted); font-size: 0.9rem;">Back to Dashboard</a>
    </div>

</div>

<script>
    function filterHospitals() {
        const selection = document.getElementById('illnessSelect').value;
        const container = document.getElementById('hospitalsContainer');
        const rows = document.querySelectorAll('.hospital-row');
        const noMsg = document.getElementById('noHospitalsMsg');

        if (!selection) {
            container.style.display = 'none';
            noMsg.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        let visibleCount = 0;

        rows.forEach(row => {
            const specialty = row.getAttribute('data-specialty');
            if (selection === 'ALL' || specialty === selection) {
                row.style.display = 'table-row';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            container.style.display = 'none';
            noMsg.style.display = 'block';
        } else {
            noMsg.style.display = 'none';
        }
    }
</script>

<?php include '../includes/footer.php'; ?>