<?php
include_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
        exit();
    }

    // Ensure the table exists
    $createTable = "CREATE TABLE IF NOT EXISTS subscribers (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($createTable)) {
        echo json_encode(['status' => 'error', 'message' => 'Database initialization error.']);
        exit();
    }

    $email = $conn->real_escape_string($email);
    
    // Check if already subscribed
    $check = $conn->query("SELECT id FROM subscribers WHERE email = '$email'");
    if ($check && $check->num_rows > 0) {
        echo json_encode(['status' => 'info', 'message' => 'You are already on our mailing list!']);
        exit();
    }

    $sql = "INSERT INTO subscribers (email) VALUES ('$email')";
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Subscription successful! Welcome to MedConnect.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Execution error: ' . $conn->error]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Direct access forbidden.']);
}
?>
