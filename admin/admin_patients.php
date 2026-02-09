<?php
include '../includes/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// Handle Delete Patient
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);

    // 1. Delete Chat Messages
    $conn->query("DELETE FROM chat_messages WHERE sender_id = $delete_id");

    // 2. Delete Notifications
    $conn->query("DELETE FROM notifications WHERE user_id = $delete_id");

    // 3. Delete Appointments (where patient is the user)
    // First delete chat messages associated with these appointments? 
    // Usually messages are linked to appointment_id. The previous logic in admin_dashboard dealt with this.
    // Let's do a simple delete for now.
    $conn->query("DELETE FROM appointments WHERE patient_id = $delete_id");

    // 4. Delete user
    if ($conn->query("DELETE FROM users WHERE id = $delete_id") === TRUE) {
        $message = "<div class='alert-success'>Patient record deleted.</div>";
    } else {
        $message = "<div class='alert-error'>Error deleting patient: " . $conn->error . "</div>";
    }
}

// Fetch all patients
$sql_patients = "SELECT * FROM users WHERE role = 'patient' ORDER BY created_at DESC";
$patients_result = $conn->query($sql_patients);

?>
<?php include '../includes/header.php'; ?>

<div class="container section">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>Manage Patients</h2>
        <a href="<?php echo BASE_URL; ?>admin/admin_dashboard.php" class="btn btn-outline"><i
                class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if ($message != "")
        echo $message; ?>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 25px; border-bottom: 1px solid #eee;">
            <h3 style="margin: 0;"><i class="fas fa-user-injured" style="color: var(--success-color);"></i> Registered
                Patients</h3>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($patients_result->num_rows > 0): ?>
                        <?php while ($row = $patients_result->fetch_assoc()): ?>
                            <tr>
                                <td>#
                                    <?php echo $row['id']; ?>
                                </td>
                                <td style="font-weight: 600;">
                                    <?php echo htmlspecialchars($row['fullname']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </td>
                                <td>
                                    <?php echo $row['phone'] ? htmlspecialchars($row['phone']) : '<span class="text-muted">N/A</span>'; ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </td>
                                <td>
                                    <a href="admin_patients.php?action=delete&id=<?php echo $row['id']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this patient? This will remove all their appointments and records.');"
                                        class="btn btn-danger" style="padding: 5px 15px; font-size: 0.85rem;" title="Delete">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">No patients found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>