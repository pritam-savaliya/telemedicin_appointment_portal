<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

include '../includes/header.php';
$doctor_id = $_SESSION['user_id'];

// Days of week array
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Fetch existing schedule
$existing_schedule = [];
$sql = "SELECT * FROM doctor_schedules WHERE doctor_id = $doctor_id";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $existing_schedule[$row['day_of_week']] = $row;
    }
}
?>

<div class="dashboard-layout fade-in">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
            <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                <?php 
                $doc_sidebar_pic = (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) && $_SESSION['profile_pic'] != 'default_user.png') ? BASE_URL . 'assets/uploads/profile_pics/' . $_SESSION['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname']) . '&background=random&color=fff&size=200';
                ?>
                <img src="<?php echo $doc_sidebar_pic; ?>" 
                     style="width: 70px; height: 70px; border-radius: 20px; border: 2px solid var(--primary); object-fit: cover; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['fullname']); ?>&background=random&color=fff&size=200'">
                <div style="position: absolute; bottom: -2px; right: -2px; width: 16px; height: 16px; background: var(--success); border-radius: 50%; border: 3px solid var(--bg-sidebar);"></div>
            </div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.2rem;">Dr. <?php echo str_replace('Dr. ', '', $_SESSION['fullname']); ?></h4>
            <span class="badge badge-success" style="font-size: 0.6rem; opacity: 0.8; background: rgba(16,185,129,0.1); color: var(--success);">Verified Clinician</span>
        </div>

        <a href="doctor_dashboard.php" class="sidebar-item"><i class="fas fa-chart-line"></i> Command Center</a>
        <a href="manage_schedule.php" class="sidebar-item active"><i class="fas fa-calendar-check"></i> My Schedule</a>
        <a href="view_patient_records.php" class="sidebar-item"><i class="fas fa-user-injured"></i> Patient Ledger</a>
        <a href="reports.php" class="sidebar-item"><i class="fas fa-file-contract"></i> Clinical Analytics</a>
        
        <?php 
        $has_active_consults = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctor_id AND status = 'confirmed'")->fetch_assoc()['c'] > 0;
        if($has_active_consults): ?>
        <a href="../messages.php" class="sidebar-item"><i class="fas fa-comments"></i> Consultations</a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
            <a href="../profile.php" class="sidebar-item"><i class="fas fa-user-doctor"></i> My Profile</a>
            <a href="../auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Clinical Availability</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Set your encrypted weekly session windows and active duty hours.</p>
            </div>
            <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 700; font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="fas fa-clock"></i> Live Status: Duty Active
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="badge badge-success" style="width: 100%; border-radius: 16px; margin-bottom: 2rem; padding: 1.2rem; font-size: 1rem;">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="glass-card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-glass);">
            <form action="update_schedule.php" method="POST">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--border-glass);">
                                <th style="padding: 1.5rem 2rem; text-align: left; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Clinical Day</th>
                                <th style="padding: 1.5rem 2rem; text-align: center; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Node Status</th>
                                <th style="padding: 1.5rem 2rem; text-align: left; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Service Window (Start — End)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($days as $day):
                                $data = isset($existing_schedule[$day]) ? $existing_schedule[$day] : null;
                                $is_active = $data && $data['is_available'];
                                $start = $data ? $data['start_time'] : '09:00';
                                $end = $data ? $data['end_time'] : '17:00';
                                ?>
                                <tr style="border-bottom: 1px solid var(--border-glass); transition: 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1.5rem 2rem; font-weight: 700; color: var(--text-main);">
                                        <?php echo $day; ?>
                                    </td>
                                    <td style="padding: 1.5rem 2rem; text-align: center;">
                                        <label class="switch">
                                            <input type="checkbox" name="days[<?php echo $day; ?>][active]" value="1"
                                                onchange="toggleRow(this)" <?php echo $is_active ? 'checked' : ''; ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td style="padding: 1.5rem 2rem;">
                                        <div class="time-inputs <?php echo $is_active ? '' : 'disabled-row'; ?>"
                                            id="times-<?php echo $day; ?>"
                                            style="display: flex; align-items: center; gap: 20px; transition: 0.3s;">
                                            <input type="time" name="days[<?php echo $day; ?>][start]" value="<?php echo $start; ?>" 
                                                   class="form-control" style="max-width: 150px; background: rgba(255,255,255,0.02); border-color: var(--border-glass); font-weight: 600;">
                                            <span style="color: var(--text-dim); font-weight: 900; opacity: 0.3;">&mdash;</span>
                                            <input type="time" name="days[<?php echo $day; ?>][end]" value="<?php echo $end; ?>" 
                                                   class="form-control" style="max-width: 150px; background: rgba(255,255,255,0.02); border-color: var(--border-glass); font-weight: 600;">
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="padding: 2rem; background: rgba(255,255,255,0.02); text-align: right; border-top: 1px solid var(--border-glass);">
                    <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 700; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">
                        <i class="fas fa-save" style="margin-right: 10px; opacity: 0.7;"></i> Synchronize Schedule
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
    /* Toggle Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: var(--primary);
    }

    input:checked+.slider:before {
        transform: translateX(24px);
    }

    /* Disabled State */
    .disabled-row {
        opacity: 0.4;
        pointer-events: none;
        filter: grayscale(1);
    }

    .hover-row:hover {
        background-color: var(--primary-soft);
    }
</style>

<script>
    function toggleRow(checkbox) {
        const row = checkbox.closest('tr');
        const timeInputs = row.querySelector('.time-inputs');
        if (checkbox.checked) {
            timeInputs.classList.remove('disabled-row');
        } else {
            timeInputs.classList.add('disabled-row');
        }
    }
</script>

<?php include '../includes/footer.php'; ?>