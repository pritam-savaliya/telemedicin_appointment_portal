<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'fetch' && isset($_GET['appointment_id'])) {
        $appointment_id = $_GET['appointment_id'];

        // Verify user is part of this appointment
        $check_sql = "SELECT * FROM appointments WHERE id = $appointment_id AND (patient_id = $user_id OR doctor_id = $user_id) AND status = 'confirmed'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied or appointment not confirmed']);
            exit();
        }

        // Mark messages as read where sender is NOT the current user
        $update_read_sql = "UPDATE chat_messages SET is_read = 1 WHERE appointment_id = $appointment_id AND sender_id != $user_id AND is_read = 0";
        $conn->query($update_read_sql);

        $sql = "SELECT chat_messages.*, users.fullname as sender_name 
                FROM chat_messages 
                JOIN users ON chat_messages.sender_id = users.id 
                WHERE appointment_id = $appointment_id 
                ORDER BY created_at ASC";

        $result = $conn->query($sql);
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            // Format time for frontend
            $row['formatted_time'] = date("h:i A", strtotime($row['created_at']));
            $messages[] = $row;
        }

        echo json_encode(['status' => 'success', 'messages' => $messages]);
        exit();

    } elseif ($_GET['action'] == 'send' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $appointment_id = $_POST['appointment_id'];
        $message = $conn->real_escape_string($_POST['message']);

        if (empty($message)) {
            echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
            exit();
        }

        // Verify user is part of this appointment
        $check_sql = "SELECT * FROM appointments WHERE id = $appointment_id AND (patient_id = $user_id OR doctor_id = $user_id) AND status = 'confirmed'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied or appointment not confirmed']);
            exit();
        }

        $sql = "INSERT INTO chat_messages (appointment_id, sender_id, message) VALUES ($appointment_id, $user_id, '$message')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    } elseif ($_GET['action'] == 'get_unread_count') {
        $count_sql = "SELECT COUNT(*) as count 
            FROM chat_messages 
            JOIN appointments ON chat_messages.appointment_id = appointments.id 
            WHERE chat_messages.is_read = 0 
            AND chat_messages.sender_id != $user_id 
            AND (appointments.patient_id = $user_id OR appointments.doctor_id = $user_id)";
        $result = $conn->query($count_sql);
        $count = 0;
        if ($result) {
            $count = $result->fetch_assoc()['count'];
        }
        echo json_encode(['status' => 'success', 'count' => $count]);
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>