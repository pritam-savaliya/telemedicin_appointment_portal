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

<div class="container section">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="color: var(--secondary-color); margin-bottom: 5px;">Manage Schedule</h1>
                <p style="color: var(--text-muted);">Set your weekly availability specifically for appointments.</p>
            </div>
            <div
                style="background: var(--primary-soft); padding: 10px 20px; border-radius: var(--radius-md); color: var(--primary);">
                <i class="fas fa-calendar-alt"></i> Weekly Setup
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="toast-notification show" style="background-color: var(--success-color);">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="padding: 0; overflow: hidden; box-shadow: var(--shadow-md);">
            <form action="update_schedule.php" method="POST">

                <div class="table-container">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-body); border-bottom: 2px solid var(--border-color);">
                                <th style="padding: 1.5rem; text-align: left; width: 20%;">Day</th>
                                <th style="padding: 1.5rem; text-align: center; width: 15%;">Status</th>
                                <th style="padding: 1.5rem; text-align: left; width: 65%;">Time Slots (Start - End)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($days as $index => $day):
                                $data = isset($existing_schedule[$day]) ? $existing_schedule[$day] : null;
                                $is_active = $data && $data['is_available'];
                                $start = $data ? $data['start_time'] : '09:00';
                                $end = $data ? $data['end_time'] : '17:00';
                                ?>
                                <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.2s;"
                                    class="hover-row">
                                    <td style="padding: 1.5rem; font-weight: 600; color: var(--secondary-color);">
                                        <?php echo $day; ?>
                                    </td>
                                    <td style="padding: 1.5rem; text-align: center;">
                                        <label class="switch">
                                            <input type="checkbox" name="days[<?php echo $day; ?>][active]" value="1"
                                                onchange="toggleRow(this)" <?php echo $is_active ? 'checked' : ''; ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td style="padding: 1.5rem;">
                                        <div class="time-inputs <?php echo $is_active ? '' : 'disabled-row'; ?>"
                                            id="times-<?php echo $day; ?>"
                                            style="display: flex; align-items: center; gap: 15px;">
                                            <div class="input-with-icon" style="flex: 1; max-width: 200px;">
                                                <i class="fas fa-clock" style="color: var(--text-muted);"></i>
                                                <input type="time" name="days[<?php echo $day; ?>][start]"
                                                    value="<?php echo $start; ?>" class="form-control">
                                            </div>
                                            <span style="font-weight: 600; color: var(--text-muted);">&mdash;</span>
                                            <div class="input-with-icon" style="flex: 1; max-width: 200px;">
                                                <i class="fas fa-clock" style="color: var(--text-muted);"></i>
                                                <input type="time" name="days[<?php echo $day; ?>][end]"
                                                    value="<?php echo $end; ?>" class="form-control">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div
                    style="padding: 2rem; background: var(--bg-body); text-align: right; border-top: 1px solid var(--border-color);">
                    <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
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