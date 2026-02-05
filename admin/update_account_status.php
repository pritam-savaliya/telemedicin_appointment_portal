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
            // Send email notification here if email was working
            header("Location: admin_doctors.php?msg=approved");
            exit();
        } else {
            header("Location: admin_doctors.php?msg=error");
            exit();
        }
    } elseif ($action == 'reject') {
        // Rejecting usually means deleting for now, or we could add a 'rejected' status.
        // For simplicity, let's delete the user as per current logic in admin_dashboard.

        // 1. Delete Chat Messages
        $conn->query("DELETE FROM chat_messages WHERE sender_id = $id");

        // 2. Delete Notifications
        $conn->query("DELETE FROM notifications WHERE user_id = $id");

        // 3. Delete Appointments (complex if cascading not set)
        $conn->query("DELETE FROM appointments WHERE doctor_id = $id");

        // 4. Delete User
        $sql = "DELETE FROM users WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            header("Location: admin_doctors.php?msg=rejected");
            exit();
        } else {
            header("Location: admin_doctors.php?msg=error");
            exit();
        }
    }
} else {
    header("Location: admin_doctors.php");
    exit();
}
