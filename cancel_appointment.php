<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $appt_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Check if appointment exists, belongs to user, and is pending
    $check_sql = "SELECT id FROM appointments WHERE id = $appt_id AND patient_id = $user_id AND status = 'pending'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $update_sql = "UPDATE appointments SET status = 'cancelled' WHERE id = $appt_id";
        if ($conn->query($update_sql) === TRUE) {
            header("Location: my_appointments.php?msg=cancelled");
        } else {
            header("Location: my_appointments.php?msg=error");
        }
    } else {
        header("Location: my_appointments.php?msg=invalid");
    }
} else {
    header("Location: my_appointments.php");
}
exit();
?>