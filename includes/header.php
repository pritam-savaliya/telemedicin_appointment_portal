<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/db.php';
$current_page = basename($_SERVER['PHP_SELF']);

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
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect | Premium Telemedicine</title>
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>

<body>

    <?php if (!isset($hide_header) || !$hide_header): ?>
        <header>
            <div class="container nav-wrapper">
                <div class="logo">
                    <a href="<?php echo $home_url; ?>" style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-heart-pulse"></i>
                        <span>MedConnect</span>
                    </a>
                </div>
                
                <nav class="nav-links">
                    <a href="<?php echo $home_url; ?>" class="<?php echo $current_page == 'index.php' || $current_page == basename($home_url) ? 'active' : ''; ?>">
                        Home
                    </a>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'doctor'): ?>
                        <a href="<?php echo BASE_URL; ?>doctor/manage_schedule.php" class="<?php echo $current_page == 'manage_schedule.php' ? 'active' : ''; ?>">Schedule</a>
                    <?php endif; ?>

                    <a href="<?php echo BASE_URL; ?>about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">Our Story</a>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/support_messages.php" class="<?php echo $current_page == 'support_messages.php' ? 'active' : ''; ?>">Support</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a>
                    <?php endif; ?>
                </nav>

                <div class="auth-buttons" style="display: flex; gap: 15px; align-items: center;">
                    <div id="theme-toggle" style="cursor: pointer; width: 40px; height: 40px; border-radius: 12px; background: var(--bg-glass); border: 1px solid var(--border-glass); display: flex; align-items: center; justify-content: center; color: var(--text-main); transition: var(--transition);">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>

                        <div class="notification-wrapper" style="position: relative;">
                            <div id="notif-bell" style="cursor: pointer; position: relative; color: var(--text-muted); font-size: 1.3rem; padding: 5px;">
                                <i class="fa-regular fa-bell"></i>
                                <span id="notif-badge" style="position: absolute; top: 0; right: 0; background: var(--accent); color: white; font-size: 0.65rem; padding: 2px 5px; border-radius: 50%; display: none; font-weight: 800; border: 2px solid var(--bg-dark);">0</span>
                            </div>

                            <div id="notif-dropdown" class="glass-card" style="display: none; position: absolute; top: 45px; right: 0; width: 340px; padding: 0.5rem; z-index: 1001; overflow: hidden;">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-glass);">
                                    <h5 style="margin: 0; font-size: 1rem;">Notifications</h5>
                                    <span onclick="markAllRead()" style="font-size: 0.75rem; color: var(--primary); cursor: pointer; font-weight: 700;">Clear All</span>
                                </div>
                                <div id="notif-list" style="max-height: 400px; overflow-y: auto;">
                                </div>
                            </div>
                        </div>

                        <a href="<?php echo BASE_URL; ?>profile.php" class="btn btn-secondary" style="padding: 0.5rem 1.2rem; border-radius: 50px;">
                            <?php
                            $nav_profile_pic = isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) && $_SESSION['profile_pic'] != 'default_user.png' ? $_SESSION['profile_pic'] : null;
                            if ($nav_profile_pic) {
                                echo '<img src="' . BASE_URL . 'assets/uploads/profile_pics/' . $nav_profile_pic . '" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;" onerror="this.src=\'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname']) . '&background=random&color=fff&size=50\'">';
                            } else {
                                echo '<img src="https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname']) . '&background=random&color=fff&size=50" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">';
                            }
                            ?>
                            <span style="font-size: 0.85rem;"><?php echo explode(' ', $_SESSION['fullname'])[0]; ?></span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>auth/logout.php" class="btn btn-ghost" title="Logout">
                            <i class="fas fa-arrow-right-from-bracket"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-ghost">Login</a>
                        <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-primary">Join Now</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
    <?php endif; ?>
    
    <div id="toast-notification" class="toast-notification"></div>

    <script>
        const apiBase = "<?php echo BASE_URL; ?>api/";
        const bell = document.getElementById('notif-bell');
        const dropdown = document.getElementById('notif-dropdown');
        const badge = document.getElementById('notif-badge');
        const list = document.getElementById('notif-list');

        if(bell) {
            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            });

            document.addEventListener('click', (e) => {
                if (dropdown && !dropdown.contains(e.target) && !bell.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            function checkNotifications() {
                fetch(apiBase + 'get_notifications.php?action=fetch')
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (data.count > 0) {
                                badge.style.display = 'block';
                                badge.innerText = data.count;
                            } else {
                                badge.style.display = 'none';
                            }

                            if (data.notifications.length > 0) {
                                let html = '';
                                data.notifications.forEach(n => {
                                    const opacity = n.is_read == 0 ? '1' : '0.6';
                                    html += `
                                        <div onclick="markRead(${n.id})" style="padding: 1.2rem; border-bottom: 1px solid var(--border-glass); cursor: pointer; opacity: ${opacity}; transition: 0.2s; hover: background: var(--bg-glass)">
                                            <div style="font-size: 0.9rem; margin-bottom: 4px; color: var(--text-main)">${n.message}</div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;"><i class="far fa-clock"></i> ${n.time_ago}</div>
                                        </div>
                                    `;
                                });
                                list.innerHTML = html;
                            } else {
                                list.innerHTML = '<div style="padding: 2rem; text-align: center; color: var(--text-muted); font-size: 0.9rem;">No new notifications</div>';
                            }
                        }
                    });
            }

            function markRead(id) {
                const formData = new FormData();
                formData.append('id', id);
                fetch(apiBase + 'get_notifications.php?action=mark_read', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => checkNotifications());
            }

            function markAllRead() {
                fetch(apiBase + 'get_notifications.php?action=mark_all_read')
                    .then(res => res.json())
                    .then(data => {
                        checkNotifications();
                        dropdown.style.display = 'none';
                    });
            }

            setInterval(checkNotifications, 10000);
            checkNotifications();
        }

        // Theme Management Engine
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const html = document.documentElement;

        function updateThemeUI(theme) {
            if (theme === 'light') {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            }
        }

        // Sync UI on load
        updateThemeUI(html.getAttribute('data-theme'));

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeUI(newTheme);
        });
    </script>