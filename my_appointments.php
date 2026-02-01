<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$sql = "SELECT appointments.*, users.fullname as doctor_name,
        (SELECT COUNT(*) FROM prescriptions WHERE appointment_id = appointments.id) as has_prescription,
        (SELECT COUNT(*) FROM reviews WHERE appointment_id = appointments.id) as has_review
        FROM appointments 
        JOIN users ON appointments.doctor_id = users.id 
        WHERE patient_id = $patient_id 
        ORDER BY appointments.date DESC";
$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>

<div class="container section">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin-bottom: 10px;">My Appointments</h2>
            <p style="color: var(--text-muted);">Track your consultation history</p>
        </div>
        <a href="book_appointment.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Booking</a>
    </div>

    <!-- Appointment List -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight: 500;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div
                                            style="background: #e9ecef; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                                            <i class="fas fa-user-md" style="font-size: 0.8rem;"></i>
                                        </div>
                                        Dr. <?php echo $row['doctor_name']; ?>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                <td><?php echo date("h:i A", strtotime($row['time'])); ?></td>
                                <td>
                                    <?php
                                    $status = $row['status'];
                                    $badge_class = 'badge-pending';
                                    if ($status == 'confirmed')
                                        $badge_class = 'badge-success';
                                    elseif ($status == 'rejected')
                                        $badge_class = 'badge-danger';
                                    elseif ($status == 'completed')
                                        $badge_class = 'badge-success'; // Reuse success for completed, or add specific style
                            
                                    // Custom style for completed if needed, or just use badge-success
                                    if ($status == 'completed') {
                                        echo "<span class='badge' style='background: #e2e8f0; color: #475569;'>Completed</span>";
                                    } else {
                                        echo "<span class='badge $badge_class'>" . ucfirst($status) . "</span>";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($status == 'confirmed'): ?>
                                        <a href="chat.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-secondary"
                                            style="padding: 6px 15px; font-size: 0.85rem;">
                                            <i class="fas fa-comments"></i> Chat
                                        </a>
                                    <?php elseif ($status == 'completed'): ?>
                                        <?php if ($row['has_prescription'] > 0): ?>
                                            <a href="view_prescription.php?appointment_id=<?php echo $row['id']; ?>"
                                                class="btn btn-outline" style="padding: 4px 10px; font-size: 0.8rem; margin-left: 5px;">
                                                <i class="fas fa-file-prescription"></i> View Rx
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($row['has_review'] > 0): ?>
                                            <span class="btn btn-secondary"
                                                style="padding: 4px 10px; font-size: 0.8rem; margin-left: 5px; opacity: 0.7; pointer-events: none;">
                                                <i class="fas fa-check"></i> Rated
                                            </span>
                                        <?php else: ?>
                                            <a href="rate_doctor.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-primary"
                                                style="padding: 4px 10px; font-size: 0.8rem; margin-left: 5px;">
                                                <i class="fas fa-star"></i> Rate
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.85rem; margin-right: 10px;">Pending
                                            approval</span>
                                        <a href="cancel_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-outline"
                                            onclick="return confirm('Are you sure you want to cancel this appointment?');"
                                            style="padding: 4px 10px; font-size: 0.8rem; color: #ff7675; border-color: #ff7675;">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                No appointments found. <a href="book_appointment.php"
                                    style="color: var(--primary-color); font-weight: 600;">Book Now</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>