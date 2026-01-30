<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$sql = "SELECT appointments.*, users.fullname as doctor_name 
        FROM appointments 
        JOIN users ON appointments.doctor_id = users.id 
        WHERE patient_id = $patient_id 
        ORDER BY appointments.date DESC";
$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="padding: 4rem 5%; max-width: 1200px; margin: 0 auto; min-height: 60vh;">
    <h2 style="margin-bottom: 2rem;">My Appointments</h2>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
            <thead>
                <tr style="background: var(--primary-color); color: white; text-align: left;">
                    <th style="padding: 12px 15px;">Doctor Name</th>
                    <th style="padding: 12px 15px;">Date</th>
                    <th style="padding: 12px 15px;">Time</th>
                    <th style="padding: 12px 15px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px 15px;">Dr.
                                <?php echo $row['doctor_name']; ?>
                            </td>
                            <td style="padding: 12px 15px;">
                                <?php echo $row['date']; ?>
                            </td>
                            <td style="padding: 12px 15px;">
                                <?php echo date("h:i A", strtotime($row['time'])); ?>
                            </td>
                            <td style="padding: 12px 15px;">
                                <?php
                                $status = $row['status'];
                                $color = 'orange';
                                if ($status == 'confirmed')
                                    $color = 'green';
                                if ($status == 'rejected')
                                    $color = 'red';
                                ?>
                                <?php echo $status; ?>
                                </span>
                                <?php if ($status == 'confirmed'): ?>
                                    <a href="chat.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-primary"
                                        style="margin-left: 10px; padding: 5px 10px; font-size: 0.8rem; background-color: var(--secondary-color);">Chat</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">No appointments found. <a
                                href="book_appointment.php" style="color: var(--primary-color);">Book Now</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>