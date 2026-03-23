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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = 'http://localhost/telemedicine_appointment/';
define('BASE_URL', rtrim($baseUrl, '/') . '/');
?>