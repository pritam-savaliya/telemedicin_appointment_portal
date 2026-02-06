<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/db.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Dynamic Home URL Logic
$home_url = BASE_URL . 'index.php';
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'patient') {
        $home_url = BASE_URL . 'patient/patient_dashboard.php';
    } elseif ($_SESSION['role'] == 'doctor') {
        $home_url = BASE_URL . 'doctor/doctor_dashboard.php';
    } elseif ($_SESSION['role'] == 'admin') {
        $home_url = BASE_URL . 'admin/admin_dashboard.php';
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Inline script to prevent FOUC (Flash of Unstyled Content) -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme-preference') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
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
                        class="<?php echo $current_page == 'index.php' || $current_page == basename($home_url) ? 'active' : ''; ?>">
                        <i class="fas fa-home" style="margin-right: 5px;"></i> Home
                        <span id="unread-badge" class="badge badge-danger"
                            style="font-size: 0.6rem; vertical-align: top; margin-left: 5px; display: none;">0</span>
                    </a>

                    <!-- Doctor Schedule Link -->
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'doctor'): ?>
                        <a href="<?php echo BASE_URL; ?>doctor/manage_schedule.php"
                            class="<?php echo $current_page == 'manage_schedule.php' ? 'active' : ''; ?>">My Schedule</a>
                    <?php endif; ?>

                    <a href="<?php echo BASE_URL; ?>about.php"
                        class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">About Us</a>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/support_messages.php"
                            class="<?php echo $current_page == 'support_messages.php' ? 'active' : ''; ?>">Support Messages</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>contact.php"
                            class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a>
                    <?php endif; ?>
                </nav>
                <div class="auth-buttons" style="display: flex; gap: 20px; align-items: center;">
                    <!-- Manual Theme Switcher -->
                    <button class="theme-toggle-btn" id="theme-switch" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon"></i>
                    </button>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Notification Bell -->
                        <div class="notification-wrapper" style="position: relative;">
                            <div id="notif-bell"
                                style="cursor: pointer; position: relative; margin-right: 15px; color: var(--text-muted); font-size: 1.2rem;">
                                <i class="fas fa-bell"></i>
                                <span id="notif-badge"
                                    style="position: absolute; top: -8px; right: -8px; background: var(--danger-color); color: white; font-size: 0.65rem; padding: 2px 5px; border-radius: 50%; display: none;">0</span>
                            </div>

                            <!-- Dropdown -->
                            <div id="notif-dropdown"
                                style="display: none; position: absolute; top: 35px; right: 0; width: 320px; background: white; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.15); z-index: 1001; border: 1px solid #f0f0f0;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-bottom: 1px solid #eee;">
                                    <h5 style="margin: 0; color: var(--secondary-color);">Notifications</h5>
                                    <span onclick="markAllRead()"
                                        style="font-size: 0.75rem; color: var(--primary-color); cursor: pointer;">Mark all
                                        read</span>
                                </div>
                                <div id="notif-list" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Items loads here -->
                                </div>
                            </div>
                        </div>

                        <script>
                            // Notification Logic
                            // Use absolute path for API calls
                            const apiBase = "<?php echo BASE_URL; ?>api/";

                            const bell = document.getElementById('notif-bell');
                            const dropdown = document.getElementById('notif-dropdown');
                            const badge = document.getElementById('notif-badge');
                            const list = document.getElementById('notif-list');

                            // Toggle Dropdown
                            bell.addEventListener('click', (e) => {
                                e.stopPropagation();
                                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                            });

                            // Close when clicking outside
                            document.addEventListener('click', (e) => {
                                if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
                                    dropdown.style.display = 'none';
                                }
                            });

                            // Poll for Notifications
                            function checkNotifications() {
                                fetch(apiBase + 'get_notifications.php?action=fetch')
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            // Update Badge
                                            if (data.count > 0) {
                                                badge.style.display = 'block';
                                                badge.innerText = data.count;
                                            } else {
                                                badge.style.display = 'none';
                                            }

                                            // Update List
                                            if (data.notifications.length > 0) {
                                                let html = '';
                                                data.notifications.forEach(n => {
                                                    const bg = n.is_read == 0 ? '#f0f2f5' : 'white';
                                                    html += `
                                                        <div onclick="markRead(${n.id})" style="padding: 12px 15px; border-bottom: 1px solid #f9f9f9; background: ${bg}; cursor: pointer; transition: background 0.2s;">
                                                            <div style="font-size: 0.9rem; color: var(--secondary-color); margin-bottom: 4px;">${n.message}</div>
                                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><i class="far fa-clock"></i> ${n.time_ago}</div>
                                                        </div>
                                                    `;
                                                });
                                                list.innerHTML = html;
                                            } else {
                                                list.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--text-muted);">No notifications</div>';
                                            }
                                        }
                                    });
                            }

                            function markRead(id) {
                                const formData = new FormData();
                                formData.append('id', id);
                                fetch(apiBase + 'get_notifications.php?action=mark_read', { method: 'POST', body: formData })
                                    .then(res => res.json())
                                    .then(data => {
                                        checkNotifications(); // Refresh
                                    });
                            }

                            function markAllRead() {
                                fetch(apiBase + 'get_notifications.php?action=mark_all_read')
                                    .then(res => res.json())
                                    .then(data => {
                                        checkNotifications(); // Refresh
                                        dropdown.style.display = 'none';
                                    });
                            }

                            setInterval(checkNotifications, 5000); // Poll every 5s
                            checkNotifications(); // Initial call
                        </script>

                        <a href="<?php echo BASE_URL; ?>profile.php" class="btn btn-secondary">
                            <?php
                            $nav_profile_pic = isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : 'default_user.png';
                            if ($nav_profile_pic != 'default_user.png') {
                                echo '<img src="' . BASE_URL . 'assets/uploads/profile_pics/' . $nav_profile_pic . '" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">';
                            } else {
                                echo '<i class="fas fa-user-circle" style="font-size: 1.2rem;"></i>';
                            }
                            ?>
                            <span>Profile</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>auth/logout.php" class="btn btn-primary">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-secondary">Login</a>
                        <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
    <?php endif; ?>
    <div id="toast-notification" class="toast-notification"></div>