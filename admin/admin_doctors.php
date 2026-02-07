<?php
include '../includes/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// Check for messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'approved') {
        $message = "<div class='alert-success'>Doctor account approved successfully!</div>";
    } elseif ($_GET['msg'] == 'rejected') {
        $message = "<div class='alert-success'>Doctor account rejected/deleted.</div>";
    } elseif ($_GET['msg'] == 'error') {
        $message = "<div class='alert-error'>An error occurred. Please try again.</div>";
    }
}

// Fetch all doctors
$sql_doctors = "SELECT * FROM users WHERE role = 'doctor' ORDER BY created_at DESC";
$doctors_result = $conn->query($sql_doctors);

?>
<?php include '../includes/header.php'; ?>

<div class="container section">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>Manage Doctors</h2>
        <div style="display: flex; gap: 10px;">
            <a href="add_doctor.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Doctor</a>
            <a href="<?php echo BASE_URL; ?>admin/admin_dashboard.php" class="btn btn-outline"><i
                    class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

    <?php if ($message != "")
        echo $message; ?>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 25px; border-bottom: 1px solid #eee;">
            <h3 style="margin: 0;"><i class="fas fa-user-md" style="color: var(--primary-color);"></i> Registered
                Doctors</h3>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($doctors_result->num_rows > 0): ?>
                        <?php while ($row = $doctors_result->fetch_assoc()): ?>
                            <tr>
                                <td>#
                                    <?php echo $row['id']; ?>
                                </td>
                                <td style="font-weight: 600;">Dr.
                                    <?php echo $row['fullname']; ?>
                                </td>
                                <td>
                                    <?php echo $row['email']; ?>
                                </td>
                                <td>
                                    <?php if ($row['is_approved'] == 1): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending Approval</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 10px;">
                                        <?php if ($row['is_approved'] == 0): ?>
                                            <a href="update_account_status.php?action=approve&id=<?php echo $row['id']; ?>"
                                                class="btn btn-success" style="padding: 5px 15px; font-size: 0.85rem;"
                                                title="Approve">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                        <?php endif; ?>

                                        <a href="update_account_status.php?action=reject&id=<?php echo $row['id']; ?>"
                                            onclick="return confirm('Are you sure you want to reject/delete this doctor?');"
                                            class="btn btn-danger" style="padding: 5px 15px; font-size: 0.85rem;"
                                            title="Reject">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">No doctors found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>