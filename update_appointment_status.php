<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    $appt_id = intval($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);
    $doctor_id = $_SESSION['user_id'];

    // Only allow specific statuses
    $allowed_statuses = ['confirmed', 'rejected', 'completed'];
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit();
    }

    // Verify appointment belongs to this doctor
    $check_sql = "SELECT id FROM appointments WHERE id = $appt_id AND doctor_id = $doctor_id";
    if ($conn->query($check_sql)->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Appointment not found or access denied']);
        exit();
    }

    $sql = "UPDATE appointments SET status = '$status' WHERE id = $appt_id";
    if ($conn->query($sql) === TRUE) {

        // If completing, maybe send a notification?
        if ($status == 'completed') {
            // Get patient ID
            $pat_sql = "SELECT patient_id FROM appointments WHERE id = $appt_id";
            $pat_res = $conn->query($pat_sql);
            if ($pat_res->num_rows > 0) {
                $patient_id = $pat_res->fetch_assoc()['patient_id'];
                $doc_name = $_SESSION['fullname'];
                $message = "Your treatment/appointment has been marked as completed by Dr. $doc_name.";
                $notif_sql = "INSERT INTO notifications (user_id, message) VALUES ($patient_id, '$message')";
                $conn->query($notif_sql);
            }
        }

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>