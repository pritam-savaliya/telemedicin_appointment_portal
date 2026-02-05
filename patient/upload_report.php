<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$message = "";

// Handle File Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["report"])) {
    $description = $conn->real_escape_string($_POST['description']);

    $target_dir = "../assets/uploads/reports/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = basename($_FILES["report"]["name"]);
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_file_name = $patient_id . "_" . time() . "." . $file_type;
    $target_file = $target_dir . $new_file_name;

    // Allowed file types
    $allowed_types = array("jpg", "png", "jpeg", "pdf");

    if (in_array($file_type, $allowed_types)) {
        if (move_uploaded_file($_FILES["report"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO medical_records (patient_id, file_name, file_path, description) VALUES ('$patient_id', '$file_name', '$target_file', '$description')";
            if ($conn->query($sql) === TRUE) {
                $message = "<div class='alert-success'>Report uploaded successfully!</div>";
            } else {
                $message = "<div class='alert-error'>Database Error: " . $conn->error . "</div>";
            }
        } else {
            $message = "<div class='alert-error'>Error uploading file.</div>";
        }
    } else {
        $message = "<div class='alert-error'>Only JPG, JPEG, PNG, and PDF files are allowed.</div>";
    }
}

// Fetch Records
$records_sql = "SELECT * FROM medical_records WHERE patient_id = $patient_id ORDER BY uploaded_at DESC";
$records_result = $conn->query($records_sql);
?>

<?php include '../includes/header.php'; ?>

<div class="container section">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin-bottom: 10px;">Medical Records</h2>
            <p style="color: var(--text-muted);">Securely store and share your medical history.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>patient/patient_dashboard.php" class="btn btn-secondary"><i
                class="fas fa-arrow-left"></i> Back</a>
    </div>

    <?php echo $message; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <!-- Upload Form -->
        <div class="card">
            <h3 style="margin-bottom: 20px;">Upload New Report</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="description">Description / Title</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g. Blood Test Results"
                        required>
                </div>

                <div class="form-group">
                    <label for="report">Select File</label>
                    <div
                        style="border: 2px dashed #ddd; padding: 20px; text-align: center; border-radius: 5px; cursor: pointer; position: relative;">
                        <i class="fas fa-cloud-upload-alt"
                            style="font-size: 2rem; color: var(--primary-color); margin-bottom: 10px;"></i>
                        <p style="margin: 0; color: var(--text-muted);">Click to browse</p>
                        <input type="file" name="report"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;"
                            required>
                    </div>
                    <small style="color: var(--text-muted);">PDF, JPG, PNG (Max 5MB)</small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Upload <i
                        class="fas fa-upload"></i></button>
            </form>
        </div>

        <!-- Records List -->
        <div class="card">
            <h3 style="margin-bottom: 20px;">Your Files</h3>
            <div class="table-container">
                <?php if ($records_result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>File Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $records_result->fetch_assoc()): ?>
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
                                        <a href="<?php echo $row['file_path']; ?>" target="_blank" class="btn btn-outline"
                                            style="padding: 5px 10px; font-size: 0.8rem;">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 20px;">No medical records uploaded yet.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>