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

    // Set target directory relative to this script
    $relative_target_dir = "../assets/uploads/reports/";
    $db_target_dir = "assets/uploads/reports/";

    if (!file_exists($relative_target_dir)) {
        mkdir($relative_target_dir, 0777, true);
    }

    $file_name = basename($_FILES["report"]["name"]);
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_file_name = $patient_id . "_" . time() . "." . $file_type;
    
    $target_file = $relative_target_dir . $new_file_name;
    $db_file_path = $db_target_dir . $new_file_name;

    // Allowed file types
    $allowed_types = array("jpg", "png", "jpeg", "pdf");

    if (in_array($file_type, $allowed_types)) {
        if (move_uploaded_file($_FILES["report"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO medical_records (patient_id, file_name, file_path, description) VALUES ('$patient_id', '$file_name', '$db_file_path', '$description')";
            if ($conn->query($sql) === TRUE) {
                $message = "<div class='badge badge-success' style='width:100%; border-radius:12px; margin-bottom:1.5rem; padding:1rem;'><i class='fas fa-check-circle'></i> Report uploaded and secured successfully!</div>";
            } else {
                $message = "<div class='badge badge-danger' style='width:100\%; border-radius:12px; margin-bottom:1.5rem; padding:1rem;'>Database Error: " . $conn->error . "</div>";
            }
        } else {
            $message = "<div class='badge badge-danger' style='width:100%; border-radius:12px; margin-bottom:1.5rem; padding:1rem;'>Error: System could not move the file. Check permissions.</div>";
        }
    } else {
        $message = "<div class='badge badge-danger' style='width:100%; border-radius:12px; margin-bottom:1.5rem; padding:1rem;'>Only JPG, JPEG, PNG, and PDF files are allowed.</div>";
    }
}

// Fetch Records
$records_sql = "SELECT * FROM medical_records WHERE patient_id = $patient_id ORDER BY uploaded_at DESC";
$records_result = $conn->query($records_sql);
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
        <a href="upload_report.php" class="sidebar-item active"><i class="fas fa-file-medical"></i> Medical Records</a>
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
        <div style="margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Medical Records</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Securely store and manage your clinical data and reports.</p>
        </div>

        <?php if($message) echo $message; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <!-- Upload Form -->
        <div class="glass-card">
            <h3 style="margin-bottom: 2rem; color: var(--primary);"><i class="fas fa-cloud-arrow-up"></i> Upload New Report</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Report Title / Description</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g. Annual Blood Panel" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Select Medical Document</label>
                    <div id="drop-zone" style="border: 2px dashed var(--border-glass); padding: 3rem 2rem; text-align: center; border-radius: var(--radius-md); background: var(--bg-glass); cursor: pointer; position: relative; transition: var(--transition);">
                        <div id="upload-prompt">
                            <i class="fas fa-file-medical" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p style="margin: 0; color: var(--text-muted); font-weight: 500;">Click or drag file here to upload</p>
                        </div>
                        
                        <div id="preview-area" style="display: none;">
                            <img id="image-preview" src="#" style="max-width: 100%; max-height: 200px; border-radius: 12px; margin-bottom: 1rem; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                            <div id="pdf-preview" style="display: none; padding: 1.5rem; background: rgba(99, 102, 241, 0.1); border-radius: 12px; color: var(--primary); margin-bottom: 1rem;">
                                <i class="fas fa-file-pdf" style="font-size: 2.5rem; margin-bottom: 0.5rem;"></i>
                                <div style="font-size: 0.9rem; font-weight: 700;">PDF Document Ready</div>
                            </div>
                            <p id="file-name-display" style="font-size: 0.85rem; color: var(--text-main); font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"></p>
                            <span style="font-size: 0.75rem; color: var(--primary); cursor: pointer;" onclick="resetUpload(event)">Change File</span>
                        </div>

                        <input type="file" name="report" id="report-input" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem;">
                    Securely Upload Report <i class="fas fa-shield-halved" style="margin-left: 8px;"></i>
                </button>
            </form>
        </div>

        <!-- Records List -->
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem 2.5rem; border-bottom: 1px solid var(--border-glass);">
                <h3 style="margin: 0; color: var(--secondary);"><i class="fas fa-folder-open"></i> Your Secure Vault</h3>
            </div>
            <div class="table-container" style="overflow-x: auto;">
                <?php if ($records_result->num_rows > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--border-glass);">
                                <th style="padding: 1rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-dim);">Date</th>
                                <th style="padding: 1rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-dim);">Title</th>
                                <th style="padding: 1rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-dim); text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $records_result->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid var(--border-glass); transition: var(--transition);" onmouseover="this.style.background='var(--bg-glass)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.2rem 1.5rem; font-size: 0.9rem;">
                                        <?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?>
                                    </td>
                                    <td style="padding: 1.2rem 1.5rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                        <div style="font-size: 0.7rem; color: var(--text-dim); margin-top: 4px;"><?php echo htmlspecialchars($row['file_name']); ?></div>
                                    </td>
                                    <td style="padding: 1.2rem 1.5rem; text-align: center;">
                                        <a href="<?php echo BASE_URL . $row['file_path']; ?>" target="_blank" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem; border-radius: 10px;">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding: 5rem; text-align: center; opacity: 0.5;">
                        <i class="fas fa-folder-minus" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>No medical documents found in your repository.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const reportInput = document.getElementById('report-input');
    const dropZone = document.getElementById('drop-zone');
    const uploadPrompt = document.getElementById('upload-prompt');
    const previewArea = document.getElementById('preview-area');
    const imagePreview = document.getElementById('image-preview');
    const pdfPreview = document.getElementById('pdf-preview');
    const fileNameDisplay = document.getElementById('file-name-display');

    reportInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            uploadPrompt.style.display = 'none';
            previewArea.style.display = 'block';
            fileNameDisplay.innerText = file.name;

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    pdfPreview.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                imagePreview.style.display = 'none';
                pdfPreview.style.display = 'block';
            }
        }
    });

    function resetUpload(e) {
        e.preventDefault();
        e.stopPropagation();
        reportInput.value = '';
        uploadPrompt.style.display = 'block';
        previewArea.style.display = 'none';
    }

    // Drag and drop highlights
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.style.borderColor = 'var(--primary)', false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.style.borderColor = 'var(--border-glass)', false);
    });
</script>

<?php include '../includes/footer.php'; ?>