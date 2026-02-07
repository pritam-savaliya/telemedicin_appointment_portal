<?php
include '../includes/db.php';

// Allow NULL doctor_id
$sql1 = "ALTER TABLE appointments MODIFY doctor_id INT NULL";
if ($conn->query($sql1) === TRUE) {
    echo "Modified doctor_id to allow NULL.<br>";
} else {
    echo "Error modifying doctor_id: " . $conn->error . "<br>";
}

// Add hospital_id
$sql2 = "ALTER TABLE appointments ADD COLUMN IF NOT EXISTS hospital_id INT NULL";
if ($conn->query($sql2) === TRUE) {
    echo "Added hospital_id column.<br>";
} else {
    echo "Error adding hospital_id: " . $conn->error . "<br>";
}

// Add is_emergency
$sql3 = "ALTER TABLE appointments ADD COLUMN IF NOT EXISTS is_emergency BOOLEAN DEFAULT FALSE";
if ($conn->query($sql3) === TRUE) {
    echo "Added is_emergency column.<br>";
} else {
    echo "Error adding is_emergency: " . $conn->error . "<br>";
}
?>