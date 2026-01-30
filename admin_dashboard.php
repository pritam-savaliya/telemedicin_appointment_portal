<?php
include 'includes/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
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
        $message = "Invalid email format";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters";
    } else {
        // Hash Password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $checkEmail = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($checkEmail);

        if ($result->num_rows > 0) {
            $message = "Email already registered!";
        } else {
            // Insert Admin
            $sql = "INSERT INTO users (fullname, email, password, role) VALUES ('$fullname', '$email', '$hashed_password', '$role')";

            if ($conn->query($sql) === TRUE) {
                $message = "<span style='color:green'>New Admin added successfully!</span>";
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }
}

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);

    // Prevent deleting self
    if ($delete_id != $_SESSION['user_id']) {
        // First delete related appointments to avoid foreign key constraints
        $conn->query("DELETE FROM appointments WHERE patient_id = $delete_id OR doctor_id = $delete_id");

        // Delete user
        if ($conn->query("DELETE FROM users WHERE id = $delete_id") === TRUE) {
            header("Location: admin_dashboard.php?msg=deleted");
            exit();
        } else {
            $message = "Error deleting user: " . $conn->error;
        }
    } else {
        $message = "You cannot delete yourself!";
    }

}

// Handle Approve User
if (isset($_GET['action']) && $_GET['action'] == 'approve_user' && isset($_GET['id'])) {
    $approve_id = intval($_GET['id']);
    if ($conn->query("UPDATE users SET is_approved = 1 WHERE id = $approve_id") === TRUE) {
        header("Location: admin_dashboard.php?msg=approved");
        exit();
    } else {
        $message = "Error approving user: " . $conn->error;
    }
}

// Check for delete success message
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $message = "<span style='color:green'>User deleted successfully!</span>";
    } elseif ($_GET['msg'] == 'approved') {
        $message = "<span style='color:green'>User approved successfully!</span>";
    }
}

// Fetch all users
$sql_users = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($sql_users);

?>
<?php include 'includes/header.php'; ?>

<!-- Stats Overview -->
<?php
$stats_patients = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='patient'")->fetch_assoc()['c'];
$stats_doctors = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='doctor'")->fetch_assoc()['c'];
$stats_appts = $conn->query("SELECT COUNT(*) as c FROM appointments")->fetch_assoc()['c'];
$stats_pending = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
?>
<div
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div
        style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
        <div
            style="background: rgba(0, 184, 148, 0.1); padding: 15px; border-radius: 50%; color: var(--success-color);">
            <i class="fas fa-user-injured" style="font-size: 1.5rem;"></i>
        </div>
        <div>
            <h3 style="margin: 0; font-size: 1.8rem;"><?php echo $stats_patients; ?></h3>
            <span style="color: var(--text-muted); font-size: 0.9rem;">Total Patients</span>
        </div>
    </div>
    <div
        style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
        <div
            style="background: rgba(15, 76, 117, 0.1); padding: 15px; border-radius: 50%; color: var(--primary-color);">
            <i class="fas fa-user-md" style="font-size: 1.5rem;"></i>
        </div>
        <div>
            <h3 style="margin: 0; font-size: 1.8rem;"><?php echo $stats_doctors; ?></h3>
            <span style="color: var(--text-muted); font-size: 0.9rem;">Total Doctors</span>
        </div>
    </div>
    <div
        style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
        <div
            style="background: rgba(253, 203, 110, 0.1); padding: 15px; border-radius: 50%; color: var(--warning-color);">
            <i class="fas fa-calendar-check" style="font-size: 1.5rem;"></i>
        </div>
        <div>
            <h3 style="margin: 0; font-size: 1.8rem;"><?php echo $stats_appts; ?></h3>
            <span style="color: var(--text-muted); font-size: 0.9rem;">Total Appointments</span>
        </div>
    </div>
    <div
        style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(214, 48, 49, 0.1); padding: 15px; border-radius: 50%; color: var(--danger-color);">
            <i class="fas fa-clock" style="font-size: 1.5rem;"></i>
        </div>
        <div>
            <h3 style="margin: 0; font-size: 1.8rem;"><?php echo $stats_pending; ?></h3>
            <span style="color: var(--text-muted); font-size: 0.9rem;">Pending Actions</span>
        </div>
    </div>
</div>

<!-- Add New Admin Section -->
<div class="card"
    style="background: white; border-radius: 12px; margin-bottom: 40px; padding: 30px; box-shadow: var(--shadow-md);">
    <h3 style="margin-bottom: 20px; color: var(--dark-bg); border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
        <i class="fas fa-user-shield" style="margin-right: 10px; color: var(--primary-color);"></i> Add New Admin
    </h3>
    <?php if ($message != ""): ?>
        <div
            style="background: <?php echo strpos($message, 'green') !== false ? '#ddffdd' : '#ffdddd'; ?>; color: <?php echo strpos($message, 'green') !== false ? '#4CAF50' : '#d8000c'; ?>; padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label for="fullname" style="font-weight: 600;">Full Name</label>
            <div class="input-with-icon">
                <i class="fas fa-user"></i>
                <input type="text" name="fullname" class="form-control" required placeholder="Admin Name"
                    style="background: #f8f9fa;">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label for="email" style="font-weight: 600;">Email</label>
            <div class="input-with-icon">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" class="form-control" required placeholder="admin@example.com"
                    style="background: #f8f9fa;">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label for="password" style="font-weight: 600;">Password</label>
            <div class="input-with-icon">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="form-control" required placeholder="Password"
                    style="background: #f8f9fa;">
            </div>
        </div>
        <button type="submit" name="add_admin" class="btn btn-primary" style="height: 48px; font-weight: 600;">
            <i class="fas fa-plus-circle"></i> Create Admin
        </button>
    </form>
</div>

<!-- Users List -->
<div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow-sm); margin-bottom: 40px;">
    <h3 style="margin-bottom: 20px; color: var(--dark-bg); display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-users" style="color: var(--secondary-color);"></i> User Management
    </h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr style="background-color: #f8f9fa; text-align: left;">
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">ID</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">Name</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">Email</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">Role</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">Joined</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($users_result->num_rows > 0) {
                    while ($row = $users_result->fetch_assoc()) {
                        $role_badge_bg = '#eee';
                        $role_badge_color = '#333';
                        if ($row['role'] == 'admin') {
                            $role_badge_bg = '#fab1a0';
                            $role_badge_color = '#d63031';
                        } elseif ($row['role'] == 'doctor') {
                            $role_badge_bg = '#74b9ff';
                            $role_badge_color = '#0984e3';
                        } elseif ($row['role'] == 'patient') {
                            $role_badge_bg = '#55efc4';
                            $role_badge_color = '#00b894';
                        }

                        echo "<tr style='transition: background 0.2s;' onmouseover='this.style.background=\"#f9f9f9\"' onmouseout='this.style.background=\"transparent\"'>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee; font-weight: bold; color: #aaa;'>#" . $row['id'] . "</td>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee;'>" . $row['fullname'] . "</td>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee;'>" . $row['email'] . "</td>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee;'><span style='background: $role_badge_bg; color: $role_badge_color; padding: 5px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;'>" . $row['role'] . "</span>";
                        if ($row['is_approved'] == 0) {
                            echo " <span style='background: #ffeaa7; color: #d63031; padding: 2px 6px; border-radius: 8px; font-size: 0.7rem; font-weight: bold;'>PENDING</span>";
                        }
                        echo "</td>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee; color: var(--text-muted); font-size: 0.9rem;'>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee;'>";
                        if ($row['id'] != $_SESSION['user_id']) {
                            if ($row['is_approved'] == 0) {
                                echo "<a href='admin_dashboard.php?action=approve_user&id=" . $row['id'] . "' style='color: var(--success-color); text-decoration: none; padding: 5px; transition: color 0.2s;' title='Approve User'><i class='fas fa-check-circle'></i></a> ";
                            }
                            echo "<a href='admin_dashboard.php?action=delete_user&id=" . $row['id'] . "' onclick='return confirm(\"Are you sure?\");' style='color: #ff7675; text-decoration: none; padding: 5px; transition: color 0.2s;' title='Delete User'><i class='fas fa-trash-alt'></i></a>";
                        } else {
                            echo "<span style='color: #bbb; font-size: 0.8rem;'>Me</span>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='padding: 20px; text-align: center; color: var(--text-muted);'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- All Appointments Section -->
<div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow-sm);">
    <h3 style="margin-bottom: 20px; color: var(--dark-bg); display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-calendar-alt" style="color: var(--secondary-color);"></i> Appointment History
    </h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr style="background-color: #f8f9fa; text-align: left;">
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">ID</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">Patient</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">Doctor</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">Date & Time</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee; color: var(--text-muted);">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all appointments
                $sql_all_appts = "SELECT a.*, p.fullname AS patient_name, d.fullname AS doctor_name 
                                      FROM appointments a 
                                      JOIN users p ON a.patient_id = p.id 
                                      JOIN users d ON a.doctor_id = d.id 
                                      ORDER BY a.date DESC, a.time ASC";
                $all_appts_result = $conn->query($sql_all_appts);

                if ($all_appts_result->num_rows > 0) {
                    while ($appt = $all_appts_result->fetch_assoc()) {
                        $status = $appt['status'];
                        $status_bg = '#ffeaa7';
                        $status_color = '#d63031'; // default orange-ish
                        if ($status == 'confirmed') {
                            $status_bg = '#55efc4';
                            $status_color = '#00b894';
                        } elseif ($status == 'rejected') {
                            $status_bg = '#ff7675';
                            $status_color = '#d63031';
                        } elseif ($status == 'pending') {
                            $status_bg = '#ffeaa7';
                            $status_color = '#fdcb6e';
                        }

                        echo "<tr style='transition: background 0.2s;' onmouseover='this.style.background=\"#f9f9f9\"' onmouseout='this.style.background=\"transparent\"'>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee; font-weight: bold; color: #aaa;'>#" . $appt['id'] . "</td>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee; font-weight: 500;'>" . $appt['patient_name'] . "</td>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee; color: var(--primary-color);'><i class='fas fa-user-md'></i> " . $appt['doctor_name'] . "</td>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee; color: var(--text-muted);'>" . $appt['date'] . " <span style='font-size:0.85em; color:#aaa;'>" . date("h:i A", strtotime($appt['time'])) . "</span></td>";
                        echo "<td style='padding: 15px; border-bottom: 1px solid #eee;'><span style='background: $status_bg; color: $status_color; padding: 5px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;'>" . $status . "</span></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='padding: 20px; text-align: center; color: var(--text-muted);'>No appointments found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>