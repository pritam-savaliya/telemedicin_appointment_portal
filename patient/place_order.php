<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $address = $conn->real_escape_string($_POST['delivery_address']);
    $details = isset($_POST['order_details']) ? $conn->real_escape_string($_POST['order_details']) : '';
    $file_path = NULL;

    // Validation
    if (empty($address)) {
        $_SESSION['error'] = "Delivery address is required.";
        header("Location: " . BASE_URL . "patient/order_medicine.php");
        exit();
    }

    if (empty($details) && (empty($_FILES['prescription']['name']))) {
        $_SESSION['error'] = "Please provide either a prescription file or manual order details.";
        header("Location: " . BASE_URL . "patient/order_medicine.php");
        exit();
    }

    // Handle File Upload
    if (isset($_FILES['prescription']) && $_FILES['prescription']['error'] == 0) {
        $upload_dir = '../assets/uploads/prescriptions/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($_FILES['prescription']['name']);
        $target_file = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allowed file types
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array($file_type, $allowed)) {
            if (move_uploaded_file($_FILES['prescription']['tmp_name'], $target_file)) {
                $file_path = $target_file;
            } else {
                $_SESSION['error'] = "Failed to upload prescription file.";
                header("Location: " . BASE_URL . "patient/order_medicine.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and PDF allowed.";
            header("Location: " . BASE_URL . "patient/order_medicine.php");
            exit();
        }
    }

    // Insert into Database
    $sql = "INSERT INTO medicine_orders (patient_id, delivery_address, prescription_path, order_details, status) 
            VALUES ($user_id, '$address', " . ($file_path ? "'$file_path'" : "NULL") . ", '$details', 'pending')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Order placed successfully! We will contact you shortly.";
    } else {
        $_SESSION['error'] = "Database Error: " . $conn->error;
    }

    header("Location: " . BASE_URL . "patient/order_medicine.php");
    exit();

} else {
    header("Location: " . BASE_URL . "patient/order_medicine.php");
    exit();
}
