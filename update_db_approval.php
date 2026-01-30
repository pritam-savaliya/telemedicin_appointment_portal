<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';

echo "Checking database...\n";
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_approved'");
if ($result && $result->num_rows > 0) {
    echo "Column 'is_approved' already exists.\n";
} else {
    echo "Adding 'is_approved' column...\n";
    $sql = "ALTER TABLE users ADD COLUMN is_approved TINYINT(1) DEFAULT 0"; // Default 0 (pending)
    if ($conn->query($sql) === TRUE) {
        echo "Column added successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
        exit;
    }
}

// Set all CURRENT users to 1 (approved) so we don't lock out current admin/users
echo "Approving existing users...\n";
$update = "UPDATE users SET is_approved = 1";
if ($conn->query($update) === TRUE) {
    echo "Existing users approved.\n";
} else {
    echo "Error updating users: " . $conn->error . "\n";
}

echo "Database update complete.\n";
?>