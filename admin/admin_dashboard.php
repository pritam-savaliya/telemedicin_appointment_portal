<?php
include '../includes/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// Handle Add Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $role = 'admin';

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert-error'>Invalid email format</div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='alert-error'>Password must be at least 6 characters</div>";
    } else {
        // Hash Password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $checkEmail = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($checkEmail);

        if ($result->num_rows > 0) {
            $message = "<div class='alert-error'>Email already registered!</div>";
        } else {
            // Insert Admin
            $sql = "INSERT INTO users (fullname, email, password, role) VALUES ('$fullname', '$email', '$hashed_password', '$role')";

            if ($conn->query($sql) === TRUE) {
                $message = "<div class='alert-success'>New Admin added successfully!</div>";
            } else {
                $message = "<div class='alert-error'>Error: " . $conn->error . "</div>";
            }
        }
    }
}

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);

    // Prevent deleting self
    if ($delete_id != $_SESSION['user_id']) {
        // 1. Delete Chat Messages (linked to appointments OR sent by user)
        // Get all appointment IDs involving this user
        $get_appts = $conn->query("SELECT id FROM appointments WHERE patient_id = $delete_id OR doctor_id = $delete_id");
        $appt_ids = [];
        if ($get_appts) {
            while ($row = $get_appts->fetch_assoc()) {
                $appt_ids[] = $row['id'];
            }
        }

        if (!empty($appt_ids)) {
            $ids_str = implode(',', $appt_ids);
            // Delete messages in those appointments
            $conn->query("DELETE FROM chat_messages WHERE appointment_id IN ($ids_str)");
        }
        // Also delete messages sent by user
        $conn->query("DELETE FROM chat_messages WHERE sender_id = $delete_id");

        // 2. Delete Notifications
        $conn->query("DELETE FROM notifications WHERE user_id = $delete_id");

        // 3. Delete Appointments
        $conn->query("DELETE FROM appointments WHERE patient_id = $delete_id OR doctor_id = $delete_id");

        // 4. Delete user
        if ($conn->query("DELETE FROM users WHERE id = $delete_id") === TRUE) {
            header("Location: admin_dashboard.php?msg=deleted");
            exit();
        } else {
            $message = "<div class='alert-error'>Error deleting user: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert-error'>You cannot delete yourself!</div>";
    }
}

// Handle Approve User
if (isset($_GET['action']) && $_GET['action'] == 'approve_user' && isset($_GET['id'])) {
    $approve_id = intval($_GET['id']);
    if ($conn->query("UPDATE users SET is_approved = 1 WHERE id = $approve_id") === TRUE) {
        header("Location: admin_dashboard.php?msg=approved");
        exit();
    } else {
        $message = "<div class='alert-error'>Error approving user: " . $conn->error . "</div>";
    }
}

// Check for delete success message
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $message = "<div class='alert-success'>User deleted successfully!</div>";
    } elseif ($_GET['msg'] == 'approved') {
        $message = "<div class='alert-success'>User approved successfully!</div>";
    }
}

// Fetch all users
$sql_users = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($sql_users);

?>
<?php include '../includes/header.php'; ?>

<div class="container section">

    <!-- Stats Overview -->
    <?php
    $stats_patients = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='patient'")->fetch_assoc()['c'];
    $stats_doctors = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='doctor'")->fetch_assoc()['c'];
    $stats_appts = $conn->query("SELECT COUNT(*) as c FROM appointments")->fetch_assoc()['c'];
    $stats_pending = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
    ?>
    <h2 style="margin-bottom: 30px;">Admin Dashboard</h2>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 40px;">
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(0, 184, 148, 0.1); padding: 16px; border-radius: 50%; color: var(--success-color);">
                <i class="fas fa-user-injured" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <a href="admin_patients.php" style="text-decoration: none; color: inherit;">
                    <h3 style="font-size: 2rem; margin-bottom: 5px;"><?php echo $stats_patients; ?></h3>
                    <span style="color: var(--text-muted);">Total Patients</span>
                </a>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(108, 92, 231, 0.1); padding: 16px; border-radius: 50%; color: var(--primary-color);">
                <i class="fas fa-user-md" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <a href="admin_doctors.php" style="text-decoration: none; color: inherit;">
                    <h3 style="font-size: 2rem; margin-bottom: 5px;"><?php echo $stats_doctors; ?></h3>
                    <span style="color: var(--text-muted);">Manage Doctors</span>
                </a>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(253, 203, 110, 0.1); padding: 16px; border-radius: 50%; color: var(--warning-color);">
                <i class="fas fa-calendar-check" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <a href="admin_appointments.php" style="text-decoration: none; color: inherit;">
                    <h3 style="font-size: 2rem; margin-bottom: 5px;"><?php echo $stats_appts; ?></h3>
                    <span style="color: var(--text-muted);">Total Appointments</span>
                </a>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(255, 118, 117, 0.1); padding: 16px; border-radius: 50%; color: var(--danger-color);">
                <i class="fas fa-clock" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <a href="admin_appointments.php?status=pending" style="text-decoration: none; color: inherit;">
                    <h3 style="font-size: 2rem; margin-bottom: 5px;"><?php echo $stats_pending; ?></h3>
                    <span style="color: var(--text-muted);">Pending Actions</span>
                </a>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div style="background: rgba(100, 100, 100, 0.1); padding: 16px; border-radius: 50%; color: #333;">
                <i class="fas fa-star" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <a href="admin_reviews.php" style="text-decoration: none; color: inherit;">
                    <h3 style="font-size: 1.5rem; margin-bottom: 5px;">Manage Reviews</h3>
                    <span style="color: var(--text-muted);">View & Moderate</span>
                </a>
            </div>
        </div>


    </div>

    <!-- Analytics Charts -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-bottom: 40px;">
        <div class="card">
            <h3 style="margin-bottom: 20px;">Appointments (Last 7 Days)</h3>
            <canvas id="appointmentsChart"></canvas>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 20px;">Revenue Overview</h3>
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('../api/analytics_endpoint.php')
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const data = result.data;

                        // Appointments Chart
                        new Chart(document.getElementById('appointmentsChart'), {
                            type: 'line',
                            data: {
                                labels: data.appointments.labels,
                                datasets: [{
                                    label: 'Appointments',
                                    data: data.appointments.data,
                                    borderColor: '#6c5ce7',
                                    tension: 0.4,
                                    fill: true,
                                    backgroundColor: 'rgba(108, 92, 231, 0.1)'
                                }]
                            },
                            options: { responsive: true }
                        });

                        // Revenue Chart
                        new Chart(document.getElementById('revenueChart'), {
                            type: 'doughnut',
                            data: {
                                labels: data.revenue.labels,
                                datasets: [{
                                    data: data.revenue.data,
                                    backgroundColor: ['#00b894', '#ff7675', '#fdcb6e']
                                }]
                            },
                            options: { responsive: true }
                        });
                    }
                });
        });
    </script>

    <?php if ($message != "")
        echo $message; ?>

    <!-- Add New Admin Section -->
    <div class="card" style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <i class="fas fa-user-shield" style="color: var(--primary-color);"></i> Add New Admin
        </h3>
        <form action="" method="POST"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="fullname">Full Name</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" name="fullname" class="form-control" required placeholder="Admin Name">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="email">Email</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" required placeholder="admin@example.com">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="password">Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" required placeholder="Password">
                </div>
            </div>
            <button type="submit" name="add_admin" class="btn btn-primary" style="height: 50px;">
                <i class="fas fa-plus-circle"></i> Create Admin
            </button>
        </form>
    </div>

    <!-- Users List -->
    <div class="card" style="margin-bottom: 40px; padding: 0; overflow: hidden;">
        <div style="padding: 25px; border-bottom: 1px solid #eee;">
            <h3 style="margin: 0;"><i class="fas fa-users" style="color: var(--secondary-color);"></i> User Management
            </h3>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users_result->num_rows > 0): ?>
                        <?php while ($row = $users_result->fetch_assoc()): ?>
                            <?php $badge_class = "badge-" . $row['role']; ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td style="font-weight: 600;"><?php echo $row['fullname']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                    <?php if ($row['is_approved'] == 0): ?>
                                        <span class="badge badge-warning" style="font-size: 0.7em; margin-left: 5px;">PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <div style="display: flex; gap: 10px;">
                                            <?php if ($row['is_approved'] == 0): ?>
                                                <a href="admin_dashboard.php?action=approve_user&id=<?php echo $row['id']; ?>"
                                                    class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.8rem;"
                                                    title="Approve User">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="admin_dashboard.php?action=delete_user&id=<?php echo $row['id']; ?>"
                                                onclick="return confirm('Are you sure?');" class="btn btn-danger"
                                                style="padding: 5px 10px; font-size: 0.8rem;" title="Delete User">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">(You)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- All Appointments Section -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 25px; border-bottom: 1px solid #eee;">
            <h3 style="margin: 0;"><i class="fas fa-calendar-alt" style="color: var(--secondary-color);"></i>
                Appointment History</h3>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_all_appts = "SELECT a.*, p.fullname AS patient_name, d.fullname AS doctor_name 
                                          FROM appointments a 
                                          JOIN users p ON a.patient_id = p.id 
                                          JOIN users d ON a.doctor_id = d.id 
                                          ORDER BY a.date DESC, a.time ASC";
                    $all_appts_result = $conn->query($sql_all_appts);

                    if ($all_appts_result->num_rows > 0) {
                        while ($appt = $all_appts_result->fetch_assoc()) {
                            $status = $appt['status'];
                            $badge_class = 'badge-pending';
                            if ($status == 'confirmed')
                                $badge_class = 'badge-success';
                            elseif ($status == 'rejected')
                                $badge_class = 'badge-danger';

                            echo "<tr>";
                            echo "<td>#" . $appt['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($appt['patient_name']) . "</td>";
                            echo "<td style='color: var(--primary-color);'><i class='fas fa-user-md'></i> " . htmlspecialchars($appt['doctor_name']) . "</td>";
                            echo "<td>" . $appt['date'] . " <span style='font-size:0.85em; color:var(--text-muted);'>" . date("h:i A", strtotime($appt['time'])) . "</span></td>";
                            echo "<td><span class='badge " . $badge_class . "'>" . ucfirst($status) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center; padding: 30px;'>No appointments found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>