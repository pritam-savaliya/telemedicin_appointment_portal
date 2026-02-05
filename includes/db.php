<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "telemedicine_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define Base URL
define('BASE_URL', 'http://localhost/telemedicine_appointment/');
?>