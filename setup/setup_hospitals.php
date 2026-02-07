<?php
include '../includes/db.php';

// Create hospitals table
$sql = "CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    google_map_url TEXT
)";

if ($conn->query($sql) === TRUE) {
    echo "Table hospitals created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Check if data exists
$check = $conn->query("SELECT count(*) as count FROM hospitals");
$row = $check->fetch_assoc();

if ($row['count'] == 0) {
    // Insert 10 dummy hospitals
    $hospitals = [
        ["City Care Hospital", "123 Main St, Tech City", "Tech City", "555-0101", "General & Emergency", 12.9716, 77.5946, "https://maps.google.com/?q=12.9716,77.5946"],
        ["Sunrise Trauma Center", "456 Oak Ave, Metroville", "Metroville", "555-0102", "Trauma & Accident", 12.9352, 77.6245, "https://maps.google.com/?q=12.9352,77.6245"],
        ["Green Valley Heart Institute", "789 Pine Rd, Green Valley", "Green Valley", "555-0103", "Cardiology", 12.9279, 77.6271, "https://maps.google.com/?q=12.9279,77.6271"],
        ["Northside General", "321 Elm St, Northside", "Northside", "555-0104", "General Surgery", 13.0358, 77.5970, "https://maps.google.com/?q=13.0358,77.5970"],
        ["West End Children's Hospital", "654 Maple Dr, West End", "West End", "555-0105", "Pediatrics", 12.9904, 77.5533, "https://maps.google.com/?q=12.9904,77.5533"],
        ["Central Orthopedic", "987 Cedar Ln, Central City", "Central City", "555-0106", "Orthopedics", 12.9719, 77.6412, "https://maps.google.com/?q=12.9719,77.6412"],
        ["Lakeside Neuro", "159 Birch Blvd, Lakeside", "Lakeside", "555-0107", "Neurology", 12.9141, 77.6101, "https://maps.google.com/?q=12.9141,77.6101"],
        ["Riverfront Critical Care", "753 Willow Way, Riverfront", "Riverfront", "555-0108", "Critical Care", 12.9592, 77.5750, "https://maps.google.com/?q=12.9592,77.5750"],
        ["Hilltop Maternity", "147 Aspen Ct, Hilltop", "Hilltop", "555-0109", "Maternity & Gynecology", 13.0012, 77.5678, "https://maps.google.com/?q=13.0012,77.5678"],
        ["Downtown Eye Hospital", "258 Spruce St, Downtown", "Downtown", "555-0110", "Ophthalmology", 12.9654, 77.6013, "https://maps.google.com/?q=12.9654,77.6013"]
    ];

    $stmt = $conn->prepare("INSERT INTO hospitals (name, address, city, phone, specialty, latitude, longitude, google_map_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($hospitals as $hospital) {
        $stmt->bind_param("sssssdds", $hospital[0], $hospital[1], $hospital[2], $hospital[3], $hospital[4], $hospital[5], $hospital[6], $hospital[7]);
        $stmt->execute();
    }
    echo "Inserted 10 hospitals successfully.";
} else {
    echo "Hospitals already exist.";
}
?>