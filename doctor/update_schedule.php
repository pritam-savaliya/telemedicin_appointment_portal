<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] == 'doctor') {
    $doctor_id = $_SESSION['user_id'];
    $days = $_POST['days'];

    foreach ($days as $day => $data) {
        $day = $conn->real_escape_string($day);
        $is_active = isset($data['active']) ? 1 : 0;
        $start = $conn->real_escape_string($data['start']);
        $end = $conn->real_escape_string($data['end']);

        // Use Upsert (INSERT ... ON DUPLICATE KEY UPDATE) logic
        // But since we have a unique constraint on (doctor_id, day_of_week)

        // First check if exists
        $check = "SELECT id FROM doctor_schedules WHERE doctor_id = $doctor_id AND day_of_week = '$day'";
        $res = $conn->query($check);

        if ($res->num_rows > 0) {
            // Update
            $sql = "UPDATE doctor_schedules SET 
                    start_time = '$start', 
                    end_time = '$end', 
                    is_available = $is_active 
                    WHERE doctor_id = $doctor_id AND day_of_week = '$day'";
        } else {
            // Insert
            $sql = "INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, is_available) 
                    VALUES ($doctor_id, '$day', '$start', '$end', $is_active)";
        }

        $conn->query($sql);
    }

    $_SESSION['success'] = "Schedule updated successfully!";
    header("Location: " . BASE_URL . "doctor/manage_schedule.php");
    exit();

} else {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}
