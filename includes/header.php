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
            <div class="container navbar">
                <div class="logo">
                    <a href="<?php echo $home_url; ?>">
                        <i class="fas fa-heartbeat"></i> TeleMed
                    </a>
                </div>
                <nav class="nav-links">
                    <a href="<?php echo $home_url; ?>"
                        class="<?php echo $current_page == 'home.php' || $current_page == basename($home_url) ? 'active' : ''; ?>">
                        Home
                        <span id="unread-badge" class="badge badge-danger"
                            style="font-size: 0.6rem; vertical-align: top; margin-left: 5px; display: none;">0</span>
                    </a>
                    <a href="about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">About Us</a>
                    <a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a>
                </nav>
                <div class="auth-buttons" style="display: flex; gap: 15px; align-items: center;">
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
                        <a href="profile.php" class="btn btn-secondary" style="padding: 0.6rem 1.2rem;">
                            <i class="fas fa-user-circle"></i> Profile
                        </a>
                        <a href="logout.php" class="btn btn-primary" style="padding: 0.6rem 1.2rem;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary">Login</a>
                        <a href="register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
    <?php endif; ?>
    <div id="toast-notification" class="toast-notification"></div>