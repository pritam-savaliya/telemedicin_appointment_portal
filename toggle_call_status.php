<?php
session_start();
include 'includes/db.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status']; // 1 for active, 0 for inactive
    $doctor_id = $_SESSION['user_id'];

    // Security check: Ensure appointment belongs to doctor
    $check_sql = "SELECT id FROM appointments WHERE id = $appointment_id AND doctor_id = $doctor_id";
    if ($conn->query($check_sql)->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Access']);
        exit();
    }

    $sql = "UPDATE appointments SET is_call_active = $status WHERE id = $appointment_id";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}
?>