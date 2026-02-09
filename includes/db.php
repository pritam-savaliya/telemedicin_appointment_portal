<?php
// Use environment variables for production (e.g., Vercel), fallback to local defaults for XAMPP
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: "";
$dbname = getenv('DB_NAME') ?: "telemedicine_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define Base URL: Allow overriding via environment variable
$baseUrl = getenv('BASE_URL') ?: 'http://localhost/telemedicine_appointment/';
define('BASE_URL', rtrim($baseUrl, '/') . '/');
?>