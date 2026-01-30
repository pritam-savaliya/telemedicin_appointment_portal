<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/db.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Dynamic Home URL Logic
$home_url = 'home.php';
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'patient') {
        $home_url = 'patient_dashboard.php';
    } elseif ($_SESSION['role'] == 'doctor') {
        $home_url = 'doctor_dashboard.php';
    } elseif ($_SESSION['role'] == 'admin') {
        $home_url = 'admin_dashboard.php';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeleMed - Premium Healthcare</title>
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <?php if (!isset($hide_header) || !$hide_header): ?>
        <header>
            <div class="navbar">
                <div class="logo">
                    <a href="<?php echo $home_url; ?>">
                        <i class="fas fa-heartbeat" style="color: var(--success-color);"></i> TeleMed
                    </a>
                </div>
                <nav class="nav-links">
                    <?php
                    // Initial load count (PHP Fallback)
                    $unread_count = 0;
                    if (isset($_SESSION['user_id'])) {
                        $u_id = $_SESSION['user_id'];
                        $count_sql = "SELECT COUNT(*) as count 
                                  FROM chat_messages 
                                  JOIN appointments ON chat_messages.appointment_id = appointments.id 
                                  WHERE chat_messages.is_read = 0 
                                  AND chat_messages.sender_id != $u_id 
                                  AND (appointments.patient_id = $u_id OR appointments.doctor_id = $u_id)";
                        $c_res = $conn->query($count_sql);
                        if ($c_res) {
                            $unread_count = $c_res->fetch_assoc()['count'];
                        }
                    }
                    ?>
                    <a href="<?php echo $home_url; ?>"
                        class="<?php echo $current_page == 'home.php' || $current_page == basename($home_url) ? 'active' : ''; ?>">
                        Home
                        <span id="unread-badge"
                            style="background: var(--danger-color); color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 50%; vertical-align: top; margin-left: 2px; display: <?php echo $unread_count > 0 ? 'inline-block' : 'none'; ?>;">
                            <?php echo $unread_count; ?>
                        </span>
                    </a>
                    <a href="about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">About Us</a>
                    <?php
                    // Initial load count (PHP Fallback)
                    $unread_count = 0;
                    if (isset($_SESSION['user_id'])) {
                        $u_id = $_SESSION['user_id'];
                        $count_sql = "SELECT COUNT(*) as count 
                                  FROM chat_messages 
                                  JOIN appointments ON chat_messages.appointment_id = appointments.id 
                                  WHERE chat_messages.is_read = 0 
                                  AND chat_messages.sender_id != $u_id 
                                  AND (appointments.patient_id = $u_id OR appointments.doctor_id = $u_id)";
                        $c_res = $conn->query($count_sql);
                        if ($c_res) {
                            $unread_count = $c_res->fetch_assoc()['count'];
                        }
                    }
                    ?>


                    <a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a>
                </nav>
                <div class="auth-buttons" style="display: flex; gap: 10px;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <script>
                            // Real-time notification polling
                            setInterval(function () {
                                fetch('chat_endpoint.php?action=get_unread_count')
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            const badge = document.getElementById('unread-badge');
                                            if (data.count > 0) {
                                                badge.style.display = 'inline-block';
                                                badge.innerText = data.count;
                                            } else {
                                                badge.style.display = 'none';
                                            }
                                        }
                                    })
                                    .catch(err => console.error('Error fetching notifications:', err));
                            }, 3000); // Check every 3 seconds
                        </script>
                        <a href="profile.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                            <i class="fas fa-user-circle"></i> <?php echo explode(' ', $_SESSION['fullname'])[0]; ?>
                        </a>
                        <a href="logout.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Login</a>
                        <a href="register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
    <?php endif; ?>
    <div id="toast-notification" class="toast-notification"></div>