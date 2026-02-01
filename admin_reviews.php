<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle Delete Review
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if ($conn->query("DELETE FROM reviews WHERE id = $delete_id") === TRUE) {
        $message = "<div class='alert-success'>Review deleted successfully.</div>";
    } else {
        $message = "<div class='alert-error'>Error deleting review: " . $conn->error . "</div>";
    }
}

// Fetch Reviews
$sql = "SELECT r.*, 
        p.fullname as patient_name, 
        d.fullname as doctor_name 
        FROM reviews r 
        JOIN users p ON r.patient_id = p.id 
        JOIN users d ON r.doctor_id = d.id 
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>

<div class="container section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 0;">Manage Reviews</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php echo $message; ?>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Patient</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </td>
                                <td style="font-weight: 600; color: var(--primary-color);">Dr.
                                    <?php echo $row['doctor_name']; ?>
                                </td>
                                <td>
                                    <?php echo $row['patient_name']; ?>
                                </td>
                                <td>
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $row['rating']) ? '<i class="fas fa-star" style="color: #ffd700;"></i>' : '<i class="far fa-star" style="color: #ddd;"></i>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo $row['comment']; ?>
                                </td>
                                <td>
                                    <a href="admin_reviews.php?action=delete&id=<?php echo $row['id']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this review?');"
                                        class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                No reviews found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>