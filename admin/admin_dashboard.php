<?php
include '../includes/db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = "";

// PHP Logic for Add/Delete/Approve remains the same for functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    if ($conn->query("INSERT INTO users (fullname, email, password, role, is_approved) VALUES ('$fullname', '$email', '$password', 'admin', 1)")) {
        $message = "<div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'>Admin created successfully.</div>";
    }
}

include '../includes/header.php'; 
?>

<div class="dashboard-layout fade-in">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
            <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem;"><i class="fas fa-shield-halved"></i></div>
            <h4>Admin Terminal</h4>
            <span class="badge badge-danger" style="font-size: 0.6rem; margin-top: 0.5rem; background: rgba(244,63,94,0.1); color: var(--accent);">System Administrator</span>
        </div>

        <a href="admin_dashboard.php" class="sidebar-item active"><i class="fas fa-gauge-high"></i> Control Center</a>
        <a href="admin_patients.php" class="sidebar-item"><i class="fas fa-id-card"></i> Patient Registry</a>
        <a href="admin_doctors.php" class="sidebar-item"><i class="fas fa-stethoscope"></i> Doctor Network</a>
        <a href="admin_appointments.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> Global Traffic</a>
        <a href="admin_reviews.php" class="sidebar-item"><i class="fas fa-star-half-stroke"></i> Quality Control</a>
        
        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="system_settings.php" class="sidebar-item"><i class="fas fa-gears"></i> Platform Settings</a>
            <a href="support_messages.php" class="sidebar-item"><i class="fas fa-headset"></i> Support Hub</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
         <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Platform Pulse</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Real-time overview of platform health and user activity.</p>
            </div>
            <div style="text-align: right;">
                 <span class="badge badge-success"><i class="fas fa-check-circle"></i> System Shield Active</span>
            </div>
        </div>

        <?php
        $stats_patients = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='patient'")->fetch_assoc()['c'];
        $stats_doctors = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='doctor'")->fetch_assoc()['c'];
        $stats_appts = $conn->query("SELECT COUNT(*) as c FROM appointments")->fetch_assoc()['c'];
        $stats_active_calls = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE is_call_active = 1")->fetch_assoc()['c'] ?? 0;
        ?>

        <!-- Performance Matrix -->
        <div class="stats-grid">
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                    <i class="fas fa-user-injured"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $stats_patients; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Total Patients</span>
                </div>
            </div>
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                    <i class="fas fa-user-md"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $stats_doctors; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Verified Doctors</span>
                </div>
            </div>
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(6, 182, 212, 0.1); color: var(--secondary);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $stats_appts; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Appointments</span>
                </div>
            </div>
            <div class="glass-card stat-card">
                <div class="stat-icon" style="background: rgba(244, 63, 94, 0.1); color: var(--accent);">
                    <i class="fas fa-wave-square"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo $stats_active_calls; ?></h2>
                    <span style="color: var(--text-dim); font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Live Traffic</span>
                </div>
            </div>
        </div>

        <!-- Analytics Visualization -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 3rem;">
            <div class="glass-card">
                <h3 style="margin-bottom: 2rem;">Traffic Forecasting</h3>
                <canvas id="appointmentsChart" height="250"></canvas>
            </div>
            <div class="glass-card">
                <h3 style="margin-bottom: 2rem;">User Distribution</h3>
                <canvas id="revenueChart" height="250"></canvas>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                fetch('../api/analytics_endpoint.php')
                    .then(res => res.json())
                    .then(result => {
                        if (result.status === 'success') {
                            const data = result.data;
                            new Chart(document.getElementById('appointmentsChart'), {
                                type: 'line',
                                data: {
                                    labels: data.appointments.labels,
                                    datasets: [{
                                        label: 'Traffic Volume',
                                        data: data.appointments.data,
                                        borderColor: '#6366f1',
                                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                        borderWidth: 3,
                                        tension: 0.4,
                                        fill: true,
                                        pointBackgroundColor: '#6366f1',
                                        pointRadius: 4
                                    }]
                                },
                                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } } }
                            });

                            new Chart(document.getElementById('revenueChart'), {
                                type: 'doughnut',
                                data: {
                                    labels: data.revenue.labels,
                                    datasets: [{
                                        data: data.revenue.data,
                                        backgroundColor: ['#10b981', '#6366f1', '#f59e0b'],
                                        borderWidth: 0,
                                        hoverOffset: 15
                                    }]
                                },
                                options: { responsive: true, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.7)', padding: 20 } } } }
                            });
                        }
                    });
            });
        </script>

        <!-- Management Tables -->
        <div style="display: grid; gap: 3rem;">
            <!-- Register New Admin -->
            <div class="glass-card">
                <h3 style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-glass); padding-bottom: 1rem;">
                    <i class="fas fa-plus-circle" style="color: var(--primary);"></i> Authority Escalation
                </h3>
                <form action="" method="POST" style="display: grid; grid-template-columns: repeat(3, 1fr) auto; gap: 1.5rem; align-items: flex-end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Admin Full Name</label>
                        <input type="text" name="fullname" class="form-control" placeholder="Superuser Name" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Secure Email</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@medconnect.sys" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Access Key</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" name="add_admin" class="btn btn-primary" style="padding: 1rem 2rem;">Authorize</button>
                </form>
            </div>

            <!-- User Directory -->
            <div class="glass-card" style="padding: 0;">
                <div style="padding: 2rem; border-bottom: 1px solid var(--border-glass);">
                    <h3>Global User Registry</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; background: rgba(255,255,255,0.02);">
                                <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Identity</th>
                                <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Permission Level</th>
                                <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Status</th>
                                <th style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $users_res = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
                            while ($row = $users_res->fetch_assoc()):
                                $role_badge = 'badge-info';
                                if($row['role'] == 'admin') $role_badge = 'badge-danger';
                                if($row['role'] == 'doctor') $role_badge = 'badge-success';
                            ?>
                                <tr style="border-bottom: 1px solid var(--border-glass);">
                                    <td style="padding: 1.5rem 2rem;">
                                        <div style="font-weight: 700; color: var(--text-main);"><?php echo $row['fullname']; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-dim);"><?php echo $row['email']; ?></div>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <span class="badge <?php echo $role_badge; ?>"><?php echo strtoupper($row['role']); ?></span>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <?php if($row['is_approved']): ?>
                                            <span style="color: var(--success); font-size: 0.85rem; font-weight: 600;"><i class="fas fa-check-double"></i> Verified</span>
                                        <?php else: ?>
                                            <span style="color: var(--warning); font-size: 0.85rem; font-weight: 600;"><i class="fas fa-clock"></i> Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <?php if($row['id'] != $_SESSION['user_id']): ?>
                                            <a href="admin_dashboard.php?action=delete_user&id=<?php echo $row['id']; ?>" onclick="return confirm('Terminate this identity?')" style="color: var(--accent);"><i class="fas fa-trash-can"></i></a>
                                        <?php else: ?>
                                            <span style="opacity: 0.5; font-size: 0.8rem;">Master Session</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>