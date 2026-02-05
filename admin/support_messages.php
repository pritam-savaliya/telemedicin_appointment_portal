<?php
include '../includes/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Clear "New support message" notifications for this admin
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id AND message LIKE 'New support message%'");

?>
<?php include '../includes/header.php'; ?>

<div class="container section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Support Messages</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <!-- Messages List -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>#" . $row['id'] . "</td>";
                            echo "<td>
                                    <div style='font-weight: 600;'>" . $row['name'] . "</div>
                                    <div style='font-size: 0.85rem; color: var(--text-muted);'>" . $row['email'] . "</div>
                                  </td>";
                            echo "<td>" . $row['subject'] . "</td>";
                            echo "<td><div style='max-width: 400px; white-space: pre-wrap;'>" . $row['message'] . "</div></td>";
                            echo "<td>" . date('M d, Y h:i A', strtotime($row['created_at'])) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center; padding: 30px;'>No messages found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>