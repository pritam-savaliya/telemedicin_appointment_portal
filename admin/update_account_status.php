<?php
include '../includes/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    // Prevent deleting self (just in case)
    if ($id == $_SESSION['user_id']) {
        header("Location: admin_doctors.php?msg=error");
        exit();
    }

    if ($action == 'approve') {
        $sql = "UPDATE users SET is_approved = 1 WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            header("Location: admin_doctors.php?msg=approved");
            exit();
        } else {
            header("Location: admin_doctors.php?msg=error");
            exit();
        }
    } elseif ($action == 'reject') {
        // First, fetch user role to determine redirection and role-specific cleanup
        $user_res = $conn->query("SELECT role FROM users WHERE id = $id");
        $user_data = $user_res->fetch_assoc();
        $role = $user_data ? $user_data['role'] : 'doctor';
        $redirect_page = ($role === 'patient') ? 'admin_patients.php' : 'admin_doctors.php';

        // Rejecting means thorough purging of all user-associated metadata
        // Note: CASCADE handles most of this, but we explicitly purge sensitive areas
        
        // 1. Delete Multi-directional Chat Logs
        $conn->query("DELETE FROM chat_messages WHERE sender_id = $id OR receiver_id = $id");

        // 2. Delete Notifications
        $conn->query("DELETE FROM notifications WHERE user_id = $id");

        // 3. Delete Appointments & Related Logs
        $conn->query("DELETE FROM video_call_logs WHERE appointment_id IN (SELECT id FROM appointments WHERE doctor_id = $id OR patient_id = $id)");
        $conn->query("DELETE FROM appointments WHERE doctor_id = $id OR patient_id = $id");
        
        // 4. Role Specific Cleanup
        if ($role === 'doctor') {
            $conn->query("DELETE FROM doctor_profiles WHERE user_id = $id");
            $conn->query("DELETE FROM doctor_schedules WHERE doctor_id = $id");
            $conn->query("DELETE FROM prescriptions WHERE doctor_id = $id");
            $conn->query("DELETE FROM reviews WHERE doctor_id = $id");
        } else {
            $conn->query("DELETE FROM medical_records WHERE patient_id = $id");
            $conn->query("DELETE FROM prescriptions WHERE patient_id = $id");
            $conn->query("DELETE FROM reviews WHERE patient_id = $id");
            $conn->query("DELETE FROM payments WHERE user_id = $id");
        }

        // 5. Delete User Identity
        $sql = "DELETE FROM users WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            header("Location: $redirect_page?msg=rejected");
            exit();
        } else {
            header("Location: $redirect_page?msg=error");
            exit();
        }
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
