<?php
include '../includes/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action_id = intval($_GET['id']);
    $action = $_GET['action']; // confirm, cancel, delete

    if ($action == 'delete') {
        // Delete chat messages linked to appointment
        $conn->query("DELETE FROM chat_messages WHERE appointment_id = $action_id");
        if ($conn->query("DELETE FROM appointments WHERE id = $action_id") === TRUE) {
            $message = "<div class='alert-success'>Appointment deleted.</div>";
        } else {
            $message = "<div class='alert-error'>Error deleting appointment.</div>";
        }
    } elseif ($action == 'confirm') {
        $conn->query("UPDATE appointments SET status = 'confirmed' WHERE id = $action_id");
        $message = "<div class='alert-success'>Appointment confirmed.</div>";
    } elseif ($action == 'cancel') {
        $conn->query("UPDATE appointments SET status = 'cancelled' WHERE id = $action_id"); // or rejected
        $message = "<div class='alert-success'>Appointment cancelled.</div>";
    }
}

// Filter Logic
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$where_clause = "";
if ($filter_status != 'all') {
    $filter_status_safe = $conn->real_escape_string($filter_status);
    $where_clause = "WHERE a.status = '$filter_status_safe'";
}

// Fetch Appointments
$sql = "SELECT a.*, p.fullname AS patient_name, d.fullname AS doctor_name 
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        JOIN users d ON a.doctor_id = d.id 
        $where_clause
        ORDER BY a.date DESC, a.time ASC";
$result = $conn->query($sql);

?>
<?php include '../includes/header.php'; ?>

<div class="container section">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>Manage Appointments</h2>
        <a href="<?php echo BASE_URL; ?>admin/admin_dashboard.php" class="btn btn-outline"><i
                class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <!-- Filter Tabs -->
    <div style="margin-bottom: 30px; display: flex; gap: 10px;">
        <a href="admin_appointments.php?status=all"
            class="btn <?php echo $filter_status == 'all' ? 'btn-primary' : 'btn-outline'; ?>">All</a>
        <a href="admin_appointments.php?status=pending"
            class="btn <?php echo $filter_status == 'pending' ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
        <a href="admin_appointments.php?status=confirmed"
            class="btn <?php echo $filter_status == 'confirmed' ? 'btn-primary' : 'btn-outline'; ?>">Confirmed</a>
        <a href="admin_appointments.php?status=completed"
            class="btn <?php echo $filter_status == 'completed' ? 'btn-primary' : 'btn-outline'; ?>">Completed</a>
        <a href="admin_appointments.php?status=cancelled"
            class="btn <?php echo $filter_status == 'cancelled' ? 'btn-primary' : 'btn-outline'; ?>">Cancelled</a>
    </div>

    <?php if ($message != "")
        echo $message; ?>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status = $row['status'];
                            $badge_class = 'badge-pending';
                            if ($status == 'confirmed')
                                $badge_class = 'badge-success';
                            elseif ($status == 'cancelled' || $status == 'rejected')
                                $badge_class = 'badge-danger';
                            elseif ($status == 'completed')
                                $badge_class = 'badge-primary';

                            echo '<tr>';
                            echo '<td>#' . $row['id'] . '</td>';
                            echo '<td>' . htmlspecialchars($row['patient_name']) . '</td>';
                            echo '<td>Dr. ' . htmlspecialchars($row['doctor_name']) . '</td>';
                            echo '<td>' . $row['date'] . ' <br><small class="text-muted">' . date("h:i A", strtotime($row['time'])) . '</small></td>';
                            echo '<td><span class="badge ' . $badge_class . '">' . ucfirst($status) . '</span></td>';
                            echo '<td>';

                            if ($status == 'pending') {
                                echo '<a href="admin_appointments.php?action=confirm&id=' . $row['id'] . '&status=' . $filter_status . '" class="btn btn-success" style="padding: 5px 10px; font-size: 0.8rem; margin-right: 5px;"><i class="fas fa-check"></i></a>';
                                echo '<a href="admin_appointments.php?action=cancel&id=' . $row['id'] . '&status=' . $filter_status . '" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8rem; margin-right: 5px;"><i class="fas fa-ban"></i></a>';
                            }

                            echo '<a href="admin_appointments.php?action=delete&id=' . $row['id'] . '&status=' . $filter_status . '" onclick="return confirm(\'Delete this appointment?\');" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;"><i class="fas fa-trash-alt"></i></a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6" style="text-align: center; padding: 30px;">No appointments found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div><?php include '../includes/footer.php'; ?>