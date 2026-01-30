<?php
include 'includes/db.php';

$sql = "ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'rejected', 'completed') DEFAULT 'pending'";

if ($conn->query($sql) === TRUE) {
    echo "Database schema updated successfully";
} else {
    echo "Error updating schema: " . $conn->error;
}
?>