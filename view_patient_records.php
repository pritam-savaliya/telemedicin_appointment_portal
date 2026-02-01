<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit();
}

$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

// Fetch Patient Info
$pat_sql = "SELECT fullname, email FROM users WHERE id = $patient_id";
$pat_res = $conn->query($pat_sql);

if ($pat_res->num_rows == 0) {
    die("Invalid Patient");
}
$patient = $pat_res->fetch_assoc();

// Fetch Records
$sql = "SELECT * FROM medical_records WHERE patient_id = $patient_id ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>

<div class="container section">

    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin-bottom: 5px;">Patient Medical Records</h2>
            <p style="color: var(--text-muted);">
                Patient: <span style="font-weight: 600; color: var(--primary-color);">
                    <?php echo $patient['fullname']; ?>
                </span>
            </p>
        </div>
        <a href="doctor_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Upload Date</th>
                            <th>Description</th>
                            <th>File Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?>
                                </td>
                                <td style="font-weight: 500;">
                                    <?php echo $row['description']; ?>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.9rem;">
                                    <?php echo $row['file_name']; ?>
                                </td>
                                <td>
                                    <a href="<?php echo $row['file_path']; ?>" target="_blank" class="btn btn-primary"
                                        style="padding: 5px 12px; font-size: 0.8rem;">
                                        <i class="fas fa-eye"></i> View File
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <div style="margin-bottom: 15px; font-size: 2rem; opacity: 0.5;">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <p>No medical records found for this patient.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>